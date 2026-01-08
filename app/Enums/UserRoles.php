<?php

namespace App\Enums;

enum UserRoles: string
{
    case Influencer = 'influencer';
    case Company = 'company';
    case Agency = 'agency';
    case Admin = 'admin';
}
