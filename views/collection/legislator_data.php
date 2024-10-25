<?php
    $PM = $this->data->data;
    $img_url = $PM->照片位址 ?? '';
    $leave_date = $PM->離職日期 ?? '';
?>
<style>
  .all-middle-table td {
    text-align: center;
    vertical-align: middle;
  }
</style>
<p class="mt-1 mb-1 text-right">
  歷屆立法委員編號：<?= $this->escape($PM->歷屆立法委員編號 ?? '') ?>
  <br>
  <i class="fa fa-question-circle text-primary"> 委員在立院的終身編號</i> 
</p>
<div class="row">
  <div class="col-xl-3 col-12">
    <div class="card shadow mb-4">
      <div class="card-body">
        <div class="row justify-content-center mt-1">
          <div class="col-xl-9">
          <img class="img-fluid img-thumbnail" src="<?= $this->escape($img_url) ?>">
          </div>
        </div>
        <div class="row table-responsive table-sm">
          <table class="table all-middle-table">
            <tr>
              <td>姓名</td>
              <td><?= $this->escape($PM->委員姓名 ?? '') ?></td>
            </tr>
            <tr>
              <td>英文姓名</td>
              <td><?= $this->escape($PM->委員英文姓名 ?? '') ?></td>
            </tr>
            <tr>
              <td>政黨</td>
              <td><?= $this->escape($PM->黨籍 ?? '') ?></td>
            </tr>
            <tr>
              <td>所屬黨團</td>
              <td><?= $this->escape($PM->黨團 ?? '') ?></td>
            </tr>
            <tr>
              <td>選區</td>
              <td><?= $this->escape($PM->選區名稱 ?? '') ?></td>
            </tr>
            <tr>
              <td>到職日</td>
              <td><?= $this->escape($PM->到職日 ?? '') ?></td>
            </tr>
            <?php if ($leave_date != '') { ?>
              <tr>
                <td>離職日</td>
                <td><?= $this->escape($PM->離職日期 ?? '') ?></td>
              </tr>
              <tr>
                <td>離職原因</td>
                <td><?= $this->escape($PM->離職原因 ?? '') ?></td>
              </tr>
            <?php } ?>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-9 col-12">
    <div class="card shadow mb-4">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <tr>
              <td class="text-center align-middle">所屬委員會</td>
              <td><?= implode('<br>', array_map([$this, 'escape'], $PM->委員會 ?? [])) ?></td>
            </tr>
            <tr>
              <td class="text-center align-middle">經歷</td>
              <td><?= implode('<br>', array_map([$this, 'escape'], $PM->經歷 ?? [])) ?></td>
            </tr>
            <tr>
              <td class="text-center align-middle">學歷</td>
              <td><?= implode('<br>', array_map([$this, 'escape'], $PM->學歷 ?? [])) ?></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
