<?php
    if (!property_exists($this->data->data, '對照表')) {
        echo '無法律對照表';
        return;
    }
    $bill = $this->data->data;
    [$related_bills, $diff_result] = LawDiffHelper::lawDiff($bill);
?>
<div class="card shadow mb-4">
  <div class="card-header py-3">
    <h6 class="m-0 font-weight-bold text-primary">關聯提案</h6>
  </div>
  <div class="card-body">
    <div class="table-responsive" style="overflow-x: auto;">
      <table class="table table-bordered table-hover table-sm" id="dataTable" width="100%" cellspacing="0">
        <thead>
          <th width="3%">選擇</th>
          <th>法案版本</th>
          <th>版本名稱</th>
          <th>主提案非第一人</th>
          <th width="8%">提案編號</th>
          <th>提案日期</th>
        </thead>
        <tbody>
          <?php foreach ($related_bills as $related_bill): ?>
              <tr>
                <td class="text-center">
                  <input type="checkbox" value="{{ $related_bill['bill_idx'] }}">
                </td>
              <td><?= $related_bill->bill_name ?></td>
              <td><?= $related_bill->version_name ?></td>
              <td><?= $related_bill->non_first_proposers ?></td>
              <td><?= $related_bill->bill_no ?></td>
              <td><?= $related_bill->initial_date ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
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
      <div class="card-body">
        <?php foreach ($diff_result as $law_idx => $diff): ?>
          <a class="law-idx <?= $law_idx ?>" href="#<?= $law_idx ?>"><?= $law_idx ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-lg-10">
    <?php foreach ($diff_result as $law_idx => $diff): ?>
      <div id="<?= $law_idx ?>" class="diff-comparison <?= $law_idx ?> card shadow mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary"><?= $law_idx ?></h6>
        </div>
        <div class="card-body">
          <table class="table table-bordered table-sm nowrap">
            <thead>
              <th style="width: 20%">版本名稱</th>
              <th>條文內容</th>
            </thead>
            <tbody>
              <tr>
                <td>現行條文</td>
                <td>
                  <?php if (is_null($diff->current)): ?>
                    本條新增無現行版本
                  <?php else: ?>
                    <?= $diff->current ?>
                  <?php endif; ?>
                </td>
              </tr>
              <?php foreach ($related_bills as $bill_idx => $bill): ?>
                <tr class="diff <?= $bill_idx ?>">
                  <td><?= $bill->version_name ?></td>
                  <td>
                    <?php if (property_exists($diff->commits, $bill_idx)): ?>
                      <?= $diff->commits->{$bill_idx} ?>
                    <?php else: ?>
                      無
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
