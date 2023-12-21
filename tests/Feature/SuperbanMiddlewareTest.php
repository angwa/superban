<?php

namespace Superban\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;
use Superban\Middleware\SuperbanMiddleware;

class SuperbanMiddlewareTest extends TestCase
{
    public function testUserIsBannedAfterExceedingRateLimit()
    {

        $this->withoutExceptionHandling();

        Route::middleware([SuperbanMiddleware::class . ':3,1,1'])->get('/test-route', function () {
            return 'OK';
        });

        // Simulate 3 requests within 1 minute and ban duration of 1 minute x 3
        $response1 = $this->withMiddleware(SuperbanMiddleware::class, '3,1,1')->get('/test-route');
        $response2 = $this->withMiddleware(SuperbanMiddleware::class, '3,1,1')->get('/test-route');
        $response3 = $this->withMiddleware(SuperbanMiddleware::class, '3,1,1')->get('/test-route');

        // Fourth request should be blocked
        $response4 = $this->withMiddleware(SuperbanMiddleware::class, '3,1,1')->get('/test-route');

        $response1->assertStatus(200);
        $response2->assertStatus(200);
        $response3->assertStatus(200);
        $response4->assertStatus(403);
    }
}
