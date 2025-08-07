<?php

namespace Api\Crons;

require_once __DIR__ . '/../Bootstraps/BootstrapConsole.php';

use Artisan\Services\Doctrine;
use Artisan\Services\Logger;
use DateTime;
use DateTimeZone;

class TokenCleaner
{
    public function run(): void
    {
        try {
            $now = new DateTime('now', new DateTimeZone('UTC'));

            $sql = 'DELETE FROM tokens WHERE remaining_uses < 1 OR expiration_at < :now';

            $params = ['now' => $now->format('Y-m-d H:i:s')];

            $deleted = Doctrine::i()->query($sql, $params);

            echo "Deleted tokens: $deleted".PHP_EOL;
        }
        catch (\Throwable $t) {
            $msg =  "token_cleaner ERROR: ".$t->getMessage().PHP_EOL;
            echo $msg;
            Logger::i()->error($msg);
        }

    }
}

(new TokenCleaner())->run();


