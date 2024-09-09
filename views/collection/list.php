<?php
$config = TypeHelper::getTypeConfig()[$this->type];
?>
<?php $this->yield_start('content') ?>
<h1><?= $this->escape($config['name'] . ' / ' . $this->type) ?></h1>
<ul class="nav nav-tabs">
    <?php foreach ($this->features as $ftab => $fname) { ?>
    <li class="nav-item">
    <a class="nav-link <?= $this->if($ftab == $this->tab, 'active') ?>" href="/collection/list/<?= $this->type ?>/<?= $ftab ?>"><?= $this->escape($fname) ?></a>
    </li>
    <?php } ?>
</ul>
<?php if ($this->tab == 'table') { ?>
<?= $this->partial('collection/table', $this) ?>
<?php } else { ?>
<?= $this->partial("collection/{$this->type}_{$this->tab}", $this) ?>
<?php } ?>
<?php $this->yield_end() ?>

<?php $this->yield_start('body-load') ?>
<?= $this->yield('list-body-load') ?>
<?php $this->yield_end() ?>

<?= $this->partial('layout/app', $this) ?>
