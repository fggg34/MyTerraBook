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
}
