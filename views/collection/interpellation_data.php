<?php
$interpellation = $this->data->data;
?>
<div class="card shadow mt-3 mb-3">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table">
        <tr>
          <td style="width: 15%">刊登日期</td>
          <td><?= $this->escape($interpellation->刊登日期 ?? '') ?></td>
        </tr>
        <tr>
          <td>會議</td>
          <td>
            <?= $this->escape($interpellation->{'會議代碼:str'} ?? '') ?><?= $this->escape("（{$interpellation->會議代碼}）" ?? '') ?>
            <a href="/collection/item/meet/<?= $this->escape($interpellation->會議代碼 ?? '') ?>">
              <i class="fas fa-fw fa-eye"></i>
            </a>
          </td>
        </tr>
        <tr>
          <td>屆-會期</td>
          <td><?= $this->escape($interpellation->屆 ?? '') ?>-<?= $this->escape($interpellation->會期 ?? '') ?></td>
        </tr>
        <tr>
          <td>質詢委員</td>
          <td><?= $this->escape(implode('、', $interpellation->質詢委員 ?? [])) ?></td>
        </tr>
        <tr>
          <td>事由</td>
          <td><?= $this->escape($interpellation->事由 ?? '') ?></td>
        </tr>
        <tr>
          <td>說明</td>
          <td><?= $this->escape($interpellation->說明 ?? '') ?></td>
        </tr>
      </table>
    </div>
  </div>
</div>
