<?php

namespace App\Services;

use Filament\Forms\Components\Component;
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
     * @return list<Component>
     */
    private function buildFields(string $pageKey, string $sectionKey, array $fields, bool $isRootSection): array
    {
        $liveFieldKeys = $this->visibleWhenControllerKeys($fields);
        $components = [];

        foreach ($fields as $field) {
            $component = $this->buildField($pageKey, $sectionKey, $field, $isRootSection, $liveFieldKeys);
            if ($component !== null) {
                $components[] = $component;
            }
        }

        return $components;
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     * @return list<string>
     */
    private function visibleWhenControllerKeys(array $fields): array
    {
        $keys = [];

        foreach ($fields as $field) {
            $whenField = $field['visibleWhen']['field'] ?? null;
            if (is_string($whenField) && $whenField !== '') {
                $keys[] = $whenField;
            }

            if (($field['type'] ?? '') === 'repeater') {
                foreach ($field['fields'] ?? [] as $subField) {
                    $subWhenField = $subField['visibleWhen']['field'] ?? null;
                    if (is_string($subWhenField) && $subWhenField !== '') {
                        $keys[] = $subWhenField;
                    }
                }
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * @param  array<string, mixed>  $field
     */
    /**
     * @param  list<string>  $liveFieldKeys
     */
    private function buildField(string $pageKey, string $sectionKey, array $field, bool $isRootSection, array $liveFieldKeys = []): mixed
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

        if ($key !== null && in_array($key, $liveFieldKeys, true) && in_array($type, ['toggle', 'select'], true)) {
            $component->live();
        }

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
            $whenStatePath = $sectionKey !== ''
                ? $this->statePath($sectionKey, $whenField, [], $isRootSection)
                : $whenField;
            $component->visible(function (Get $get) use ($whenStatePath, $whenValue): bool {
                $actual = $get($whenStatePath);

                if (is_bool($whenValue)) {
                    return (bool) $actual === $whenValue;
                }

                return ($actual ?? null) === $whenValue;
            });
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

        if (isset($field['acceptedFileTypes']) && is_array($field['acceptedFileTypes'])) {
            return $upload->acceptedFileTypes($field['acceptedFileTypes']);
        }

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

        if ($isRootSection || $sectionKey === '') {
            return (string) $key;
        }

        return "{$sectionKey}.{$key}";
    }
}
