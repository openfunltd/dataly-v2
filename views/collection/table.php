<?php $this->yield_start('content') ?>
<div class="row">
    <div class="col-md-3">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">篩選</h6>
            </div>
            <div class="card-body">
                <div class="dropdown mb-4">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownFilter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        新增/取消篩選欄位
                    </button>
                    <div class="dropdown-menu animated--fade-in" aria-labelledby="dropdownFilter">
                        <?php foreach ($this->data->supported_filter_fields as $field) { ?>
                        <a class="dropdown-item" href="#"><?= $this->escape($field) ?></a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">資料</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <?php foreach (TypeHelper::getColumns($this->type) as $col) { ?>
                            <th><?= $this->escape($col) ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (TypeHelper::getData($this->data, $this->type) as $row) { ?>
                        <tr>
                            <?php foreach (TypeHelper::getColumns($this->type) as $col) { ?>
                            <?php $v = $row->{$col} ?? ''; ?>
                            <td>
                                <?php if (is_array($v)) { ?>
                                    <?php foreach ($v as $item) { ?>
                                    <p><?= $this->escape($item) ?></p>
                                    <?php } ?>
                                <?php } else { ?>
                                    <?= $this->escape($v) ?>
                                <?php } ?>
                            </td>
                            <?php } ?>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $this->yield_end() ?>
<?php $this->yield_start('body-load') ?>
<script>
$(document).ready(function() {
  $('#dataTable').DataTable();
});
</script>
<?php $this->yield_end() ?>

<?= $this->partial('layout/app', $this) ?>
