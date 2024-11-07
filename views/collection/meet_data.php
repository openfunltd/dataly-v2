<?php
    $meet = $this->data->data;
    $meet_data = $meet->會議資料 ?? [];

    //會議時間與地點
    $date_n_locations = [];
    foreach ($meet_data as $data) {
        $location = $data->會議地點 ?? '';
        $date = $data->日期 ?? '';
        $time_segment = $data->會議時間區間 ?? '';
        $datetime = ($time_segment != '') ? $date . ' ' . explode(' ', $time_segment)[1] : $date;
        $date_n_locations[] = $datetime . ' ' . $location;
    }

    //召集人
    $is_plenary = $meet->會議種類 === '院會'; //判斷是否為全體院會
    $conveners = array_map(fn($data) => $data->委員會召集委員 ?? '', $meet_data);
    $has_one_convener = count(array_unique($conveners)) === 1;
    if ($has_one_convener) {
        $convener_str = $conveners[0];
    } else {
        $conveners_str = [];
        foreach ($meet_data as $data) {
            $convener = $data->委員會召集委員 ?? '';
            $date = $data->日期 ?? '';
            $conveners_str[] = sprintf('%s（%s）', $convener, $date);
        }
    }

    //會議事由
    $meet_subjects = [];
    foreach ($meet_data as $idx0 => $data) {
        $meet_date = $data->日期 ?? '';
        $meet_subject = $data->會議事由 ?? '';
        //Initialize array $meet_subjects
        if ($idx0 === 0) {
            $subject_obj = new stdClass();
            $subject_obj->date = [$meet_date];
            $subject_obj->subject = $meet_subject;
            $meet_subjects[] = $subject_obj;
            continue;
        }
        foreach ($meet_subjects as $idx1 => $subject) {
            //重複事由合併
            if ($subject->subject === $meet_subject) {
                $subject->date[] = $meet_date;
                break;
            }
            //新事由
            if ($idx1 === count($meet_subjects) - 1) {
                $subject_obj = new stdClass();
                $subject_obj->date = [$date];
                $subject_obj->subject = $meet_subject;
                $meet_subjects[] = $subject_obj;
            }
        }
    }

    $ppg_data = $meet->議事網資料 ?? [];

    //連結
    $merged_links = (object) [
        'urls' => [],
        'titles' => [],
    ];
    foreach ($ppg_data as $ppg_single_data) {
        $links = $ppg_single_data->連結 ?? [];
        //ppg 上的影片都不會連到單一個 ivod_id 使得這些 video 連結變得很沒意義，就先把它們都去掉
        $links = array_filter($links, function($link) {
            $type = $link->類型 ?? '';
            return $type != 'video';
        });
        foreach ($links as $link) {
            $url = $link->連結 ?? '';
            $idx = array_search($url, $merged_links->urls);
            if ($idx === false) {
                $merged_links->urls[] = $url;
                $merged_links->titles[] = $link->標題 ?? '';
            }
        }
    }

    //附件
    $merged_attaches = (object) [
        'urls' => [],
        'titles' => [],
    ];
    foreach ($ppg_data as $ppg_single_data) {
        $attaches = $ppg_single_data->附件 ?? [];
        //有一些附件是空的連結，這些就去掉不算附件
        $attaches = array_filter($attaches, function($attach) {
            $url = $attach->連結 ?? '';
            return $url != '';
        });
        foreach ($attaches as $attach) {
            $url = $attach->連結 ?? '';
            $idx = array_search($url, $merged_attaches->urls);
            if ($idx === false) {
                $merged_attaches->urls[] = $url;
                $merged_attaches->titles[] = $attach->標題 ?? '';
            }
        }
    }

    //關係文書
    $merged_related_docs = (object) [
        'bill_ids' => [],
        'dates' => [],
        'titles' => [],
    ];
    foreach ($ppg_data as $ppg_single_data) {
        $related_docs = $ppg_single_data->關係文書 ?? new stdClass();
        $bill_related_docs = $related_docs->議案 ?? [];
        //get dates of the ppg_single_date
        $ppg_dates = $ppg_single_data->日期 ?? [];
        $ppg_formatted_dates = [];
        foreach ($ppg_dates as $ppg_date) {
            if (preg_match('/(\d{3})年(\d{1,2})月(\d{1,2})日/', $ppg_date, $matches)) {
                $year = $matches[1] + 1911;
                $ppg_formatted_dates[] = "{$year}-{$matches[2]}-{$matches[3]}";
            }
        }
        foreach ($bill_related_docs as $bill) {
            $bill_id = $bill->議案編號 ?? '-';
            $idx = array_search($bill_id, $merged_related_docs->bill_ids);
            if ($idx !== false) {
                $merged_related_docs->dates[$idx] = array_merge($merged_related_docs->dates[$idx], $ppg_formatted_dates);
            } else {
                $merged_related_docs->bill_ids[] = $bill_id;
                $merged_related_docs->dates[] = $ppg_formatted_dates;
                $merged_related_docs->titles[] = $bill->標題 ?? '';
            }
        }
    }

    //ivods
    $ivods = LYAPI::apiQuery(
        sprintf('/meet/%s/ivods', urlencode($this->data->id[0])),
        sprintf("取得關連的 IVOD 影片", $this->data->id[0])
    );
    $ivods = $ivods->ivods ?? [];
    $clip_ivods = array_filter($ivods, function($ivod) {
        return $ivod->影片種類 == 'Clip';
    });
    $full_ivods = array_filter($ivods, function($ivod) {
        return $ivod->影片種類 == 'Full';
    });
    function ivodStartTimeSort($ivodA, $ivodB) {
        return $ivodA->開始時間 <=> $ivodB->開始時間;
    }
    usort($clip_ivods, 'ivodStartTimeSort');
    usort($full_ivods, 'ivodStartTimeSort');
    $ivods = array_merge($full_ivods, $clip_ivods);

    //i12n = interpellation
    $i12ns = LYAPI::apiQuery(
        sprintf('/meet/%s/interpellations', urlencode($this->data->id[0])),
        sprintf("取得關連的書面質詢", $this->data->id[0])
    );
    $i12ns = $i12ns->interpellations ?? [];
?>
<style>
  .table td, .table th {
    white-space: nowrap;
  }
  .truncate-2 {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
  }
</style>
<div class="card shadow mt-3 mb-3">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table">
        <tr>
          <td style="width: 15%" class="text-center align-middle">會議名稱</td>
          <td><?= $this->escape($meet->name ?? '') ?></td>
        </tr>
        <tr>
          <td class="text-center align-middle">會議時間/地點</td>
          <td><?= implode('<br>', array_map([$this,'escape'], $date_n_locations)) ?></td>
        </tr>
        <?php if (!$is_plenary) { ?>
          <tr>
            <td class="text-center align-middle">召集人</td>
            <?php if (isset($convener_str)) { ?>
              <td><?= $this->escape($convener_str) ?></td>
            <?php } ?>
            <?php if (isset($conveners_str)) { ?>
              <td><?= implode('<br>', array_map([$this,'escape'], $conveners_str)) ?></td>
            <?php } ?>
          </tr>
        <?php } ?>
        <?php foreach ($meet_subjects as $idx => $subject_obj) { ?>
          <tr>
            <?php if ($idx === 0) { ?>
              <td class="text-center align-middle" rowspan="<?= count($meet_subjects) ?>">事由</td>
            <?php } ?>
            <?php if (count($meet_subjects) === 1) { ?>
              <td><p class="meet-reason truncate-2"><?= nl2br($this->escape($subject_obj->subject)) ?></p></td>
            <?php } else { ?>
              <td>
                <p class="meet-reason truncate-2">
                  <?= $this->escape(implode('、', $subject_obj->date)) ?>
                  <br>
                  <?= nl2br($this->escape($subject_obj->subject)) ?>
                </p>
              </td>
            <?php } ?>
          </tr>
        <?php } ?>
        <?php if (!empty($merged_links->urls)) { ?>
          <tr>
            <td class="text-center align-middle">連結</td>
            <td>
              <?php foreach ($merged_links->urls as $idx => $url) { ?>
                <p>
                  <a href="<?= $this->escape($url) ?>" target="_blank">
                    <?= $this->escape($merged_links->titles[$idx]) ?>
                  </a>
                </p>
              <?php } ?>
            </td>
          </tr>
        <?php } ?>
        <?php if (!empty($merged_attaches->urls)) { ?>
          <tr>
            <td class="text-center align-middle">附件</td>
            <td>
              <?php foreach ($merged_attaches->urls as $idx => $url) { ?>
                <p class="m-0">
                  <a href="<?= $this->escape($url) ?>" target="_blank">
                    <?= $this->escape($merged_attaches->titles[$idx]) ?>
                  </a>
                </p>
              <?php } ?>
            </td>
          </tr>
        <?php } ?>
        <tr>
          <td class="text-center align-middle">原始資料連結</td>
          <td>
            <?php foreach ($this->data->data->{'會議資料'} as $data) { ?>
              <p class="m-0">
                <a href="<?= $this->escape($data->ppg_url) ?>" target="_blank">
                  立法院議事暨公報資訊網：會議 <?= $this->escape($data->{'日期'}) ?>
                </a>
              </p>
            <?php } ?>
          </td>
        </tr>
      </table>
    </div>
  </div>
</div>
<?php if (!empty($merged_related_docs->bill_ids)) { ?>
    <h2 id="related_doc" class="ml-2 mt-4 mb-3 h5">關係文書</h2>
    <div class="card shadow mt-3 mb-3">
      <div class="card-body">
        <div class="table-responsive">
          <table id="related-doc-table" class="table table-bordered table-hover table-sm">
            <thead>
              <tr>
                <th class="text-center align-middle">議案編號</th>
                <th class="text-center align-middle">日期</th>
                <th class="text-center align-middle">議案名稱</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($merged_related_docs->bill_ids as $idx => $bill_id) { ?>
                <tr>
                  <td class="text-center align-middle">
                    <?= $this->escape($bill_id) ?>
                    <?php if ($bill_id != '-') { ?>
                      <a href="/collection/item/bill/<?= $this->escape($bill_id) ?>">
                        <i class="fas fa-fw fa-eye"></i>
                      </a>
                    <?php } ?>
                  </td>
                  <td class="text-center align-middle">
                    <?= $this->escape(implode('、', $merged_related_docs->dates[$idx])) ?>
                  </td>
                  <td class="align-middle"><?= $this->escape($merged_related_docs->titles[$idx]) ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
<?php } ?>
<?php if (!empty($i12ns)) { ?>
  <h2 id="interpellations" class="ml-2 mt-4 mb-3 h5">書面質詢</h2>
  <div class="card shadow mt-3 mb-3">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm">
          <thead>
            <tr>
              <th class="text-center align-middle">質詢編號</th>
              <th class="text-center align-middle">刊登日期</th>
              <th class="text-center align-middle">質詢委員</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($i12ns as $i12n) { ?>
              <tr>
                <td class="text-center align-middle" style="width: 20%;">
                  <?= $this->escape($i12n->質詢編號) ?>
                  <a href="/collection/item/interpellation/<?= $this->escape($i12n->質詢編號) ?>">
                    <i class="fas fa-fw fa-eye"></i>
                  </a>
                </td>
                <td class="text-center align-middle" style="width: 20%;">
                  <?= $this->escape($i12n->刊登日期) ?>
                </td>
                <td class="text-center align-middle">
                  <?= $this->escape(implode('、', $i12n->質詢委員 ?? [])) ?>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php } ?>
<?php if (!empty($ivods)) { ?>
  <h2 id="ivods" class="ml-2 mt-4 mb-3 h5">會議 IVOD</h2>
  <div class="card shadow mt-3 mb-3">
    <div class="card-body">
      <div class="table-responsive">
        <table id="ivod-table" class="table table-bordered table-hover table-sm">
          <thead>
            <tr>
              <th class="text-center align-middle">IVOD_ID</th>
              <th class="text-center align-middle">日期</th>
              <th class="text-center align-middle">質詢委員/影片種類</th>
              <th class="text-center align-middle">時間</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($ivods as $ivod) { ?>
              <tr class="text-center align-middle">
                <td>
                  <?= $this->escape($ivod->IVOD_ID) ?>
                  <a href="/collection/item/ivod/<?= $this->escape($ivod->IVOD_ID) ?>">
                    <i class="fas fa-fw fa-eye"></i>
                  </a>
                </td>
                <td><?= $this->escape($ivod->日期) ?></td>
                <td><?= $this->escape($ivod->委員名稱) ?></td>
                <td><?= $this->escape($ivod->委員發言時間) ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php } ?>
<script>
  window.onload = function() {
    $('.meet-reason').click(function(){
      selectionLength = window.getSelection().toString().length;
      if (selectionLength === 0) {
        $(this).toggleClass('truncate-2');
      }
    });
  }
</script>
<script src="/static/js/meet/custom_data.js"></script>
