<?php
$config = TypeHelper::getTypeConfig()[$this->type];
?>
<?php $this->yield_start('content') ?>
<h1 class="mt-4 mb-3"><?= $this->escape($config['name'] . ' / ' . $this->id) ?></h1>
<ul class="nav nav-tabs">
    <?php foreach ($this->features as $ftab => $fname) { ?>
    <li class="nav-item">
    <a class="nav-link <?= $this->if($ftab == $this->tab, 'active') ?>" href="/collection/item/<?= $this->type ?>/<?= urlencode($this->id) ?>/<?= $ftab ?>"><?= $this->escape($fname) ?></a>
    </li>
    <?php } ?>
</ul>
<?php if ($this->tab == 'rawdata') { ?>
<?= $this->partial('collection/rawdata', $this) ?>
<?php } else { ?>
<?= $this->partial("collection/{$this->type}_{$this->tab}", $this) ?>
<?php } ?>

<?php $this->yield_end() ?>
<?php $this->yield_start('body-load') ?>
<?php $this->yield_end() ?>

<?= $this->partial('layout/app', $this) ?>
