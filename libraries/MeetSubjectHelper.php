<?php

class MeetSubjectHelper
{
    public static function getSubjects($meet_name) {
        $first_order_indexes = self::$first_order_indexes;
        $content = self::parseReason(trim($meet_name));
        $with_first_order_index = mb_strpos($content, $first_order_indexes[0]) === 0;

        if (! $with_first_order_index) {
            $subjects = [];
            $subjects[] = $content;
        } else {
            $subjects = self::parseSubjects($content, $first_order_indexes);
        }

        return $subjects;
    }

    public static function digestSubjects($subjects)
    {
        $digested_subjects = array_map(function ($subject) {
            $digest = self::getBillSubject($subject);
            if (! $digest) {
                $digest = self::getI12nSubject($subject);
            }
            return $digest;
        }, $subjects);
        $merged_subjects = [];
        foreach ($digested_subjects as $idx => $metadata) {
            if (! $metadata) {
                $merged_subjects[] = ['polyfill', $subjects[$idx]];
                continue;
            }
            $subject_type = $metadata[0];
            if ($subject_type == 'i12n') {
                $merged_subjects[] = $metadata;
            }
            if ($subject_type == 'bill') {
                $isMerged = false;
                foreach ($merged_subjects as &$existing_metadata) {
                    if ($existing_metadata[1] == $metadata[1]) {
                        $existing_metadata[3] = $existing_metadata[3] + $metadata[3];
                        $isMerged = true;
                        break;
                    }
                }
                if (! $isMerged) {
                    $merged_subjects[] = $metadata;
                }
            }
        }
        $digested_subjects = array_map(function ($metadata) {
            $subject_type = $metadata[0];
            if (in_array($subject_type, ['i12n', 'polyfill'])) {
                return $metadata[1];
            }
            if ($subject_type == 'bill') {
                $law = $metadata[1];
                $law_type = $metadata[2];
                $bill_cnt = $metadata[3];
                if ($bill_cnt == 1) {
                    $result = sprintf("審查「%s」%s草案", $law, $law_type);
                } else {
                    $result = sprintf("併 %d 案審查「%s」%s草案", $bill_cnt, $law, $law_type);
                }
                return $result;
            }
            return 'error';
        }, $merged_subjects);
        return $digested_subjects;
    }

    public static function getLaws($subjects)
    {
        $bracket_pairs = ['「」', '（）', '《》', '『』'];
        $bracket_starters = array_map(fn ($pairs) => mb_substr($pairs, 0, 1), $bracket_pairs);
        $raw_texts = [];
        foreach($subjects as $subject) {
            $text_start_idx = null;
            $bracket_ender = null;
            foreach(mb_str_split($subject) as $char_idx => $char) {
                $bracket_idx = array_search($char, $bracket_starters);
                if ($bracket_idx !== false) {
                    $text_start_idx = $char_idx;
                    $bracket_ender = mb_substr($bracket_pairs[$bracket_idx], 1);
                    continue;
                }
                if ($bracket_ender == $char) {
                    $raw_texts[] = mb_substr($subject, $text_start_idx + 1, $char_idx - ($text_start_idx + 1));
                    $text_start_idx = null;
                    $bracket_ender = null;
                }
            }
        }
        $laws = [];
        foreach ($raw_texts as $raw_text) {
            $law = self::extractLawName($raw_text);
            if (isset($law) && ! in_array($law, $laws)) {
                $laws[] = $law;
            }
        }
        return $laws;
    }

    public static function getRelatedLawsWithId($related_laws)
    {
        foreach ($related_laws as &$law) {
            $law_name = $law;
            $res = LyAPI::apiQuery("/laws?q=\"$law_name\"", "查詢 law_id {$law_name}");
            $law_id = null;
            $res_laws = $res->laws ?? [];
            foreach ($res_laws as $res_law) {
                $res_law_name = $res_law->名稱 ?? '';
                if ($res_law_name == $law_name) {
                    $law_id = $res_law->法律編號;
                    break;
                }
            }
            $law = (object) [
                'law_name' => $law_name,
                'law_id' => $law_id,
            ];
        }
        return $related_laws;
    }

    private static function parseReason($raw) {
        $start_idx = mb_strpos($raw, "（事由：");
        $end_idx = mb_strrpos($raw, "）");
        $content = mb_substr($raw, $start_idx + 4, $end_idx - ($start_idx + 4));
        $content = preg_replace('/【.*?】/', '', $content);
        $content = trim($content);
        return $content;
    }

    private static function parseSubjects($content, $first_order_indexes)
    {
        $subjects = [];
        $last_index = 0;
        foreach ($first_order_indexes as $order => $idx) {
            try {
                $current_idx_offset = mb_strlen($first_order_indexes[$order + 1]);
            } catch (\Exception $e) {
                // chinese index 超出 $first_order_indexes 支援
                return null;
            }
            $last_idx_offset = mb_strlen($idx);
            if ($order == 100) {
                //代表有可能該會會議要處理的事項超過 100 個，目前僅支援 100 個
                $subjects[] = trim(mb_substr($content, $last_index + $last_idx_offset));
            }
            $current_index = mb_strpos($content, $first_order_indexes[$order + 1]);

            // current_index 應該要是最上層索引編號的位置
            // 但有時會遇到「第十六條之『二、』」的「二、」被認為是索引的誤判
            // 所以特別用下列的 code 偵測誤判並跳過
            $previous_char = mb_substr($content, $current_index - 1, 1);
            while ($current_index !== false && ! in_array($previous_char, ["\n", ' ', '。'])) {
                $current_index = mb_strpos($content, $first_order_indexes[$order + 1], $current_index + $current_idx_offset);
                $previous_char = mb_substr($content, $current_index - 1, 1);
            }

            if (! $current_index) {
                $subjects[] = trim(mb_substr($content, $last_index + $last_idx_offset));
                break;
            }
            $subjects[] = trim(mb_substr($content, $last_index + $last_idx_offset, $current_index - ($last_index + $last_idx_offset)));
            $last_index = $current_index;
        }
        return $subjects;
    }

    private static function getBillSubject($subject)
    {
        $keyword = '擬具';
        if (mb_strpos($subject, $keyword)) {
            $lines = explode("\n", $subject);
            $bill_cnt = 0;
            $law_raw = '';
            foreach ($lines as $line) {
                if (mb_strpos($line, $keyword)) {
                    $bill_cnt++;
                    $start_idx = mb_strpos($line, '「');
                    $end_idx = mb_strpos($line, '」');
                    $current_law_raw = mb_substr($line, $start_idx + 1, $end_idx - ($start_idx + 1));
                    //以提案中法條名稱字最少的那一個為準
                    if (mb_strlen($law_raw) == 0 || mb_strlen($law_raw) > mb_strlen($current_law_raw)) {
                        $law_raw = $current_law_raw;
                    }
                }
            }
            //擷取提案法條名稱中母法名稱
            $law = self::extractLawName($law_raw) ?? $law_raw;

            //辨認 commit 是全新、修正或增訂
            $isUpdate = mb_strpos($law_raw, '修正');
            $isAppend = mb_strpos($law_raw, '增訂');
            $law_type = '新法';
            if ($isUpdate) {
                $law_type = '修正';
            } else if ($isAppend) {
                $law_type = '增訂';
            }

            return ['bill', $law, $law_type, $bill_cnt];
        }
        return false;
    }

    private static function getI12nSubject($subject)
    {
        $keyword = '質詢';
        if (mb_strpos($subject, $keyword)) {
            return ['i12n', $subject];
        }
        return false;
    }

    private static function extractLawName($raw_text)
    {
        $law_end_idx1 = mb_strrpos($raw_text, '法');
        $law_end_idx2 = mb_strrpos($raw_text, '條例');
        $exception_end_idx1 = mb_strrpos($raw_text, '作法');
        $exception_end_idx2 = mb_strrpos($raw_text, '做法');
        $law_name = null;
        if ($law_end_idx1 and ($law_end_idx1 != $exception_end_idx1 + 1) and ($law_end_idx1 != $exception_end_idx2 + 1)) {
            $law_name = mb_substr($raw_text, 0, $law_end_idx1 + 1);
        } else if ($law_end_idx2) {
            $law_name = mb_substr($raw_text, 0, $law_end_idx2 + 2);
        }
        return $law_name;
    }

    private static $first_order_indexes = ['一、', '二、', '三、', '四、', '五、', '六、', '七、', '八、', '九、', '十、',
        '十一、', '十二、', '十三、', '十四、', '十五、', '十六、', '十七、', '十八、', '十九、', '二十、',
        '二十一', '二十二', '二十三', '二十四', '二十五', '二十六', '二十七', '二十八', '二十九', '三十',
        '三十一、', '三十二、', '三十三、', '三十四、', '三十五、', '三十六、', '三十七、', '三十八、', '三十九、', '四十、',
        '四十一、', '四十二、', '四十三、', '四十四、', '四十五、', '四十六、', '四十七、', '四十八、', '四十九、', '五十、',
        '五十一、', '五十二、', '五十三、', '五十四、', '五十五、', '五十六、', '五十七、', '五十八、', '五十九、', '六十、',
        '六十一、', '六十二、', '六十三、', '六十四、', '六十五、', '六十六、', '六十七、', '十六八、', '六十九、', '六七十、',
        '七十一、', '七十二、', '七十三、', '七十四、', '七十五、', '七十六、', '七十七、', '七十八、', '七十九、', '八十、',
        '八十一、', '八十二、', '八十三、', '八十四、', '八十五、', '八十六、', '八十七、', '八十八、', '八十九、', '九十、',
        '九十一、', '九十二、', '九十三、', '九十四、', '九十五、', '九十六、', '九十七、', '九十八、', '九十九、', '一百、',
        '一百零一、',
    ];
}
