<?php

namespace Api\Services;

use Api\Models\User;
use Api\Strategies\JWTStrategy;

class Who
{
    public static function is(): ?User
    {
        $user = ServiceContainer::i()->getUser();
        if ($user) return $user;

        try {
            (new JWTStrategy)->authenticate();
            return ServiceContainer::i()->getUser();
        } catch(\Throwable $t) { }

        return null;
    }

}