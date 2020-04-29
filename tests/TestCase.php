<?php

namespace Rennokki\LaravelSnsEvents\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Rennokki\LaravelSnsEvents\LaravelSnsEventsServiceProvider::class,
            TestServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application   $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
    }

    /**
     * Get an example subscription payload for testing.
     *
     * @return array
     */
    protected function getSubscriptionConfirmationPayload(): array
    {
        return [
            'Type' => 'SubscriptionConfirmation',
            'MessageId' => '165545c9-2a5c-472c-8df2-7ff2be2b3b1b',
            'Token' => '2336412f37...',
            'TopicArn' => 'arn:aws:sns:us-west-2:123456789012:MyTopic',
            'Message' => 'You have chosen to subscribe to the topic arn:aws:sns:us-west-2:123456789012:MyTopic.\nTo confirm the subscription, visit the SubscribeURL included in this message.',
            'SubscribeURL' => 'https://example.com',
            'Timestamp' => '2012-04-26T20:45:04.751Z',
            'SignatureVersion' => '1',
            'Signature' => 'EXAMPLEpH+DcEwjAPg8O9mY8dReBSwksfg2S7WKQcikcNKWLQjwu6A4VbeS0QHVCkhRS7fUQvi2egU3N858fiTDN6bkkOxYDVrY0Ad8L10Hs3zH81mtnPk5uvvolIC1CXGu43obcgFxeL3khZl8IKvO61GWB6jI9b5+gLPoBc1Q=',
            'SigningCertURL' => 'https://sns.us-west-2.amazonaws.com/SimpleNotificationService-f3ecfb7224c7233fe7bb5f59f96de52f.pem'
        ];
    }

    /**
     * Get an example notification payload for testing.
     *
     * @param  string  $message
     * @return array
     */
    protected function getNotificationPayload($message = ''): array
    {
        return [
            'Type' => 'Notification',
            'MessageId' => '22b80b92-fdea-4c2c-8f9d-bdfb0c7bf324',
            'TopicArn' => 'arn:aws:sns:us-west-2:123456789012:MyTopic',
            'Subject' => 'My First Message',
            'Message' => "{$message}",
            'Timestamp' => '2012-05-02T00:54:06.655Z',
            'SignatureVersion' => '1',
            'Signature' => 'EXAMPLEw6JRN...',
            'SigningCertURL' => 'https://sns.us-west-2.amazonaws.com/SimpleNotificationService-f3ecfb7224c7233fe7bb5f59f96de52f.pem',
            'UnsubscribeURL' => 'https://sns.us-west-2.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=arn:aws:sns:us-west-2:123456789012:MyTopic:c9135db0-26c4-47ec-8998-413945fb5a96'
        ];
    }
}
