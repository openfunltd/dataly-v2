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
                    '屆',
                    '會期',
                    '會議代碼',
                    '日期',
                    '會議標題',
                ],
                'default_aggs' => [
                    '屆',
                    '會期',
                    '會議種類',
                ],
                'item_features' => [
                    'info' => '資訊',
                    'opendata' => '開放資料',
                    'proceedings' => '議事錄',
                    'gazette' => '公報記錄',
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
                'item_features' => [
                    'law-diff' => '法律對照表',
                ],
            ],
            'legislator' => [
                'name' => '立委',
                'icon' => 'fas fa-fw fa-user-tie',
                'cols' => [
                    '屆',
                    '委員姓名',
                    '黨籍',
                    '選區名稱',
                    '歷屆立法委員編號',
                ],
                'default_aggs' => [
                    '屆',
                    '黨籍',
                ],
                'collection_features' => [
                    'table' => '列表',
                    'list' => '立委列表',
                ],
            ],
            'committee' => [
                'name' => '委員會',
                'icon' => 'fas fa-fw fa-users',
                'cols' => [
                    '委員會代號',
                    '委員會名稱',
                    '委員會類別:str',
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
                'item_features' => [
                    'ai-transcript' => 'AI 逐字稿',
                    'gazette' => '公報紀錄',
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

    public static function getDataColumn($type)
    {
        $type = str_replace('_', '', $type);
        return $type . 's';
    }

    public static function getDataByID($type, $id)
    {
        $ret = LYAPI::apiQuery("/{$type}/" . urlencode($id), "抓取 {$type} 的 {$id} 資料");
        return $ret;
    }

    public static function getData($data, $type)
    {
        return $data->{self::getDataColumn($type)} ?? [];
    }

    public static function getAPIURL($type)
    {
        if (getenv('LYAPI_HOST')) {
            $url = 'https://' . getenv('LYAPI_HOST');
        } else {
            $url = 'https://v2.ly.govapi.tw';
        }
        return "{$url}/{$type}s";
    }

    public static function getDataFromAPI($type)
    {
        $agg = self::getCurrentAgg($type);
        $url = self::getAPIURL($type);
        $terms = [];
        foreach ($agg as $field) {
            $terms[] = "agg=" . urlencode($field);
        }
        if ($terms) {
            $url .= '?' . implode('&', $terms);
        }
        return LYAPI::apiQuery($url, "抓取 {$type} 的資料");
    }

    public static function getCurrentFilter()
    {
        $config = self::getTypeConfig();
        $query_string = $_SERVER['QUERY_STRING'];
        $terms = explode('&', $query_string);
        $filter = [];
        foreach ($terms as $term) {
            list($k, $v) = array_map('urldecode', explode('=', $term));
            if ($k === 'filter') {
                $filter[] = explode(':', $v, 2);
            }
        }
        return $filter;
    }

    public static function getCurrentAgg($type)
    {
        $config = self::getTypeConfig();
        $query_string = $_SERVER['QUERY_STRING'];
        $terms = explode('&', $query_string);
        $agg = [];
        foreach ($terms as $term) {
            list($k, $v) = array_map('urldecode', explode('=', $term));
            if ($k === 'agg') {
                $agg[] = $v;
            }
        }
        if ($agg) {
            return $agg;
        }

        return $config[$type]['default_aggs'] ?? [];
    }

    public static function getRecordList($data, $prefix = '')
    {
        if (is_scalar($data)) {
            return [[
                'key' => rtrim($prefix, '.'),
                'value' => $data,
            ]];
        }

        if (is_array($data)) {
            $ret = [];
            foreach ($data as $idx => $item) {
                $ret = array_merge(
                    $ret,
                    self::getRecordList($item, rtrim($prefix, '.') . "[{$idx}].")
                );
            }
            return $ret;
        }

        $ret = [];
        foreach ($data as $k => $v) {
            $ret = array_merge(
                $ret,
                self::getRecordList($v, "{$prefix}{$k}.")
            );
        }
        return $ret;
    }

    public static function getItemFeatures($type)
    {
        $config = self::getTypeConfig();
        $features = $config[$type]['item_features'] ?? [];
        $features['rawdata'] = '原始資料';
        return $features;
    }

    public static function getCollectionFeatures($type)
    {
        $config = self::getTypeConfig();
        $features = $config[$type]['collection_features'] ?? [];
        $features['table'] = '列表';
        return $features;
    }
}
