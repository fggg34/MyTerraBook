<?php

namespace App\Enums;

enum BlogPostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
