<?php
    $PM = $this->data->data;
    $img_url = $PM->照片位址 ?? '';
    $leave_date = $PM->離職日期 ?? '';
    $election_area = $PM->選區名稱 ?? '';

    //wiki 的選區介紹頁面
    $wiki_area_url = 'https://zh.wikipedia.org/zh-tw/';
    if (mb_strpos($election_area, '不分區') !== false) {
        $wiki_area_url .= '全國不分區及僑居國外國民立法委員選舉區';
    } else if (mb_strpos($election_area, '山地原住民') !== false) {
        $wiki_area_url .= '山地原住民選舉區';
    } else if (mb_strpos($election_area, '平地原住民') !== false) {
        $wiki_area_url .= '平地原住民選舉區';
    } else if (mb_strpos($election_area, '選舉區') !== false) {
        $zh_nums = [
            '1' => '一', '2' => '二', '3' => '三', '4' => '四', '5' => '五',
            '6' => '六', '7' => '七', '8' => '八', '9' => '九', '10' => '十',
            '11' => '十一', '12' => '十二', '13' => '十三', '14' => '十四', '15' => '十五',
            '16' => '十六', '17' => '十七', '18' => '十八', '19' => '十九', '20' => '二十',
        ];
        if (preg_match('/第(\d+)/', $election_area, $matches)) {
            $num = $matches[1];
            if (isset($zh_nums[$num])) {
                $wiki_area_url .= str_replace("第$num", "第$zh_nums[$num]", $election_area);
            } else {
                $wiki_area_url .= '臺灣選舉';
            }
        }
    } else {
        $wiki_area_url .= '臺灣選舉';
    }
?>
<style>
  .all-middle-table td {
    text-align: center;
    vertical-align: middle;
  }
</style>
<p class="mt-1 mb-1 text-right">
  歷屆立法委員編號：<?= $this->escape($PM->歷屆立法委員編號 ?? '') ?>
  <br>
  <i class="fa-solid fa-circle-info text-primary"> 委員在立院的終身編號</i>
</p>
<div class="row">
  <div class="col-xl-3 col-12">
    <div class="card shadow mb-4">
      <div class="card-body">
        <div class="row justify-content-center mt-1">
          <div class="col-xl-9">
          <img class="img-fluid img-thumbnail" src="<?= $this->escape($img_url) ?>">
          </div>
        </div>
        <div class="row table-responsive table-sm mt-3">
          <table class="table all-middle-table">
            <tr>
              <td>姓名</td>
              <td><?= $this->escape($PM->委員姓名 ?? '') ?></td>
            </tr>
            <tr>
              <td>英文姓名</td>
              <td><?= $this->escape($PM->委員英文姓名 ?? '') ?></td>
            </tr>
            <tr>
              <td>政黨</td>
              <td><?= $this->escape($PM->黨籍 ?? '') ?></td>
            </tr>
            <tr>
              <td>所屬黨團</td>
              <td><?= $this->escape($PM->黨團 ?? '') ?></td>
            </tr>
            <tr>
              <td>選區</td>
              <td>
                <?= $this->escape($PM->選區名稱 ?? '') ?>
                <a href="<?= $this->escape($wiki_area_url) ?>" target="_blank">
                  <i class="fa-brands fa-wikipedia-w"></i>
                </a>
              </td>
            </tr>
            <tr>
              <td>到職日</td>
              <td><?= $this->escape($PM->到職日 ?? '') ?></td>
            </tr>
            <?php if ($leave_date != '') { ?>
              <tr>
                <td>離職日</td>
                <td><?= $this->escape($PM->離職日期 ?? '') ?></td>
              </tr>
              <tr>
                <td>離職原因</td>
                <td><?= $this->escape($PM->離職原因 ?? '') ?></td>
              </tr>
            <?php } ?>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-9 col-12">
    <div class="card shadow mb-4">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <tr>
              <td class="text-center align-middle">所屬委員會</td>
              <td><?= implode('<br>', array_map([$this, 'escape'], $PM->委員會 ?? [])) ?></td>
            </tr>
            <tr>
              <td class="text-center align-middle">經歷</td>
              <td><?= implode('<br>', array_map([$this, 'escape'], $PM->經歷 ?? [])) ?></td>
            </tr>
            <tr>
              <td class="text-center align-middle">學歷</td>
              <td><?= implode('<br>', array_map([$this, 'escape'], $PM->學歷 ?? [])) ?></td>
            </tr>
            <tr>
              <td class="text-center align-middle">電話</td>
              <td class="align-middle" rowspan="3">
                <span>現階段請至<a href="https://www.ly.gov.tw/Pages/SearchList.aspx?nodeid=43998" target="_blank">立法院官網</a>查詢</span>
                <br>
                <i class="fa-solid fa-circle-info text-primary"> 未來會提供聯絡資訊，敬請期待</i>
              </td>
            </tr>
            <tr>
              <td class="text-center align-middle">傳真</td>
            </tr>
            <tr>
              <td class="text-center align-middle">通訊處</td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
