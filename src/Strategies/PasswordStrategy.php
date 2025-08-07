<?php

namespace Api\Strategies;

use Api\Models\User;
use Api\Services\ServiceContainer;
use Artisan\Routing\Exceptions\AuthorizationRequiredException;
use Artisan\Routing\Exceptions\BadRequestException;
use Artisan\Routing\Interfaces\IAuthenticationStrategy;
use Artisan\Routing\Services\ApiService;
use Artisan\Services\Doctrine;
use Pimple\Tests\Fixtures\Service;
use Symfony\Component\HttpFoundation\Response;

/**
 * This strategy expects to receive a password along with another field.
 * The second field must match a unique attribute of the User class
 * (username, email, ID, etc).
 */
class PasswordStrategy implements IAuthenticationStrategy
{
    const string LOGIN_FIELD = 'email';

    /**
     * @throws AuthorizationRequiredException
     * @throws BadRequestException
     */
    public function authenticate(): void
    {
        $request = ApiService::i()->getRequest();

        $uniqueValue = $request->getPayload()->get(self::LOGIN_FIELD);
        $password = $request->getPayload()->get('password');

        if (empty($password) || empty($uniqueValue)) {
            throw new BadRequestException('Missing required parameters.');
        }

        $user = $this->findUser([self::LOGIN_FIELD => strtolower($uniqueValue)]);

        if (!$user || !password_verify($password, $user->getPassword())) {
            throw new AuthorizationRequiredException('Invalid credentials.');
        }

        ServiceContainer::i()->setUser($user);
    }

    public static function encript(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 15]);
    }

    private function findUser(array $filter): ?User
    {
        $em = Doctrine::i()->getEntityManager();
        return $em->getRepository(User::class)->findOneBy($filter);
    }
}