<?php

namespace Rennokki\LaravelSnsEvents\Concerns;

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;

trait GeneratesSnsMessages
{
    /**
     * Get the private key to sign the request.
     *
     * @var string
     */
    protected static $privateKey;

    /**
     * The certificate to sign the request.
     *
     * @var string
     */
    protected static $certificate;

    /**
     * An valid certificate URL for test.
     *
     * @var string
     */
    public static $validCertUrl = 'https://sns.us-west-2.amazonaws.com/bar.pem';

    /**
     * Initialize the SSL keys and private keys.
     *
     * @return void
     */
    protected static function initializeSsl(): void
    {
        self::$privateKey = openssl_pkey_new();

        $csr = openssl_csr_new([], self::$privateKey);

        $x509 = openssl_csr_sign($csr, null, self::$privateKey, 1);

        openssl_x509_export($x509, self::$certificate);

        // Deprecated in PHP >= 8.0
        // openssl_x509_free($x509);
    }

    /**
     * Deinitialize the SSL keys.
     *
     * @return void
     */
    protected static function tearDownSsl(): void
    {
        // Deprecated in PHP >= 8.0
        // openssl_pkey_free(self::$privateKey);
    }

    /**
     * Get the signature for the message.
     *
     * @param  string  $stringToSign
     * @return string
     */
    protected function getSignature($stringToSign)
    {
        openssl_sign($stringToSign, $signature, self::$privateKey);

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
            'SigningCertURL' => static::$validCertUrl,
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
            'SigningCertURL' => static::$validCertUrl,
            'UnsubscribeURL' => 'https://sns.us-west-2.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=arn:aws:sns:us-west-2:123456789012:MyTopic:c9135db0-26c4-47ec-8998-413945fb5a96',
        ], $custom);

        $message['Signature'] = $this->getSignature(
            $validator->getStringToSign(new Message($message))
        );

        return $message;
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
            'X-AMZ-SNS-MESSAGE-TYPE' => $message['Type'],
            'X-AMZ-SNS-MESSAGE-ID' => $message['MessageId'],
            'X-AMZ-SNS-TOPIC-ARN' => $message['TopicArn'],
            'X-AMZ-SNS-SUBSCRIPTION-ARN' => "{$message['TopicArn']}:c9135db0-26c4-47ec-8998-413945fb5a96",
        ];
    }
}
