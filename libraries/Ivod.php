<?php

class Ivod {

    public static function getSubjects($meet_name)
    {
        $meet_name = trim($meet_name);
        $reason_raw = self::getRawReason($meet_name);
        $zh_one = ZhNumConverter::getZhNum(1);
        $with_first_order_index = mb_strpos($reason_raw, $zh_one) === 0;

        if (!$with_first_order_index) {
            $subjects = [];
            $subjects[] = $reason_raw;
        } else {
            $subjects = self::parseSubjects($reason_raw);
        }

        return $subjects;
    }

    public static function getRawReason($meet_name)
    {
        $meet_name = trim($meet_name);
        $start_idx = mb_strpos($meet_name, "（事由：");
        $end_idx = mb_strrpos($meet_name, "）");
        $reason = mb_substr($meet_name, $start_idx + 4, $end_idx - ($start_idx + 4));
        $reason = preg_replace('/【.*?】/', '', $reason);
        $reason = trim($reason);
        return $reason;
    }

    public static function parseSubjects($reason_raw)
    {
        $subjects = [];
        $last_index = 0;
        for ($order = 1; $order <= 9999; $order++) {
            $idx = ZhNumConverter::getZhNum($order + 1);
            $last_idx = ZhNumConverter::getZhNum($order);
            try {
                $current_idx_offset = mb_strlen($idx);
            } catch (\Exception $e) {
                // zh index 超出 ZhNumConverter 支援
                return null;
            }
            $last_idx_offset = mb_strlen($last_idx) + 1;
            $current_index = mb_strpos($reason_raw, $idx);

            // current_index 應該要是最上層索引編號的位置
            // 但有時會遇到「第十六條之『二、』」的「二、」被認為是索引的誤判
            // 所以特別用下列的 code 偵測誤判並跳過
            $previous_char = mb_substr($reason_raw, $current_index - 1, 1);
            while ($current_index !== 0 && $current_index !== false && !in_array($previous_char, ["\n", ' '])) {
                $current_index = mb_strpos($reason_raw, $idx, $current_index + $current_idx_offset);
                $previous_char = mb_substr($reason_raw, $current_index - 1, 1);
            }

            if ($current_index === false) {
                $subjects[] = trim(mb_substr($reason_raw, $last_index + $last_idx_offset));
                break;
            }
            $subjects[] = trim(mb_substr($reason_raw, $last_index + $last_idx_offset, $current_index - ($last_index + $last_idx_offset)));
            $last_index = $current_index;
        }
        return $subjects;
    }
}
