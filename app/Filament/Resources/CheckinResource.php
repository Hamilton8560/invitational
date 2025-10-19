<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckinResource\Pages;
use App\Models\Checkin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CheckinResource extends Resource
{
    protected static ?string $model = Checkin::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationLabel = 'Check-Ins';

    protected static ?string $modelLabel = 'Check-In';

    protected static ?string $pluralModelLabel = 'Check-Ins';

    protected static ?string $navigationGroup = 'Events';

    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_id')
                    ->relationship('event', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('sale_id')
                    ->relationship('sale', 'id')
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('checked_in_by')
                    ->relationship('checkedInBy', 'name')
                    ->required()
                    ->default(auth()->id())
                    ->searchable()
                    ->preload(),

                Forms\Components\DateTimePicker::make('checked_in_at')
                    ->required()
                    ->default(now()),

                Forms\Components\Select::make('check_in_type')
                    ->options([
                        'team' => 'Team',
                        'individual' => 'Individual Player',
                        'vendor' => 'Vendor',
                        'spectator' => 'Spectator',
                    ])
                    ->required(),

                Forms\Components\Select::make('team_id')
                    ->relationship('team', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('individual_player_id')
                    ->relationship('individualPlayer', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('booth_id')
                    ->relationship('booth', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('banner_id')
                    ->relationship('banner', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('event.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Attendee')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('check_in_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'team' => 'warning',
                        'individual' => 'info',
                        'vendor' => 'purple',
                        'spectator' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('team.name')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('individualPlayer.name')
                    ->label('Player')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('booth.name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('banner.name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('checkedInBy.name')
                    ->label('Checked In By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('checked_in_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('checked_in_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('check_in_type')
                    ->options([
                        'team' => 'Team',
                        'individual' => 'Individual Player',
                        'vendor' => 'Vendor',
                        'spectator' => 'Spectator',
                    ]),

                Tables\Filters\Filter::make('checked_in_today')
                    ->label('Checked in today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('checked_in_at', today())),
            ])
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCheckins::route('/'),
            'create' => Pages\CreateCheckin::route('/create'),
            'edit' => Pages\EditCheckin::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_checkin') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_checkin') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_checkin') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_checkin') ?? false;
    }
}
