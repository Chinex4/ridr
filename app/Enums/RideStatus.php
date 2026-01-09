<?php

namespace App\Enums;

enum RideStatus: string
{
    case Requested = 'requested';
    case Accepted = 'accepted';
    case Arrived = 'arrived';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
