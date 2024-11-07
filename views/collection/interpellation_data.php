<?php
$i12n = $this->data->data;
?>
<div class="card shadow mt-3 mb-3">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table">
        <tr>
          <td style="width: 15%">刊登日期</td>
          <td><?= $this->escape($i12n->刊登日期 ?? '') ?></td>
        </tr>
        <tr>
          <td>會議</td>
          <td>
            <?= $this->escape($i12n->{'會議代碼:str'} ?? '') ?><?= $this->escape("（{$i12n->會議代碼}）" ?? '') ?>
            <a href="/collection/item/meet/<?= $this->escape($i12n->會議代碼 ?? '') ?>">
              <i class="fas fa-fw fa-eye"></i>
            </a>
          </td>
        </tr>
        <tr>
          <td>屆-會期</td>
          <td><?= $this->escape($i12n->屆 ?? '') ?>-<?= $this->escape($i12n->會期 ?? '') ?></td>
        </tr>
        <tr>
          <td>質詢委員</td>
          <td><?= $this->escape(implode('、', $i12n->質詢委員 ?? [])) ?></td>
        </tr>
        <tr>
          <td>事由</td>
          <td><?= $this->escape($i12n->事由 ?? '') ?></td>
        </tr>
        <tr>
          <td>說明</td>
          <td><?= $this->escape($i12n->說明 ?? '') ?></td>
        </tr>
      </table>
    </div>
  </div>
</div>
