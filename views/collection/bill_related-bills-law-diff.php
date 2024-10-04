<?php
    if (!property_exists($this->data->data, '對照表')) {
        echo '無法律對照表';
        return;
    }
    $bill = $this->data->data;
    [$related_bills, $diff, $bill_n_law_idx_mapping] = LawDiffHelper::lawDiff($bill);
?>
<link href="/static/css/bill/custom_law-diff.css" rel="stylesheet">
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
                  <input type="checkbox" value="<?= $related_bill->bill_idx ?>">
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
      <div class="card-body law-idx-a-list"></div>
    </div>
  </div>
  <div class="col-lg-10 diff-tables"></div>
</div>
<script>
  const bill_n_law_idx_mapping = <?= json_encode($bill_n_law_idx_mapping) ?>;
  const diffData = <?= json_encode($diff) ?>;
  const relatedBills = <?= json_encode($related_bills) ?>;
</script>
<script type="module">
    import Diff from 'https://cdn.jsdelivr.net/npm/text-diff@1.0.1/+esm';
    window.Diff = Diff;
</script>
<script src="/static/js/bill/custom_law-diff.js"></script>
