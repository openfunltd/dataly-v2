<?php
$ivod_count = LYAPI::apiQuery(
    sprintf("/meet/%s/ivods?limit=0", urlencode($this->data->data->{'會議代碼'})),
    sprintf("取得 %s 的 IVod 數量", $this->data->data->{'會議代碼'})
)->total;

$interpellation_count = LYAPI::apiQuery(
    sprintf("/meet/%s/interpellations?limit=0", urlencode($this->data->data->{'會議代碼'})),
    sprintf("取得 %s 的質詢數量", $this->data->data->{'會議代碼'})
)->total;

?>
<div class="card">
    <div class="card-header">
        <h2><?= $this->escape($this->data->data->name) ?></h2>
    </div>
    <div class="card-body">
        <table class="table">
            <tbody>
            <tr>
                <td colspan="2">
                基本資料
                </td>
            </tr>
            <tr>
                <td>會議代碼</td>
                <td><?= $this->escape($this->data->data->{'會議代碼'}) ?></td>
            </tr>
            <tr>
                <td>會議名稱</td>
                <td><?= $this->escape($this->data->data->name) ?></td>
            </tr>
            <tr>
                <td>會議日期</td>
                <td><?= $this->escape(implode('、', $this->data->data->日期)) ?></td>
            </tr>
            </tbody>
            <tr>
                <td colspan="2">
                    關聯資料
                </td>
            </tr>
            <?php if ($ivod_count > 0) { ?>
            <tr>
                <td>IVod</td>
                <td><a href="/collection/list/ivod?filter=<?= urlencode('會議資料.會議代碼') ?>:<?= urlencode($this->data->data->{'會議代碼'}) ?>"><?= $ivod_count ?> 筆</a></td>
            </tr>
            <?php } ?>
            <?php if ($interpellation_count > 0) { ?>
            <tr>
                <td>質詢</td>
                <td><a href="/collection/list/interpellation?filter=<?= urlencode('會議資料.會議代碼') ?>:<?= urlencode($this->data->data->{'會議代碼'}) ?>"><?= $interpellation_count ?> 筆</a></td>
            </tr>
            <?php } ?>

            <tr>
                <td colspan="2">
                    原始連結
                </td>
            </tr>
            <tr>
                <td>會議資料</td>
                <td>
                    <?php foreach ($this->data->data->{'會議資料'} as $data) { ?>
                    <p><a href="<?= $this->escape($data->ppg_url) ?>">立法院議事暨公報資訊網:會議:<?= $this->escape($data->{'日期'}) ?></a></p>
                    <?php } ?>
                </td>
            </tr>
        </table>
    </div>
</div>
