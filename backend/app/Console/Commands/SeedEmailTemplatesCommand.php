<?php

namespace App\Console\Commands;

use Database\Seeders\EmailTemplateSeeder;
use Illuminate\Console\Command;

class SeedEmailTemplatesCommand extends Command
{
    protected $signature = 'email:seed-templates';

    protected $description = 'Install missing default email templates (safe to run on live)';

    public function handle(): int
    {
        $created = EmailTemplateSeeder::seedMissing();
        $total = EmailTemplateSeeder::defaultTemplateCount();

        if ($created === 0) {
            $this->info("All {$total} default email templates are already installed.");
        } else {
            $this->info("Installed {$created} missing email template(s). {$total} defaults are available.");
        }

        return self::SUCCESS;
    }
}
