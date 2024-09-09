<div class="card">
    <div class="card-header">
        <h2>開放資料</h2>
    </div>
    <div class="card-body">
        <ul>
            <li>
            會議資料
            (來自 <a href="https://data.ly.gov.tw/getds.action?id=42">立法院資料開放平台</a>)
            <ul>
                <?php foreach ($this->data->data->{'會議資料'} as $data) { ?>
                <li><a href="#meet-<?= $data->{'日期'} ?>"><?= $data->{'日期'} ?></a></li>
                <?php } ?>
            </ul>
            </li>
            <li>
            發言名單
            (來自 <a href="https://data.ly.gov.tw/getds.action?id=221">立法院資料開放平台:院會發言名單</a> 和 <a href="https://data.ly.gov.tw/getds.action?id=223">立法院資料開放平台:委員會登記發言名單</a>)
            <ul>
                <?php foreach ($this->data->data->{'發言紀錄'} as $meet_data) { ?>
                <li><a href="#speaker-<?= $meet_data->smeetingDate ?>-<?= $meet_data->speechKindName ?>"><?= $meet_data->smeetingDate ?> <?= $meet_data->speechKindName ?></a></li>
                <?php } ?>
            </ul>
        </ul>
    </div>
</div>
<?php foreach ($this->data->data->{'會議資料'} as $meet_data) { ?>
<div class="card">
  <div class="card-header">
      <h2 id="meet-<?= $meet_data->{'日期'} ?>"><?= $meet_data->{'日期'} ?></h2>
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

<?php foreach ($this->data->data->{'發言紀錄'} as $meet_data) { ?>
<div class="card" id="speaker-<?= $meet_data->smeetingDate ?>-<?= $meet_data->speechKindName ?>">
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

