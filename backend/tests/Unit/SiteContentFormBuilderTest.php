<?php

namespace Tests\Unit;

use App\Services\SiteContentFormBuilder;
use Tests\TestCase;

class SiteContentFormBuilderTest extends TestCase
{
    public function test_repeater_fields_use_relative_state_paths(): void
    {
        $builder = new SiteContentFormBuilder;
        $method = new \ReflectionMethod($builder, 'statePath');
        $method->setAccessible(true);

        $this->assertSame('image', $method->invoke($builder, '', 'image', [], false));
        $this->assertSame('rentSection.cards', $method->invoke($builder, 'rentSection', 'cards', [], false));
        $this->assertSame('hero.backgroundImage', $method->invoke($builder, 'hero', 'backgroundImage', [], false));
    }

    public function test_repeater_item_label_skips_arrays_and_uses_nested_paths(): void
    {
        $builder = new SiteContentFormBuilder;
        $method = new \ReflectionMethod($builder, 'resolveRepeaterItemLabelValue');
        $method->setAccessible(true);

        $state = [
            'tall' => ['name' => 'Anna Sigurðar', 'role' => 'Campervan host'],
            'stack' => [['type' => 'stat', 'big' => '€2.4M+']],
        ];

        $this->assertSame(
            'Anna Sigurðar',
            $method->invoke($builder, $state, ['key' => 'tall_name', 'path' => 'tall.name'])
        );
        $this->assertNull($method->invoke($builder, $state, ['key' => 'stack', 'type' => 'repeater']));
        $this->assertSame('List it', $method->invoke($builder, ['title' => 'List it'], ['key' => 'title']));
    }

    public function test_build_sections_for_become_a_host_page(): void
    {
        $builder = new SiteContentFormBuilder;

        $this->assertNotEmpty($builder->buildSectionsForPage('become-a-host'));
    }

    public function test_build_sections_for_about_page_includes_story_and_media_fields(): void
    {
        $builder = new SiteContentFormBuilder;

        $this->assertNotEmpty($builder->buildSectionsForPage('about'));

        $sections = config('site_content.pages.about.sections', []);

        $this->assertArrayHasKey('storyBlocks', $sections);
        $this->assertSame('image', $this->findFieldType($sections['hero']['fields'] ?? [], 'image'));
        $this->assertSame('image', $this->findFieldType($sections['storyBlocks']['fields'][0]['fields'] ?? [], 'image'));
        $this->assertSame('image', $this->findFieldType($sections['offerings']['fields'][0]['fields'] ?? [], 'image'));
        $this->assertSame('richtext', $this->findFieldType($sections['storyBody']['fields'] ?? [], 'body'));
    }

    public function test_become_a_host_form_includes_photo_upload_fields(): void
    {
        $sections = config('site_content.pages.become-a-host.sections', []);

        $this->assertSame('image', $this->findFieldType($sections['hero']['fields'] ?? [], 'image'));
        $this->assertSame('image', $this->findFieldType($sections['howTabs']['fields'][0]['fields'] ?? [], 'image'));
        $this->assertSame('image', $this->findFieldType($sections['features']['fields'][0]['fields'] ?? [], 'image'));
        $this->assertSame('image', $this->findFieldType($sections['cta']['fields'] ?? [], 'patternImage'));
        $this->assertSame('text', $this->findFieldType($sections['featuresSection']['fields'] ?? [], 'heading'));
        $this->assertSame('textarea', $this->findFieldType($sections['featuresSection']['fields'] ?? [], 'subheading'));
        $this->assertSame('image', $this->findFieldType($sections['proof']['fields'][2]['fields'] ?? [], 'tall_image'));
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     */
    private function findFieldType(array $fields, string $key): ?string
    {
        foreach ($fields as $field) {
            if (($field['key'] ?? null) === $key) {
                return $field['type'] ?? null;
            }

            if (($field['type'] ?? '') === 'repeater') {
                $nested = $this->findFieldType($field['fields'] ?? [], $key);
                if ($nested !== null) {
                    return $nested;
                }
            }
        }

        return null;
    }
}
