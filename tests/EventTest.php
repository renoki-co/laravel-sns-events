<?php

namespace Rennokki\LaravelSnsEvents\Tests;

use Illuminate\Support\Facades\Event;
use Rennokki\LaravelSnsEvents\Events\SnsEvent;
use Rennokki\LaravelSnsEvents\Events\SnsSubscriptionConfirmation;

class EventTest extends TestCase
{
    public function test_no_event_triggering_on_bad_request()
    {
        Event::fake();

        $this
            ->json('GET', route('sns'))
            ->assertSee('OK');

        Event::assertNotDispatched(SnsEvent::class);
        Event::assertNotDispatched(SnsSubscriptionConfirmation::class);
    }

    public function test_subscription_confirmation()
    {
        Event::fake();

        $this
            ->withHeaders([
                'x-test-header' => 1,
            ])
            ->json('POST', route('sns'), $this->getSubscriptionConfirmationPayload())
            ->assertSee('OK');

        Event::assertNotDispatched(SnsEvent::class);

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

        $payload = json_encode([
            'test' => 1,
            'sns' => true,
        ]);

        $this
            ->withHeaders([
                'x-test-header' => 1,
            ])
            ->json('POST', route('sns'), $this->getNotificationPayload($payload))
            ->assertSee('OK');

        Event::assertNotDispatched(SnsSubscriptionConfirmation::class);

        Event::assertDispatched(SnsEvent::class, function ($event) {
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
}
