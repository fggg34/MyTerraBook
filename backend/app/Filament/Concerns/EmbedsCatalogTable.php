<?php

namespace App\Filament\Concerns;

use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;

trait EmbedsCatalogTable
{
    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }
}
