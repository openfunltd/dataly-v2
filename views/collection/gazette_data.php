<?php

$gazette = $this->data->data;
$scroll_idx = $gazette->卷 ?? '';
$issue_idx = $gazette->期 ?? '';
$volume_idx = $gazette->冊別 ?? '';
$gazette_idx = sprintf('立法院第%d卷 第%d期 第%d冊', 
    $scroll_idx,
    $issue_idx,
    $volume_idx,
);
$ppg_url = sprintf('https://ppg.ly.gov.tw/ppg/publications/official-gazettes/%d/%d/%s/details',
    $scroll_idx,
    $issue_idx,
    str_pad($volume_idx, 2, '0', STR_PAD_LEFT),
);

$conditions = [
    '卷' => $scroll_idx,
    '期' => $issue_idx,
    '冊別' => $volume_idx,
    'limit' => 1000,
];

$agendaTypes = [
    1 => '院會',
    2 => '國是論壇',
    3 => '委員會',
    4 => '質詢事項',
    5 => '議事錄',
    8 => '黨團協商紀錄',
    9 => '發言索引',
    10 => '報告事項',
    11 => '討論事項',
    12 => '臨時提案',
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

$ppg_url = $gazette_agendas[0]->公報網網址;
$gazette_pdf_url = $gazette_agendas[0]->公報完整PDF網址;
?>
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
        <tr>
          <td>原始資料連結</td>
          <td>
            <p class="m-0"><a href="<?= $this->escape($ppg_url) ?>" target="_blank">立法院議事暨公報資訊網</a></p>
            <p class="m-0"><a href="<?= $this->escape($gazette_pdf_url) ?>" target="_blank">完整公報 PDF</a></p>
          </td>
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
            <th class="text-center align-middle" style="width: 10%">公報議程編號</th>
            <th class="text-center align-middle" style="width: 6%">章節類別</th>
            <th class="text-center align-middle" style="width: 6%">頁碼</th>
            <th class="text-center align-middle">會議日期</th>
            <th class="text-center align-middle">案由</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($gazette_agendas as $agenda) { ?>
            <tr>
              <td class="text-center align-middle">
                <?= $this->escape($agenda->公報議程編號 ?? '') ?>
                <a href="/collection/item/gazette_agenda/<?= $this->escape($agenda->公報議程編號 ?? '') ?>">
                  <i class="fas fa-fw fa-eye"></i>
                </a>
              </td>
              <td class="text-center align-middle">
                <?php $agendaType = $agenda->類別代碼 ?? ''; ?>
                <?= $this->escape($agendaTypes[$agendaType] ?? '') ?>
              </td>
              <td class="text-center align-middle">
                <?= $this->escape($agenda->起始頁碼 ?? '') ?> ~ <?= $this->escape($agenda->結束頁碼 ?? '') ?>
              </td>
              <td class="text-center align-middle">
                <?= $this->escape(implode('、', $agenda->會議日期 ?? [])) ?>
              </td>
              <td><?= $this->escape($agenda->案由 ?? '') ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
