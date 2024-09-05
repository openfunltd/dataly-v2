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

    public static function apiQuery($url, $reason)
    {
        if (getenv('LYAPI_HOST')) {
            $url = 'https://' . getenv('LYAPI_HOST') . $url;
        } else {
            $url = 'https://v2.ly.govapi.tw' . $url;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($curl);
        $res_json = json_decode($res);
        curl_close($curl);
        if (is_null(self::$log)) {
            self::$log = [];
        }
        self::$log[$reason] = [$url, $reason];

        return $res_json;
    }
}
