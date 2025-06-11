<?php

declare(strict_types=1);

namespace App\Enums;

enum TransportTypeEnum: string
{
    case CAR = 'car';
    case PLANE = 'plane';
    case TRAIN = 'train';
    case BOAT = 'boat';
    case TRUCK = 'truck';
}
