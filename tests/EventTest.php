<?php

namespace Rennokki\LaravelSnsEvents\Tests;

use Illuminate\Support\Facades\Event;
use Rennokki\LaravelSnsEvents\Events\SnsNotification;
use Rennokki\LaravelSnsEvents\Events\SnsSubscriptionConfirmation;
use Rennokki\LaravelSnsEvents\Tests\Events\CustomSnsEvent;
use Rennokki\LaravelSnsEvents\Tests\Events\CustomSubscriptionConfirmation;

class EventTest extends TestCase
{
    public function test_no_event_triggering_on_bad_request()
    {
        Event::fake();

        $this->json('GET', route('sns'))
            ->assertSee('OK');

        Event::assertNotDispatched(SnsNotification::class);
        Event::assertNotDispatched(SnsSubscriptionConfirmation::class);

        $this->json('GET', route('sns', ['certificate' => static::$certificate]))
            ->assertSee('OK');

        Event::assertNotDispatched(SnsNotification::class);
        Event::assertNotDispatched(SnsSubscriptionConfirmation::class);

        $payload = $this->getSubscriptionConfirmationPayload();

        $this->withHeaders($this->getHeadersForMessage($payload))
            ->json('GET', route('sns', ['certificate' => static::$certificate]))
            ->assertSee('OK');

        Event::assertNotDispatched(SnsNotification::class);
        Event::assertNotDispatched(SnsSubscriptionConfirmation::class);
    }

    public function test_subscription_confirmation()
    {
        Event::fake();

        $payload = $this->getSubscriptionConfirmationPayload();

        $this->withHeaders(array_merge($this->getHeadersForMessage($payload), [
            'x-test-header' => 1,
        ]))
        ->json('POST', route('sns', ['certificate' => static::$certificate]), $payload)
        ->assertSee('OK');

        Event::assertNotDispatched(SnsNotification::class);

        Event::assertDispatched(SnsSubscriptionConfirmation::class, function ($event) {
            $this->assertTrue(
                isset($event->payload['headers']['x-test-header'])
            );

            return true;
        });
    }

    public function test_notification_confirmation()
    {
        Event::fake();

        $payload = $this->getNotificationPayload([
            'test' => 1,
            'sns' => true,
        ]);

        $this->withHeaders(array_merge($this->getHeadersForMessage($payload), [
            'x-test-header' => 1,
        ]))
        ->json('POST', route('sns', ['certificate' => static::$certificate]), $payload)
        ->assertSee('OK');

        Event::assertNotDispatched(SnsSubscriptionConfirmation::class);

        Event::assertDispatched(SnsNotification::class, function ($event) {
            $this->assertTrue(
                isset($event->payload['headers']['x-test-header'])
            );

            $message = json_decode(
                $event->payload['message']['Message'], true
            );

            $this->assertEquals(1, $message['test']);
            $this->assertEquals(true, $message['sns']);

            return true;
        });
    }

    public function test_custom_controller_confirmation()
    {
        Event::fake();

        $payload = $this->getSubscriptionConfirmationPayload();

        $this->withHeaders(array_merge($this->getHeadersForMessage($payload), [
            'x-test-header' => 1,
        ]))
        ->json('POST', route('custom-sns', ['test' => 'some-string', 'certificate' => static::$certificate]), $payload)
        ->assertSee('OK');

        Event::assertNotDispatched(CustomSnsEvent::class);

        Event::assertDispatched(CustomSubscriptionConfirmation::class, function ($event) {
            $this->assertEquals(
                'some-string', $event->payload['confirmation_test']
            );

            $this->assertFalse(
                isset($event->payload['headers'])
            );

            return true;
        });
    }

    public function test_custom_controller_notification()
    {
        Event::fake();

        $payload = $this->getNotificationPayload([
            'test' => 1,
            'sns' => true,
        ]);

        $this->withHeaders(array_merge($this->getHeadersForMessage($payload), [
            'x-test-header' => 1,
        ]))
        ->json('POST', route('custom-sns', ['test' => 'some-string', 'certificate' => static::$certificate]), $payload)
        ->assertSee('OK');

        Event::assertNotDispatched(CustomSubscriptionConfirmation::class);

        Event::assertDispatched(CustomSnsEvent::class, function ($event) {
            $this->assertEquals(
                'some-string', $event->payload['test']
            );

            $this->assertFalse(
                isset($event->payload['headers'])
            );

            $message = json_decode(
                $event->payload['message']['Message'], true
            );

            $this->assertEquals(1, $message['test']);
            $this->assertEquals(true, $message['sns']);

            return true;
        });
    }
}
