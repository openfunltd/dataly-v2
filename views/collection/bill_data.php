<?php
    $bill = $this->data->data;
    $bill_code_full = $bill->字號 ?? '';
    $bill_code_short = $bill->提案編號 ?? '';
    $bill_code = ($bill_code_short != '') ? $bill_code_full . "（" . $bill_code_short . "）" : $bill_code_full;
    $bill_reason = $bill->案由 ?? '';
    $proposers = implode('、', $bill->提案人 ?? []);
    $endorsers = implode('、', $bill->連署人 ?? []);
    $attaches = $bill->相關附件 ?? [];
    $source_url = $bill->url ?? '';

    //關連議案 ppg 版
    $related_bills_ppg = $bill->關連議案 ?? [];

    //關連議案 openfun 版
    $related_bills_endpoint = '/bill/' . $this->id . '/related_bills';
    $related_bills_openfun = LYAPI::apiQuery($this->escape($related_bills_endpoint), "抓取 OpenFun 版的關連議案");
    $related_bills_openfun = $related_bills_openfun->bills ?? [];
?>
<style>
  .table td, .table th {
    white-space: nowrap;
  }
</style>
<div class="card shadow mt-3 mb-3">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table">
        <tr>
          <td style="width: 15%">議案類別</td>
          <td><?= $this->escape($bill->議案類別 ?? '') ?></td>
        </tr>
        <tr>
          <td>議案名稱</td>
          <td><?= $this->escape($bill->議案名稱 ?? '') ?></td>
        </tr>
        <tr>
          <td>提案來源</td>
          <td><?= $this->escape($bill->提案來源 ?? '') ?></td>
        </tr>
        <tr>
          <td>提案單位/提案委員</td>
          <td><?= $this->escape($bill->{'提案單位/提案委員'} ?? '') ?></td>
        </tr>
        <tr>
          <td>議案狀態</td>
          <td><?= $this->escape($bill->議案狀態 ?? '') ?></td>
        </tr>
        <tr>
          <td>字號</td>
          <td><?= $this->escape($bill_code) ?></td>
        </tr>
        <?php if ($proposers != '') { ?>
          <tr>
            <td>提案人</td>
            <td><?= $this->escape($proposers) ?></td>
          </tr>
        <?php } ?>
        <?php if ($endorsers != '') { ?>
          <tr>
            <td>連署人</td>
            <td><?= $this->escape($endorsers) ?></td>
          </tr>
        <?php } ?>
        <?php if (!empty($attaches)) { ?>
          <tr>
            <td>相關附件</td>
            <td>
              <?php
                  foreach ($attaches as $idx => $attach) {
                      if ($idx != 0) {
                          echo '<br>';
                      }
                      $url = (mb_strpos($attach->網址, 'https://') === 0) ? $attach->網址 : 'https://ppg.ly.gov.tw/ppg' . $attach->網址;
                      echo sprintf('<a href="%s" target="_blank">%s</a>', $url, $attach->名稱);
                  }
              ?>
            </td>
          </tr>
        <?php } ?>
        <tr>
          <td>原始資料連結</td>
          <td><a href="<?= $this->escape($source_url) ?>" target="_blank"><?= $this->escape($source_url) ?></a></td>
        </tr>
      </table>
    </div>
  </div>
</div>
<h2 id="related-bills-ppg" class="ml-2 mt-4 mb-3 h5">關連議案 - 立法院議事暨公報資訊網版</h2>
<?php if (empty($related_bills_ppg)) { ?>
  <div class="mt-2 card border-left-danger">
    <div class="card-body">
      無關連議案
    </div>
  </div>
<?php } ?>
<?php if (!empty($related_bills_ppg)) { ?>
  <div class="card shadow mt-3 mb-3">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm">
          <thead>
            <tr>
              <th class="text-center" style="width: 20%;">議案編號</td>
              <th class="text-center">議案名稱</td>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($related_bills_ppg as $related_bill) { ?>
              <tr>
                <td class="text-center align-middle">
                  <?= $this->escape($related_bill->billNo ?? '') ?>
                  <a href="/collection/item/bill/<?= $this->escape($related_bill->billNo ?? '') ?>">
                    <i class="fas fa-fw fa-eye"></i>
                  </a>
                </td>
                <td><?= $this->escape($related_bill->議案名稱 ?? '') ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php } ?>
<h2 id="related-bills-ppg" class="ml-2 mt-4 mb-3 h5">關連議案 - OpenFun 版</h2>
<?php if (empty($related_bills_openfun)) { ?>
  <div class="mt-2 card border-left-danger">
    <div class="card-body">
      無關連議案
    </div>
  </div>
<?php } ?>
<?php if (!empty($related_bills_openfun)) { ?>
  <div class="card shadow mt-3 mb-3">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm">
          <thead>
            <tr>
              <th class="text-center">議案編號</td>
              <th class="text-center">字號</td>
              <th class="text-center">提案單位/提案委員</td>
              <th class="text-center">議案狀態</td>
              <th class="text-center">議案名稱</td>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($related_bills_openfun as $related_bill) { ?>
              <tr>
                <td>
                  <?= $this->escape($related_bill->billNo ?? '') ?>
                  <a href="/collection/item/bill/<?= $this->escape($related_bill->billNo ?? '') ?>">
                    <i class="fas fa-fw fa-eye"></i>
                  </a>
                </td>
                <td class="text-center"><?= $this->escape($related_bill->提案編號 ?? '') ?></td>
                <td class="text-center"><?= $this->escape($related_bill->{'提案單位/提案委員'} ?? '') ?></td>
                <td class="text-center"><?= $this->escape($related_bill->議案狀態 ?? '') ?></td>
                <td><?= $this->escape($related_bill->議案名稱 ?? '') ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php } ?>
