<?php
$handle_data = function ($data) {
    if (is_array($data)) {
        return $this->escape(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
    if (strpos($data, 'http') === 0) {
        return sprintf("<a href=\"%s\">%s</a>", $this->escape($data), $this->escape($data));
    }
    if (is_object($data)) {
        return $this->escape(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
    return $this->escape($data);
};
?>
<div class="card">
    <div class="card-header">
        <h2>議事錄</h2>
    </div>
    <div class="card-body">
        <?php if ($this->data->data->{'議事錄'} ?? false) { ?>
        議事錄來源是從 <a href="<?= $this->Escape($this->data->data->{'議事錄'}->source_url) ?>">議事錄</a> 抓取(從 Word 中取得)
      <table class="table">
          <thead>
              <tr>
                  <th style="width: 20%;">項目</th>
                  <th>資料</th>
              </tr>
          </thead>
          <tbody>
          <?php foreach ($this->data->data->{'議事錄'} as $key => $value) { ?>
          <tr>
              <td><?= $this->escape($key) ?></td>
              <td>
                  <?= $handle_data($value) ?>
              </td>
          </tr>
          <?php } ?>
          </tbody>
      </table>
      <?php } else { ?>
      <p>目前沒有議事錄</p>
      <?php } ?>
  </div>
</div>
