<?php

namespace App\Messaging\Config;

readonly class AwsMessagingConfig
{
    public function __construct(
        public string $region,
        public string $endpoint,
        public string $key,
        public string $secret,
        public array  $snsTopics,
        public array  $sqsQueues,
    ) {}

    public static function make(): self
    {
        return new self(
            region:    config('messaging.aws.region',   'ap-southeast-1'),
            endpoint:  config('messaging.aws.endpoint', 'http://localhost:4566'),
            key:       config('messaging.aws.key',      'test'),
            secret:    config('messaging.aws.secret',   'test'),
            snsTopics: [
                'order_status_updated' => config('messaging.sns.topics.order_status_updated'),
            ],
            sqsQueues: [
                'order.payment.paid.v1' => config('messaging.sqs.queues.order_payment_paid'),
            ],
        );
    }
}
