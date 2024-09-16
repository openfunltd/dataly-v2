<?php
    if (!in_array('ai-transcript', $this->data->data->支援功能)) {
        echo '無 AI 逐字稿<br>';
        return;
    }
    $MP_name = $this->escape($this->data->data->委員名稱);
    $meet_title = $this->escape($this->data->data->會議資料->標題);
    $ai_transcript = [];
    foreach ($this->data->data->transcript->whisperx as $idx => $segment) {
        $content = new stdClass();
        $start = sprintf(
            "%02d:%02d:%02d,%03d",
            $segment->start / 3600,
            $segment->start / 60 % 60,
            $segment->start % 60, (1000 * $segment->start) % 1000
        );
        $end = sprintf(
            "%02d:%02d:%02d,%03d",
            $segment->end / 3600,
            $segment->end / 60 % 60,
            $segment->end % 60, (1000 * $segment->end) % 1000
        );
        $content->start = $start;
        $content->end = $end;
        $content->text = $this->escape($segment->text);
        $ai_transcript[] = $content;
    }
    $subtitles = json_encode($this->data->data->transcript->whisperx);
?>
<div id="ai-transcript" class="card shadow mb-4">
    <div class="card-header py-3">
        <h1 class="h3 mb-0 text-gray-800">
            <?= $MP_name ?> @ <?= $meet_title ?>
        </h1>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 col-lg-6">
                <div>
                    <table id="subtitleTable" class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Text</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ai_transcript as $idx => $segment): ?>
                                <tr id="s-<?= $idx ?>">
                                    <td><?= $segment->start ?></td>
                                    <td><?= $segment->end ?></td>
                                    <td><?= $segment->text ?></td>
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
<script>
    var subtitles = <?= $subtitles ?>;
</script>
<script src="/static/js/ivod/custom.js"></script>
