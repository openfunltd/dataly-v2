<?php
    if (!in_array('gazette', $this->data->data->支援功能)) {
        echo '無公報紀錄';
        return;
    }
    $MP_name = $this->escape($this->data->data->委員名稱);
    $meet_title = $this->escape($this->data->data->會議資料->標題);

    function ivod_gazette_epoch($iso) {
        return (float) (new DateTime($iso))->format('U.u');
    }

    function ivod_gazette_format_timecode($seconds) {
        $seconds = max(0, $seconds);
        return sprintf(
            "%02d:%02d:%02d,%03d",
            $seconds / 3600,
            $seconds / 60 % 60,
            $seconds % 60,
            (1000 * $seconds) % 1000
        );
    }

    $video_start = ivod_gazette_epoch($this->data->data->開始時間);

    $gazette_transcript = [];
    $subtitles = [];
    $has_timeline = false;
    foreach ($this->data->data->transcript->gazette as $segment) {
        $content = new stdClass();
        $content->speaker = $this->escape($segment->speaker);
        $content->text = $this->escape($segment->text);
        $content->guessed = $segment->guessed;
        if ($segment->start !== null && $segment->end !== null) {
            $start_sec = ivod_gazette_epoch($segment->start) - $video_start;
            $end_sec = ivod_gazette_epoch($segment->end) - $video_start;
            $content->start = ivod_gazette_format_timecode($start_sec);
            $content->end = ivod_gazette_format_timecode($end_sec);
            $subtitles[] = ['start' => max(0, $start_sec), 'end' => max(0, $end_sec)];
            $has_timeline = true;
        } else {
            $content->start = null;
            $content->end = null;
            $subtitles[] = ['start' => null, 'end' => null];
        }
        $gazette_transcript[] = $content;
    }
    $subtitles_json = json_encode($subtitles);

    $agenda = $this->data->data->gazette->agenda ?? null;
?>
<link rel="stylesheet" href="/static/css/ivod/custom_ai-transcript.css">
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
                                <?php if ($has_timeline): ?>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <?php endif; ?>
                                <th>Speaker</th>
                                <th>Text</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gazette_transcript as $idx => $segment): ?>
                                <tr id="s-<?= $idx ?>" <?= $segment->start === null ? 'class="text-muted"' : '' ?>>
                                    <?php if ($has_timeline): ?>
                                    <td>
                                        <?= $segment->start ?? '—' ?>
                                        <?php if ($segment->start !== null && $segment->guessed): ?>
                                            <span class="text-muted" title="時間為 AI 推估值，可能有誤差">≈</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $segment->end ?? '—' ?></td>
                                    <?php endif; ?>
                                    <td><?= $segment->speaker ?></td>
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
<?php if ($agenda): ?>
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
                        <th scope="row" class="col-3"><?= $key ?></th>
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
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
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
    var subtitles = <?= $subtitles_json ?>;
    var hasTimeline = <?= $has_timeline ? 'true' : 'false' ?>;
</script>
<script src="/static/js/ivod/custom_gazette.js"></script>
