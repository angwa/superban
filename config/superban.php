<?php

return [
    /*
     * This package will look for SUPERBAN_CACHE_DRIVER in your env file
     * This package will look for SUPERBAN_MAXIMUM_REQUESTS, SUPERBAN_TIME_INTERVAL and SUPERBAN_BAN_DURATION in your env file
     * You can decide to set the default values for your superban generally.
     */

    'superban_cache_driver' => env('SUPERBAN_CACHE_DRIVER', 'file'),
    'superban_max_requests' => env('SUPERBAN_MAXIMUM_REQUESTS', 200),
    'superban_time_interval' => env('SUPERBAN_TIME_INTERVAL', 2),
    'superban_ban_duration' => env('SUPERBAN_BAN_DURATION', 1440),

];
