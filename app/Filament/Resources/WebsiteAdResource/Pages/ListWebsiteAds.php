<?php

namespace App\Filament\Resources\WebsiteAdResource\Pages;

use App\Filament\Resources\WebsiteAdResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWebsiteAds extends ListRecords
{
    protected static string $resource = WebsiteAdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
