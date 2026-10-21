<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_backend_root_redirects_to_login_when_served_under_a_prefix(): void
    {
        URL::forceRootUrl('http://localhost/backend');
        $request = Request::create('http://localhost/backend/', 'GET', [], [], [], [
            'SCRIPT_NAME' => '/backend/index.php',
            'SCRIPT_FILENAME' => '/var/www/html/backend/public/index.php',
            'PHP_SELF' => '/backend/index.php',
        ]);
        $this->assertSame('/', $request->getPathInfo());
        $response = TestResponse::fromBaseResponse(
            $this->app->make(Kernel::class)->handle($request),
        );
        $response->assertRedirect('http://localhost/backend/admin/login');
    }

    /**
     * A basic test example.
     */
    public function test_root_redirects_to_filament_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('filament.admin.auth.login'));
    }
}
