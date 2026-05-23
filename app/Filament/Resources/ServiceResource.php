<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Section::make('Basic Info')->schema([
                TextInput::make('number')->required()->maxLength(10)->label('Service Number')->placeholder('01'),
                TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                TextInput::make('tagline')->maxLength(255)->columnSpanFull(),
                FileUpload::make('card_image')
                    ->label('Card Image')
                    ->helperText('Displayed when this service appears as a card.')
                    ->image()
                    ->directory('services/cards')
                    ->columnSpanFull(),
                FileUpload::make('hero_image')
                    ->label('Hero Banner Image')
                    ->helperText('Displayed at the top of the service page alongside the title.')
                    ->image()
                    ->directory('services/heroes')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('order')->numeric()->default(0),
                Toggle::make('is_active')->default(true)->inline(false),
            ])->columns(2),

            Section::make('Overview')->schema([
                TextInput::make('overview_title')->maxLength(255)->columnSpanFull(),
                Textarea::make('overview_description')->rows(4)->columnSpanFull(),
                FileUpload::make('overview_image')->image()->directory('services')->columnSpanFull(),
            ]),

            Section::make('Process')->schema([
                TextInput::make('process_title')->maxLength(255)->columnSpanFull(),
                Textarea::make('process_description')->rows(3)->columnSpanFull(),
                Repeater::make('steps')
                    ->schema([
                        FileUpload::make('icon')->image()->directory('services/icons')->label('Icon'),
                        TextInput::make('title')->required()->maxLength(255),
                        Textarea::make('description')->rows(3)->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->addActionLabel('Add Step')
                    ->defaultItems(0),
            ]),

            Section::make('FAQs')->schema([
                Repeater::make('faqs')
                    ->schema([
                        TextInput::make('question')->required()->columnSpanFull(),
                        Textarea::make('answer')->rows(3)->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->addActionLabel('Add FAQ')
                    ->defaultItems(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')->sortable()->label('#'),
                TextColumn::make('number')->badge()->label('No.'),
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('tagline')->limit(40)->color('gray'),
                TextColumn::make('steps')->label('Steps')->formatStateUsing(fn ($record) => count($record->steps ?? []))->badge(),
                TextColumn::make('faqs')->label('FAQs')->formatStateUsing(fn ($record) => count($record->faqs ?? []))->badge(),
                IconColumn::make('is_active')->boolean()->sortable(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index'  => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit'   => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
