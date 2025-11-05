<?php

namespace Api\Routers;

use Api\Controllers\AccountsController;
use Api\Controllers\HomeController;
use Artisan\Routing\Interfaces\IRouter;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouterList implements IRouter
{
    private const string GET = 'GET';
    private const string POST = 'POST';
    private const string PUT = 'PUT';
    private const string PATCH = 'PATCH';
    private const string DELETE = 'DELETE';

    public function getRoutes(): RouteCollection
    {
        $routes = new RouteCollection();

        $routes->add('home', new Route('/', [
            '_authRequired' => false,
            '_methods' => [self::GET],
            '_controller' => [HomeController::class, 'landing'],
        ]));

        $routes->add('register', new Route('/register', [
            '_authRequired' => false,
            '_methods' => [self::POST],
            '_controller' => [AccountsController::class, 'register'],
        ]));

        $routes->add('login', new Route('/login', [
            '_authRequired' => false,
            '_methods' => [self::POST, self::PUT],
            '_controller' => [AccountsController::class, 'login'],
        ]));

        $routes->add('validation_request', new Route('/code_request', [
            '_authRequired' => true,
            '_validationRequired' => false,
            '_methods' => [self::GET],
            '_controller' => [AccountsController::class, 'code_request'],
        ]));

        $routes->add('validation', new Route('/validate', [
            '_authRequired' => true,
            '_validationRequired' => false,
            '_methods' => [self::POST],
            '_controller' => [AccountsController::class, 'validation'],
        ]));

        $routes->add('recovery', new Route('/recovery', [
            '_authRequired' => false,
            '_methods' => [self::POST],
            '_controller' => [AccountsController::class, 'recovery'],
        ]));

        $routes->add('my_account', new Route('/my-account', [
            '_authRequired' => true,
            '_validationRequired' => false,
            '_methods' => [self::GET, self::PATCH],
            '_controller' => [AccountsController::class, 'settings'],
        ]));

        $routes->add('dashboard', new Route('/dashboard', [
            '_authRequired' => true,
            '_methods' => [self::GET],
            '_controller' => [HomeController::class, 'dashboard'],
        ]));

        return $routes;
    }
}