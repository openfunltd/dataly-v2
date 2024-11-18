<?php
$is_valid_date = false;
$date_input = filter_input(INPUT_GET, '日期',FILTER_SANITIZE_STRING);
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_input)) {
    echo $this->partial('partial/ivod_datelist_date', ['date_input' => $date_input]);
} else {
    echo $this->partial('partial/ivod_datelist_list');
}
?>
