<?php
    if (!in_array('gazette', $this->data->data->支援功能)) {
        echo '無公報紀錄';
        return;
    }
    $MP_name = $this->escape($this->data->data->委員名稱);
    $meet_title = $this->escape($this->data->data->會議資料->標題);
    $meet_subjects = Ivod::getSubjects($this->data->data->會議名稱);
    $gazette_transcript = [];
    foreach ($this->data->data->gazette->blocks as $block) {
        foreach ($block as $text) {
            $gazette_transcript[] = $text;
        }
    }
    $agenda = $this->data->data->gazette->agenda;
?>
<div id="ai-transcript" class="card shadow mb-4">
    <div class="card-header py-3">
        <h1 class="h3 mb-0 text-gray-800">
            <?= $MP_name ?> @ <?= $meet_title ?>
        </h1>
        <?= implode('<br>', array_map([$this, 'escape'], $meet_subjects)) ?>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <div>
                    <table id="subtitleTable" class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Index</th>
                                <th>Text</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gazette_transcript as $idx => $text): ?>
                                <tr id="s-<?= $idx ?>">
                                    <td><?= $idx ?></td>
                                    <td><?= $text ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-12 col-lg-6">
                <video id="video" controls width="100%"></video>
            </div>
        </div>
    </div>
</div>
<div id="metadata" class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">公報詮釋資料</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm" width="100%" cellspacing="0">
                <tbody>
                    <?php foreach ($agenda as $key => $val): ?>
                    <tr>
                        <th scpoe="row" class="col-3"><?= $key ?></th>
                        <td class="col-9">
                            <?php if (is_string($val) && strpos($val, 'https://') === 0): ?>
                                <a href="<?= $val ?>"><?= $val ?></a>
                            <?php elseif (is_array($val)): ?>
                                <?= json_encode($val, JSON_UNESCAPED_UNICODE) ?>
                            <?php elseif (is_null($val) || $val == 'null'): ?>
                                Null
                            <?php else: ?>
                                <?= $val ?>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="/static/js/ivod/hls.js"></script>
<script>
    if(Hls.isSupported()) {
        var video = document.getElementById('video');
        var hls = new Hls();
        hls.loadSource(<?= json_encode($this->escape($this->data->data->video_url)) ?>);
        hls.attachMedia(video);
        hls.on(Hls.Events.MANIFEST_PARSED,function() {
            video.play();
        });
    }
</script>
<script src="/static/js/ivod/custom_gazette.js"></script>
