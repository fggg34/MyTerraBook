<?php

namespace App\Enums;

enum GuestHouseType: string
{
    case Room = 'room';
    case Apartment = 'apartment';
    case Villa = 'villa';
    case Cottage = 'cottage';
    case Chalet = 'chalet';
    case Studio = 'studio';
}
