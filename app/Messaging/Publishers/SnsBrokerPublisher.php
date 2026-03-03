<?php

namespace App\Messaging\Publishers;

use App\Messaging\Config\AwsMessagingConfig;
use App\Messaging\Contracts\EventPublisherInterface;
use App\Messaging\DTOs\MessageEnvelope;
use App\Messaging\Enums\PublisherEvent;
use App\Repositories\DomainEventRepository;
use Aws\Sns\SnsClient;
use Illuminate\Support\Str;

readonly class SnsBrokerPublisher implements EventPublisherInterface
{
    public function __construct(
        private AwsMessagingConfig    $config,
        private SnsClient             $snsClient,
        private DomainEventRepository $domainEventRepository,
    ) {}

    public function dispatch(PublisherEvent $event, array $data): void
    {
        $envelope = $this->buildEnvelope($event, $data);

        // 1. Publish to SNS
        $this->snsClient->publish([
            'TopicArn' => $this->config->snsTopics[strtolower($event->name)],
            'Message'  => json_encode($envelope->toArray()),
        ]);

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
