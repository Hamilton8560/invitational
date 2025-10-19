<?php

namespace App\Filament\Resources\IndividualPlayerResource\Pages;

use App\Filament\Resources\IndividualPlayerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIndividualPlayers extends ListRecords
{
    protected static string $resource = IndividualPlayerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
