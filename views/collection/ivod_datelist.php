<?php
$week_data = array('日', '一', '二', '三', '四', '五', '六');
$term_selected = 0;
$session_period_selected = 0;
$date_list = [];

//無提供參數時選擇最新的屆期與會期
if (empty($_GET)) {
    $res = LYAPI::apiQuery('/ivods?&limit=1&output_fields=會議資料.屆&output_fields=會議資料.會期&agg=屆', '查詢最新的屆期/會期');
    $term_selected = $res->ivods[0]->會議資料->屆;
    $session_period_latest = $res->ivods[0]->會議資料->會期;
    $res = LYAPI::apiQuery("/ivods?屆={$term_selected}&agg=會期", "查詢第 {$term_selected} 屆所有的會期選項");
    $session_period_options = array_map(function($session_period) {
        $obj = (object) [
            '會期' => $session_period->會期,
        ];
        return $obj;
    },$res->aggs[0]->buckets);
    rsort($session_period_options);
    $session_period_selected = $session_period_options[0]->會期;
}
foreach ($session_period_options as $option) {
    $session_period = $option->會期;
    $res = LYAPI::apiQuery("/ivods?屆={$term_selected}&會期={$session_period}&agg=日期&limit=0",
        "查詢第 {$term_selected} 屆第 {$session_period} 會期 IVOD 數量，依日期分群");
    $option->date_count = count($res->aggs[0]->buckets);
    if ($session_period == $session_period_selected) {
        $date_list = $res->aggs[0]->buckets;
    }
}
foreach ($date_list as $row) {
    $row->日期 = substr($row->日期, 0, 10);
    $row->星期 = $week_data[date('w', strtotime($row->日期))];
}
usort($date_list, function ($rowA, $rowB) {
    return $rowB->日期 <=> $rowA->日期;
});
?>
<div class="row mt-3">
  <div class="col-md-3">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">篩選</h6>
      </div>
      <div class="card-body">
        <div class="mb-4">
          <div id="filter-fields">
            <div class="card shadow mb-4">
              <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary agg-name">屆</h6>
              </div>
              <div class="card-body">
                <?php foreach ($term_options as $idx => $option) { ?>
                <?php } ?>
              </div>
            </div>
          </div>
          <div id="filter-fields">
            <div class="card shadow mb-4">
              <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary agg-name">會期</h6>
              </div>
              <div class="card-body">
                <?php foreach ($session_period_options as $idx => $option) { ?>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-9">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">資料</h6>
      </div>
    <div class="card-body">
    <table class="table table-bordered" id="ivod-date-table" width="100%" cellspacing="0">
      <thead>
        <tr>
          <th>日期</th>
          <th>星期</th>
          <th>IVOD數量</th>
          <th>連結</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($date_list as $row) { ?>
          <tr>
            <td><?= $this->escape($row->日期 ?? '') ?></td>
            <td><?= $this->escape($row->星期 ?? '') ?></td>
            <td><?= $this->escape($row->count ?? 0) ?></td>
            <td>
              <a href="/collection/list/ivod/datelist?date=<?= $this->escape($row->日期 ?? '') ?>">
                <i class="fas fa-fw fa-eye"></i>
              </a>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<script>
  window.onload = function(){
    if ($("#ivod-date-table").length) {
      const table = $('#ivod-date-table').DataTable({
        fixedHeader: true,
      });
    }
  }
</script>
