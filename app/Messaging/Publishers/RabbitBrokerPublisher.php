<?php

namespace App\Messaging\Publishers;

use App\Messaging\Config\RabbitMessagingConfig;
use App\Messaging\Contracts\EventPublisherInterface;
use App\Messaging\DTOs\MessageEnvelope;
use App\Messaging\Enums\PublisherEvent;
use App\Repositories\DomainEventRepository;
use Exception;
use Illuminate\Support\Str;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

readonly class RabbitBrokerPublisher implements EventPublisherInterface
{
    public function __construct(
        private RabbitMessagingConfig $config,
        private DomainEventRepository $domainEventRepository,
    ) {}

    /**
     * @throws Exception
     */
    public function dispatch(PublisherEvent $event, array $data): void
    {
        $envelope   = $this->buildEnvelope($event, $data);
        $connection = new AMQPStreamConnection(
            $this->config->host,
            $this->config->port,
            $this->config->username,
            $this->config->password,
            $this->config->vhost,
        );

        $channel = $connection->channel();
        $channel->exchange_declare($this->config->exchange, 'topic', false, true, false);
        $channel->basic_publish(
            new AMQPMessage(json_encode($envelope->toArray()), ['delivery_mode' => 2]),
            $this->config->exchange,
            $event->value
        );

        $channel->close();
        $connection->close();

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
