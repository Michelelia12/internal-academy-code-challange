<?php

declare(strict_types=1);

namespace App\Enums;

enum RegistrationStatus: string
{
    case Confirmed = 'confirmed';
    case Waiting = 'waiting';
}
