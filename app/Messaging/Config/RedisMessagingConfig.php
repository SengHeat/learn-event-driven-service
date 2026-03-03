<?php

namespace App\Messaging\Config;

readonly class RedisMessagingConfig
{
    public function __construct(
        public string $subscribeConnection = 'pubsub',
        public string $publishConnection   = 'default',
        public int    $readTimeout         = -1,
    ) {}

    public static function make(): self
    {
        return new self(
            subscribeConnection: 'pubsub',
            publishConnection:   'default',
            readTimeout:         -1,
        );
    }
}
