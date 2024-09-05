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
            'bill' => [
                'cols' => [
                    '議案編號',
                    '提案來源',
                    '議案類別',
                    '議案狀態',
                    '提案單位/提案委員',
                    '議案名稱',
                ],
                'default_aggs' => [
                    '屆',
                    '提案來源',
                    '議案類別',
                    '議案狀態',
                ],
            ],
            'legislator' => [
                'cols' => [
                    '屆期',
                    '委員姓名',
                    '黨籍',
                    '選區名稱',
                    '歷屆立法委員編號',
                ],
                'default_aggs' => [
                    '屆期',
                    '黨籍',
                ],
            ],
            'ivod' => [
                'cols' => [
                    'IVOD_ID',
                    '日期',
                    '委員發言時間',
                    '委員名稱',
                    '會議名稱',
                ],
                'default_aggs' => [
                    '屆',
                    '影片種類',
                ],
            ],
            'gazette' => [
                'cols' => [
                    '公報編號',
                    '卷',
                    '期',
                    '冊別',
                    '發布日期',
                ],
                'default_aggs' => [
                    '卷',
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
