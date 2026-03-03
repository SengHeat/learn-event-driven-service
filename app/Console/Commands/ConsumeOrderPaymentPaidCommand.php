<?php

namespace App\Console\Commands;

use App\Actions\AssignOrderToPurchaseAction;
use App\Messaging\Contracts\BrokerInterface;
use App\Messaging\Contracts\EventSubscriberInterface;
use App\Messaging\Enums\SubscriberEvent;
use App\Messaging\Events\SubEvent;
use App\Repositories\DomainEventRepository;
use Illuminate\Console\Command;

class ConsumeOrderPaymentPaidCommand extends Command
{
    protected $signature   = 'messaging:consume-order-payment-paid';
    protected $description = 'Consume order_payment_paid events from message broker';

    public function __construct(
        private readonly BrokerInterface             $broker,
        private readonly EventSubscriberInterface    $subscriber,
        private readonly AssignOrderToPurchaseAction $assignOrderAction,
        private readonly DomainEventRepository       $domainEventRepository,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Consuming ' . SubscriberEvent::ORDER_PAYMENT_PAID->value . ' events...');

        $this->broker->consume(SubscriberEvent::ORDER_PAYMENT_PAID, function (string $message) {
                try {
                    $event = SubEvent::fromMessage(SubscriberEvent::ORDER_PAYMENT_PAID->value, $message);

                    if (empty($event->envelope->data['order_id'])) {
                        $this->warn('Invalid payload: ' . $message);
                        return;
                    }

                    // 1. Store inbound event (pending)
                    $this->subscriber->handle($event);

                    // 2. Mark as processing
                    $this->domainEventRepository->updateStatus($event->envelope->eventId, 'processing');

                    // 3. Execute business logic
                    $this->assignOrderAction->execute($event);

                    // 4. Mark as processed
                    $this->domainEventRepository->updateStatus($event->envelope->eventId, 'processed');

                    $this->info("Consumed order [{$event->envelope->data['order_id']}] ✅");

                } catch (\Exception $e) {
                    // 5. Mark as failed
                    $this->domainEventRepository->updateStatus($event->envelope->eventId ?? null, 'failed', $e->getMessage());
                    $this->error('Error: ' . $e->getMessage());
                }
            }
        );
    }
}
