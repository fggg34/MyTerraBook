<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Designer = 'designer';
    case Customer = 'customer';
    case Host = 'host';
}
