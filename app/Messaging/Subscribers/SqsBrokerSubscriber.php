<?php

namespace App\Messaging\Subscribers;

use App\Messaging\Config\AwsMessagingConfig;
use App\Messaging\Contracts\BrokerInterface;
use App\Messaging\Contracts\EventSubscriberInterface;
use App\Messaging\Enums\SubscriberEvent;
use App\Messaging\Events\SubEvent;
use App\Repositories\DomainEventRepository;
use Aws\Sqs\SqsClient;

readonly class SqsBrokerSubscriber implements EventSubscriberInterface, BrokerInterface
{
    public function __construct(
        private AwsMessagingConfig    $config,
        private SqsClient             $sqsClient,
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
        $queueUrl = $this->config->sqsQueues[$event->value];

        while (true) {
            $result = $this->sqsClient->receiveMessage([
                'QueueUrl'            => $queueUrl,
                'MaxNumberOfMessages' => 10,
                'WaitTimeSeconds'     => 10,
            ]);

            foreach ($result->get('Messages') ?? [] as $message) {
                $callback($message['Body']);

                // Delete after processing ✅
                $this->sqsClient->deleteMessage([
                    'QueueUrl'      => $queueUrl,
                    'ReceiptHandle' => $message['ReceiptHandle'],
                ]);
            }
        }
    }
}
