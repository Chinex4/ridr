<?php

namespace App\Enums;

enum Role: string
{
    case Rider = 'rider';
    case Driver = 'driver';
    case Admin = 'admin';
}
