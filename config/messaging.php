<?php

return [
    'driver' => env('MESSAGING_DRIVER', 'redis'),

    'aws' => [
        'region'   => env('AWS_DEFAULT_REGION', 'ap-southeast-1'),
        'endpoint' => env('AWS_ENDPOINT'),
        'key'      => env('AWS_ACCESS_KEY_ID'),
        'secret'   => env('AWS_SECRET_ACCESS_KEY'),
    ],

    'sns' => [
        'topics' => [
            'order_status_updated' => env('SNS_TOPIC_ORDER_STATUS_UPDATED'),
        ],
    ],

    'sqs' => [
        'queues' => [
            'order_payment_paid' => env('SQS_QUEUE_ORDER_PAYMENT_PAID'),
        ],
    ],
];
