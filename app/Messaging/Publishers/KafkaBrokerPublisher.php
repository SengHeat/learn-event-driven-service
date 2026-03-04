<?php

namespace App\Messaging\Publishers;

use App\Messaging\Config\KafkaMessagingConfig;
use App\Messaging\Contracts\EventPublisherInterface;
use App\Messaging\DTOs\MessageEnvelope;
use App\Messaging\Enums\PublisherEvent;
use App\Repositories\DomainEventRepository;
use Illuminate\Support\Str;
use RdKafka\Conf;
use RdKafka\Producer;

readonly class KafkaBrokerPublisher implements EventPublisherInterface
{
    public function __construct(
        private KafkaMessagingConfig  $config,
        private DomainEventRepository $domainEventRepository,
    ) {}

    public function dispatch(PublisherEvent $event, array $data): void
    {
        $envelope = $this->buildEnvelope($event, $data);

        $conf = new Conf();
        $conf->set('metadata.broker.list', $this->config->brokers);

        $producer = new Producer($conf);
        $topic    = $producer->newTopic($event->value);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($envelope->toArray()));
        $producer->flush(3000);

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
