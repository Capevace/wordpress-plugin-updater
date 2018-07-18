<?php

namespace Smoolabs\WPU\V3\Util;

if (!class_exists('\\Smoolabs\\WPU\\V3\\Util\\Singleton')):

class Singleton
{
    private static $instances = array();
    protected function __construct() {}
    protected function __clone() {}
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    public static function instance()
    {
        $cls = get_called_class(); // late-static-bound class name
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static;
        }
        return self::$instances[$cls];
    }
}

endif;