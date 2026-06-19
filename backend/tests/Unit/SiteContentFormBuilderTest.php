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
}
