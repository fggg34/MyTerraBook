<?php

namespace Tests\Feature;

use App\Models\AvailabilityBlock;
use App\Models\Car;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ImportExternalIcalendarCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_counts_vevents(): void
    {
        $category = Category::query()->create(['name' => 'iCal', 'is_active' => true]);
        $car = Car::query()->create([
            'category_id' => $category->id,
            'name' => 'Calendar car',
            'units_available' => 1,
            'is_active' => true,
        ]);

        $path = tempnam(sys_get_temp_dir(), 'tb').'.ics';
        file_put_contents($path, implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            'UID:test-1@example.test',
            'DTSTAMP:20260101T000000Z',
            'DTSTART:20260101T100000Z',
            'DTEND:20260101T110000Z',
            'SUMMARY:Blocked',
            'END:VEVENT',
            'END:VCALENDAR',
        ]));

        try {
            Artisan::call('calendar:import-external', ['path' => $path, '--car_id' => $car->id]);
            $this->assertStringContainsString('Imported 1 event', Artisan::output());
            $this->assertDatabaseHas('availability_blocks', [
                'car_id' => $car->id,
                'source' => 'ical_import',
                'external_uid' => 'test-1@example.test',
                'is_active' => 1,
            ]);
            $this->assertSame(1, AvailabilityBlock::query()->count());
        } finally {
            @unlink($path);
        }
    }
}
