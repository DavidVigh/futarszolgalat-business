<?php

namespace App\Policies;

use App\Models\Rendeles;
use App\Models\User;

class RendelesPolicy
{
    /** A user only sees their own orders (BL 2); an admin sees every order. */
    public function view(User $user, Rendeles $rendeles): bool
    {
        return $user->isAdmin() || $rendeles->felhasznalo_id === $user->id;
    }

    /** Only the customer may change, cancel or rate their own order (BL 2, 5). */
    public function update(User $user, Rendeles $rendeles): bool
    {
        return $rendeles->felhasznalo_id === $user->id;
    }
}
