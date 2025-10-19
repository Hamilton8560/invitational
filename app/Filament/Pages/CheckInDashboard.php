<?php

namespace App\Filament\Pages;

use App\Models\Checkin;
use App\Models\Event;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;

class CheckInDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static string $view = 'filament.pages.check-in-dashboard';

    protected static ?string $navigationLabel = 'Check-In Dashboard';

    protected static ?string $title = 'Check-In Dashboard';

    protected static ?string $navigationGroup = 'Events';

    protected static ?int $navigationSort = 10;

    public ?int $eventId = null;

    public function mount(): void
    {
        Gate::authorize('view_checkin_dashboard');

        // Default to the most recent event
        $this->eventId = Event::latest('start_date')->first()?->id;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('eventId')
                    ->label('Select Event')
                    ->options(Event::pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->required(),
            ]);
    }

    public function getStats(): array
    {
        if (! $this->eventId) {
            return [];
        }

        $totalCheckins = Checkin::forEvent($this->eventId)->count();
        $todayCheckins = Checkin::forEvent($this->eventId)->today()->count();
        $teamCheckins = Checkin::forEvent($this->eventId)->byType('team')->count();
        $individualCheckins = Checkin::forEvent($this->eventId)->byType('individual')->count();
        $vendorCheckins = Checkin::forEvent($this->eventId)
            ->where(function ($q) {
                $q->byType('vendor');
            })
            ->count();
        $spectatorCheckins = Checkin::forEvent($this->eventId)->byType('spectator')->count();

        return [
            [
                'label' => 'Total Check-Ins',
                'value' => $totalCheckins,
                'icon' => 'heroicon-o-check-circle',
                'color' => 'success',
            ],
            [
                'label' => 'Today',
                'value' => $todayCheckins,
                'icon' => 'heroicon-o-calendar',
                'color' => 'info',
            ],
            [
                'label' => 'Teams',
                'value' => $teamCheckins,
                'icon' => 'heroicon-o-user-group',
                'color' => 'warning',
            ],
            [
                'label' => 'Individual Players',
                'value' => $individualCheckins,
                'icon' => 'heroicon-o-user',
                'color' => 'primary',
            ],
            [
                'label' => 'Vendors',
                'value' => $vendorCheckins,
                'icon' => 'heroicon-o-building-storefront',
                'color' => 'secondary',
            ],
            [
                'label' => 'Spectators',
                'value' => $spectatorCheckins,
                'icon' => 'heroicon-o-eye',
                'color' => 'gray',
            ],
        ];
    }

    public function getRecentCheckins()
    {
        if (! $this->eventId) {
            return collect();
        }

        return Checkin::with(['user', 'checkedInBy', 'team', 'individualPlayer', 'booth', 'banner'])
            ->forEvent($this->eventId)
            ->orderBy('checked_in_at', 'desc')
            ->limit(10)
            ->get();
    }

    public static function canAccess(): bool
    {
        return Gate::allows('view_checkin_dashboard');
    }
}
