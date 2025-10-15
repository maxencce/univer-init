<?php

namespace App\Enum;

enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case GESEC = 'GESEC';
    case ADHERENT = 'ADHERENT';
} 