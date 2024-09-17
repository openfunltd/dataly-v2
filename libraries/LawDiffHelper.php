<?php

class LawDiffHelper
{
    public static function lawDiff($bill)
    {
        $bill_no = $bill->議案編號;
        $bills = LyAPI::apiQuery(
            sprintf("/bill/%d/related_bills", urlencode($bill_no)),
            sprintf("查詢 bill %d 的關聯議案", $bill_no)
        );

        if (! property_exists($bills, 'bills')) {
            $bills = [];
            $bills[] = $bill;
        } else {
            $bills = $bills->bills;
            $bills = array_merge([$bill], $bills);
        }

        $diff = [];
        $related_bills = [];
        $bill_n_law_idx_mapping = [];
        foreach ($bills as $bill_idx => $bill) {
            if (! property_exists($bill, '對照表')) {
                continue;
            }

            //hot fix for 對照表會有沒有 rows 的狀況 bill_SN: 20委11005501
            if (! property_exists($bill->對照表[0], 'rows')) {
                $commits = $bill->對照表[1]->rows;
            } else {
                $commits = $bill->對照表[0]->rows;
            }

            $bill_n_law_indexes = [];
            $bill_n_law_indexes['bill_idx'] = $bill_idx;
            $bill_n_law_indexes['law_indexes'] = [];

            foreach ($commits as $commit) {
                $law_idx = self::getLawIndex($commit);
                $isNewLawIndex = (property_exists($commit, '現行') && $commit->現行 != '');
                if (! array_key_exists($law_idx, $diff)) {
                   $diff[$law_idx] = []; 
                   $diff[$law_idx]['current'] = ($isNewLawIndex) ? $commit->現行 : null;
                   $diff[$law_idx]['commits'] = new \stdClass();
                }
                $diff[$law_idx]['commits']->{$bill_idx} = (property_exists($commit, '修正')) ? $commit->修正 : $commit->增訂;
                $bill_n_law_indexes['law_indexes'][] = $law_idx;
            }
            $bill_n_law_idx_mapping[] = $bill_n_law_indexes;

            // render column values into related bills 
            $related_bill = [];
            $related_bill['bill_idx'] = $bill_idx;
            $related_bill['bill_name'] = self::parseBillName($bill);
            $related_bill['version_name'] = $bill->{'提案單位/提案委員'};
            $related_bill['non_first_proposers'] = self::parseNonFirstProposers($bill);
            $related_bill['bill_no'] = (property_exists($bill, '提案編號')) ? $bill->提案編號 : $bill->billNo;
            $related_bill['initial_date'] = self::getInitialDate($bill);
            $related_bills[$bill_idx] = $related_bill;
        }
        $diff_result = self::prettyHtmls($diff);
        return $diff_result;
    }

    public static function prettyHtmls($contents)
    {
        $input = tempnam('/tmp/', 'law-diff-');
        $outcome = file_put_contents($input, json_encode($contents));
        $output = tempnam('/tmp/', 'law-diff-');

        $cmd = sprintf("env NODE_PATH=%s node %s %s %s",
            escapeshellarg(getenv('BASE_PATH') . '/node_modules'),
            escapeshellarg(getenv('BASE_PATH') . '/scripts/text_diff_worker.js'),
            escapeshellarg($input),
            escapeshellarg($output)
        );

        system($cmd, $ret);

        $result = file_get_contents($output);
        $result = json_decode($result);

        unlink($input);
        unlink($output);
        return $result;
    } 

    private static function getLawIndex($commit)
    {
        if (property_exists($commit, '現行') && $commit->現行 != '') {
            $text = $commit->現行;
        } else if (property_exists($commit, '修正')) {
            $text = $commit->修正;
        } else {
            $text = $commit->增訂;
        }
        $text = str_replace('　', ' ', $text);
        return explode(' ', $text)[0];
    }

    private static function parseBillName($bill)
    {
        $bill_name = $bill->議案名稱;
        if (mb_substr($bill_name, 0, 2) === "廢止") {
            $bill_name = explode('，', $bill_name)[0];
            $bill_name = str_replace(['「','」'], '', $bill_name);
        } else if (mb_substr($bill_name, 0, 3) === "擬撤回") {
            return $bill_name;
        } else {
            $start_idx = mb_strpos($bill_name, "「");
            $end_idx = mb_strpos($bill_name, "」");
            $bill_name = mb_substr($bill_name, $start_idx + 1, $end_idx - 1);
        }
        return $bill_name;
    }

    private static function parseNonFirstProposers($bill)
    {
        if (! property_exists($bill, '提案人') || count($bill->提案人) == 1) {
            return '';
        }
        $proposers = $bill->提案人;
        return implode('、', array_slice($proposers, 2));
    }

    private static function getInitialDate($bill)
    {
        $initial_date = 'No Data';
        if (is_null($bill->議案流程) || count($bill->議案流程) === 0) {
            return $initial_date;
        }
        $date_array = $bill->議案流程[0]->日期;
        if (is_null($date_array) || count($date_array) === 0) {
            return $initial_date;
        }
        return $date_array[0];
    }

    public static function add($a, $b) {
        return $a + $b;
    }
}
