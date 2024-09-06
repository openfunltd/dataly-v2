<?php
$config = TypeHelper::getTypeConfig()[$this->type];
?>
<?php $this->yield_start('content') ?>
<h1><?= $this->escape($config['name'] . ' / ' . $this->id) ?></h1>
<table class="table">
    <thead>
    <tr>
        <th>Field</th>
        <th>Value</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach (TypeHelper::getRecordList($this->data->data) as $record) { ?>
    <tr>
        <td><?= $this->escape($record['key']) ?></td>
        <td><?= $this->escape($record['value']) ?></td>
    </tr>
    <?php } ?>
    </tbody>
</table>
<?php $this->yield_end() ?>
<?php $this->yield_start('body-load') ?>
<?php $this->yield_end() ?>

<?= $this->partial('layout/app', $this) ?>
