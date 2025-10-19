<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebsiteAdResource\Pages;
use App\Models\WebsiteAd;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WebsiteAdResource extends Resource
{
    protected static ?string $model = WebsiteAd::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'Marketing & Advertising';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_id')
                    ->relationship('event', 'name')
                    ->required(),
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                Forms\Components\Select::make('buyer_id')
                    ->relationship('buyer', 'name')
                    ->required(),
                Forms\Components\TextInput::make('ad_placement')
                    ->required(),
                Forms\Components\TextInput::make('company_name')
                    ->required(),
                Forms\Components\Textarea::make('ad_image_url')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('ad_link_url')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('contact_email')
                    ->email()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('buyer.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ad_placement')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebsiteAds::route('/'),
            'create' => Pages\CreateWebsiteAd::route('/create'),
            'edit' => Pages\EditWebsiteAd::route('/{record}/edit'),
        ];
    }
}
