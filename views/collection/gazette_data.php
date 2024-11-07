<?php

$gazette = $this->data->data;
$scroll_idx = $gazette->卷 ?? '';
$issue_idx = $gazette->期 ?? '';
$volume_idx = $gazette->冊別 ?? '';
$gazette_idx = sprintf('立法院第%d卷 第%d期 冊別%d', 
    $scroll_idx,
    $issue_idx,
    $volume_idx,
);

$conditions = [
    '卷' => $scroll_idx,
    '期' => $issue_idx,
    '冊別' => $volume_idx,
    'limit' => 1000,
];

$get_query = '';
foreach ($conditions as $key => $val) {
    $get_query .= "&{$key}={$val}";
}
$get_query = preg_replace('/&/', '?', $get_query, 1);
$gazette_agendas = LYAPI::apiQuery('/gazette_agendas' . $get_query, "查詢關連的公報章節");
$gazette_agendas = $gazette_agendas->gazetteagendas;
$gazette_agendas = array_filter($gazette_agendas, function($agenda) use ($scroll_idx, $issue_idx, $volume_idx) {
    return $agenda->卷 == $scroll_idx and $agenda->期 == $issue_idx and $agenda->冊別 == $volume_idx;
});
?>
<style>
  .table td, .table th {
    white-space: nowrap;
  }
</style>
<div class="card shadow mt-3 mb-3">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table">
        <tr>
          <td style="width: 15%">索引編號</td>
          <td><?= $this->escape($gazette_idx) ?></td>
        </tr>
        <tr>
          <td>發布日期</td>
          <td><?= $this->escape($gazette->發布日期 ?? '') ?></td>
        </tr>
      </table>
    </div>
  </div>
</div>
<h2 id="agendas" class="ml-2 mt-4 mb-3 h5">公報章節</h2>
<div class="card shadow mt-3 mb-3">
  <div class="card-body">
    <div class="table-responsive">
      <table id="agendas-table" class="table table-bordered table-hover table-sm">
        <thead>
          <tr>
            <th class="text-center align-middle">公報議程編號</th>
            <th class="text-center align-middle">頁碼</th>
            <th class="text-center align-middle">會議日期</th>
            <th>案由</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($gazette_agendas as $agendas) { ?>
            <tr>
              <td class="text-center align-middle">
                <?= $this->escape($agendas->公報議程編號 ?? '') ?>
                <a href="/collection/item/gazette_agenda/<?= $this->escape($agendas->公報議程編號 ?? '') ?>">
                  <i class="fas fa-fw fa-eye"></i>
                </a>
              </td>
              <td class="text-center align-middle">
                <?= $this->escape($agendas->起始頁碼 ?? '') ?> ~ <?= $this->escape($agendas->結束頁碼 ?? '') ?>
              </td>
              <td class="text-center align-middle">
                <?= $this->escape(implode('、', $agendas->會議日期 ?? [])) ?>
              </td>
              <td><?= $this->escape($agendas->案由 ?? '') ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
