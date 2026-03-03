<?php

namespace App\Messaging\Contracts;

use App\Messaging\DTOs\MessageEnvelope;
use App\Messaging\Enums\PublisherEvent;

interface EventPublisherInterface
{
    public function dispatch(PublisherEvent $event, array $data): void;
    public function buildEnvelope(PublisherEvent $event, array $data): MessageEnvelope;
}
