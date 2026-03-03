<?php

namespace App\Messaging\Enums;

enum PublisherEvent: string
{
    case ORDER_STATUS_UPDATED = 'order.status.updated.v1';
}
