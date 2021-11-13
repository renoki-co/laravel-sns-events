<?php

namespace Rennokki\LaravelSnsEvents\Concerns;

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;

trait GeneratesSnsMessages
{
    /**
     * Get the private key to sign the request for SNS.
     *
     * @var string
     */
    protected static $snsPrivateKey;

    /**
     * The certificate to sign the request for SNS.
     *
     * @var string
     */
    protected static $snsCertificate;

    /**
     * An valid certificate URL to test SNS.
     *
     * @var string
     */
    public static $snsValidCertUrl = 'https://sns.us-west-2.amazonaws.com/bar.pem';

    /**
     * Initialize the SSL keys and private keys.
     *
     * @return void
     */
    protected static function initializeSsl(): void
    {
        static::$snsPrivateKey = openssl_pkey_new();

        $csr = openssl_csr_new([], static::$snsPrivateKey);

        $x509 = openssl_csr_sign($csr, null, static::$snsPrivateKey, 1);

        openssl_x509_export($x509, static::$snsCertificate);
    }

    /**
     * Get the signature for the message.
     *
     * @param  string  $stringToSign
     * @return string
     */
    protected function getSignature($stringToSign)
    {
        static::initializeSsl();

        openssl_sign($stringToSign, $signature, static::$snsPrivateKey);

        return base64_encode($signature);
    }

    /**
     * Get an example subscription payload for testing.
     *
     * @param  array  $custom
     * @return array
     */
    protected function getSubscriptionConfirmationPayload(array $custom = []): array
    {
        $validator = new MessageValidator;

        $message = array_merge([
            'Type' => 'SubscriptionConfirmation',
            'MessageId' => '165545c9-2a5c-472c-8df2-7ff2be2b3b1b',
            'Token' => '2336412f37...',
            'TopicArn' => 'arn:aws:sns:us-west-2:123456789012:MyTopic',
            'Message' => 'You have chosen to subscribe to the topic arn:aws:sns:us-west-2:123456789012:MyTopic.\nTo confirm the subscription, visit the SubscribeURL included in this message.',
            'SubscribeURL' => 'https://example.com',
            'Timestamp' => now()->toDateTimeString(),
            'SignatureVersion' => '1',
            'Signature' => true,
            'SigningCertURL' => static::$snsValidCertUrl,
        ], $custom);

        $message['Signature'] = $this->getSignature(
            $validator->getStringToSign(new Message($message))
        );

        return $message;
    }

    /**
     * Get an example notification payload for testing.
     *
     * @param  array  $payload
     * @param  array  $custom
     * @return array
     */
    protected function getNotificationPayload(array $payload = [], array $custom = []): array
    {
        $validator = new MessageValidator;

        $payload = json_encode($payload);

        $message = array_merge([
            'Type' => 'Notification',
            'MessageId' => '22b80b92-fdea-4c2c-8f9d-bdfb0c7bf324',
            'TopicArn' => 'arn:aws:sns:us-west-2:123456789012:MyTopic',
            'Subject' => 'My First Message',
            'Message' => "{$payload}",
            'Timestamp' => now()->toDateTimeString(),
            'SignatureVersion' => '1',
            'Token' => '2336412f37...',
            'Signature' => true,
            'SigningCertURL' => static::$snsValidCertUrl,
            'UnsubscribeURL' => 'https://sns.us-west-2.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=arn:aws:sns:us-west-2:123456789012:MyTopic:c9135db0-26c4-47ec-8998-413945fb5a96',
        ], $custom);

        $message['Signature'] = $this->getSignature(
            $validator->getStringToSign(new Message($message))
        );

        return $message;
    }

    /**
     * Send the SNS-signed message to the given URL.
     *
     * @param  string  $url
     * @param  array  $snsPayload
     * @return \Illuminate\Testing\TestResponse
     */
    protected function sendSnsMessage($url, array $snsPayload = [])
    {
        /** @var \Illuminate\Foundation\Testing\Concerns\MakesHttpRequests&\Rennokki\LaravelSnsEvents\Concerns\GeneratesSnsMessages $this */
        return $this->withHeaders($this->getHeadersForMessage($snsPayload))
            ->withHeaders(['X-Sns-Testing-Certificate' => static::$snsCertificate])
            ->json('POST', $url, $snsPayload);
    }

    /**
     * Get the right headers for a SNS message.
     *
     * @param  array  $message
     * @return array
     */
    protected function getHeadersForMessage(array $message): array
    {
        return [
            'X-AMZ-SNS-MESSAGE-TYPE' => $message['Type'] ?? null,
            'X-AMZ-SNS-MESSAGE-ID' => $message['MessageId'] ?? null,
            'X-AMZ-SNS-TOPIC-ARN' => $message['TopicArn'] ?? null,
            'X-AMZ-SNS-SUBSCRIPTION-ARN' => ($message['TopicArn'] ?? null).':c9135db0-26c4-47ec-8998-413945fb5a96',
        ];
    }
}
