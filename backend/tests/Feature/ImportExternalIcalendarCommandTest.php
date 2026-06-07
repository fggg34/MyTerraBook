<?php

namespace Tests\Feature;

use App\Models\AvailabilityBlock;
use App\Models\Car;
use App\Models\MainCategory;
use App\Models\SubCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ImportExternalIcalendarCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_counts_vevents(): void
    {
        $main = MainCategory::query()->firstOrCreate(['slug' => 'car'], ['name' => 'Car', 'is_active' => true]);
        $category = SubCategory::query()->create(['main_category_id' => $main->id, 'name' => 'iCal', 'is_active' => true, 'is_search_filter' => true]);
        $car = Car::query()->create([
            'sub_category_id' => $category->id,
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
