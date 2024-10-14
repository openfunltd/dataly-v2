<?php

class ZhNumConverter {
    // only support 1 ~ 9999 for now
    public static function getZhNum($integer)
    {
        if ($integer < 0 || $integer >= 10000) {
            return null;
        }
        $digits = str_split(strval($integer));
        $max_place = count($digits);
        $zh_units = array_slice(self::$zh_units, count(self::$zh_units) - $max_place);
        $output = '';
        $previous_is_zero = false;
        $zh_digits = self::$zh_digits;
        foreach ($digits as $idx => $digit) {
            if ($previous_is_zero and $digit != '0') {
                $output .= $zh_digits[0];
            }
            if ($digit == '0') {
                $previous_is_zero = true;
                continue;
            }
            $output .= $zh_digits[$digit] . $zh_units[$idx];    
            $previous_is_zero = false;
        }
        if (mb_strpos($output, '一十') == 0) {
            $output = preg_replace('/一十/', '十', $output);
        }
        return $output;
    }

    private static $zh_units = ['千', '百', '十', ''];
    private static $zh_digits = ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九'];
}
