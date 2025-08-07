<?php

namespace Api\Traits;

trait Singleton
{
    private static mixed $instance = null;

    private function __construct() {}

    private function __clone() {}

    public static function i(): static
    {
        if (!self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }
}