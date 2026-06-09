<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RequiresAdminPermission;
use App\Filament\Resources\ContactMessageResource\Pages;
use App\Support\AdminPermission;
use App\Models\ContactMessage;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    use RequiresAdminPermission;

    protected static ?string $model = ContactMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'Contact Messages';
    protected static ?int $navigationSort = 20;

    protected static function adminPermission(): ?string
    {
        return AdminPermission::VIEW_CONTACT_MESSAGES;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('is_read', false)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->disabled(),
            TextInput::make('email')->disabled(),
            TextInput::make('phone')->disabled(),
            TextInput::make('subject')->disabled(),
            Textarea::make('message')->disabled()->rows(6)->columnSpanFull(),
            Toggle::make('is_read')->label('Mark as read')->inline(false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone'),
                TextColumn::make('subject')->badge()->color('info'),
                TextColumn::make('message')->limit(50)->color('gray'),
                IconColumn::make('is_read')->boolean()->label('Read')->sortable(),
                TextColumn::make('created_at')->dateTime('d M Y, H:i')->sortable()->label('Received'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_read')->label('Read status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('mark_read')
                    ->label('Mark read')
                    ->icon('heroicon-o-check')
                    ->action(fn (ContactMessage $record) => $record->update(['is_read' => true]))
                    ->visible(fn (ContactMessage $record) => ! $record->is_read),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactMessages::route('/'),
            'view'  => Pages\ViewContactMessage::route('/{record}'),
        ];
    }
}
