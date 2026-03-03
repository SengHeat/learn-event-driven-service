<?php

namespace App\Messaging\Contracts;

use App\Messaging\Enums\SubscriberEvent;

interface BrokerInterface
{
    public function consume(SubscriberEvent $event, callable $callback): void;
}
