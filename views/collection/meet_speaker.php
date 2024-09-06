以下資料來自 <a href="https://data.ly.gov.tw/getds.action?id=221">立法院資料開放平台:院會發言名單</a> 和 <a href="https://data.ly.gov.tw/getds.action?id=223">立法院資料開放平台:委員會登記發言名單</a>

<?php foreach ($this->data->data->{'發言紀錄'} as $meet_data) { ?>
<div class="card">
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

