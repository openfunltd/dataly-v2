<?php
    $law_content = $this->data->data;
    $law_id = $law_content->法律編號 ?? '';
    $law_version = $law_content->{'版本編號'} ?? '';
    $same_version_url = sprintf('/law_contents?法律編號=%s&版本編號=%s',
        $this->escape($law_id),
        $this->escape($law_version),
    );
    $law_contents = LYAPI::apiQuery($same_version_url, '查詢同版本的所有條文');
    $law_contents = $law_contents->lawcontents;

?>
<div class="card shadow mt-3 mb-3">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table">
        <tr>
          <td style="width: 15%">法律</td>
          <td>
            <?= $this->escape($law_content->{'法律編號:str'} ?? '') ?>
            （法律編號：<?= $this->escape($law_content->法律編號 ?? '') ?>）
            <a href="/collection/item/law/<?= $this->escape($law_content->法律編號 ?? '') ?>">
              <i class="fas fa-fw fa-eye"></i>
            </a>
          </td>
        </tr>
        <tr>
          <td>版本編號</td>
          <td><?= $this->escape($law_content->{'版本編號'} ?? '') ?></td>
        </tr>
        <tr>
          <td>順序 / 條號</td>
          <td>
            <?= $this->escape($law_content->{'順序'} ?? '') ?>
            /
            <?= $this->escape($law_content->{'條號'} ?? '') ?>
          </td>
        </tr>
        <tr>
          <td>內容</td>
          <td><?= nl2br($this->escape($law_content->{'內容'} ?? '')) ?></td>
        </tr>
      </table>
    </div>
  </div>
</div>
<h2 id="same-version" class="ml-2 mt-4 mb-0 h5">同版本所有條文</h2>
<p class="m-1 text-right text-primary">版本編號：<?= $this->escape($law_content->{'版本編號'} ?? '') ?></p>
<div class="card shadow mb-3">
  <div class="card-body">
    <div class="table-responsive">
      <table id="same-version-table" class="table table-bordered table-hover table-sm">
        <thead>
          <tr>
            <th class="text-center align-middle" style="width: 7%;">順序</th>
            <th class="text-center align-middle" style="width: 10%;">條號</th>
            <th class="text-center align-middle">內容</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($law_contents as $content) { ?>
            <tr>
              <td class="text-center align-middle">
                <?= $this->escape($content->順序 ?? '') ?>
                <a href="/collection/item/law_content/<?= $this->escape($content->法條編號 ?? '') ?>">
                  <i class="fas fa-fw fa-eye"></i>
                </a>
              </td>
              <td class="text-center align-middle">
                <?= $this->escape($content->條號 ?? '') ?>
              </td>
              <td><?= nl2br($this->escape($content->內容 ?? '')) ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
  window.onload = function(){
    if ($("#same-version-table").length) {
      const table = $('#same-version-table').DataTable({
        fixedHeader: true,
      });
    }
  }
</script>
