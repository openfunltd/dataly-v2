<?php
$date_input = $this->_data['date_input'] ?? date('Y-m-d');
$res = LYAPI::apiQuery("/ivods?日期={$date_input}&limit=600", "查詢 IVOD, 條件: 日期: {$date_input}");
$ivod_count = $res->total ?? 0;
?>
<?php if ($ivod_count === 0) { ?>
  <div class="mt-3 card border-left-danger">
    <div class="card-body">
      日期：<?= $this->escape($date_input) ?> 無 IVOD
    </div>
  </div>
<?php return; } ?>
<?php
$ivods = $res->ivods;
$ivods = array_reverse($ivods);
$term = max(array_map(fn($ivod) => $ivod->會議資料->屆 ?? -1, $ivods));
$session_period = max(array_map(fn($ivod) => $ivod->會議資料->會期 ?? -1, $ivods));
$meets = [];
foreach ($ivods as $ivod) {
    $meet_id = $ivod->會議資料->會議代碼 ?? 'unknown-' . crc32($ivod->會議名稱);
    if (!array_key_exists($meet_id, $meets)) {
        $meets[$meet_id] = new stdClass();
        $meets[$meet_id]->id = $meet_id;
        if (strpos($meet_id, 'unknown') === 0) {
            $meets[$meet_id]->title = preg_replace('#（事由.*#', '', $ivod->會議名稱);
            $meets[$meet_id]->reason = $meets[$meet_id]->title;
        } else {
            $meets[$meet_id]->title = $ivod->會議資料->標題 ?? preg_replace('#（事由.*#', '', $ivod->會議名稱);
            $meets[$meet_id]->reason = $ivod->會議名稱;
        }
        $subjects = MeetSubjectHelper::getSubjects($meets[$meet_id]->reason) ?? $meets[$meet_id]->reason;
        $subjects_digested = MeetSubjectHelper::digestSubjects($subjects);
        $related_laws = MeetSubjectHelper::getLaws($subjects);
        $meets[$meet_id]->compacted_reasons = $subjects_digested;
        $meets[$meet_id]->related_laws = $related_laws;
        $meets[$meet_id]->ivods = [];
    }
    $meets[$meet_id]->ivods[] = $ivod;
}
?>
<h2 class="ml-2 mt-3 h3">IVOD 列表 :: <?= $this->escape($date_input) ?></h2>
<a class="my-0 btn btn-primary" href="/collection/list/ivod/datelist?屆=<?= $this->escape($term) ?>&會期=<?= $this->escape($session_period) ?>">
  選其他日期
</a>
<div class="card shadow mt-2 mb-3">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-hover table-sm">
        <thead>
          <tr>
            <th class="text-center align-middle">會議</th>
            <th class="text-center align-middle">事由</th>
            <th class="text-center align-middle">關聯法律</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($meets as $meet) { ?>
            <tr>
              <td class="text-center align-middle" style="width: 35%;">
                <a href="#<?= $this->escape($meet->id) ?>">
                  <?= $this->escape($meet->title) ?>
                </a>
              </td>
              <td>
                <?php foreach ($meet->compacted_reasons as $compacted_reason) { ?>
                  <p class="m-0"><?= $this->escape($compacted_reason ?? '') ?></p>
                <?php } ?>
              </td>
              <td style="width: 14%;">
                <?php foreach ($meet->related_laws as $law_name) { ?>
                  <p class="m-0"><?= $this->escape($law_name ?? '') ?></p>
                <?php } ?>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php foreach ($meets as $meet) { ?>
  <div class="card shadow mt-3 mb-3">
    <div class="card-header py-1">
      <h2 id="<?= $this->escape($meet->id) ?>" class="mt-2 mb-2 h3"><?= $this->escape($meet->title) ?></h2>
      <?php foreach ($meet->compacted_reasons as $compacted_reason) { ?>
        <p class="m-0"><?= $this->escape($compacted_reason ?? '') ?></p>
      <?php } ?>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm">
          <thead>
            <tr>
              <th class="text-center align-middle">委員名稱</th>
              <th class="text-center align-middle">發言時間</th>
              <th class="text-center align-middle">影片長度</th>
              <th class="text-center align-middle">功能</th>
              <th class="text-center align-middle">原始連結</th>
            </tr>
          </thead>
          <tbody>
            <?php
            usort($meet->ivods, function($ivodA, $ivodB) {
                return $ivodA->開始時間 <=> $ivodB->開始時間;
            });
            ?>
            <?php foreach ($meet->ivods as $ivod) { ?>
              <tr>
                <td class="text-center align-middle"><?= $this->escape($ivod->委員名稱 ?? '-') ?></td>
                <td class="text-center align-middle"><?= $this->escape($ivod->委員發言時間 ?? '-') ?></td>
                <td class="text-center align-middle">
                  <?= $this->escape(gmdate('H:i:s', $ivod->影片長度) ?? '-') ?>
                </td>
                <td class="text-center align-middle">
                  <?php if (in_array('ai-transcript', $ivod->支援功能)) { ?>
                    <a href="/collection/item/ivod/<?= $this->escape($ivod->IVOD_ID) ?>/ai-transcript">
                      AI 逐字稿
                    </a>
                  <?php } else { ?>
                    AI 逐字稿
                  <?php } ?>
                  |
                  <?php if (in_array('gazette', $ivod->支援功能)) { ?>
                    <a href="/collection/item/ivod/<?= $this->escape($ivod->IVOD_ID) ?>/gazette">
                      公報逐字稿
                  <?php } else { ?>
                    公報逐字稿
                  <?php } ?>
                </td>
                <td class="text-center align-middle">
                  <a href="<?= $this->escape($ivod->IVOD_URL) ?>" target="_blank">
                    立法院 IVOD 系統
                  </a>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php } ?>
