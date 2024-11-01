<?php
    $meet = $this->data->data;
    $meet_data = $meet->會議資料 ?? [];
    $date_n_locations = [];
    foreach ($meet_data as $data) {
        $location = $data->會議地點 ?? '';
        $date = $data->日期 ?? '';
        $time_segment = $data->會議時間區間 ?? '';
        $datetime = ($time_segment != '') ? $date . ' ' . explode(' ', $time_segment)[1] : $date;
        $date_n_locations[] = $datetime . ' ' . $location;
    }
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
?>
<div class="card shadow mt-3 mb-3">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table">
        <tr>
          <td style="width: 15%">會議名稱</td>
          <td><?= $this->escape($meet->name ?? '') ?></td>
        </tr>
        <tr>
          <td>會議時間/地點</td>
          <td><?= implode('<br>', array_map([$this,'escape'], $date_n_locations)) ?></td>
        </tr>
        <tr>
          <td>召集人</td>
          <?php if (isset($convener_str)) { ?>
            <td><?= $this->escape($convener_str) ?></td>
          <?php } ?>
          <?php if (isset($conveners_str)) { ?>
            <td><?= implode('<br>', array_map([$this,'escape'], $conveners_str)) ?></td>
          <?php } ?>
        </tr>
      </table>
    </div>
  </div>
</div>
