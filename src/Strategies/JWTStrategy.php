<?php

namespace Api\Strategies;

use Api\Controllers\AccountsController;
use Api\Models\User;
use Api\Services\ServiceContainer;
use Artisan\Routing\Entities\Config;
use Artisan\Routing\Exceptions\AuthorizationRequiredException;
use Artisan\Routing\Interfaces\IAuthenticationStrategy;
use Artisan\Routing\Services\ApiService;
use Artisan\Services\Doctrine;
use Artisan\Services\JWT;

/**
 * This strategy expects an Authorization header containing a Bearer token in JWT format.
 */
class JWTStrategy implements IAuthenticationStrategy
{


    public function authenticate(): void
    {
        $config = AccountsController::getAccountsConfig();

        $request = ApiService::i()->getRequest();
        $token = preg_replace('/^Bearer\s+/i', '', $request->headers->get('Authorization', ''));
        if (
            !$token
            || !($decoded = JWT::i()->decode($token, $config['token_salt']))
            || !($user = User::findOne(['id' => $decoded['data']]))
        ) {
            throw new AuthorizationRequiredException('Invalid credentials.');
        }

        ServiceContainer::i()->setUser($user);
    }

    public static function generateToken(string $id): string
    {
        $config = AccountsController::getAccountsConfig();

        return JWT::i()->encode($config['token_salt'], $config['token_exp_days']*86400, $id);
    }
}