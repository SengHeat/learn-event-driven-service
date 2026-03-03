<?php

namespace App\Repositories\Contracts;

use App\Messaging\DTOs\MessageEnvelope;
use App\Messaging\Enums\PublisherEvent;
use App\Messaging\Events\SubEvent;

interface DomainEventRepositoryInterface
{
    // SUB → inbound
    public function store(SubEvent $event): void;

    // PUB → outbound
    public function storeFromPublisher(PublisherEvent $event, MessageEnvelope $envelope): void;

    // Update status
    public function updateStatus(string $eventId, string $status, ?string $errorMessage = null): void;
}
