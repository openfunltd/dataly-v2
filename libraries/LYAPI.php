<?php

class LYAPI
{
    protected static $log = [];
    public static function hasLog()
    {
        return count(self::$log) > 0;
    }

    public static function getLogs()
    {
        return self::$log;
    }
}
