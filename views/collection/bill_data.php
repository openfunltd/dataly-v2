<?php
    $bill = $this->data->data;
    $bill_code_full = $bill->字號 ?? '';
    $bill_code_short = $bill->提案編號 ?? '';
    $bill_code = ($bill_code_short != '') ? $bill_code_full . "（" . $bill_code_short . "）" : $bill_code_full;
    $bill_reason = $bill->案由 ?? '';
    $proposers = implode('、', $bill->提案人 ?? []);
    $endorsers = implode('、', $bill->連署人 ?? []);
    $attaches = $bill->相關附件 ?? [];
    $source_url = $bill->url ?? '';
?>
<div class="card shadow mt-3 mb-3">
  <div class="card-body">
    <table class="table">
      <tr>
        <td style="width: 15%">議案類別</td>
        <td><?= $this->escape($bill->議案類別 ?? '') ?></td>
      </tr>
      <tr>
        <td>議案名稱</td>
        <td><?= $this->escape($bill->議案名稱 ?? '') ?></td>
      </tr>
      <tr>
        <td>提案來源</td>
        <td><?= $this->escape($bill->提案來源 ?? '') ?></td>
      </tr>
      <tr>
        <td>提案單位/提案委員</td>
        <td><?= $this->escape($bill->{'提案單位/提案委員'} ?? '') ?></td>
      </tr>
      <tr>
        <td>議案狀態</td>
        <td><?= $this->escape($bill->議案狀態 ?? '') ?></td>
      </tr>
      <tr>
        <td>字號</td>
        <td><?= $this->escape($bill_code) ?></td>
      </tr>
      <?php if ($proposers != '') { ?>
        <tr>
          <td>提案人</td>
          <td><?= $this->escape($proposers) ?></td>
        </tr>
      <?php } ?>
      <?php if ($endorsers != '') { ?>
        <tr>
          <td>連署人</td>
          <td><?= $this->escape($endorsers) ?></td>
        </tr>
      <?php } ?>
      <?php if (!empty($attaches)) { ?>
        <tr>
          <td>相關附件</td>
          <td>
            <?php
                foreach ($attaches as $idx => $attach) {
                    if ($idx != 0) {
                        echo '<br>';
                    }
                    $url = (mb_strpos($attach->網址, 'https://') === 0) ? $attach->網址 : 'https://ppg.ly.gov.tw/ppg' . $attach->網址;
                    echo sprintf('<a href="%s" target="_blank">%s</a>', $url, $attach->名稱);
                }
            ?>
          </td>
        </tr>
      <?php } ?>
      <tr>
        <td>原始資料連結</td>
        <td><a href="<?= $this->escape($source_url) ?>" target="_blank"><?= $this->escape($source_url) ?></a></td>
      </tr>
    </table>
  </div>
</div>
