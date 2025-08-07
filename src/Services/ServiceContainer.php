<?php

namespace Api\Services;

use Api\Models\User;
use Artisan\Routing\Entities\Config;
use Artisan\Services\Doctrine;
use Artisan\Services\Email;
use Artisan\Services\JWT;
use Artisan\Services\Language;
use Artisan\Services\Logger;
use Artisan\Services\Twig;
use Artisan\Services\Where;
use Artisan\TokenManager\Managers\TokenManager;
use Exception;

class ServiceContainer extends \Pimple\Container
{
    private static ServiceContainer $instance;

    /**
     * @throws Exception
     */
    public static function initialize(): static
    {
        return self::$instance = new static();
    }

    public static function i(): ServiceContainer
    {
        return self::$instance;
    }

    /**
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->initializeServices();
    }

    public function setUser(User $user): self
    {
        $this['logged_user'] = $user;
        return $this;
    }
    public function getUser(): ?User
    {
        return $this['logged_user'] ?? null;
    }

    /**
     * @throws Exception
     */
    private function initializeServices(): void
    {
        Logger::load(Config::get('logs'), 'artisan');

        $this['doctrine'] = Doctrine::i()->load(Config::get('db'));

        Email::load(Config::get('email'));

        JWT::load(Config::get('jwt')['algorithm']);

        Twig::load(Config::get('twig'));

        Language::load(Config::get('i18n'));

        TokenManager::load(Config::get('token-manager'));

        Where::load(Config::get('geoip'));
    }
}