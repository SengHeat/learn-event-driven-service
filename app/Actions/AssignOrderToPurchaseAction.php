<?php

namespace App\Actions;

use App\Messaging\Contracts\EventPublisherInterface;
use App\Messaging\Enums\PublisherEvent;
use App\Messaging\Events\SubEvent;

readonly class AssignOrderToPurchaseAction
{
    public function __construct(
        private EventPublisherInterface $publisher,
    ) {}

    public function execute(SubEvent $event): void
    {
        $data = $event->envelope->data;
        // 1. Dispatch back
        $this->publisher->dispatch(PublisherEvent::ORDER_STATUS_UPDATED, [
            'order_id'     => $data['order_id'],
            'purchaser_id' => $data['purchaser_id'],
            'status'       => 'paid',
            'created_by'   => $data['created_by'] ?? null,
            'timestamp'    => now()->toISOString(),
        ]);
    }
}
