<?php

class TypeHelper
{
    public static function getTypeConfig()
    {
        return [
            'meet' => [
                'cols' => [
                    '會議代碼',
                    '日期',
                    '屆',
                    '會期',
                    '會議標題',
                ],
                'default_aggs' => [
                    '屆',
                    '會議種類',
                ],
            ],
        ];
    }

    public static function getColumns($type)
    {
        $config = self::getTypeConfig();
        return $config[$type]['cols'] ?? [];
    }

    public static function getData($data, $type)
    {
        return $data->{$type . 's'} ?? [];
    }

    public static function getDataFromAPI($type)
    {
        $agg = self::getCurrentAgg($type);
        $url = "/{$type}s";
        $terms = [];
        foreach ($agg as $field) {
            $terms[] = "agg=" . urlencode($field);
        }
        if ($terms) {
            $url .= '?' . implode('&', $terms);
        }
        return LYAPI::apiQuery($url, "抓取 {$type} 的資料");
    }

    public static function getCurrentAgg($type)
    {
        $config = self::getTypeConfig();
        return $config[$type]['default_aggs'] ?? [];
    }
}
