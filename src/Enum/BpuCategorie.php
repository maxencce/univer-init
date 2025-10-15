<?php

namespace App\Enum;

enum BpuCategorie: string
{
    case PLOMBERIE_SANITAIRES = 'Plomberie/Sanitaires';
    case CHAUFFAGE = 'Chauffage';
    case CLIMATISATION = 'Climatisation';
    case VENTILATION = 'Ventilation';
    case COURANTS_FORTS = 'Courants forts';
    case COURANTS_FAIBLES = 'Courants faibles';
    case GTB_GTC = 'GTB/GTC';
    case DESENFUMAGE_SECURITE_INCENDIE = 'Désenfumage/Sécurité incendie';
} 