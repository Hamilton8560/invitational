<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SponsorPackageResource\Pages;
use App\Models\SponsorPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SponsorPackageResource extends Resource
{
    protected static ?string $model = SponsorPackage::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Sponsorships';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Package Details')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->relationship('event', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Event (leave empty for template)')
                            ->helperText('Templates can be reused across multiple events'),
                        Forms\Components\Select::make('tier')
                            ->required()
                            ->options([
                                'gold' => 'Gold',
                                'silver' => 'Silver',
                                'bronze' => 'Bronze',
                                'custom' => 'Custom',
                            ])
                            ->default('custom'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Gold Sponsor Package'),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Describe what this package includes...'),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing & Availability')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0),
                        Forms\Components\TextInput::make('max_quantity')
                            ->numeric()
                            ->minValue(1)
                            ->label('Maximum Available')
                            ->helperText('Leave empty for unlimited'),
                        Forms\Components\TextInput::make('current_quantity')
                            ->numeric()
                            ->default(0)
                            ->label('Currently Sold')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('display_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),
                    ])->columns(4),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Active')
                            ->helperText('Only active packages are visible to customers'),
                        Forms\Components\Toggle::make('is_template')
                            ->default(false)
                            ->label('Template')
                            ->helperText('Templates can be copied to create new packages'),
                    ])->columns(2),

                Forms\Components\Section::make('Package Benefits')
                    ->schema([
                        Forms\Components\Repeater::make('benefits')
                            ->relationship('benefits')
                            ->schema([
                                Forms\Components\Select::make('benefit_type')
                                    ->required()
                                    ->options([
                                        'jersey_logo' => 'Jersey/Uniform Logo',
                                        'stage_banner' => 'Main Stage Banner',
                                        'court_signage' => 'Court/Field Signage',
                                        'website_logo' => 'Website Logo Placement',
                                        'social_media' => 'Social Media Shoutouts',
                                        'email_marketing' => 'Email Marketing Mention',
                                        'program_ad' => 'Program/Booklet Ad',
                                        'video_board' => 'Video Board Advertisement',
                                        'booth_space' => 'Booth Space',
                                        'registration_table' => 'Registration Table Space',
                                        'giveaway_rights' => 'Giveaway Distribution Rights',
                                        'pa_announcements' => 'PA Announcements',
                                        'speaking_opportunity' => 'Speaking Opportunity',
                                        'award_presentation' => 'Award Presentation',
                                        'vip_tickets' => 'VIP Tickets',
                                        'networking_event' => 'Networking Event Access',
                                        'custom' => 'Custom Benefit',
                                    ])
                                    ->searchable()
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Display name for this benefit')
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->label('Qty')
                                    ->helperText('e.g., 5 posts')
                                    ->columnSpan(1),
                                Forms\Components\Textarea::make('description')
                                    ->rows(2)
                                    ->placeholder('Additional details...')
                                    ->columnSpan(2),
                                Forms\Components\Toggle::make('requires_asset_upload')
                                    ->label('Requires Upload')
                                    ->helperText('Sponsor must upload files')
                                    ->columnSpan(1),
                                Forms\Components\Toggle::make('is_enabled')
                                    ->default(true)
                                    ->label('Enabled')
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->reorderable('display_order')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->defaultItems(0)
                            ->addActionLabel('Add Benefit'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('tier')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'gold' => 'warning',
                        'silver' => 'gray',
                        'bronze' => 'danger',
                        default => 'info',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('event.name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Template')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('usd')
                    ->sortable(),
                Tables\Columns\TextColumn::make('availability')
                    ->label('Available')
                    ->getStateUsing(function (SponsorPackage $record): string {
                        if ($record->max_quantity === null) {
                            return 'Unlimited';
                        }
                        $remaining = $record->max_quantity - $record->current_quantity;

                        return "{$remaining} / {$record->max_quantity}";
                    })
                    ->badge()
                    ->color(function (SponsorPackage $record): string {
                        if ($record->max_quantity === null) {
                            return 'success';
                        }
                        $remaining = $record->max_quantity - $record->current_quantity;
                        if ($remaining === 0) {
                            return 'danger';
                        }
                        if ($remaining < 3) {
                            return 'warning';
                        }

                        return 'success';
                    }),
                Tables\Columns\TextColumn::make('benefits_count')
                    ->counts('benefits')
                    ->label('Benefits')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sponsorships_count')
                    ->counts('sponsorships')
                    ->label('Sold')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_template')
                    ->boolean()
                    ->label('Template')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('display_order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tier')
                    ->options([
                        'gold' => 'Gold',
                        'silver' => 'Silver',
                        'bronze' => 'Bronze',
                        'custom' => 'Custom',
                    ]),
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All packages')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
                Tables\Filters\TernaryFilter::make('is_template')
                    ->label('Template')
                    ->placeholder('All packages')
                    ->trueLabel('Templates only')
                    ->falseLabel('Event packages only'),
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
            ->defaultSort('display_order');
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
            'index' => Pages\ListSponsorPackages::route('/'),
            'create' => Pages\CreateSponsorPackage::route('/create'),
            'view' => Pages\ViewSponsorPackage::route('/{record}'),
            'edit' => Pages\EditSponsorPackage::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
