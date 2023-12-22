<?php

namespace Superban\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class SuperbanMiddleware
{
    protected $cache;
    protected $cacheDriver;
    protected $limiter;

    public function __construct()
    {
        $this->cacheDriver = Config::get('superban.superban_cache_driver');
        $this->cache = Cache::store($this->cacheDriver);
        $this->limiter = app(RateLimiter::class);
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @param mixed ...$parameters
     * 
     * @return [type]
     */
    public function handle(Request $request, Closure $next, ...$conditions)
    {
        list($maxRequests, $timeInterval, $banDuration) = $this->parseRateLimitParameters($conditions);

        if ($this->isUserBanned($request)) {
            return response("You are currently banned from using this service for {$banDuration} minutes.", 403);
        }

        $key = $this->resolveBanKey($request);

        // Check rate limiting attempts and ban user
        if ($this->limiter->tooManyAttempts($key, $maxRequests)) {
            // Ban the user for the specified duration
            $this->banUser($request, $banDuration);

            return response("Too many requests. You have been banned for {$banDuration} minutes.", 403);
        }

        // Here, I am incrementing the rate limiter hits
        $this->limiter->hit($key, $timeInterval * 60);

        return $next($request);
    }

    /**
     * @param Request $request
     * 
     * @return bool
     */
    protected function isUserBanned(Request $request): bool
    {
        $banKey = $this->resolveBanKey($request);
        $bannedInfo = $this->cache->get('superban');
        $isUserBanned = ($bannedInfo === $banKey) ? true : false;

        return $isUserBanned;
    }

    /**
     * @param Request $request
     * @param mixed $banDuration
     * 
     * @return void
     */
    protected function banUser(Request $request, $banDuration): void
    {
        $banKey = $this->resolveBanKey($request);
        $this->cache->put('superban', $banKey, now()->addMinutes($banDuration));

        // I am clearing the rate limiter hits below since it has already been cached and we can use cache to check.
        $this->limiter->clear($banKey);
    }

    /**
     * @param Request $request
     * 
     * @return string
     */
    protected function resolveBanKey(Request $request): string
    {
        $key = $request->user_id ?? $request->email ?? $request->ip();

        return 'ban_user_' . $key;
    }

    /**
     * @param array $conditions
     * 
     * @return array
     */
    protected function parseRateLimitParameters(array $conditions): array
    {
        // get default values from env
        $maxRequests = Config::get('superban.superban_max_requests');
        $timeInterval = Config::get('superban.superban_time_interval');
        $banDuration = Config::get('superban.superban_ban_duration');

        // The checks below will override the above if the params exist in the routes
        if (count($conditions) >= 1) {
            $maxRequests = (int) $conditions[0];
        }
        if (count($conditions) >= 2) {
            $timeInterval = (int) $conditions[1];
        }
        if (count($conditions) >= 3) {
            $banDuration = (int) $conditions[2];
        }

        return [$maxRequests, $timeInterval, $banDuration];
    }
}
