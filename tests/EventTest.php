<?php

namespace Rennokki\LaravelSnsEvents\Tests;

use Illuminate\Support\Facades\Event;
use Rennokki\LaravelSnsEvents\Events\SnsNotification;
use Rennokki\LaravelSnsEvents\Events\SnsSubscriptionConfirmation;
use Rennokki\LaravelSnsEvents\Tests\Controllers\CustomSnsController;
use Rennokki\LaravelSnsEvents\Tests\Controllers\SnsController;
use Rennokki\LaravelSnsEvents\Tests\Events\CustomSnsEvent;
use Rennokki\LaravelSnsEvents\Tests\Events\CustomSubscriptionConfirmation;

class EventTest extends TestCase
{
    public function test_no_event_triggering_on_bad_request()
    {
        Event::fake();

        $this->sendSnsMessage(route('sns'))->assertSee('OK');

        Event::assertNotDispatched(SnsNotification::class);
        Event::assertNotDispatched(SnsSubscriptionConfirmation::class);

        $this->sendSnsMessage(route('sns'))->assertSee('OK');

        Event::assertNotDispatched(SnsNotification::class);
        Event::assertNotDispatched(SnsSubscriptionConfirmation::class);

        $payload = $this->getSubscriptionConfirmationPayload();

        $this->sendSnsMessage(route('sns'))->assertSee('OK');

        Event::assertNotDispatched(SnsNotification::class);
        Event::assertNotDispatched(SnsSubscriptionConfirmation::class);
    }

    public function test_subscription_confirmation()
    {
        Event::fake();
        $this->mockCallSubscribeUrlResult(true);

        $payload = $this->getSubscriptionConfirmationPayload();

        $this->withHeaders(['x-test-header' => 1])
            ->sendSnsMessage(route('sns'), $payload)
            ->assertSee('OK');

        Event::assertNotDispatched(SnsNotification::class);

        Event::assertDispatched(SnsSubscriptionConfirmation::class, function ($event) {
            $this->assertTrue(
                isset($event->payload['headers']['x-test-header'])
            );

            return true;
        });
    }

    public function test_subscription_not_confirmated_if_subscription_url_call_fails()
    {
        Event::fake();
        $this->mockCallSubscribeUrlResult(false);

        $payload = $this->getSubscriptionConfirmationPayload();

        $this->withHeaders(['x-test-header' => 1])
            ->sendSnsMessage(route('sns'), $payload)
            ->assertSee('OK');

        Event::assertNotDispatched(SnsNotification::class);
        Event::assertNotDispatched(SnsSubscriptionConfirmation::class);
    }

    public function test_notification_confirmation()
    {
        Event::fake();

        $payload = $this->getNotificationPayload([
            'test' => 1,
            'sns' => true,
        ]);

        $this->withHeaders(['x-test-header' => 1])
            ->sendSnsMessage(route('sns'), $payload)
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
        $this->mockCustomCallSubscribeUrlResult(true);

        $payload = $this->getSubscriptionConfirmationPayload();

        $this->withHeaders(['x-test-header' => 1])
            ->sendSnsMessage(route('custom-sns', ['test' => 'some-string']), $payload)
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

        $this->withHeaders(['x-test-header' => 1])
            ->sendSnsMessage(route('custom-sns', ['test' => 'some-string']), $payload)
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

    private function mockCallSubscribeUrlResult(bool $result)
    {
        $this->partialMock(SnsController::class)
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('callSubscribeUrl')
            ->with('https://example.com')
            ->once()
            ->andReturn($result);
    }

    private function mockCustomCallSubscribeUrlResult(bool $result)
    {
        $this->partialMock(CustomSnsController::class)
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('callSubscribeUrl')
            ->with('https://example.com')
            ->once()
            ->andReturn($result);
    }
}
