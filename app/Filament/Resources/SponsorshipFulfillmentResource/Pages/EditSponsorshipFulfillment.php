<?php

namespace App\Filament\Resources\SponsorshipFulfillmentResource\Pages;

use App\Filament\Resources\SponsorshipFulfillmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSponsorshipFulfillment extends EditRecord
{
    protected static string $resource = SponsorshipFulfillmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
