<?php
namespace Api\Controllers;

use Api\Services\ServiceContainer;
use Artisan\Routing\Services\ApiService;
use Artisan\Services\Where;
use Api\Services\Who;
use Artisan\Services\Language;
use Symfony\Component\HttpFoundation\Request;
use Artisan\Routing\Interfaces\IApiResponse;
use Throwable;

class HomeController
{
    /**
     * @throws Throwable
     */
    public function landing(Request $request, IApiResponse $response): void
    {
        $user = Who::is();
        $username = $user ? $user->getName() : 'usuario';
        $hello = Language::i()->trans('welcome_to', ['user' => $username]);

        $ip = $request->getClientIp();
        if (ApiService::i()->isDevelopment() && !$this->isPublicIp($ip)) {
            $ip = '79.117.230.245';
        }

        $location = Where::is($ip);
        if ($location) {
            $youAreHere = [
                'continent' => $location->getContinent(),
                'country' => $location->getCountry(),
                'ISO' => $location->getISO(),
                'region' => $location->getRegion(),
                'city' => $location->getCity(),
                'latitude' => $location->getLatitude(),
                'longitude' => $location->getLongitude(),
                'timezone' => $location->getTimezone(),
            ];
        } else {
            $youAreHere = 'Location not found';
        }


        $response->setPayload([
            'message' => $hello,
            'ENV' => ENVIRONMENT,
            'locale' => Language::i()->getLocale(),
            'you_are_here' => $youAreHere,
        ]);
    }

    private function isPublicIp(?string $ip): bool
    {
        if (!$ip) return false;
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }
        return false;
    }

    public function dashboard(Request $request, IApiResponse $response): void
    {
        $user = ServiceContainer::i()->getUser();
        $response->setPayload([
            'message' => Language::i()->trans('welcome', ['username' => $user->getName()]),
        ]);
    }
}