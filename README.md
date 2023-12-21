# Superban

This laravel package helps to limit request and ban suspicious users for a period of time.


## Installation and usage

This package requires PHP  and Laravel installed.  

You can install the package via composer:

```bash
composer require angwa/superban:dev-master
```
Next step is to register our service providers. Simply open ```config/app.php``` and locate  providers section and add  ```Superban\SuperbanServiceProvider::class,,``` Like below
```
'providers' => [
    ...
    Superban\SuperbanServiceProvider::class,
    ...
]

```

### Load Configuration file
Our configuration file is named superban.php and will be created when you run the bash code below
```bash
php artisan vendor:publish --provider="Superban\SuperbanServiceProvider"

```

You can also copy the code below, create a file named superban.php in the config folder and paste the code below
```
<?php

return [
    /*
     * This package will look for SUPERBAN_CACHE_DRIVER in your env file
     * This package may look for ```SUPERBAN_MAXIMUM_REQUESTS```,  ```SUPERBAN_TIME_INTERVAL``` and ```SUPERBAN_BAN_DURATION``` in your env file
     * You can decide to set the default values for your superban generally.
     */

    'superban_cache_driver' => env('SUPERBAN_CACHE_DRIVER', 'file'),
    'superban_max_requests' => env('SUPERBAN_MAXIMUM_REQUESTS', 200),
    'superban_time_interval' => env('SUPERBAN_TIME_INTERVAL', 2),
    'superban_ban_duration' => env('SUPERBAN_BAN_DURATION', 1440),

];

```

The above env variables are used to set the application.

The following are optional. If you can decide to set it only when you want a uniform rate limiter for your multiple routes. You just simply set it from here and it will apply globally
```SUPERBAN_MAXIMUM_REQUESTS```,  ```SUPERBAN_TIME_INTERVAL``` and ```SUPERBAN_BAN_DURATION``` 
## Usage

### Description
This package will require you to set the SUPERBAN_CACHE_DRIVER enviroment in your .env. It will default to file as the caching machanism.
but if you want a different caching driver that is installed in your laravel application, you can specify it.
```
SUPERBAN_CACHE_DRIVER=file
```

it can be database, redis or which ever caching driver you want to use

## Example
```
//For Example
// You can use the middleware in your route like this 

Route::middleware(['superban:10,1,1'])->group(function () {
    Route::get('/test-route', function () {
        return "Hello world";
    });
});

```

Where superban is the name of the middleware, 10 is the number of request, 1 is the number of minutes interval for the 10 request. and the last parameter 1 is the number of minutes you will be banned if you make a request more than 10 times per minute.

Update the limit to which ever value you want and you are good to go

## Testing

To run the tests on package, update composer and type this:

``` bash
vendor/bin/phpunit
```

### Security

If you discover any security related issues, please email angwamoses@gmail.com instead of using the issue tracker.

## Credits

- [Angwa Moses](https://github.com/angwa)


## License

The MIT License (MIT).

