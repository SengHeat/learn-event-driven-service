<?php

namespace App\Messaging\Events;

use App\Messaging\DTOs\MessageEnvelope;

readonly class SubEvent
{
    public MessageEnvelope $envelope;

    public function __construct(
        public string $channel,
        public string $rawMessage,
        array                  $payload,
    ) {
        $this->envelope = MessageEnvelope::fromArray($payload);
    }

    public static function fromMessage(string $channel, string $message): self
    {
        return new self(
            channel:    $channel,
            rawMessage: $message,
            payload:    json_decode($message, true) ?? [],
        );
    }
}
