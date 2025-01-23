<?php

namespace App\Traits;

use App\Models\User;
use Filament\Panel;

/** @mixin User */
trait HasFilamentPanels
{
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_photo_url;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }
}
