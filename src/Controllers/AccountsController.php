<?php

namespace Api\Controllers;

use Api\Models\User;
use Api\Services\ServiceContainer;
use Api\Strategies\CodeStrategy;
use Api\Strategies\PasswordStrategy;
use Api\Strategies\JWTStrategy;
use Artisan\Routing\Entities\Config;
use Artisan\Routing\Exceptions\AuthorizationRequiredException;
use Artisan\Routing\Exceptions\BadRequestException;
use Artisan\Routing\Interfaces\IApiResponse;
use Artisan\Routing\Services\ApiService;
use Artisan\Services\Doctrine;
use Artisan\Services\Language;
use Artisan\Services\Where;
use Artisan\TokenManager\Exceptions\UnknownTypeException;
use Artisan\TokenManager\Managers\TokenManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AccountsController
{
    const string TOKEN_TYPE_EMAIL_VALIDATION = 'email_validation';
    const string TOKEN_TYPE_ACCOUNT_LOGIN = 'login';

    /**
     * @throws BadRequestException
     * @throws ORMException
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function register(Request $request, IApiResponse $response): void
    {
        $email = $request->getPayload()->get('email');
        $password = $request->getPayload()->get('password');
        $name = $request->getPayload()->get('name');
        $surname = $request->getPayload()->get('surname');

        if (empty($email) || empty($password) || empty($name) || empty($surname)) {
            throw new BadRequestException(Language::i()->trans('miss_req_data'));
        }

        $user = User::findOne(['email' => strtolower($email)]);
        if ($user) {
            throw new BadRequestException(Language::i()->trans('email_exists'));
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPassword(PasswordStrategy::encript($password));
        $user->setName($name);
        $user->setSurname($surname);

        $em = Doctrine::i()->getEntityManager();
        $em->persist($user);
        $em->flush();

        $this->responseWithToken();
        (new CodeStrategy())->sendToEmail($user, self::TOKEN_TYPE_EMAIL_VALIDATION, true);
    }

    /**
     * @throws AuthorizationRequiredException
     * @throws BadRequestException
     */
    public function login(Request $request, IApiResponse $response): void
    {
        $strategy = match ($request->getMethod()) {
            Request::METHOD_POST => new PasswordStrategy(),
            Request::METHOD_PUT => new CodeStrategy(),
            default => throw new AuthorizationRequiredException(),
        };

        $strategy->authenticate();
        $this->responseWithToken();
    }

    /**
     * Validate the new accounts.
     *  - confirm the email
     *  - try to store the user's country
     *  - try to store the user's timezone
     *
     * @throws UnknownTypeException
     * @throws Throwable
     * @throws BadRequestException
     */
    public function validation(Request $request, IApiResponse $response): void
    {
        $validationCode = $request->getPayload()->get('code', '');

        $token = TokenManager::i()->redeem($validationCode, self::TOKEN_TYPE_EMAIL_VALIDATION);
        if (!$token) {
            throw new BadRequestException('Invalid code');
        }

        $user = ServiceContainer::i()->getUser();
        $user->setVerified(true);

        $this->updateRegionalData($user);

        $response->setPayload([
            'success' => true,
            'remaining_uses' => $token->getRemainingUses()
        ]);
        $response->setCode(Response::HTTP_OK);
        $response->send();
    }

    /**
     * User requests a new email with a validation code
     */
    public function code_request(Request $request, IApiResponse $response): void
    {
        $user = ServiceContainer::i()->getUser();
        $message = 'Account validated';

        if (!$user->isVerified()) {
            if ((new CodeStrategy())->sendToEmail($user, self::TOKEN_TYPE_EMAIL_VALIDATION)) {
                $message = 'Check email';
            } else {
                $response->setPayload([
                    'success' => false,
                    'error' => 'Await'
                ]);
                $response->send();
                return;
            }
        }

        $response->setPayload([
            'success' => true,
            'message' => $message
        ]);

        $response->send();
    }

    /**
     * POST : user receives a validation code in their email.
     * PUT : user send the validation code and receive a login response.
     *
     * @throws BadRequestException
     */
    public function recovery(Request $request, IApiResponse $response): void
    {
        $email = $request->getPayload()->get('email');
        if (empty($email)) {
            throw new BadRequestException(Language::i()->trans('miss_req_data'));
        }

        $user = User::findOne(['email' => strtolower($email)]);
        if ($user) {
            (new CodeStrategy())->sendToEmail($user, self::TOKEN_TYPE_ACCOUNT_LOGIN);
        }

        $response->setPayload([
            'success' => true,
            'message' => Language::i()->trans('check_email')
        ]);

        $response->send();
    }

    /**
     * @throws Throwable
     */
    public function settings(Request $request, IApiResponse $response): void
    {
        $config = self::getAccountsConfig();
        $user = ServiceContainer::i()->getUser();

        if ($request->getMethod() == Request::METHOD_GET) {
            $response->setPayload([
                'success' => true,
                'user' => $user->toArray(),
            ]);
        }
        elseif ($request->getMethod() == Request::METHOD_PATCH) {
            $needSave = false;
            $needSendNewCode = false;

            $newEmail = trim(strtolower($request->getPayload()->get('email')));
            if ($newEmail) {
                if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                    throw new BadRequestException(Language::i()->trans('invalid_email_format'));
                }

                if (!User::findOne(['email' => $newEmail])) {
                    $user->setEmail($newEmail);
                    $user->setVerified(false);
                    $needSave = true;
                    $needSendNewCode = true;
                }
            }

            $name = trim($request->getPayload()->get('name'));
            if ($name) {
                $user->setName($name);
                $needSave = true;
            }

            $surname = trim($request->getPayload()->get('surname'));
            if ($surname) {
                $user->setSurname($surname);
                $needSave = true;
            }

            $countryCode = trim(strtoupper($request->getPayload()->get('country_code')));
            if ($countryCode) {
                if (!Where::validCountry($countryCode)) {
                    throw new BadRequestException(Language::i()->trans('invalid_country_code'));
                }
                $user->setCountryCode($countryCode);
                $needSave = true;
            }

            $timezone = $request->getPayload()->get('timezone');
            if ($timezone) {
                if (!in_array($timezone, \DateTimeZone::listIdentifiers())) {
                    throw new BadRequestException(Language::i()->trans('invalid_timezone'));
                }
                $user->setTimezone($timezone);
                $needSave = true;
            }

            $newPassword = $request->getPayload()->get('password');
            if ($newPassword) {
                if (!$this->passwordValidation($newPassword)) {
                    throw new BadRequestException(Language::i()->trans('password_too_short', ['n' => $config['pwd_min_length']]));
                }

                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $user->setPassword($hashedPassword);
                $needSave = true;
            }

            if ($needSave) {
                $em = Doctrine::i()->getEntityManager();
                $em->flush();

                if ($needSendNewCode) {
                    (new CodeStrategy())->sendToEmail($user, self::TOKEN_TYPE_EMAIL_VALIDATION);
                }
            }

            $response->setPayload([
                'success' => true,
                'message' => Language::i()->trans('data_saved'),
            ]);
        }

        $response->send();
    }

    /**
     * @throws OptimisticLockException
     * @throws Throwable
     * @throws ORMException
     */
    private function updateRegionalData(User $user): void
    {
        $request = ApiService::i()->getRequest();
        $location = Where::is($request->getClientIp());
        if ($location) {
            $user->setCountryCode($location->getISO());
            $user->setTimezone($location->getTimezone());
            $em = Doctrine::i()->getEntityManager();
            $em->flush();
        }
    }

    private function responseWithToken(array $additionalPayload = []): void
    {
        if (!isset($additionalPayload['success'])) {
            $additionalPayload['success'] = true;
        }

        $user = ServiceContainer::i()->getUser();
        $additionalPayload['token'] = JWTStrategy::generateToken($user->getId());

        $response = ApiService::i()->getResponse();
        $response->setPayload($additionalPayload);
        $response->setCode(Response::HTTP_OK);
        $response->send();
    }

    public static function getAccountsConfig(): array
    {
        static $defaultConf = [
            'token_exp_days' => 180,
            'token_salt' => 'dummy',
            'pwd_min_length' => 3,
            'pwd_mix_upper_lower_case' => true,
            'pwd_numbers' => true,
            'pwd_symbols' => true,
        ];

        static $conf;
        if (!$conf) {
            $conf = Config::get('accounts', []);

            foreach ($defaultConf as $k => $v) {
                if (!isset($conf[$k])) $conf[$k] = $v;
            }
        }

        return $conf;
    }

    private function passwordValidation(string $pwd): bool
    {
        $config = self::getAccountsConfig();
        $minLength = $config['pwd_min_length'];
        $mustHaveUpperLowerCase = $config['pwd_mix_upper_lower_case'];
        $mustHaveNumbers = $config['pwd_numbers'];
        $mustHaveSymbols = $config['pwd_symbols'];

        if (strlen($pwd) < $minLength) {
            return false;
        }

        $patternParts = [];

        if ($mustHaveUpperLowerCase) {
            $patternParts[] = '(?=.*[a-z])(?=.*[A-Z])';
        }

        if ($mustHaveNumbers) {
            $patternParts[] = '(?=.*\d)';
        }

        if ($mustHaveSymbols) {
            $patternParts[] = '(?=.*[\W_])';
        }

        $pattern = '/' . implode('', $patternParts) . '/';

        return preg_match($pattern, $pwd) === 1;
    }
}