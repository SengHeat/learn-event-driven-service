<?php

namespace App\Messaging\Publishers;

use App\Messaging\Config\RedisMessagingConfig;
use App\Messaging\Contracts\EventPublisherInterface;
use App\Messaging\DTOs\MessageEnvelope;
use App\Messaging\Enums\PublisherEvent;
use App\Repositories\DomainEventRepository;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

readonly class RedisBrokerPublisher implements EventPublisherInterface
{
    public function __construct(
        private RedisMessagingConfig  $config,
        private DomainEventRepository $domainEventRepository,
    ) {}

    public function dispatch(PublisherEvent $event, array $data): void
    {
        $envelope = $this->buildEnvelope($event, $data);

        // 1. Publish to Redis
        Redis::connection($this->config->publishConnection)->publish(
            $event->value,
            json_encode($envelope->toArray())
        );

        // 2. Store to domain_events
        $this->domainEventRepository->storeFromPublisher($event, $envelope);
    }

    public function buildEnvelope(PublisherEvent $event, array $data): MessageEnvelope
    {
        return new MessageEnvelope(
            eventId:       (string) Str::uuid(),
            version:       'v1',
            eventType:     strtolower($event->name),
            occurredAt:    now()->toISOString(),
            producer:      'purchase-service',
            aggregateId:   (string) $data['order_id'],
            aggregateType: 'Order',
            headers:       [],
            data:          $data,
        );
    }
}
