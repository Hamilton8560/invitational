<?php

namespace App\Filament\Resources\EventTimeSlotResource\Pages;

use App\Filament\Resources\EventTimeSlotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEventTimeSlots extends ListRecords
{
    protected static string $resource = EventTimeSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
