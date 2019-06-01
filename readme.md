[![Build Status](https://travis-ci.org/rennokki/laravel-sns-events.svg?branch=master)](https://travis-ci.org/rennokki/laravel-sns-events)
[![codecov](https://codecov.io/gh/rennokki/laravel-sns-events/branch/master/graph/badge.svg)](https://codecov.io/gh/rennokki/laravel-sns-events/branch/master)
[![StyleCI](https://github.styleci.io/repos/189254977/shield?branch=master)](https://github.styleci.io/repos/189254977)
[![Latest Stable Version](https://poser.pugx.org/rennokki/laravel-sns-events/v/stable)](https://packagist.org/packages/rennokki/laravel-sns-events)
[![Total Downloads](https://poser.pugx.org/rennokki/laravel-sns-events/downloads)](https://packagist.org/packages/rennokki/laravel-sns-events)
[![Monthly Downloads](https://poser.pugx.org/rennokki/laravel-sns-events/d/monthly)](https://packagist.org/packages/rennokki/laravel-sns-events)
[![License](https://poser.pugx.org/rennokki/laravel-sns-events/license)](https://packagist.org/packages/rennokki/laravel-sns-events)

[![PayPal](https://img.shields.io/badge/PayPal-donate-blue.svg)](https://paypal.me/rennokki)

# Laravel SNS Events
Laravel SNS Events allow you to listen to SNS webhooks via Laravel Events. It implements a controller that is made to properly listen to SNS HTTP(s) webhooks and trigger events on which you can listen to in Laravel.

If you are not familiar with Laravel Events & Listeners, make sure you check the [documentation section on laravel.com](https://laravel.com/docs/5.8/events) because this package will need you to understand this concept.

## Install
```bash
$ composer require rennokki/laravel-sns-events
```

## Usage
The package comes with two event classes:
* `Rennokki\LaravelSnsEvents\Events\SnsEvent` - triggered on each SNS message
* `Rennokki\LaravelSnsEvents\Events\SnsSubscriptionConfirmation` - triggered when the subscription is confirmed

This package comes with a controller that will handle the response for you. Register the route in your `web.php`:
```php
...

// you can choose any route
Route::any('/aws/sns', 'Rennokki\LaravelSnsEvents\Http\Controllers\SnsController@handle');
```

SNS sends data through POST, so you will need to whitelist your route in your `VerifyCsrfToken.php`:
```php
protected $except = [
    ...
    'aws/sns/',
];
```

You will need an AWS account and register a SNS Topic and set up a subscription for HTTP(s) protocol that will point out to the route you just registered.

If you have registered the route and created a SNS Topic, you should register the URL and click the confirmation button from the AWS Dashboard. In a short while, if you implemented the route well, you'll be seeing that your endpoint is registered.

To process the events, you should add the events in your `app/Providers/EventServiceProvider.php`:
```php
use Rennokki\LaravelSnsEvents\Events\SnsEvent;
use Rennokki\LaravelSnsEvents\Events\SnsSubscriptionConfirmation;

...

protected $listen = [
    ...
    SnsEvent::class => [
        // add your listeners here for SNS events
    ],
    SnsSubscriptionConfirmation::class => [
        // add your listeners here in case you want to listen to subscription confirmation
    ],
]
```

You will be able to access the SNS message from your listeners like this:
```php
class MyListener
{
    ...

    public function handle($event)
    {
        // $event->message is an array
    }
}
```

For example, synthetizing an AWS Polly Text-To-Speech would return in `$event->message` an array like this:
```
[
    'taskId' => '...',
    'taskStatus' => 'FAILED',
    'taskStatusReason' => 'Error occurred while trying to upload file to S3. Please verify that the bucket existsin this region and you have permission to write objects to the specified bucket.',
    'outputUri' => 's3://...',
    'creationTime' => '2019-05-29T15:27:31.231Z',
    'requestCharacters' => 58,
    'snsTopicArn' => '...',
    'outputFormat' => 'Mp3',
    'sampleRate' => '22050',
    'speechMarkTypes' => [],
    'textType' => 'Text',
    'voiceId' => 'Joanna',
]
```

## Testing
Run the tests with:

``` bash
vendor/bin/phpunit
```

## Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security
If you discover any security-related issues, please email DummyAuthorEmail instead of using the issue tracker.

## License
The MIT License (MIT). Please see [License File](/LICENSE.md) for more information.
