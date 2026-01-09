<?php

namespace App\Enums;

enum CancelBy: string
{
    case Rider = 'rider';
    case Driver = 'driver';
    case System = 'system';
}
