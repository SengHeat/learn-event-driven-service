<?php

namespace App\Messaging\Config;

readonly class KafkaMessagingConfig
{
    public function __construct(
        public string $brokers,
        public string $groupId,
        public string $securityProtocol,
        public string $saslMechanism,
        public string $saslUsername,
        public string $saslPassword,
    ) {}

    public static function make(): self
    {
        return new self(
            brokers:          config('messaging.kafka.brokers',           'localhost:9092'),
            groupId:          config('messaging.kafka.group_id',          'purchase-service'),
            securityProtocol: config('messaging.kafka.security_protocol', 'plaintext'),
            saslMechanism:    config('messaging.kafka.sasl_mechanism',    'plain'),
            saslUsername:     config('messaging.kafka.sasl_username',     ''),
            saslPassword:     config('messaging.kafka.sasl_password',     ''),
        );
    }
}
