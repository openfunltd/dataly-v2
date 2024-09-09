<?php
$handle_data = function ($data) {
    if (is_array($data)) {
        return implode('、', array_map(function ($item) {
            if (is_string($item) and strpos($item, 'http') === 0) {
                return sprintf("<a href=\"%s\">%s</a>", $this->escape($item), $this->escape($item));
            }
            return $this->escape($item);
        }, $data));
    }
    if (strpos($data, 'http') === 0) {
        return sprintf("<a href=\"%s\">%s</a>", $this->escape($data), $this->escape($data));
    }
    return $this->escape($data);
};
?>
<div class="card">
    <div class="card-header">
        <h2>公報紀錄</h2>
    </div>
    <div class="card-body">
        來自 <a href="https://ppg.ly.gov.tw/ppg/publications?queryType=0">出版品 :: 立法院議事暨公報資訊網</a>
        <ul>
            <?php foreach ($this->data->data->{'公報發言紀錄'} as $data) { ?>
            <li><a href="#gazette-<?= $data->{'gazette_id'} ?>-<?= $data->page_start ?>" title="<?= $this->escape($data->content) ?>"><?= $this->escape(mb_strimwidth($data->content, 0, 100, '...')) ?></a></li>
            <?php } ?>
        </ul>
    </div>
</div>
<?php foreach ($this->data->data->{'公報發言紀錄'} as $meet_data) { ?>
<div class="card">
  <div class="card-header">
        <h6 id="gazette-<?= $meet_data->{'gazette_id'} ?>-<?= $meet_data->page_start ?>"><?= $this->escape($meet_data->content) ?></h6>
  </div>
  <div class="card-body">
      <table class="table">
          <thead>
              <tr>
                  <th style="width: 20%;">項目</th>
                  <th>資料</th>
              </tr>
          </thead>
          <tbody>
          <?php foreach ($meet_data as $key => $value) { ?>
          <tr>
              <td><?= $this->escape($key) ?></td>
              <td>
                  <?= $handle_data($value) ?>
              </td>
          </tr>
          <?php } ?>
          </tbody>
      </table>
  </div>
</div>
<?php } ?>
