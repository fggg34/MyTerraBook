<?php

namespace Database\Factories;

use App\Models\Backup;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Backup> */
class BackupFactory extends Factory
{
    protected $model = Backup::class;

    public function definition(): array
    {
        $filename = 'backup-'.fake()->date('Y-m-d-His').'.zip';

        return [
            'disk' => 'local',
            'path' => 'backups/'.$filename,
            'filename' => $filename,
            'size_bytes' => fake()->numberBetween(100000, 50000000),
            'backup_type' => fake()->randomElement(['full', 'management']),
        ];
    }
}
