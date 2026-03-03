<?php

namespace App\Repositories;

use App\Messaging\DTOs\MessageEnvelope;
use App\Messaging\Enums\PublisherEvent;
use App\Messaging\Events\SubEvent;
use App\Repositories\Contracts\DomainEventRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DomainEventRepository implements DomainEventRepositoryInterface
{
    // SUB → inbound
    public function store(SubEvent $event): void
    {
        $e = $event->envelope;

        $existing = DB::table('domain_events')
            ->where('event_id', $e->eventId)
            ->first();

        if ($existing) {
            DB::table('domain_events')
                ->where('event_id', $e->eventId)
                ->update([
                    'data_version' => $existing->data_version + 1,
                    'data'         => json_encode($e->data),
                    'updated_at'   => now(),
                ]);
            return;
        }

        DB::table('domain_events')->insert([
            'event_id'       => $e->eventId,
            'version'        => $e->version,
            'event_type'     => $e->eventType,
            'occurred_at'    => $e->occurredAt,
            'producer'       => $e->producer,
            'aggregate_id'   => $e->aggregateId,
            'aggregate_type' => $e->aggregateType,
            'headers'        => json_encode($e->headers),
            'data'           => json_encode($e->data),
            'data_version'   => $e->dataVersion,
            'direction'      => 'subscribe',
            'status'         => 'pending',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    // PUB → outbound
    public function storeFromPublisher(PublisherEvent $event, MessageEnvelope $envelope): void
    {
        $existing = DB::table('domain_events')
            ->where('event_id', $envelope->eventId)
            ->first();

        if ($existing) {
            DB::table('domain_events')
                ->where('event_id', $envelope->eventId)
                ->update([
                    'data_version' => $existing->data_version + 1,
                    'data'         => json_encode($envelope->data),
                    'updated_at'   => now(),
                ]);
            return;
        }

        DB::table('domain_events')->insert([
            'event_id'       => $envelope->eventId,
            'version'        => $envelope->version,
            'event_type'     => $envelope->eventType,
            'occurred_at'    => $envelope->occurredAt,
            'producer'       => $envelope->producer,
            'aggregate_id'   => $envelope->aggregateId,
            'aggregate_type' => $envelope->aggregateType,
            'headers'        => json_encode($envelope->headers),
            'data'           => json_encode($envelope->data),
            'data_version'   => $envelope->dataVersion,
            'direction'      => 'publish',
            'status'         => 'processed',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }
    // Update status
    public function updateStatus(string $eventId, string $status, ?string $errorMessage = null): void
    {
        DB::table('domain_events')
            ->where('event_id', $eventId)
            ->update([
                'status'        => $status,
                'error_message' => $errorMessage,
                'updated_at'    => now(),
            ]);
    }
}
