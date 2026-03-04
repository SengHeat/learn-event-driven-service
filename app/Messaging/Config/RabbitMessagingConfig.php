<?php

namespace App\Messaging\Config;

readonly class RabbitMessagingConfig
{
    public function __construct(
        public string $host,
        public int    $port,
        public string $username,
        public string $password,
        public string $vhost,
        public string $exchange,
    ) {}

    public static function make(): self
    {
        return new self(
            host:     config('messaging.rabbit.host',     'localhost'),
            port:     config('messaging.rabbit.port',     5672),
            username: config('messaging.rabbit.username', 'guest'),
            password: config('messaging.rabbit.password', 'guest'),
            vhost:    config('messaging.rabbit.vhost',    '/'),
            exchange: config('messaging.rabbit.exchange', 'purchase.service'),
        );
    }
}
