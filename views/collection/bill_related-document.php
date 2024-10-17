<?php
    if (!property_exists($this->data->data, '對照表')) {
        echo '無法律對照表';
        return;
    }
    $bill = $this->data->data;

    //提案人
    if ($bill->提案來源 != '委員提案') {
        $proposers = $bill->{'提案單位/提案委員'};
    } else {
        $proposers = $bill->提案人;
        $proposers = implode('、', $proposers);
    }

    //連署人
    $endorsers = '';
    if (property_exists($bill, '連署人')) {
        $endorsers = implode('、', $bill->連署人);
    }

    //相關附件
    $attachment_html = '';
    $attachments = [];
    if (property_exists($bill, '相關附件') and count($bill->相關附件) > 0) {
        foreach ($bill->相關附件 as $attachment) {
            $attachments[] = sprintf('<a href="%s" target="_blank">%s</a>', $attachment->網址, $attachment->名稱);
        }
    }
    $attachment_html = implode('<br>', $attachments);

    $diff = LawDiffHelper::lawDiff($bill);
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
        <tr>
          <td>字號</td>
          <td><?= $this->escape($bill->字號) . "（" . $this->escape($bill->提案編號) . "）" ?></td>
        </tr>
        <tr>
          <td>關聯會議</td>
          <td>
            <?= $this->escape($bill->{'會議代碼:str'}) ?>
            （<a href="/collection/item/meet/<?= $this->escape($bill->會議代碼) ?>">連結</a>）
          </td>
        </tr>
        <tr>
          <td>提案人</td>
          <td><?= $this->escape($proposers) ?></td>
        </tr>
        <?php if ($endorsers != '') { ?>
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
        <?php if ($attachment_html != '') { ?>
          <tr>
            <td>相關附件</td>
            <td><?= $attachment_html ?></td>
          </tr>
        <?php } ?>
      </table>
    </div>
  </div>
</div>
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
