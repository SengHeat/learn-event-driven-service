<?php

namespace App\Messaging\Subscribers;

use App\Messaging\Config\RedisMessagingConfig;
use App\Messaging\Contracts\BrokerInterface;
use App\Messaging\Contracts\EventSubscriberInterface;
use App\Messaging\Enums\SubscriberEvent;
use App\Messaging\Events\SubEvent;
use App\Repositories\DomainEventRepository;
use Illuminate\Support\Facades\Redis;

readonly class RedisBrokerSubscriber implements EventSubscriberInterface, BrokerInterface
{
    public function __construct(
        private RedisMessagingConfig  $config,
        private DomainEventRepository $domainEventRepository,
    ) {}

    public function handle(SubEvent $event): array
    {
        // Store to domain_events
        $this->domainEventRepository->store($event);

        return $event->envelope->data;
    }

    public function consume(SubscriberEvent $event, callable $callback): void
    {
        Redis::connection($this->config->subscribeConnection)
            ->subscribe([$event->value], $callback);
    }
}
