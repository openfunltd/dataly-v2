<?php
$term = LYAPI::apiQuery('/stat', '取得最新屆數')->legislator->terms[0]->term;
$legislators = LYAPI::apiQuery("/legislators?屆=$term", '取得立委列表')->legislators;
?>
<h1>第 <?= $term ?> 屆立委列表</h1>
<div class="row row-cols-1 row-cols-md-6">
    <?php foreach ($legislators as $legislator) { ?>
    <div class="col mb-4">
        <div class="card">
            <img src="<?= $this->escape($legislator->{'照片位址'}) ?>" class="card-img-top" alt="<?= $this->escape($legislator->委員姓名) ?>">
            <div class="card-body">
                <h5 class="card-title"><?= $legislator->委員姓名 ?></h5>
                <p class="card-text"><?= $legislator->黨籍 ?></p>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
