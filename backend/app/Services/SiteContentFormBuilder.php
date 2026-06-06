<?php

namespace App\Services;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;

class SiteContentFormBuilder
{
    /**
     * @return list<Tabs>
     */
    public function buildSectionsForPage(string $pageKey): array
    {
        $pageConfig = config("site_content.pages.{$pageKey}", []);
        $sections = $pageConfig['sections'] ?? [];

        $tabs = [];
        foreach ($sections as $sectionKey => $sectionConfig) {
            $fields = $sectionConfig['fields'] ?? [];
            $components = $this->buildFields($pageKey, $sectionKey, $fields, (bool) ($sectionConfig['isRootSection'] ?? false));

            if ($components === []) {
                continue;
            }

            $tabs[] = Tab::make($sectionConfig['label'] ?? str($sectionKey)->headline()->toString())
                ->schema($components)
                ->columns(2);
        }

        if ($tabs === []) {
            return [];
        }

        return [
            Tabs::make('Page sections')
                ->tabs($tabs)
                ->contained()
                ->persistTabInQueryString('section')
                ->dehydratedWhenHidden(),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     * @return list<\Filament\Forms\Components\Component>
     */
    private function buildFields(string $pageKey, string $sectionKey, array $fields, bool $isRootSection): array
    {
        $components = [];

        foreach ($fields as $field) {
            $component = $this->buildField($pageKey, $sectionKey, $field, $isRootSection);
            if ($component !== null) {
                $components[] = $component;
            }
        }

        return $components;
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function buildField(string $pageKey, string $sectionKey, array $field, bool $isRootSection): mixed
    {
        $type = $field['type'] ?? 'text';
        $key = $field['key'] ?? null;

        if (! $key && $type !== 'repeater') {
            return null;
        }

        $statePath = $this->statePath($sectionKey, $key, $field, $isRootSection);

        $component = match ($type) {
            'textarea' => Textarea::make($statePath)->rows(3),
            'richtext' => RichEditor::make($statePath),
            'toggle' => Toggle::make($statePath),
            'image' => $this->buildImageUpload($statePath, $pageKey, $field),
            'file' => $this->buildFileUpload($statePath, $pageKey, $field),
            'number' => TextInput::make($statePath)->numeric(),
            'tags' => TagsInput::make($statePath),
            'select' => Select::make($statePath)->options($field['options'] ?? []),
            'repeater' => $this->buildRepeater($statePath, $field, $pageKey),
            default => TextInput::make($statePath),
        };

        if (isset($field['label'])) {
            $component->label($field['label']);
        }

        if ($field['required'] ?? false) {
            $component->required();
        }

        if ($field['columnSpanFull'] ?? false) {
            $component->columnSpanFull();
        }

        if (isset($field['helperText'])) {
            $component->helperText($field['helperText']);
        }

        if (isset($field['visibleWhen']) && is_array($field['visibleWhen'])) {
            $whenField = $field['visibleWhen']['field'] ?? '';
            $whenValue = $field['visibleWhen']['value'] ?? null;
            $component->visible(fn (Get $get): bool => ($get($whenField) ?? null) === $whenValue);
        }

        $component->dehydratedWhenHidden();

        return $component;
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function buildImageUpload(string $statePath, string $pageKey, array $field): FileUpload
    {
        $upload = FileUpload::make($statePath)
            ->disk('public')
            ->directory("site-content/{$pageKey}")
            ->visibility('public');

        if ($field['allowSvg'] ?? false) {
            return $upload->acceptedFileTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif',
                'image/svg+xml',
            ]);
        }

        return $upload->image();
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function buildFileUpload(string $statePath, string $pageKey, array $field): FileUpload
    {
        $upload = FileUpload::make($statePath)
            ->disk('public')
            ->directory("site-content/{$pageKey}")
            ->visibility('public');

        $types = $field['acceptedFileTypes'] ?? ['image/svg+xml', 'image/png', 'image/x-icon', 'image/vnd.microsoft.icon'];

        return $upload->acceptedFileTypes($types);
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function buildRepeater(string $statePath, array $field, string $pageKey): Repeater
    {
        $repeater = Repeater::make($statePath)
            ->collapsible()
            ->defaultItems(0);

        $subFields = $field['fields'] ?? [];
        $repeater->schema($this->buildFields($pageKey, '', $subFields, false));

        if (isset($field['label'])) {
            $repeater->label($field['label']);
        }

        $repeater->itemLabel(function (array $state) use ($subFields): ?string {
            foreach ($subFields as $subField) {
                $subKey = $subField['key'] ?? null;
                if ($subKey && ! empty($state[$subKey])) {
                    return (string) $state[$subKey];
                }
            }

            return 'Item';
        });

        return $repeater;
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function statePath(string $sectionKey, ?string $key, array $field, bool $isRootSection): string
    {
        if (isset($field['path']) && is_string($field['path']) && $field['path'] !== '') {
            return $field['path'];
        }

        if ($field['isRoot'] ?? false) {
            return $key;
        }

        if ($field['isRootList'] ?? false) {
            return $key;
        }

        if ($isRootSection) {
            return $key;
        }

        return "{$sectionKey}.{$key}";
    }
}
