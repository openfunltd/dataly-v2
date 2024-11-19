<?php

class CollectionController extends MiniEngine_Controller
{
    public function listAction($type, $tab = null)
    {
        $this->view->type = $type;
        $this->view->features = TypeHelper::getCollectionFeatures($type);
        if (!$tab) {
            $tab = key($this->view->features);
        }
        $this->view->tab = $tab;
        if (!array_key_exists($tab, $this->view->features)) {
            throw new Exception('Invalid tab: ' . $tab);
        }

        if (method_exists($this, "list_{$type}_{$tab}")) {
            $this->{"list_{$type}_{$tab}"}();
        }
    }

    public function itemAction($type, $id, $tab = null)
    {
        $this->view->type = $type;
        $this->view->id = $id;
        $this->view->data = TypeHelper::getDataByID($type, $id);
        $this->view->features = TypeHelper::getItemFeatures($type);
        if (!$tab) {
            $tab = key($this->view->features);
        }
        $this->view->tab = $tab;
        if (!array_key_exists($tab, $this->view->features)) {
            throw new Exception('Invalid tab: ' . $tab);
        }

        if (method_exists($this, "item_{$type}_{$tab}")) {
            $this->{"item_{$type}_{$tab}"}();
        }
    }

    public function list_ivod_datelist()
    {
        $week_data = array('日', '一', '二', '三', '四', '五', '六');
        $term_selected = filter_input(INPUT_GET, '屆', FILTER_VALIDATE_INT) ?? -1;
        $session_period_selected = filter_input(INPUT_GET, '會期', FILTER_VALIDATE_INT) ?? -1;
        $date_list = [];

        $res = LYAPI::apiQuery(
            '/ivods?&limit=30&output_fields=會議資料.屆&output_fields=會議資料.會期&agg=屆',
            '查詢所有的屆期選項'
        );
        $ivods = array_filter($res->ivods, function($ivod) {
            $meet_data = $ivod->會議資料;
            return isset($meet_data);
        });
        $ivods = array_values($ivods);
        $term_latest = $ivods[0]->會議資料->屆;
        $session_period_latest = max(array_map(fn($ivod) => $ivod->會議資料->會期, $ivods));
        $term_options = array_map(function($term) {
            return $term->屆;
        }, $res->aggs[0]->buckets);
        rsort($term_options);

        $res = LYAPI::apiQuery("/ivods?屆={$term_selected}&agg=會期", "查詢第 {$term_selected} 屆所有的會期選項");
        if ($res->total == 0) {
            $params['屆'] = $term_latest;
            $params['會期'] = $session_period_latest;
            $url = strtok($_SERVER['REQUEST_URI'], '?') . '?' . http_build_query($params);
            header('Location: ' . $url, true, 302);
            exit;
        }

        $session_period_options = array_map(function($session_period) {
            $obj = (object) [
                '會期' => $session_period->會期,
            ];
            return $obj;
        },$res->aggs[0]->buckets);
        rsort($session_period_options);

        $found = array_filter($session_period_options, function($option) use ($session_period_selected) {
            return $option->會期 == $session_period_selected;
        });
        if (empty($found)) {
            $params['屆'] = $term_selected;
            $params['會期'] = $session_period_options[0]->會期;
            $url = strtok($_SERVER['REQUEST_URI'], '?') . '?' . http_build_query($params);
            header('Location: ' . $url, true, 302);
            exit;
        }

        $this->view->term_selected = $term_selected;
        $this->view->session_period_selected = $session_period_selected;
        $this->view->term_options = $term_options;
        $this->view->session_period_options = $session_period_options;
    }

    public function list_ivod_date()
    {
        $date_input = filter_input(INPUT_GET, '日期',FILTER_SANITIZE_STRING) ?? 'latest';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_input)) {
            $res = LYAPI::apiQuery("/ivods?limit=1", "查詢最新 IVOD 日期");
            $ivod_latest = $res->ivods[0];
            $date_input = $ivod_latest->日期;
        }

        $res = LYAPI::apiQuery("/ivods?日期={$date_input}&limit=600", "查詢 IVOD, 條件: 日期: {$date_input}");
        $this->view->date = $date_input;
        $this->view->data = $res;
    }
}
