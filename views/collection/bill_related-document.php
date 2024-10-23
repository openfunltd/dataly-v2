<?php
    $bill = $this->data->data;
    $attaches = $bill->相關附件 ?? [];
    $has_related_doc = false;
    foreach ($attaches as $attach) {
        if (mb_strpos($attach->名稱, '關係文書') !== false) {
            $has_related_doc = true;
            break;
        }
    }
?>
<?php if (!$has_related_doc) { ?>
  <div class="mt-2 card border-left-danger">
    <div class="card-body">
      無關係文書
    </div>
  </div>
<?php return; ?>
<?php } ?>
<?php
    //retrieve data
    $bill_source = $bill->提案來源 ?? '';
    $bill_source_title = $bill->{'提案單位/提案委員'} ?? '';
    $proposers = implode('、', $bill->提案人 ?? []);
    $endorsers = implode('、', $bill->連署人 ?? []);

    //對照表
    $diff = null;
    if (property_exists($bill, '對照表')) {
        $law_diff_title = $bill->對照表[0]->title ?? '法律對照表';
        $diff = LawDiffHelper::lawDiff($bill);
    }
?>
<link href="/static/css/bill/custom_law-diff.css" rel="stylesheet">
<div class="card shadow mt-3 mb-3">
  <div class="card-body">
    <table class="table">
      <?php if (property_exists($bill, '字號')) { ?>
      <tr>
        <td style="width: 15%">字號</td>
        <td><?= $this->escape($bill->字號) . "（" . $this->escape($bill->提案編號) . "）" ?></td>
      </tr>
      <?php } ?>
      <?php if (property_exists($bill, '案由')) { ?>
        <tr>
          <td style="width: 15%">案由</td>
          <td><?= $this->escape($bill->案由) ?></td>
        </tr>
      <?php } ?>
      <?php if ($bill_source != '委員提案') { ?>
      <tr>
        <td style="width: 15%">提案單位</td>
        <td><?= $this->escape($bill_source_title) ?></td>
      </tr>
      <?php } ?>
      <?php if ($bill_source == '委員提案') { ?>
      <tr>
        <td style="width: 15%">提案人</td>
        <td><?= $this->escape($proposers) ?></td>
      </tr>
        <tr>
          <td>連署人</td>
          <td><?= $this->escape($endorsers) ?></td>
        </tr>
      <?php } ?>
      <?php foreach ($attaches as $attach) {
          if (mb_strpos($attach->名稱, '關係文書') === false) {
              continue;
          }
          $attach_url = (mb_strpos($attach->網址, 'https') === 0) ? $attach->網址 : 'https://ppg.ly.gov.tw' . $attach->網址;
          $links[] = sprintf('<a href="%s" target="_blank">%s</a>', $this->escape($attach_url), $this->escape($attach->名稱));
      } ?>
      <tr>
        <td style="width: 15%">檔案連結</td>
        <td>
          <?= implode("<br>", $links) ?>
        </td>
      </tr>
    </table>
  </div>
</div>
<?php if (is_null($diff)) { ?>
<div class="card border-left-danger">
  <div class="card-body">
    無法律對照表
  </div>
</div>
<?php } ?>
<?php if (isset($diff)) { ?>
<h2 id="law-diff" class="ml-2 mt-4 mb-3 h5"><?= $law_diff_title ?></h2>
<div class="row">
  <div class="col-lg-2 law-idx-list">
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">條文索引</h6>
      </div>
      <div class="card-body law-idx-a-list"></div>
    </div>
  </div>
  <div class="col-lg-10 diff-tables"></div>
</div>
<script>
  const diffData = <?= json_encode($diff) ?>;
</script>
<script type="module">
    import Diff from 'https://cdn.jsdelivr.net/npm/text-diff@1.0.1/+esm';
    window.Diff = Diff;
</script>
<script src="/static/js/bill/custom_law-diff.js"></script>
<?php } ?>
