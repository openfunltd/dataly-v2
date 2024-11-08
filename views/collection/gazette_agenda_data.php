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
?>
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
          <td>類別代碼</td>
          <td><?= $this->escape($agenda->類別代碼 ?? '') ?></td>
        </tr>
        <tr>
          <td>屆-會期</td>
          <td><?= $this->escape($agenda->屆 ?? '') ?>-<?= $this->escape($agenda->會期 ?? '') ?></td>
        </tr>
      </table>
    </div>
  </div>
</div>
