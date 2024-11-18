<?php
$week_data = array('日', '一', '二', '三', '四', '五', '六');
$term_selected = filter_input(INPUT_GET, '屆', FILTER_VALIDATE_INT) ?? -1;
$session_period_selected = filter_input(INPUT_GET, '會期', FILTER_VALIDATE_INT) ?? -1;
$date_list = [];

function redirectValidParams($term, $session_period) {
    $params['屆'] = $term;
    $params['會期'] = $session_period;
    $url = strtok($_SERVER['REQUEST_URI'], '?') . '?' . http_build_query($params);
    header('Location: ' . $url, true, 302);
    exit;
}

$res = LYAPI::apiQuery('/ivods?&limit=30&output_fields=會議資料.屆&output_fields=會議資料.會期&agg=屆', '查詢最新的屆期/會期');
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
    redirectValidParams($term_latest, $session_period_latest);
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
    redirectValidParams($term_selected, $session_period_options[0]->會期);
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
                <?php foreach ($term_options as $term) { ?>
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="radio" name="term"
                      id="term-<?= $this->escape($term) ?>"
                      value="<?= $this->escape($term) ?>"
                      <?= ($term == $term_selected) ? 'checked' : '' ?>
                    >
                    <label class="form-check-label" for="term-<?= $this->escape($term) ?>">
                      <?= $this->escape($term) ?>
                    </label>
                  </div>
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
                <?php foreach ($session_period_options as $option) { ?>
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="radio"
                      name="session_period"
                      id="session_period-<?= $this->escape($option->會期) ?>"
                      value="<?= $this->escape($option->會期) ?>"
                      <?= ($option->會期 == $session_period_selected) ? 'checked' : '' ?>
                    >
                    <label class="form-check-label" for="session_period-<?= $this->escape($option->會期) ?>">
                      <?= $this->escape($option->會期) ?>
                    </label>
                  </div>
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
              <a href="/collection/list/ivod/datelist?日期=<?= $this->escape($row->日期 ?? '') ?>">
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
    $('input[type="radio"][name="term"]').on('change', function() {
        const term = $(this).val();
        const nextUrl = `/collection/list/ivod?屆=${term}`;
        window.location.replace(nextUrl);
    });
    $('input[type="radio"][name="session_period"]').on('change', function() {
        const term = $('input[name="term"]:checked').val();
        const sessionPeriod = $(this).val();
        const nextUrl = `/collection/list/ivod?屆=${term}&會期=${sessionPeriod}`;
        window.location.replace(nextUrl);
    });
  }
</script>
