[![Latest Stable Version](https://img.shields.io/packagist/v/f9webltd/laravel-queue-check.svg)](https://packagist.org/packages/f9webltd/laravel-queue-check)
[![Scrutinizer coverage (GitHub/BitBucket)](https://img.shields.io/scrutinizer/coverage/g/f9webltd/laravel-queue-check)](https://packagist.org/packages/f9webltd/laravel-queue-check)
[![Scrutinizer code quality (GitHub/Bitbucket)](https://img.shields.io/scrutinizer/quality/g/f9webltd/laravel-queue-check)](https://packagist.org/packages/f9webltd/laravel-queue-check)
[![Build Status](https://travis-ci.org/f9webltd/laravel-queue-check.svg?branch=master)](https://travis-ci.org/f9webltd/laravel-queue-check)
[![StyleCI Status](https://github.styleci.io/repos/272114358/shield)](https://github.styleci.io/repos/272114358)
[![License](https://poser.pugx.org/f9webltd/laravel-validation-rules/license)](https://packagist.org/packages/f9webltd/laravel-validation-rules)

# Laravel Redis Queue Check

A crude and simple [Laravel console command](https://laravel.com/docs/master/artisan) to determine if your Redis queue worker is running.

When the command determines that your worker is *not* running, the event `QueueCheckFailed` is dispatched.

Behind the scenes, the command `ps aux` is ran to determine if you expected command is present.

## Requirements

PHP >= 7.2, Laravel >= 5.8.

## Installation

``` bash
composer require f9webltd/laravel-queue-check
```

## Documentation

### Configuration Settings

The configuration is extremely basic and allows two expectations to be set.

#### `processes`

An integer, the required number of processes that should be present. 

#### `expected-output`

The target command that should be running. 

Some examples: 

- `artisan queue:work redis`
- `artisan horizon`
- `php artisan horizon:work redis --delay=0 --memory=128 --queue=default --sleep=3 --timeout=60 --tries=3 --supervisor=abc:supervisor-1`
 
The specificity is optional. 

To determine the output typically `ps aux | grep artisan` can be ran from the command line. So if the output of which was:

``` bash
user+ 17981  0.0  0.4 384080 38544 ?        Ss   Jun12   0:31 php /home/user/artisan queue:work redis --queue=live --sleep=3 --tries=3
user+ 18004  0.0  0.4 384080 38548 ?        Ss   Jun12   0:31 php /home/user/artisan queue:work redis --queue=live --sleep=3 --tries=3
user+ 18027  0.0  0.4 384080 38548 ?        Ss   Jun12   0:31 php /home/user/artisan queue:work redis --queue=live --sleep=3 --tries=3
```

The following configuration values could apply:

- `expected-output` = `artisan queue:work redis`
- `processes` = 3
 
### Usage within your application

When desired command or required number of processes is not running, the event `QueueCheckFailed` os dispatched.

Within your application you can [listen for that event](https://laravel.com/docs/master/events).

Define the event listener, in this case `QueueFailed`.

``` php
// App\Providers\EventServiceProvider

protected $listen = [
    \F9Web\QueueCheck\Events\QueueCheckFailed::class => [
        \App\Listeners\QueueFailed::class,
    ],
];
```

Register the [scheduled task](https://laravel.com/docs/master/scheduling) `f9web:queue-check`:

``` php
// App\Console\Kernel

protected function schedule(Schedule $schedule) 
{
    $schedule->command('f9web:queue-check')->everyThirtyMinutes();
}


```

The event returns the console output that caused the command to fail, In the below case, that is simply logged. The listener can of course do anything required, such as sending an email or a [Slack notification](https://laravel.com/docs/master/notifications#slack-notifications).

``` php
namespace App\Listeners;

use F9Web\QueueCheck\Events\QueueCheckFailed;

class QueueFailed
{
    public function __construct()
    {
        //
    }
    
    public function handle(QueueCheckFailed $event)
    {
        info('Failing', [
            'output' => $event->getOutput(),
        ]);
    }
}

``` 

N.B: When running a Redis queue, there is a high chance your application will queue mail. If the queue is down the notification email is queued, the email will never be sent. If sending an email ensure the mail is not queued. If using a "mailable" object the `Queueable` trait can be omitted. 
 
## Contribution

Any ideas are welcome. Feel free to submit any issues or pull requests.

## Testing

``` bash
composer test
```

## Security

If you discover any security related issues, please email rob@f9web.co.uk instead of using the issue tracker.

## Credits

- [Rob Allport](https://github.com/ultrono)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
