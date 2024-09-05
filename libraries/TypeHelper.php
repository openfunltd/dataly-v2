<?php

class TypeHelper
{
    public static function getTypeConfig()
    {
        return [
            'meet' => [
                'name' => '會議',
                'icon' => 'fas fa-fw fa-calendar-day',
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
                'name' => '議案',
                'icon' => 'fas fa-fw fa-file-alt',
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
                'name' => '立委',
                'icon' => 'fas fa-fw fa-user-tie',
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
            'committee' => [
                'name' => '委員會',
                'icon' => 'fas fa-fw fa-users',
                'cols' => [
                    '委員會代號',
                    '委員會名稱',
                    '委員會類別',
                ],
                'default_aggs' => [
                    '委員會類別',
                ],
            ],
            'ivod' => [
                'name' => 'iVOD',
                'icon' => 'fas fa-fw fa-video',
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
            'law' => [
                'name' => '法律',
                'icon' => 'fas fa-fw fa-balance-scale',
                'cols' => [
                    '法律編號',
                    '類別',
                    '母法編號',
                    '名稱',
                    '其他名稱',
                ],
                'default_aggs' => [
                    '類別',
                ],
            ],
            'gazette' => [
                'name' => '公報',
                'icon' => 'fas fa-fw fa-newspaper',
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
            'gazette_agenda' => [
                'name' => '公報章節',
                'icon' => 'fas fa-fw fa-newspaper',
                'cols' => [
                    '公報議程編號',
                    '卷',
                    '期',
                    '冊別',
                    '會議日期',
                    '案由',
                ],
                'default_aggs' => [
                    '卷',
                ],
            ],
            'interpellation' => [
                'name' => '書面質詢',
                'icon' => 'fas fa-fw fa-question',
                'cols' => [
                    '質詢編號',
                    '屆',
                    '質詢委員',
                    '刊登日期',
                    '事由',
                ],
                'default_aggs' => [
                    '屆',
                    '質詢委員',
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
        $type = str_replace('_', '', $type);
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
