<?php

namespace App\Services;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Str;

class SiteContentFormBuilder
{
    /**
     * Collapsible cards, matching Homepage, Global chrome, and Terms.
     * The first card stays open. Later cards stay in the form so a save
     * still writes every section.
     *
     * @return list<Section>
     */
    public function buildSectionsForPage(string $pageKey, string $pattern = 'homepage'): array
    {
        $pageConfig = config("site_content.pages.{$pageKey}", []);
        $sections = $pageConfig['sections'] ?? [];

        $cards = [];
        foreach ($sections as $sectionKey => $sectionConfig) {
            $fields = $sectionConfig['fields'] ?? [];
            if ($sectionKey !== 'seo' && ! collect($fields)->contains(fn (array $field): bool => ($field['key'] ?? '') === 'visible')) {
                array_unshift($fields, [
                    'key' => 'visible',
                    'type' => 'toggle',
                    'label' => 'Show section',
                    'path' => $sectionKey.'.visible',
                    'default' => true,
                ]);
            }
            $isRootSection = (bool) ($sectionConfig['isRootSection'] ?? false);
            $components = $this->layoutSectionFields($pageKey, (string) $sectionKey, $fields, $isRootSection);

            if ($components === []) {
                continue;
            }

            $onlyColors = $fields !== [] && collect($fields)->every(
                fn (array $field): bool => ($field['type'] ?? '') === 'color',
            );
            $hasMobile = collect($fields)->contains(
                fn (array $field): bool => str_starts_with((string) ($field['key'] ?? ''), 'mobile'),
            );

            $cards[] = Section::make($sectionConfig['label'] ?? str($sectionKey)->headline()->toString())
                ->schema($components)
                ->columns($pattern === 'terms' || $hasMobile ? 1 : ($onlyColors ? ['default' => 1, 'md' => 3] : ['default' => 1, 'md' => 2]))
                ->collapsible()
                ->collapsed($pattern === 'terms' ? false : $cards !== [])
                ->extraAttributes(['class' => 'site-content-card']);
        }

        return $cards;
    }

    /**
     * Desktop and mobile copies stay in the form together.
     * A hidden tab would drop the concealed fields from a save.
     *
     * @param  list<array<string, mixed>>  $fields
     * @return list<Component|Group>
     */
    private function layoutSectionFields(string $pageKey, string $sectionKey, array $fields, bool $isRootSection): array
    {
        $desktop = [];
        $mobile = [];

        foreach ($fields as $field) {
            if (str_starts_with((string) ($field['key'] ?? ''), 'mobile')) {
                $mobile[] = $field;
            } else {
                $desktop[] = $field;
            }
        }

        if ($mobile === []) {
            return $this->buildFields($pageKey, $sectionKey, $fields, $isRootSection);
        }

        $groups = [];

        if ($desktop !== []) {
            $groups[] = Group::make($this->buildFields($pageKey, $sectionKey, $desktop, $isRootSection))
                ->columns(['default' => 1, 'md' => 2])
                ->columnSpanFull()
                ->extraAttributes(['class' => 'site-content-device site-content-device-desktop']);
        }

        $groups[] = Group::make($this->buildFields($pageKey, $sectionKey, $mobile, $isRootSection))
            ->columns(['default' => 1, 'md' => 2])
            ->columnSpanFull()
            ->extraAttributes(['class' => 'site-content-device site-content-device-mobile']);

        return $groups;
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
            'textarea' => Textarea::make($statePath)->rows(3)->autosize(),
            'richtext' => RichEditor::make($statePath)
                ->fileAttachmentsDisk('public')
                ->fileAttachmentsDirectory("site-content/{$pageKey}/attachments")
                ->fileAttachmentsVisibility('public'),
            'toggle' => Toggle::make($statePath),
            'color' => ColorPicker::make($statePath)->hex(),
            'password' => TextInput::make($statePath)->password()->revealable(),
            'image' => $this->buildImageUpload($statePath, $pageKey, $field),
            'file' => $this->buildFileUpload($statePath, $pageKey, $field),
            'number' => TextInput::make($statePath)->numeric(),
            'tags' => TagsInput::make($statePath),
            'select' => Select::make($statePath)
                ->options($this->selectOptions($field))
                ->searchable((bool) ($field['searchable'] ?? false))
                ->native(! (bool) ($field['searchable'] ?? false)),
            'repeater' => $this->buildRepeater($statePath, $field, $pageKey),
            default => TextInput::make($statePath),
        };

        if ($key !== null && in_array($key, $liveFieldKeys, true) && in_array($type, ['toggle', 'select'], true)) {
            $component->live();
        }

        if ($sectionKey === '' && in_array($key, ['title', 'heading', 'name', 'label', 'question'], true) && $type === 'text') {
            $component->live(onBlur: true);
        }

        if (isset($field['label'])) {
            $component->label($field['label']);
        }

        if (array_key_exists('default', $field)) {
            $component->default($field['default']);
        }

        if ($field['required'] ?? false) {
            $component->required();
        }

        if ($field['columnSpanFull'] ?? in_array($type, ['repeater', 'richtext', 'textarea'], true)) {
            $component->columnSpanFull();
        }

        $component->extraAttributes(['class' => $this->fieldClass($field)], merge: true);

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

    private function containedImagePreview(FileUpload $upload): FileUpload
    {
        return $upload
            ->imagePreviewHeight('168')
            ->extraAttributes(['class' => 'site-content-image-upload'], merge: true);
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function buildImageUpload(string $statePath, string $pageKey, array $field): FileUpload
    {
        $upload = $this->containedImagePreview(
            FileUpload::make($statePath)
                ->disk('public')
                ->directory("site-content/{$pageKey}")
                ->visibility('public'),
        );

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
        $upload = $this->containedImagePreview(
            FileUpload::make($statePath)
                ->disk('public')
                ->directory("site-content/{$pageKey}")
                ->visibility('public'),
        );

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
            ->collapsed()
            ->columns(['default' => 1, 'md' => 2])
            ->itemNumbers()
            ->truncateItemLabel()
            ->reorderable()
            ->extraAttributes(['class' => 'site-content-repeater'], merge: true)
            ->addActionLabel('Add '.Str::lower(Str::singular($field['label'] ?? 'item')))
            ->helperText(trim(($field['helperText'] ?? '').' Expand an item to edit it. Drag or use the arrows to change its display order.'))
            ->defaultItems(0);

        $subFields = $field['fields'] ?? [];
        $repeater->schema($this->buildFields($pageKey, '', $subFields, false));

        if (isset($field['label'])) {
            $repeater->label($field['label']);
        }

        // Prefer a human-readable heading over IDs, icon names, URLs or image paths.
        $labelFields = collect($subFields)->sortBy(function (array $subField): int {
            $key = Str::afterLast($subField['path'] ?? $subField['key'] ?? '', '.');

            return array_search($key, ['title', 'heading', 'name', 'label', 'question', 'text', 'caption'], true) === false
                ? 99
                : array_search($key, ['title', 'heading', 'name', 'label', 'question', 'text', 'caption'], true);
        })->filter(fn (array $subField): bool => in_array($subField['type'] ?? 'text', ['text', 'textarea', 'richtext'], true)
            && ! preg_match('/(?:id|url|href|icon|image|alt|color|type)$/i', $subField['key'] ?? ''));

        $repeater->itemLabel(function (array $state) use ($labelFields): ?string {
            foreach ($labelFields as $subField) {
                $label = $this->resolveRepeaterItemLabelValue($state, $subField);
                if ($label !== null && trim(strip_tags($label)) !== '') {
                    return Str::limit(trim(strip_tags($label)), 80);
                }
            }

            $code = strtoupper(trim((string) ($state['countryCode'] ?? '')));
            $country = config('destinations.'.$code);

            return is_string($country) && $country !== '' ? $country : 'Untitled item';
        });

        return $repeater;
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $subField
     */
    private function resolveRepeaterItemLabelValue(array $state, array $subField): ?string
    {
        $path = $subField['path'] ?? null;
        if (is_string($path) && $path !== '') {
            $value = data_get($state, $path);
        } else {
            $key = $subField['key'] ?? null;
            if (! is_string($key) || $key === '') {
                return null;
            }

            $value = $state[$key] ?? null;
        }

        if (is_string($value) || is_numeric($value)) {
            return (string) $value;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<string, string>
     */
    private function selectOptions(array $field): array
    {
        if (($field['optionsFrom'] ?? '') === 'destinations') {
            return config('destinations', []);
        }

        $options = $field['options'] ?? [];

        return is_array($options) ? $options : [];
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function fieldClass(array $field): string
    {
        $type = (string) ($field['type'] ?? 'text');
        $key = (string) ($field['key'] ?? '');
        $classes = ['site-content-field', 'site-content-field-'.$type];

        if (str_starts_with($key, 'mobile')) {
            $classes[] = 'site-content-field-mobile';
        }

        if (preg_match('/(alt|imageAlt|backgroundAlt)$/i', $key) === 1) {
            $classes[] = 'site-content-field-alt';
        }

        if (in_array($key, ['label', 'href'], true) || preg_match('/(Label|Href|Url)$/', $key) === 1) {
            $classes[] = 'site-content-field-link';
        }

        return implode(' ', $classes);
    }

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
