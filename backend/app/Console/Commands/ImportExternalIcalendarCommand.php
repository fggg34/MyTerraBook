<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Sabre\VObject\Reader;

class ImportExternalIcalendarCommand extends Command
{
    protected $signature = 'calendar:import-external {path? : Absolute path to a .ics file}';

    protected $description = 'Parse an external .ics (VEVENT count). Optional path or settings key ical.import.file_path.';

    public function handle(): int
    {
        $path = $this->argument('path');
        if ($path === null || $path === '') {
            $path = (string) data_get(Setting::getValue('ical.import', []), 'file_path', '');
        }

        if ($path === '' || ! is_readable($path)) {
            $this->warn('No readable .ics path provided or configured (ical.import.file_path).');

            return self::SUCCESS;
        }

        $stream = fopen($path, 'r');
        if ($stream === false) {
            $this->error('Could not open file.');

            return self::FAILURE;
        }

        $vcalendar = Reader::read($stream);
        fclose($stream);

        $count = isset($vcalendar->VEVENT) ? count($vcalendar->VEVENT) : 0;
        $this->info("Parsed {$count} VEVENT entr".($count === 1 ? 'y' : 'ies')." from {$path}.");

        return self::SUCCESS;
    }
}
