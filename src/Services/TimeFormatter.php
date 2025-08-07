<?php

namespace Api\Services;

use Artisan\Services\Language;

class TimeFormatter
{
    /**
     * Convierte segundos en una estructura detallada de tiempo
     */
    public static function format(int $seconds): array
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        if ($hours > 0) {
            $amount = $hours;
            $unit = 'hour';
        } elseif ($minutes > 0) {
            $amount = $minutes;
            $unit = 'minute';
        } else {
            $amount = $remainingSeconds;
            $unit = 'second';
        }

        return [
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $remainingSeconds,
            'amount' => $amount,
            'unit' => $unit,
        ];
    }

    public static function formatString(int $seconds, ?string $langCode = null): string
    {
        $time = self::format($seconds);
        $unit = Language::i()->trans($time['unit'], ['n' => $time['amount']], null, $langCode);

        return Language::i()->trans('time_notation', [
            'n' => $time['amount'],
            'unit' => $unit,
        ], null, $langCode);
    }

    /**
     * Devuelve una representación legible de una cantidad de segundos transcurrida,
     *
     * TimeFormatter::ago(3667); // "hace 1 hora"
     * TimeFormatter::ago(185);  // "hace 3 minutos"
     * TimeFormatter::ago(25);   // "hace unos segundos"
     * TimeFormatter::ago(5);    // "justo ahora"
     */
    public static function ago(int $seconds, ?string $langCode = null): string
    {
        $time = self::format($seconds);

        if ($time['unit'] == 'second') {
            if ($time['amount'] < 20) {
                return Language::i()->trans(key: 'just_now', locale: $langCode);
            }
            return Language::i()->trans('a_few_seconds');
        }

        $unit = Language::i()->trans($time['unit'], ['n' => $time['amount']], null, $langCode);

        return Language::i()->trans('before', [
            'before_number' => $time['amount'],
            'before_unit' => $unit
        ], null, $langCode);
    }
}
