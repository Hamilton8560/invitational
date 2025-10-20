<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EventTimeSlotsRelationManager extends RelationManager
{
    protected static string $relationship = 'eventTimeSlots';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('start_time')
                    ->seconds(false)
                    ->required(),
                Forms\Components\DateTimePicker::make('end_time')
                    ->seconds(false)
                    ->after('start_time')
                    ->required(),
                Forms\Components\TextInput::make('available_space_sqft')
                    ->label('Available Space (sq ft)')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('sq ft')
                    ->helperText('Total square footage available during this time slot'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('start_time')
            ->columns([
                Tables\Columns\TextColumn::make('start_time')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('available_space_sqft')
                    ->label('Space (sq ft)')
                    ->numeric()
                    ->suffix(' sq ft')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_time', 'asc');
    }
}
