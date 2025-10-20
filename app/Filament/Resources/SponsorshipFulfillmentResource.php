<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SponsorshipFulfillmentResource\Pages;
use App\Models\SponsorshipFulfillment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SponsorshipFulfillmentResource extends Resource
{
    protected static ?string $model = SponsorshipFulfillment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('sponsorship_id')
                    ->relationship('sponsorship', 'id')
                    ->required(),
                Forms\Components\TextInput::make('sponsor_package_benefit_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('pending'),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('proof_files'),
                Forms\Components\DateTimePicker::make('completed_at'),
                Forms\Components\TextInput::make('completed_by')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sponsorship.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sponsor_package_benefit_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_by')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListSponsorshipFulfillments::route('/'),
            'create' => Pages\CreateSponsorshipFulfillment::route('/create'),
            'edit' => Pages\EditSponsorshipFulfillment::route('/{record}/edit'),
        ];
    }
}
