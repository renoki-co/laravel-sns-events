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

## 🤝 Supporting

Renoki Co. on GitHub aims on bringing a lot of open source projects and helpful projects to the world. Developing and maintaining projects everyday is a harsh work and tho, we love it.

If you are using your application in your day-to-day job, on presentation demos, hobby projects or even school projects, spread some kind words about our work or sponsor our work. Kind words will touch our chakras and vibe, while the sponsorships will keep the open source projects alive.

## 🚀 Installation

```bash
$ composer require rennokki/laravel-sns-events
```

There are two classes that get triggered, depending on the request sent by AWS:

* `Rennokki\LaravelSnsEvents\Events\SnsNotification` - triggered on each SNS notification
* `Rennokki\LaravelSnsEvents\Events\SnsSubscriptionConfirmation` - triggered when the subscription is confirmed

A controller that will handle the response for you should be registered in your routes:

```php
...

// you can choose any route
Route::any('/aws/sns', '\Rennokki\LaravelSnsEvents\Http\Controllers\SnsController@handle');
```

Make sure you whitelist your route in `VerifyCsrfToken.php`:

```php
protected $except = [
    ...
    'aws/sns/',
];
```

You will need an AWS account and register a SNS Topic and set up a subscription for HTTP(s) protocol that will point out to the route you just registered.

If you have registered the route and created a SNS Topic, you should register the URL and click the confirmation button from the AWS Dashboard. In a short while, if you implemented the route well, you'll be seeing that your endpoint is registered.

## 🙌 Usage

To process the events, you should add the events in your `app/Providers/EventServiceProvider.php`:

```php
use Rennokki\LaravelSnsEvents\Events\SnsNotification;
use Rennokki\LaravelSnsEvents\Events\SnsSubscriptionConfirmation;

...

protected $listen = [
    ...
    SnsNotification::class => [
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
        // $event->payload is the data passed to the event.

        $content = json_decode($event->payload['message']['Message'], true);

        // ...
    }
}
```

## Custom Payload

Altough the event sends an unique array with filled data, you may customize the way the final payload looks like.

By default, the payload looks like this:

```php
use Illuminate\Http\Request;

/**
 * Get the event payload to stream to the event in case
 * AWS sent a notification payload.
 *
 * @param  array  $snsMessage
 * @param  \Illuminate\Http\Request  $request
 * @return array
 */
protected function getNotificationPayload(array $snsMessage, Request $request): array
{
    // $snsMessage is the SNS message from the request body (as array)
    // You may also access the request.

    return [
        'message' => $snsMessage,
        'headers' => $request->headers->all(),
    ];
}
```

While extending the controller, you can replace the `getNotificationPayload` and `getSubscriptionConfirmationPayload` methods with your own:

```php
use Illuminate\Http\Request;

/**
 * Get the event payload to stream to the event in case
 * AWS sent a notification payload.
 *
 * @param  array  $snsMessage
 * @param  \Illuminate\Http\Request  $request
 * @return array
 */
protected function getNotificationPayload(array $snsMessage, Request $request): array
{
    return [
        'message' => $snsMessage,
        'user' => $request->user(),
    ];
}

/**
 * Get the event payload to stream to the event in case
 * AWS sent a subscription confirmation payload.
 *
 * @param  array  $snsMessage
 * @param  \Illuminate\Http\Request  $request
 * @return array
 */
protected function getSubscriptionConfirmationPayload(array $snsMessage, Request $request): array
{
    return [
        'message' => $snsMessage,
        'headers' => $request->headers->all(),
    ];
}
```

This way, you can customize the payloads for both Subscription Confirmation `SnsSubscriptionConfirmation` and the usual Notification `SnsNotification`.

**Remember that after extending the controller, to point the SNS route defined earlier to the new controller.**

## Custom Event Classes

Like the payload, you can also change the event classes to trigger on confirmation or notification.

Simply, replace the following two methods on the extended controller:

```php
/**
 * Get the event class to trigger during subscription confirmation.
 *
 * @return string
 */
protected function getSubscriptionConfirmationEventClass(): string
{
    return CustomSubscriptionConfirmation::class;
}

/**
 * Get the event class to trigger during SNS event.
 *
 * @return string
 */
protected function getNotificationEventClass(): string
{
    return CustomSnsEvent::class;
}
```

**Make sure you also point the custom event classes in the `EventServiceProvider` class as described in the [Usage section](#-usage).**

To avoid any issues, remember to extend the respective, original event classes before the change:

```php
// CustomSnsEvent.php

use Rennokki\LaravelSnsEvents\Events\SnsNotification;

class CustomSnsEvent extends SnsNotification
{
    //
}
```

```php
// CustomSubscriptionConfirmation.php

use Rennokki\LaravelSnsEvents\Events\SnsSubscriptionConfirmation;

class CustomSubscriptionConfirmation extends SnsSubscriptionConfirmation
{
    //
}
```

## Custom Method Hooks

When extending the controller, in case you don't want to handle events, you can handle the methods sent on notification or subscription:

```php
use Rennokki\LaravelSnsEvents\Http\Controllers\SnsController;

class CustomSnsController extends SnsController
{
    /**
     * Handle logic at the controller level on notification.
     *
     * @param  array  $snsMessage
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function onNotification(array $snsMessage, Request $request): void
    {
        //
    }

    /**
     * Handle logic at the controller level on subscription.
     *
     * @param  array  $snsMessage
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function onSubscriptionConfirmation(array $snsMessage, Request $request): void
    {
        //
    }
}
```

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
