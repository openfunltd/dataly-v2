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

    //ivods
    $ivods = LYAPI::apiQuery(
        sprintf('/meet/%s/ivods', urlencode($this->data->id[0])),
        sprintf("取得關連的 IVOD 影片資料", $this->data->id[0])
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
<h2 id="ivods" class="ml-2 mt-4 mb-3 h5">會議 IVOD</h2>
<div class="card shadow mt-3 mb-3">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-hover table-sm">
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
