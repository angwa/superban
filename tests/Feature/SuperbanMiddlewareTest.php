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

    public function testUserIsNotBannedWhenBelowRateLimit()
    {
        $this->withoutExceptionHandling();

        Route::middleware([SuperbanMiddleware::class . ':3,1,1'])->get('/test-route', function () {
            return 'OK';
        });

        // Simulate 2 requests within 1 minute (below the limit)
        $response1 = $this->withMiddleware(SuperbanMiddleware::class, '3,1,1')->get('/test-route');
        $response2 = $this->withMiddleware(SuperbanMiddleware::class, '3,1,1')->get('/test-route');

        // Third request should be allowed
        $response3 = $this->withMiddleware(SuperbanMiddleware::class, '3,1,1')->get('/test-route');

        $response1->assertStatus(200);
        $response2->assertStatus(200);
        $response3->assertStatus(200);
    }

    public function testUserIsBannedAfterExceedingRateLimitForDifferentRoute()
    {
        $this->withoutExceptionHandling();

        // Set rate limit to 2 requests per minute for a different route
        Route::middleware([SuperbanMiddleware::class . ':2,1,1'])->get('/another-route', function () {
            return 'OK';
        });

        // Simulate 3 requests within 1 minute for the different route
        $response1 = $this->withMiddleware(SuperbanMiddleware::class, '2,1,1')->get('/another-route');
        $response2 = $this->withMiddleware(SuperbanMiddleware::class, '2,1,1')->get('/another-route');
        $response3 = $this->withMiddleware(SuperbanMiddleware::class, '2,1,1')->get('/another-route');

        // Fourth request for the different route should be blocked
        $response4 = $this->withMiddleware(SuperbanMiddleware::class, '2,1,1')->get('/another-route');

        $response1->assertStatus(200);
        $response2->assertStatus(200);
        $response3->assertStatus(200);
        $response4->assertStatus(403); // Corrected assertion
        $response4->assertSee("You are currently banned from using this service for 1 minutes.");
    }
}
