<?php

namespace Api\Bootstraps;

use Api\Middlewares\UserValidationMiddleware;
use Api\Routers\RouterList;
use Api\Services\ServiceContainer;
use Api\Strategies\JWTStrategy;
use Artisan\Middlewares\Locale;
use Artisan\Routing\Entities\ApiOptions;
use Artisan\Routing\Managers\ApiManager;
use Artisan\Routing\Services\ApiService;
use Artisan\Services\Logger;
use Symfony\Component\HttpFoundation\Response;

class BootstrapApi
{
    public function run(): void
    {
        date_default_timezone_set('UTC');

        define('PROJECT_DIR', realpath(__DIR__ . '/../../'));

        try {
            $apiOptions = (new ApiOptions())
                ->setLabel('artisan_api_example')
                ->setRequestType(ApiOptions::REQUEST_JSON)
                ->setResponseType(ApiOptions::RESPONSE_JSON)
                ->setAllowedHeaders(ApiOptions::COMMON_CORS_HEADERS)
                ->setAllowedMethods(ApiOptions::COMMON_CORS_METHODS)
                ->setAcceptedApiKeys(['123']) // X-API-KEY header
                ->setConfigFile(PROJECT_DIR . '/.config.php')
            ;

            ApiService::initialize($apiOptions);
            ServiceContainer::initialize();

            $apiManager = new ApiManager();
            $apiManager->setAuthStrategy(new JWTStrategy());
            $apiManager->addMiddleware(new Locale());
            $apiManager->addMiddleware(new UserValidationMiddleware());
            $apiManager->addRouter(new RouterList());

            $apiManager->processRequest(ApiService::i()->getRequest());
        } catch (\Throwable $t) {
            error_log($t);
            Logger::i()->critical($t);
            if (ApiService::i()->isDevelopment()) {
                $response = new Response($t, Response::HTTP_INTERNAL_SERVER_ERROR);
            } else {
                $response = new Response(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $response->send();
        }
    }
}