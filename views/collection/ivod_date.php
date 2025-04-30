<?php
$date = $this->date;
$data = $this->data;
$ivod_count = $data->total ?? 0;
?>
<?php if ($ivod_count === 0) { ?>
  <div class="mt-3 card border-left-danger">
    <div class="card-body">
      日期：<?= $this->escape($date) ?> 無 IVOD
    </div>
  </div>
  <a class="mt-3 btn btn-primary" href="/collection/list/ivod/datelist">
    選其他日期
  </a>
<?php return; } ?>
<?php
$ivods = $data->ivods;
$ivods = array_reverse($ivods);
$term = max(array_map(fn($ivod) => $ivod->會議資料->屆 ?? -1, $ivods));
$session_period = max(array_map(fn($ivod) => $ivod->會議資料->會期 ?? -1, $ivods));
$meets = [];
foreach ($ivods as $ivod) {
    $meet_id = $ivod->會議資料->會議代碼 ?? 'unknown-' . crc32($ivod->會議名稱);
    if (!array_key_exists($meet_id, $meets)) {
        $meets[$meet_id] = new stdClass();
        $meets[$meet_id]->id = $meet_id;
        $title_end_idx = mb_strpos($ivod->會議名稱, '（事由');
        $ivod_meet_title = ($title_end_idx !== false) ? mb_substr($ivod->會議名稱, 0, $title_end_idx) : $ivod->會議名稱;
        if (strpos($meet_id, 'unknown') === 0) {
            $meets[$meet_id]->title = $ivod_meet_title;
        } else {
            $meets[$meet_id]->title = $ivod->會議資料->標題 ?? $ivod_meet_title;
        }
        $meets[$meet_id]->reason = $ivod->會議名稱;
        $subjects = MeetSubjectHelper::getSubjects($meets[$meet_id]->reason);
        $subjects_digested = MeetSubjectHelper::digestSubjects($subjects);
        $related_laws = MeetSubjectHelper::getLaws($subjects);
        $related_laws = MeetSubjectHelper::getRelatedLawsWithId($related_laws);
        $meets[$meet_id]->compacted_reasons = $subjects_digested;
        $meets[$meet_id]->related_laws = $related_laws;
        $meets[$meet_id]->ivods = [];
    }
    $meets[$meet_id]->ivods[] = $ivod;
}
$res = LyAPI::apiQuery(
    "/legislators?屆={$term}&output_fields=委員姓名&output_fields=黨籍&limit=300",
    "查詢第 {$term} 立法委員基本資料"
);
$legislators = $res->legislators;
$party_icon_urls = PartyHelper::$icon_urls;
?>
<style>
.party-icon {
    width: 20px;
    margin: 0 3px 3px 3px;
}
</style>
<div class="card mt-3 border-left-danger">
  <div class="card-body">
    近期因歐噴採用 4090 顯卡實驗在 fine-tune 微調訓練更準確立法院辨識模型，因此會有 AI 逐字稿產製較慢的情況，請多多見諒，請期待歐噴訓練結果，之後就會有更準確更好用的 AI 逐字稿給大家使用
  </div>
</div>
<h2 class="ml-2 mt-3 h3">IVOD 列表 :: <?= $this->escape($date) ?></h2>
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
                <?php foreach ($meet->related_laws as $law_obj) { ?>
                  <p class="m-0">
                    <?= $this->escape($law_obj->law_name) ?>
                    <?php if (isset($law_obj->law_id)) { ?>
                      <a href="/collection/item/law/<?= $this->escape($law_obj->law_id) ?>">
                        <i class="fas fa-fw fa-eye"></i>
                      </a>
                    <?php }?>
                  </p>
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
            <?php
            foreach ($meet->ivods as $ivod) {
                $ivod_legislator_name = $ivod->委員名稱 ?? '-';
                if (str_contains($ivod_legislator_name, '伍麗華')) {
                    $ivod_legislator_name = str_replace(' ', '‧', $ivod_legislator_name);
                }
                $legislator_info = array_values(array_filter($legislators, function($legislator) use ($ivod_legislator_name) {
                   return preg_replace('/[\s‧]+/', '', $ivod_legislator_name) == preg_replace('/[\s‧]+/', '', $legislator->委員姓名);
                }))[0] ?? new stdClass();
                $party = $legislator_info->黨籍 ?? '-';
            ?>
              <tr>
                <td class="text-center align-middle">
                  <?php if (array_key_exists($party, $party_icon_urls)) { ?>
                    <span
                      class="wiki-tooltip"
                      term="<?= $this->escape($term) ?>"
                      legislator-name="<?= $this->escape($ivod_legislator_name) ?>"
                    >
                      <a class="no-link"><?= $this->escape($ivod_legislator_name) ?></a>
                    </span>
                    <img class="party-icon" src="<?= $this->escape($party_icon_urls[$party]) ?>" alt="<?= $this->escape($party) ?>">
                  <?php } else { ?>
                    <span><?= $this->escape($ivod_legislator_name) ?></span>
                  <?php } ?>
                </td>
                <td class="text-center align-middle"><?= $this->escape($ivod->委員發言時間 ?? '-') ?></td>
                <td class="text-center align-middle">
                  <?= $this->escape(gmdate('H:i:s', $ivod->影片長度) ?? '-') ?>
                </td>
                <td class="text-center align-middle">
                  <?php if (in_array('ai-transcript', $ivod->支援功能 ?? [])) { ?>
                    <a href="/collection/item/ivod/<?= $this->escape($ivod->IVOD_ID) ?>/ai-transcript">
                      AI 逐字稿
                    </a>
                  <?php } else { ?>
                    AI 逐字稿
                  <?php } ?>
                  |
                  <?php if (in_array('gazette', $ivod->支援功能 ?? [])) { ?>
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
<?= $this->partial('partial/tooltip');
