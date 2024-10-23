<?php
    $bill = $this->data->data;
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
    </table>
  </div>
</div>
