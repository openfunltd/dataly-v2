<?php
    if (!property_exists($this->data->data, '對照表')) {
        echo '無法律對照表';
        return;
    }
    $bill = $this->data->data;
    $diff = LawDiffHelper::lawDiff($bill);
?>
<link href="/static/css/bill/custom_law-diff.css" rel="stylesheet">
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
