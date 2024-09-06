本區資料來自 <a href="https://data.ly.gov.tw/getds.action?id=42">立法院資料開放平台</a>
<?php foreach ($this->data->data->{'會議資料'} as $meet_data) { ?>
<div class="card">
  <div class="card-header">
      <h2><?= $meet_data->{'日期'} ?></h2>
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
                  <?php if (is_array($value)) { ?>
                  <?= $this->escape(implode('、', $value)) ?>
                  <?php } else { ?>
                  <?= $this->escape($value) ?>
                  <?php } ?>
              </td>
          </tr>
          <?php } ?>
          </tbody>
      </table>
  </div>
</div>
<?php } ?>

