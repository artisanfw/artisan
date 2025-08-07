<?php

namespace Api\Middlewares;

use Api\Services\Who;
use Artisan\Routing\Exceptions\ForbiddenException;
use Artisan\Routing\Interfaces\IApiResponse;
use Artisan\Routing\Interfaces\IMiddleware;
use Symfony\Component\HttpFoundation\Request;

/**
 * Check if the user account was activated with the validation code.
 */
class UserValidationMiddleware implements IMiddleware
{
    /**
     * @throws ForbiddenException
     */
    public function run(array $routeParams, Request $request, IApiResponse $response): void
    {
        $authRequired = isset($routeParams['_authRequired']) && $routeParams['_authRequired'];

        $validationRequired = !isset($routeParams['_validationRequired']) || $routeParams['_validationRequired'];

        if ($authRequired && $validationRequired) {
            $user = Who::is();
            if (!$user->isVerified()) {
                throw new ForbiddenException('Account not verified');
            }
        }
    }
}