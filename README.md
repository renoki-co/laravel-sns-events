Laravel SNS Events
==================

![CI](https://github.com/renoki-co/laravel-sns-events/workflows/CI/badge.svg?branch=master)
[![codecov](https://codecov.io/gh/renoki-co/laravel-sns-events/branch/master/graph/badge.svg)](https://codecov.io/gh/renoki-co/laravel-sns-events/branch/master)
[![StyleCI](https://github.styleci.io/repos/189254977/shield?branch=master)](https://github.styleci.io/repos/189254977)
[![Latest Stable Version](https://poser.pugx.org/rennokki/laravel-sns-events/v/stable)](https://packagist.org/packages/rennokki/laravel-sns-events)
[![Total Downloads](https://poser.pugx.org/rennokki/laravel-sns-events/downloads)](https://packagist.org/packages/rennokki/laravel-sns-events)
[![Monthly Downloads](https://poser.pugx.org/rennokki/laravel-sns-events/d/monthly)](https://packagist.org/packages/rennokki/laravel-sns-events)
[![License](https://poser.pugx.org/rennokki/laravel-sns-events/license)](https://packagist.org/packages/rennokki/laravel-sns-events)

Laravel SNS Events allow you to listen to SNS webhooks via Laravel Events. It leverages a controller that is made to properly listen to SNS HTTP(s) webhooks and trigger events on which you can listen to in Laravel.

If you are not familiar with Laravel Events & Listeners, make sure you check the [documentation section on Laravel Documentation](https://laravel.com/docs/master/events) because this package will need you to understand this concept.

## 🚀 Installation

```bash
$ composer require rennokki/laravel-sns-events
```

There are two classes that get triggered, depending on the request sent by AWS:

* `Rennokki\LaravelSnsEvents\Events\SnsEvent` - triggered on each SNS notification
* `Rennokki\LaravelSnsEvents\Events\SnsSubscriptionConfirmation` - triggered when the subscription is confirmed

A controller that will handle the response for you should be registered in your routes:

```php
...

// you can choose any route
Route::any('/aws/sns', '\Rennokki\LaravelSnsEvents\Http\Controllers\SnsController@handle');
```

SNS sends data as raw json, so you will need to whitelist your route in your `VerifyCsrfToken.php`:

```php
protected $except = [
    ...
    'aws/sns/',
];
```

You will need an AWS account and register a SNS Topic and set up a subscription for HTTP(s) protocol that will point out to the route you just registered.

Make sure to enable RAW JSON format for your SNS Subscription.

If you have registered the route and created a SNS Topic, you should register the URL and click the confirmation button from the AWS Dashboard. In a short while, if you implemented the route well, you'll be seeing that your endpoint is registered.

## 🙌 Usage

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

You will be able to access the SNS notification from your listeners like this:

```php
class MyListener
{
    ...

    public function handle($event)
    {
        // $event->payload is an array containing the payload sent
        // $event->headers is an array containing the headers sent

        $content = json_decode($event->message['Message'], true);

        // ...
    }
}
```

### JSON-Decoded Message

Some payloads come with a `Message` field that is usually JSON-encoded. To retrieve it as array, without having to decode it yourself, you can call `getMessage()` from the event:

```php
class MyListener
{
    ...

    public function handle($event)
    {
        $content = $event->getMessage();

        // ...
    }
}
```

**In case the message is not JSON-decodable, the `getMessage` method will return the orginal sent message if it's a string or `null` if it does not exist.**

## 🐛 Testing

Run the tests with:

``` bash
vendor/bin/phpunit
```

## 🤝 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 🔒  Security

If you discover any security related issues, please email alex@renoki.org instead of using the issue tracker.

## 🎉 Credits

- [Alex Renoki](https://github.com/rennokki)
- [All Contributors](../../contributors)

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
