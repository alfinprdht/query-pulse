<?php

namespace Alfinprdht\QueryPulse\Tests\Feature;

use Alfinprdht\QueryPulse\Tests\TestCase;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Orchestra\Testbench\Http\Middleware\Authenticate;

class AssetControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            Authenticate::class,
            VerifyCsrfToken::class,
        ]);
    }

    public function test_serves_public_css(): void
    {
        $this->withBasicAuth('admin', 'secret')
            ->get('/query-pulse/assets/css/google-fonts.css')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/css; charset=utf-8');
    }

    public function test_rejects_path_traversal(): void
    {
        $this->withBasicAuth('admin', 'secret')
            ->get('/query-pulse/assets/../config/query-pulse.php')
            ->assertNotFound();
    }
}
