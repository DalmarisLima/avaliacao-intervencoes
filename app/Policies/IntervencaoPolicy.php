<?php

namespace App\Policies;

use App\Models\Intervencao;
use App\Models\User;

class IntervencaoPolicy
{
    public function view(User $user, Intervencao $intervencao): bool
    {
        return (int) $intervencao->user_id === (int) $user->id;
    }

    public function update(User $user, Intervencao $intervencao): bool
    {
        return $this->view($user, $intervencao);
    }

    public function delete(User $user, Intervencao $intervencao): bool
    {
        return $this->view($user, $intervencao);
    }
}
