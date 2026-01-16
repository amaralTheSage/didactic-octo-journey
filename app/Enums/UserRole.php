<?php

namespace App\Enums;

enum UserRole: string
{
    case INFLUENCER = 'influencer';
    case COMPANY = 'company';
    case AGENCY = 'agency';
    case ADMIN = 'admin';

    case CURATOR = 'curator';
}
