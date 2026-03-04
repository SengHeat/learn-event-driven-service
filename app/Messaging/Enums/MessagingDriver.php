<?php

namespace App\Messaging\Enums;

enum MessagingDriver: string
{
    case REDIS  = 'redis';
    case AWS    = 'aws';
    case KAFKA  = 'kafka';
    case RABBIT = 'rabbit';
}
