<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RequiresAdminPermission;
use App\Filament\Resources\CashoutResource\Pages;
use App\Models\Cashout;
use App\Models\Client;
use App\Services\CashoutApprovalService;
use App\Support\AdminPermission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CashoutResource extends Resource
{
    use RequiresAdminPermission;

    protected static ?string $model = Cashout::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Mining';
    protected static ?int $navigationSort = 4;

    protected static function adminPermission(): ?string
    {
        return AdminPermission::VIEW_CASHOUTS;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('amount')->numeric()->prefix('$')->disabled(),
            Forms\Components\TextInput::make('btc_amount')->numeric()->disabled(),
            Forms\Components\DatePicker::make('date'),
            Forms\Components\Select::make('status')
                ->options(['pending' => 'Pending', 'completed' => 'Completed', 'cancelled' => 'Cancelled']),
            Forms\Components\FileUpload::make('receipt')->directory('cashout-receipts')->nullable(),
            Forms\Components\Textarea::make('notes')->nullable()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        $approvalService = app(CashoutApprovalService::class);

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'approver1',
                'approver2',
                'approver3',
                'client.user',
                'cashoutDetail',
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('client.user.name')
                    ->label('Client')->searchable(),
                Tables\Columns\TextColumn::make('amount')->money('USD'),
                Tables\Columns\TextColumn::make('btc_amount')->numeric(8),
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('approve_1')
                    ->label('Approve 1')
                    ->state(fn (Cashout $record) => $record->approved_1_at
                        ? $record->approver1?->name . ' · ' . $record->approved_1_at->format('M j, H:i')
                        : '—')
                    ->icon(fn (Cashout $record) => $record->approved_1_at ? 'heroicon-o-check-circle' : 'heroicon-o-clock')
                    ->iconColor(fn (Cashout $record) => $record->approved_1_at ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('approve_2')
                    ->label('Approve 2')
                    ->state(fn (Cashout $record) => $record->approved_2_at
                        ? $record->approver2?->name . ' · ' . $record->approved_2_at->format('M j, H:i')
                        : '—')
                    ->icon(fn (Cashout $record) => $record->approved_2_at ? 'heroicon-o-check-circle' : 'heroicon-o-clock')
                    ->iconColor(fn (Cashout $record) => $record->approved_2_at ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('approve_3')
                    ->label('Approve 3')
                    ->state(fn (Cashout $record) => $record->approved_3_at
                        ? $record->approver3?->name . ' · ' . $record->approved_3_at->format('M j, H:i')
                        : '—')
                    ->icon(fn (Cashout $record) => $record->approved_3_at ? 'heroicon-o-check-circle' : 'heroicon-o-clock')
                    ->iconColor(fn (Cashout $record) => $record->approved_3_at ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('cashoutDetail.label')->label('Method'),
                Tables\Columns\IconColumn::make('receipt')
                    ->boolean()->label('Receipt')->getStateUsing(fn ($record) => (bool) $record->receipt),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'completed' => 'Completed', 'cancelled' => 'Cancelled']),
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Client')
                    ->options(fn () => Client::with('user')->get()->pluck('user.name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label(fn (Cashout $record) => 'Approve ' . $approvalService->getNextApprovalLevel($record))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(function (Cashout $record) use ($approvalService) {
                        $user = auth()->user();

                        return $record->status === 'pending'
                            && $approvalService->canUserApprove($record, $user);
                    })
                    ->form(function (Cashout $record) use ($approvalService) {
                        $level = $approvalService->getNextApprovalLevel($record);

                        if ($level !== 3) {
                            return [];
                        }

                        return [
                            Forms\Components\Select::make('cashout_details_id')
                                ->label('Cashout Method')
                                ->options(function (Cashout $record) {
                                    return $record->client->cashoutDetails()
                                        ->get()
                                        ->mapWithKeys(fn ($d) => [$d->id => ($d->label ?: $d->type)]);
                                })
                                ->nullable(),
                            Forms\Components\DatePicker::make('date')->default(today())->required(),
                            Forms\Components\FileUpload::make('receipt')->directory('cashout-receipts')->nullable(),
                            Forms\Components\Textarea::make('notes')->nullable(),
                        ];
                    })
                    ->requiresConfirmation(fn (Cashout $record) => $approvalService->getNextApprovalLevel($record) !== 3)
                    ->modalHeading(fn (Cashout $record) => 'Approve ' . $approvalService->getNextApprovalLevel($record))
                    ->action(function (Cashout $record, array $data) use ($approvalService) {
                        $level = $approvalService->getNextApprovalLevel($record);

                        try {
                            $approvalService->approve($record, auth()->user(), $data);
                        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                            Notification::make()->title($e->getMessage())->danger()->send();

                            return;
                        }

                        $message = $level === 3
                            ? 'Cashout fully approved and processed.'
                            : "Approve {$level} recorded. Awaiting the next approval.";

                        Notification::make()->title($message)->success()->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Cashout $record) => $record->status === 'pending' && auth()->user()?->isSuperAdmin())
                    ->form([
                        Forms\Components\Textarea::make('notes')->label('Rejection Reason')->nullable(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (Cashout $record, array $data) use ($approvalService) {
                        $approvalService->reject($record, $data['notes'] ?? '');
                        Notification::make()->title('Cashout rejected.')->warning()->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->visible(fn (Cashout $record) => $record->status === 'completed'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashouts::route('/'),
            'edit' => Pages\EditCashout::route('/{record}/edit'),
        ];
    }
}
