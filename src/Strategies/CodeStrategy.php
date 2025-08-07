<?php

namespace Api\Strategies;

use Api\Models\User;
use Api\Services\ServiceContainer;
use Api\Services\TimeFormatter;
use Artisan\Routing\Exceptions\AuthorizationRequiredException;
use Artisan\Routing\Exceptions\BadRequestException;
use Artisan\Routing\Interfaces\IAuthenticationStrategy;
use Artisan\Routing\Services\ApiService;
use Artisan\Services\Doctrine;
use Artisan\Services\Email;
use Artisan\Services\Language;
use Artisan\Services\Twig;
use Artisan\TokenManager\Exceptions\UnknownBehaviorException;
use Artisan\TokenManager\Exceptions\UnknownTypeException;
use Artisan\TokenManager\Managers\TokenManager;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Throwable;

/**
 * This strategy expects a short code received in the body data
 */
class CodeStrategy implements IAuthenticationStrategy
{
    const int VAL_CODE_DURATION = 3600; //segundos
    const int SEND_EMAIL_INTERVAL = 600;
    const int VAL_CODE_LENGTH = 6;

    /**
     * @throws \DateMalformedStringException
     * @throws UnknownTypeException
     * @throws UnknownBehaviorException
     * @throws \DateMalformedIntervalStringException
     * @throws Exception
     */
    public function sendToEmail(User $user, string $type, bool $isNewAccount = false): bool
    {
        $now = new DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $token = TokenManager::i()->create(
            entityName: User::class,
            entityId: $user->getId(),
            type: $type,
            behavior: TokenManager::BEHAVIOR_UNIQUE,
            duration: self::VAL_CODE_DURATION,
            maxUses: 1,
            codeLength: self::VAL_CODE_LENGTH
        );
        $diff = $now->getTimestamp() - $token->getCreatedAt()->getTimestamp();

        if ($diff > 0 && $diff < self::SEND_EMAIL_INTERVAL) {
            return false;
        }

        $emailContent = (new Twig())->render('Accounts/validation_code.email.twig', [
            'webname' => Language::i()->trans('webname'),
            'username' => $user->getName(),
            'code' => $token->getCode(),
            'expiration_time' => TimeFormatter::formatString(self::VAL_CODE_DURATION),
            'is_new_account' => $isNewAccount,
        ]);

        Email::i()
            ->from('rafflestars@sectorcanarias.com')
            ->to($user->getEmail())
            ->subject('Validation Code')
            ->html($emailContent)
            ->send();

        return true;
    }

    /**
     * @throws Throwable
     * @throws BadRequestException
     * @throws UnknownTypeException
     * @throws Exception
     */
    public function authenticate(): void
    {
        $request = ApiService::i()->getRequest();

        $loginCode = $request->getPayload()->get('code', '');

        $tokenType = \Api\Controllers\AccountsController::TOKEN_TYPE_ACCOUNT_LOGIN;
        $token = TokenManager::i()->redeem($loginCode, $tokenType);
        if (
            !$token
            || !($user = User::findOne(['id' => $token->getEntityId()]))
        ) {
            throw new AuthorizationRequiredException('Invalid credentials.');
        }

        if (!$user->isVerified()) {
            $user->setVerified(true);
            $em = Doctrine::i()->getEntityManager();
            $em->flush();
        }

        ServiceContainer::i()->setUser($user);
    }

}