<?php
$vote = $this->data->data;
$result = $vote->表決結果 ?? null;
?>
<div class="card shadow mt-3 mb-3">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table">
        <tr>
          <td style="width: 15%">屆</td>
          <td><?= $this->escape($vote->屆 ?? '') ?></td>
        </tr>
        <tr>
          <td>會議代碼</td>
          <td>
            <?= $this->escape($vote->會議代碼 ?? '') ?>
            <?php if (!empty($vote->會議代碼)) { ?>
              <a href="/collection/item/meet/<?= $this->escape($vote->會議代碼) ?>">
                <i class="fas fa-fw fa-eye"></i>
              </a>
            <?php } ?>
          </td>
        </tr>
        <tr>
          <td>會議名稱</td>
          <td><?= $this->escape($vote->會議名稱 ?? '') ?></td>
        </tr>
        <tr>
          <td>表決型態</td>
          <td><?= $this->escape($vote->表決型態 ?? '') ?></td>
        </tr>
        <tr>
          <td>表決時間</td>
          <td><?= $this->escape($vote->表決時間 ?? '') ?></td>
        </tr>
        <tr>
          <td>表決議題</td>
          <td><?= $this->escape($vote->表決議題 ?? '') ?></td>
        </tr>
        <?php if ($result) { ?>
        <tr>
          <td>出席人數</td>
          <td><?= $this->escape($result->出席人數 ?? '') ?></td>
        </tr>
        <tr>
          <td>贊成／反對／棄權</td>
          <td>
            <?= $this->escape($result->贊成人數 ?? '-') ?> /
            <?= $this->escape($result->反對人數 ?? '-') ?> /
            <?= $this->escape($result->棄權人數 ?? '-') ?>
          </td>
        </tr>
        <?php } ?>
        <?php if (!empty($vote->投票委員)) { ?>
        <tr>
          <td>贊成委員</td>
          <td><?= $this->escape(implode('、', $vote->贊成 ?? [])) ?></td>
        </tr>
        <?php } ?>
      </table>
    </div>
  </div>
</div>
