<?php

namespace App\Messaging\Subscribers;

use App\Messaging\Config\KafkaMessagingConfig;
use App\Messaging\Contracts\BrokerInterface;
use App\Messaging\Contracts\EventSubscriberInterface;
use App\Messaging\Enums\SubscriberEvent;
use App\Messaging\Events\SubEvent;
use App\Repositories\DomainEventRepository;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;

readonly class KafkaBrokerSubscriber implements EventSubscriberInterface, BrokerInterface
{
    public function __construct(
        private KafkaMessagingConfig  $config,
        private DomainEventRepository $domainEventRepository,
    ) {}

    public function handle(SubEvent $event): array
    {
        $this->domainEventRepository->store($event);

        return $event->envelope->data;
    }

    public function consume(SubscriberEvent $event, callable $callback): void
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', $this->config->brokers);
        $conf->set('group.id',             $this->config->groupId);
        $conf->set('auto.offset.reset',    'earliest');

        $consumer = new KafkaConsumer($conf);
        $consumer->subscribe([$event->value]);

        while (true) {
            $message = $consumer->consume(1000);

            if ($message->err === RD_KAFKA_RESP_ERR_NO_ERROR) {
                $callback($message->payload);
            }
        }
    }
}
