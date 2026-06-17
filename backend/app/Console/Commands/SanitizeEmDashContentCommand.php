<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\HomepageSection;
use App\Models\RentalCondition;
use App\Models\RentalOption;
use App\Models\SiteContentPage;
use App\Models\SitePage;
use App\Services\SiteContentService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class SanitizeEmDashContentCommand extends Command
{
    protected $signature = 'content:sanitize-em-dashes {--dry-run : Preview changes without saving}';

    protected $description = 'Replace prose em dashes (—) with commas in stored CMS and catalog text';

    /** @var list<class-string<Model>> */
    private array $jsonContentModels = [
        SiteContentPage::class,
        HomepageSection::class,
        SitePage::class,
    ];

    /** @var array<class-string<Model>, list<string>> */
    private array $stringColumnModels = [
        BlogPost::class => ['excerpt', 'body'],
        RentalOption::class => ['description'],
        RentalCondition::class => ['description'],
        SitePage::class => ['title', 'eyebrow', 'lead', 'body'],
    ];

    public function handle(SiteContentService $siteContentService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;

        foreach ($this->jsonContentModels as $modelClass) {
            /** @var Model $model */
            foreach ($modelClass::query()->cursor() as $model) {
                $content = $model->getAttribute('content');
                if (! is_array($content)) {
                    continue;
                }

                $sanitized = $this->sanitizeMixed($content);
                if ($sanitized === $content) {
                    continue;
                }

                $label = $this->modelLabel($model);
                $this->line("  {$label}");
                if (! $dryRun) {
                    $model->update(['content' => $sanitized]);
                }
                $updated++;
            }
        }

        foreach ($this->stringColumnModels as $modelClass => $columns) {
            /** @var Model $model */
            foreach ($modelClass::query()->cursor() as $model) {
                $changes = [];

                foreach ($columns as $column) {
                    $value = $model->getAttribute($column);
                    if (! is_string($value)) {
                        continue;
                    }

                    $sanitized = $this->sanitizeString($value);
                    if ($sanitized !== $value) {
                        $changes[$column] = $sanitized;
                    }
                }

                if ($changes === []) {
                    continue;
                }

                $label = $this->modelLabel($model);
                $this->line("  {$label} (".implode(', ', array_keys($changes)).')');
                if (! $dryRun) {
                    $model->update($changes);
                }
                $updated++;
            }
        }

        if (! $dryRun && $updated > 0) {
            $siteContentService->clearCache();
        }

        $this->info($dryRun
            ? "Would update {$updated} record(s). Run without --dry-run to apply."
            : "Updated {$updated} record(s).");

        return self::SUCCESS;
    }

    private function modelLabel(Model $model): string
    {
        $table = $model->getTable();
        $key = $model->getKey();

        foreach (['page_key', 'section_key', 'slug', 'name', 'title'] as $field) {
            $value = $model->getAttribute($field);
            if (is_string($value) && $value !== '') {
                return "{$table}#{$key} ({$field}={$value})";
            }
        }

        return "{$table}#{$key}";
    }

    private function sanitizeMixed(mixed $value): mixed
    {
        if (is_string($value)) {
            return $this->sanitizeString($value);
        }

        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->sanitizeMixed($item);
        }

        return $value;
    }

    private function sanitizeString(string $value): string
    {
        if ($value === '—') {
            return $value;
        }

        return str_replace(' — ', ', ', $value);
    }
}
