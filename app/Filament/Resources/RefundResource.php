<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefundResource\Pages;
use App\Filament\Resources\RefundResource\RelationManagers;
use App\Models\Refund;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RefundResource extends Resource
{
    protected static ?string $model = Refund::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?string $navigationGroup = 'Sales & Products';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('sale_id')
                    ->relationship('sale', 'id')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('reason')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\TextInput::make('paddle_refund_id'),
                Forms\Components\TextInput::make('requested_by')
                    ->required()
                    ->numeric(),
                Forms\Components\DateTimePicker::make('requested_at')
                    ->required(),
                Forms\Components\DateTimePicker::make('processed_at'),
                Forms\Components\TextInput::make('processed_by')
                    ->numeric(),
                Forms\Components\Select::make('requested_by_id')
                    ->relationship('requestedBy', 'name')
                    ->required(),
                Forms\Components\Select::make('processed_by_id')
                    ->relationship('processedBy', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('paddle_refund_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('requested_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requested_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('processed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('processed_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requestedBy.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('processedBy.name')
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
            'index' => Pages\ListRefunds::route('/'),
            'create' => Pages\CreateRefund::route('/create'),
            'edit' => Pages\EditRefund::route('/{record}/edit'),
        ];
    }
}
