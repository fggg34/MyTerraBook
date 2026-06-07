<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Production serves the Laravel app under https://myterrabook.com/backend. Livewire signs its
 * temporary file-upload (and preview) URL with URL::temporarySignedRoute() — which uses
 * URL::forceRootUrl(.../backend) — while FileUploadController validates it with
 * request()->hasValidSignature(), re-hashing request()->url().
 *
 * The 401 "failed to upload" happened because public/index.php used to strip /backend from
 * REQUEST_URI before validation, so request()->url() lost the prefix and the signature no
 * longer matched. index.php now exposes /backend as the base URL instead of stripping it, so
 * routing still drops the prefix but request()->url() keeps it. These tests guard that.
 */
class LivewireFileUploadSignedUrlTest extends TestCase
{
    public function test_signed_upload_url_validates_only_when_request_keeps_the_prefix(): void
    {
        URL::forceRootUrl('http://localhost/backend');

        $signed = URL::temporarySignedRoute('livewire.upload-file', now()->addMinutes(5));

        $this->assertStringContainsString('/backend/livewire', $signed);

        // What the server sees once index.php exposes /backend as the base URL: the request URL
        // still contains /backend, so the signature validates (the fix).
        $this->assertTrue(URL::hasValidSignature(Request::create($signed)));

        // The old behaviour: /backend stripped from REQUEST_URI, so url() lost the prefix and
        // the signature no longer matched → 401.
        $strippedPath = str_replace('/backend/livewire', '/livewire', (string) parse_url($signed, PHP_URL_PATH));
        $strippedUrl = 'http://localhost'.$strippedPath.'?'.parse_url($signed, PHP_URL_QUERY);

        $this->assertFalse(URL::hasValidSignature(Request::create($strippedUrl)));
    }

    public function test_upload_endpoint_does_not_return_401_under_backend_prefix(): void
    {
        URL::forceRootUrl('http://localhost/backend');

        $signed = URL::temporarySignedRoute('livewire.upload-file', now()->addMinutes(5));

        // Simulate the SCRIPT_NAME that public/index.php sets in production so Symfony derives
        // the /backend base path: routing matches /livewire-…/upload-file while request()->url()
        // keeps /backend and the controller's signature check passes.
        $response = $this->call('POST', $signed, [], [], [], [
            'SCRIPT_NAME' => '/backend/index.php',
            'SCRIPT_FILENAME' => '/var/www/html/backend/public/index.php',
            'PHP_SELF' => '/backend/index.php',
        ]);

        $this->assertNotSame(401, $response->getStatusCode());
    }
}
