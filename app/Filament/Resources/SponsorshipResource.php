<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SponsorshipResource\Pages;
use App\Models\Sponsorship;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SponsorshipResource extends Resource
{
    protected static ?string $model = Sponsorship::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Sponsorships';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Sponsorship Details')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->relationship('event', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),
                        Forms\Components\Select::make('sponsor_package_id')
                            ->relationship('sponsorPackage', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Select the sponsorship package tier'),
                        Forms\Components\Select::make('sports')
                            ->relationship('sports', 'name')
                            ->multiple()
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Select which sports this sponsorship covers. Select all for event-wide coverage.')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Company Information')
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        Forms\Components\FileUpload::make('company_logo_url')
                            ->label('Company Logo')
                            ->image()
                            ->directory('sponsorships/logos')
                            ->visibility('public')
                            ->helperText('Upload company logo for display'),
                        Forms\Components\TextInput::make('website_url')
                            ->url()
                            ->label('Website URL')
                            ->placeholder('https://example.com')
                            ->helperText('Where should the logo link to?'),
                    ])->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\Select::make('buyer_id')
                            ->relationship('buyer', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Account Owner'),
                        Forms\Components\TextInput::make('contact_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_phone')
                            ->tel()
                            ->maxLength(20),
                    ])->columns(2),

                Forms\Components\Section::make('Status & Administration')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                'pending' => 'Pending Review',
                                'active' => 'Active',
                                'expired' => 'Expired',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending'),
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('Approved Date'),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expiration Date')
                            ->helperText('When does this sponsorship end?'),
                        Forms\Components\Textarea::make('admin_notes')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Internal notes about this sponsorship...'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('sponsorPackage.name')
                    ->label('Package')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('sport_names')
                    ->label('Sports')
                    ->badge()
                    ->separator(',')
                    ->getStateUsing(fn (Sponsorship $record) => $record->sports->pluck('name')->toArray()),
                Tables\Columns\TextColumn::make('event.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'expired' => 'danger',
                        'cancelled' => 'gray',
                        default => 'info',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('contact_email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('sponsor_package')
                    ->relationship('sponsorPackage', 'name')
                    ->label('Package')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('sports')
                    ->relationship('sports', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListSponsorships::route('/'),
            'create' => Pages\CreateSponsorship::route('/create'),
            'edit' => Pages\EditSponsorship::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();

        return $count > 0 ? 'warning' : null;
    }
}
