<?php

namespace App\Messaging\DTOs;

readonly class MessageEnvelope
{
    public function __construct(
        public string $eventId,
        public string $version,
        public string $eventType,
        public string $occurredAt,
        public string $producer,
        public string $aggregateId,
        public string $aggregateType,
        public array  $headers,
        public array  $data,
        public int    $dataVersion = 1,
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            eventId:       $payload['event_id'],
            version:       $payload['version']        ?? 'v1',
            eventType:     $payload['event_type'],
            occurredAt:    $payload['occurred_at']    ?? now()->toISOString(),
            producer:      $payload['producer']       ?? 'unknown',
            aggregateId:   $payload['aggregate_id'],
            aggregateType: $payload['aggregate_type'] ?? 'Order',
            headers:       $payload['headers']        ?? [],
            data:          $payload['data'],
            dataVersion:   $payload['data_version']   ?? 1,   // ✅ new
        );
    }

    public function toArray(): array
    {
        return [
            'event_id'       => $this->eventId,
            'version'        => $this->version,
            'event_type'     => $this->eventType,
            'occurred_at'    => $this->occurredAt,
            'producer'       => $this->producer,
            'aggregate_id'   => $this->aggregateId,
            'aggregate_type' => $this->aggregateType,
            'headers'        => $this->headers,
            'data'           => $this->data,
            'data_version'   => $this->dataVersion,   // ✅ new
        ];
    }
}
