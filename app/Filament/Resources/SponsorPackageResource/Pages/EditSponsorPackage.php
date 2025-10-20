<?php

namespace App\Filament\Resources\SponsorPackageResource\Pages;

use App\Filament\Resources\SponsorPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSponsorPackage extends EditRecord
{
    protected static string $resource = SponsorPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
