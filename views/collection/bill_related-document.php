<?php
    if (!property_exists($this->data->data, '對照表')) {
        echo '無法律對照表';
        return;
    }
    $bill = $this->data->data;

    foreach ($bill->相關附件 as $attached) {
        if ($attached->名稱 == '關係文書PDF') {
            $related_pdf_url = $attached->網址;
        }
        if ($attached->名稱 == '關係文書DOC') {
            $related_doc_url = $attached->網址;
        }
    }

    $diff = LawDiffHelper::lawDiff($bill);
?>
<link href="/static/css/bill/custom_law-diff.css" rel="stylesheet">
<?php if (isset($related_pdf_url)): ?>
  <div class="mt-2 mb-2">
    <a class="btn btn-primary btn-icon-split" href="<?= $this->escape($related_pdf_url) ?>" target="_blank">
      <span class="icon text-white-50">
        <i class="fa fa-external-link"></i>
      </span>
      <span class="text">關係文書(PDF)</span>
    </a>
  </div>
<?php endif; ?>
<?php if (isset($related_doc_url)): ?>
  <div class="mt-2 mb-2">
    <a class="btn btn-primary btn-icon-split" href="<?= $this->escape($related_doc_url) ?>" target="_blank">
      <span class="icon text-white-50">
        <i class="fa fa-external-link"></i>
      </span>
      <span class="text">關係文書(WORD)</span>
    </a>
  </div>
<?php endif; ?>
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
