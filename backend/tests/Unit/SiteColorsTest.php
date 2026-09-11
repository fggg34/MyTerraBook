<?php

namespace Tests\Unit;

use App\Support\SiteColors;
use Tests\TestCase;

class SiteColorsTest extends TestCase
{
    public function test_normalize_hex_accepts_short_and_full_values(): void
    {
        $this->assertSame('#0f2036', SiteColors::normalizeHex('#0F2036'));
        $this->assertSame('#112233', SiteColors::normalizeHex('123'));
        $this->assertNull(SiteColors::normalizeHex('navy'));
        $this->assertNull(SiteColors::normalizeHex(''));
    }

    public function test_to_root_css_emits_only_valid_tokens(): void
    {
        $css = SiteColors::toRootCss([
            'navy' => '#112233',
            'green' => 'not-a-color',
            'accent' => '#ea580c',
        ]);

        $this->assertStringContainsString('--navy: #112233 !important', $css);
        $this->assertStringContainsString('--accent: #ea580c !important', $css);
        $this->assertStringNotContainsString('--green:', $css);
    }
}
