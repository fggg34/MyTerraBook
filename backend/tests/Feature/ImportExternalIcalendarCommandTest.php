<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ImportExternalIcalendarCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_counts_vevents(): void
    {
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
            Artisan::call('calendar:import-external', ['path' => $path]);
            $this->assertStringContainsString('Parsed 1 VEVENT', Artisan::output());
        } finally {
            @unlink($path);
        }
    }
}
