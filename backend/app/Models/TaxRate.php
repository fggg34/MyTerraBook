<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxRate extends Model
{
    protected $fillable = [
        'name',
        'basis_points',
    ];

    protected function casts(): array
    {
        return [
            'basis_points' => 'integer',
        ];
    }

    public function priceTypes(): HasMany
    {
        return $this->hasMany(PriceType::class);
    }

    public function rentalOptions(): HasMany
    {
        return $this->hasMany(RentalOption::class);
    }
}
