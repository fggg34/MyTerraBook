<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('search_promotions');
    }

    public function down(): void
    {
        // Intentionally left empty, search promotions were removed from the product.
    }
};
