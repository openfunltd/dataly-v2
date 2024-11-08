<?php
$agenda = $this->data->data;
$scroll_idx = $agenda->卷;
$issue_idx = $agenda->期;
$volume_idx = $agenda->冊別;
$gazette_idx = sprintf('立法院第%d卷 第%d期 第%d冊',
    $scroll_idx,
    $issue_idx,
    $volume_idx,
);
$agenda_types = [
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

//agenda content
$parsed_doc_urls = $agenda->處理後公報網址 ?? [];
$tikahtml_doc = array_filter($parsed_doc_urls, function($doc) {
    return $doc->type == 'tikahtml';
});
$tikahtml_url = array_shift($tikahtml_doc)->url;
$tikahtml_content = file_get_contents($tikahtml_url);
$allowedTags = '<p><b><i><ul><ol><li><br><div><span><h1><h2><h3><h4><h5><h6>';
$tikahtml_content = strip_tags($tikahtml_content, $allowedTags);
$tikahtml_content = preg_replace('/(on\w+|style)="[^"]*"/i', '', $tikahtml_content);

//doc_urls
$doc_urls = $agenda->doc檔案下載位置 ?? [];
$doc_url_titles = [];
if (count($doc_urls) == 1) {
    $doc_url_titles = ['DOC 檔案下載'];
} else if (count($doc_urls > 1)) {
    foreach ($doc_urls as $idx => $url) {
        $index = $idx + 1;
        $doc_url_titles[] = 'DOC 檔案下載-' . $index;
    }
}

//ppg_url with anchor to id
$ppg_url = $agenda->公報網網址 ?? '';
if ($ppg_url != '') {
    $ppg_url .= '#section-' . $agenda->目錄編號 ?? '';
}
?>

<style>
  #html-content p {
    margin: 2px !important;
  }
  #html-content p:has(b) {
    margin: 10px !important;
  }
</style>
<div class="card shadow mt-3 mb-3">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table">
        <tr>
          <td style="width: 15%">公報索引編號</td>
          <td>
            <?= $this->escape($gazette_idx) ?>
            <a href="/collection/item/gazette/<?= $this->escape($agenda->公報編號 ?? '') ?>">
              <i class="fas fa-fw fa-eye"></i>
            </a>
          </td>
        </tr>
        <tr>
          <td>頁碼</td>
          <td><?= $this->escape($agenda->起始頁碼 ?? '') ?> ~ <?= $this->escape($agenda->結束頁碼 ?? '') ?></td>
        </tr>
        <tr>
          <td>章節類別</td>
          <?php $agenda_type = $agenda->類別代碼 ?? '' ?>
          <td><?= $this->escape($agenda_types[$agenda_type] ?? '') ?></td>
        </tr>
        <tr>
          <td>屆-會期</td>
          <td><?= $this->escape($agenda->屆 ?? '') ?>-<?= $this->escape($agenda->會期 ?? '') ?></td>
        </tr>
        <tr>
          <td>會議日期</td>
          <td><?= $this->escape(implode('、', $agenda->會議日期 ?? [])) ?></td>
        </tr>
        <tr>
          <td>案由</td>
          <td><?= $this->escape($agenda->案由 ?? '') ?></td>
        </tr>
        <?php if (!empty($parsed_doc_urls)) { ?>
          <tr>
            <td>處理後公報網址</td>
            <td>
              <?php foreach ($parsed_doc_urls as $doc) { ?>
                <p class="m-0">
                  <a href="<?= $this->escape($doc->url ?? '') ?>" target="_blank">
                    <?= $this->escape($doc->type ?? '') ?>
                  </a>
                </p>
              <?php } ?>
            </td>
          </tr>
        <?php } ?>
        <tr>
          <td>原始資料連結</td>
          <td>
            <?php if (!empty($doc_urls)) { ?>
              <?php foreach ($doc_urls as $idx => $doc_url) { ?>
                <p class="m-0">
                  <a href="<?= $this->escape($doc_url) ?>" target="_blank">
                    <?= $this->escape($doc_url_titles[$idx]) ?>
                  </a>
                </p>
              <?php } ?>
            <?php } ?>
            <?php if ($ppg_url != '') { ?>
                <p class="m-0"><a href="<?= $this->escape($ppg_url) ?>" target="_blank">立法院議事暨公報資訊網</a></p>
            <?php } ?>
          </td>
        </tr>
      </table>
    </div>
  </div>
</div>
<h2 id="content" class="ml-2 mt-4 mb-3 h5">章節內容</h2>
<div class="card shadow mt-3 mb-3">
  <div id="html-content" class="card-body">
    <?= $tikahtml_content ?>
  </div>
</div>
