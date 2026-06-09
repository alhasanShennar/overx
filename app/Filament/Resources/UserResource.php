<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RequiresAdminPermission;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Support\AdminPermission;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    use RequiresAdminPermission;

    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 1;

    protected static function adminPermission(): ?string
    {
        return AdminPermission::VIEW_USERS;
    }

    public static function form(Form $form): Form
    {
        $groupedPermissionOptions = collect(AdminPermission::groups())
            ->flatMap(fn (array $permissions, string $group) => collect($permissions)
                ->mapWithKeys(fn (string $label, string $key) => [$key => "{$group} › {$label}"]))
            ->all();

        return $form->schema([
            Forms\Components\Section::make('Account')
                ->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->email()->required()->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $context) => $context === 'create'),
                    Forms\Components\Toggle::make('is_admin')
                        ->label('Admin panel access')
                        ->helperText('Required to sign in to the admin panel.')
                        ->dehydrated(false),
                ])
                ->columns(2),

            Forms\Components\Section::make('Panel Permissions')
                ->description('Control which sections appear in the side navigation. Dashboard is always available to admins.')
                ->schema([
                    Forms\Components\CheckboxList::make('permission_names')
                        ->label('Permissions')
                        ->options($groupedPermissionOptions)
                        ->columns(2)
                        ->bulkToggleable()
                        ->searchable()
                        ->dehydrated(true),
                ]),

            Forms\Components\Section::make('Cashout Approval Role')
                ->description('Each user may hold only one approval level. This controls which Approve action they can take on pending cashouts.')
                ->schema([
                    Forms\Components\Select::make('cashout_approval_level')
                        ->label('Approval Level')
                        ->options([
                            1 => 'Approve 1 — first sign-off',
                            2 => 'Approve 2 — second sign-off',
                            3 => 'Approve 3 — final sign-off and processing',
                        ])
                        ->placeholder('None — cannot approve cashouts')
                        ->native(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('cashout_approval_level')
                    ->label('Cashout Role')
                    ->formatStateUsing(fn ($state) => match ((int) $state) {
                        1 => 'Approve 1',
                        2 => 'Approve 2',
                        3 => 'Approve 3',
                        default => '—',
                    })
                    ->badge()
                    ->color(fn ($state) => match ((int) $state) {
                        1 => 'info',
                        2 => 'warning',
                        3 => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->staff();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
