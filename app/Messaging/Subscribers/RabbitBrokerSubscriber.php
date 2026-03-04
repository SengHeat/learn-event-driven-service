<?php

namespace App\Messaging\Subscribers;

use App\Messaging\Config\RabbitMessagingConfig;
use App\Messaging\Contracts\BrokerInterface;
use App\Messaging\Contracts\EventSubscriberInterface;
use App\Messaging\Enums\SubscriberEvent;
use App\Messaging\Events\SubEvent;
use App\Repositories\DomainEventRepository;
use PhpAmqpLib\Connection\AMQPStreamConnection;

readonly class RabbitBrokerSubscriber implements EventSubscriberInterface, BrokerInterface
{
    public function __construct(
        private RabbitMessagingConfig $config,
        private DomainEventRepository $domainEventRepository,
    ) {}

    public function handle(SubEvent $event): array
    {
        $this->domainEventRepository->store($event);

        return $event->envelope->data;
    }

    public function consume(SubscriberEvent $event, callable $callback): void
    {
        $connection = new AMQPStreamConnection(
            $this->config->host,
            $this->config->port,
            $this->config->username,
            $this->config->password,
            $this->config->vhost,
        );

        $channel  = $connection->channel();
        $queue    = $event->value;

        $channel->exchange_declare($this->config->exchange, 'topic', false, true, false);
        $channel->queue_declare($queue, false, true, false, false);
        $channel->queue_bind($queue, $this->config->exchange, $event->value);
        $channel->basic_consume($queue, '', false, true, false, false,
            fn($msg) => $callback($msg->body)
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }
}
