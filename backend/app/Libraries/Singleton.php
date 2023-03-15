<?php

namespace App\Libraries;

trait Singleton
{
    protected static $uniqueInstance;

    final private function __construct()
    {
    }

    /**
     * @return static
     */
    final public static function getInstance()
    {
        if (!static::$uniqueInstance) {
            static::$uniqueInstance = new static();
        }
        return static::$uniqueInstance;
    }

    public function __clone()
    {
    }

    public function __wakeup()
    {
    }
}
