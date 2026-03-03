<?php

namespace App\Messaging\Contracts;

use App\Messaging\Events\SubEvent;

interface EventSubscriberInterface
{
    public function handle(SubEvent $event): array;
}
