<?php

namespace App\Messaging\Enums;

enum SubscriberEvent: string
{
    case ORDER_PAYMENT_PAID = 'order.payment.paid.v1';
}
