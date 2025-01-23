<?php

namespace App\Enums;

use ArtcoreSociety\LaravelSupport\Traits\EnumSupport;

enum RoleEnum: string
{
    use EnumSupport;

    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case VISITOR = 'visitor';
}
