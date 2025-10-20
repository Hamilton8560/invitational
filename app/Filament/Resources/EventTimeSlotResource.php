<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventTimeSlotResource\Pages;
use App\Models\EventTimeSlot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EventTimeSlotResource extends Resource
{
    protected static ?string $model = EventTimeSlot::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Event Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Time Slots';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_id')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.name')
                    ->searchable()
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('event_id')
                    ->relationship('event', 'name')
                    ->label('Event')
                    ->searchable()
                    ->preload(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventTimeSlots::route('/'),
            'create' => Pages\CreateEventTimeSlot::route('/create'),
            'edit' => Pages\EditEventTimeSlot::route('/{record}/edit'),
        ];
    }
}
