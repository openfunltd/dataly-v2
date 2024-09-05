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
}
