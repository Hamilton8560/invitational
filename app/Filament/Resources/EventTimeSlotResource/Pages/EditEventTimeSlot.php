<?php

namespace App\Filament\Resources\EventTimeSlotResource\Pages;

use App\Filament\Resources\EventTimeSlotResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEventTimeSlot extends EditRecord
{
    protected static string $resource = EventTimeSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
