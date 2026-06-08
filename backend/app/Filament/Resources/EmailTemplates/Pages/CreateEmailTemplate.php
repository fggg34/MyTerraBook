<?php

namespace App\Filament\Resources\EmailTemplates\Pages;

use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use App\Models\EmailTemplate;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailTemplate extends CreateRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['key'] = strtolower(trim((string) ($data['key'] ?? '')));
        $data['sort_order'] = ((int) EmailTemplate::query()->max('sort_order')) + 10;

        return $data;
    }
}
