<?php
    $bill = $this->data->data;

    //retrieve data
    $bill_source = $bill->提案來源 ?? '';
    $bill_source_title = $bill->{'提案單位/提案委員'} ?? '';
    $proposers = $bill->提案人 ?? [];
    $endorsers = implode('、', $bill->連署人 ?? []);

    //提案人
    if ($bill_source != '委員提案' or empty($proposers)) {
        $proposers = $bill_source_title;
    } else {
        $proposers = implode('、', $proposers);
    }

    //對照表
    $diff = null;
    if (property_exists($bill, '對照表')) {
        $diff = LawDiffHelper::lawDiff($bill);
    }
?>
<link href="/static/css/bill/custom_law-diff.css" rel="stylesheet">
<div class="card shadow mt-3 mb-3">
  <a href="#collapseMetadata" class="d-block card-header py-3" data-toggle="collapse"
    role="button" aria-expanded="true" aria-controls="collapseMetadata">
    <h6 class="m-0 font-weight-bold text-primary">基本資料</h6>
  </a>
  <div class="collapse show" id="collapseMetadata">
    <div class="card-body">
      <table class="table">
        <tr>
          <td style="width: 10%;">提案編號</td>
          <td><?= $this->escape($this->data->id[0]) ?></td>
        </tr>
        <?php if (property_exists($bill, '字號')) { ?>
        <tr>
          <td>字號</td>
          <td><?= $this->escape($bill->字號) . "（" . $this->escape($bill->提案編號) . "）" ?></td>
        </tr>
        <?php } ?>
        <?php if (property_exists($bill, '會議代碼:str')) { ?>
        <tr>
          <td>關聯會議</td>
          <td>
            <?= $this->escape($bill->{'會議代碼:str'}) ?>
            （<a href="/collection/item/meet/<?= $this->escape($bill->會議代碼) ?>">連結</a>）
          </td>
        </tr>
        <?php } ?>
        <?php if (!empty($proposers)) { ?>
        <tr>
          <td>提案人</td>
          <td><?= $this->escape($proposers) ?></td>
        </tr>
        <?php } ?>
        <?php if (!empty($endorsers)) { ?>
          <tr>
            <td>連署人</td>
            <td><?= $this->escape($endorsers) ?></td>
          </tr>
        <?php } ?>
        <?php if (property_exists($bill, '案由')) { ?>
          <tr>
            <td>案由</td>
            <td><?= $this->escape($bill->案由) ?></td>
          </tr>
        <?php } ?>
        <?php if (property_exists($bill, '相關附件') and count($bill->相關附件) > 0) { ?>
          <?php foreach ($bill->相關附件 as $idx => $attach) {
            $attaches[] = sprintf('<a href="%s" target="_blank">%s</a>', $this->escape($attach->網址), $this->escape($attach->名稱));
          } ?>
          <tr>
            <td>相關附件</td>
            <td>
              <?= implode("<br>", $attaches) ?>
            </td>
          </tr>
        <?php } ?>
        <tr>
          <td>原始資料</td>
          <td>
            <a href="<?= $this->escape($bill->url) ?>" target="blank">議事暨公報資訊網</a>
          </td>
        </tr>
      </table>
    </div>
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
