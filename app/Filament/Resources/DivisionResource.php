<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DivisionResource\Pages;
use App\Models\Division;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DivisionResource extends Resource
{
    protected static ?string $model = Division::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Event Management';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_sport_id')
                    ->label('Event Sport')
                    ->relationship('eventSport')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->sport?->name ?? 'N/A')
                    ->required(),
                Forms\Components\Select::make('event_time_slot_id')
                    ->relationship('eventTimeSlot', 'start_time')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->start_time?->format('M j, Y g:i A').' - '.$record->end_time?->format('g:i A'))
                    ->searchable()
                    ->preload()
                    ->label('Time Slot')
                    ->helperText('Assign this division to a specific time slot'),
                Forms\Components\Select::make('age_group_id')
                    ->relationship('ageGroup', 'name'),
                Forms\Components\Select::make('skill_level_id')
                    ->relationship('skillLevel', 'name'),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('max_teams')
                    ->numeric(),
                Forms\Components\TextInput::make('max_players')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('eventSport.sport.name')
                    ->label('Sport')
                    ->sortable(),
                Tables\Columns\TextColumn::make('eventTimeSlot.start_time')
                    ->label('Time Slot')
                    ->dateTime('M j g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ageGroup.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('skillLevel.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('max_teams')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_players')
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
                Tables\Filters\SelectFilter::make('event_time_slot_id')
                    ->relationship('eventTimeSlot', 'start_time')
                    ->label('Time Slot')
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListDivisions::route('/'),
            'create' => Pages\CreateDivision::route('/create'),
            'edit' => Pages\EditDivision::route('/{record}/edit'),
        ];
    }
}
