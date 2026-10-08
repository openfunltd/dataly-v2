<?php
    $time = filter_input(INPUT_GET, 't', FILTER_VALIDATE_INT) ?? null;
    if (!in_array('ai-transcript', $this->data->data->支援功能)) {
        echo '無 AI 逐字稿<br>';
        return;
    }
    $MP_name = $this->escape($this->data->data->委員名稱);
    $meet_title = $this->escape($this->data->data->會議資料->標題);

    function ivod_ai_transcript_format_abs_time($epoch_seconds, $tz_offset) {
        $local = fmod($epoch_seconds + $tz_offset, 86400);
        if ($local < 0) {
            $local += 86400;
        }
        return sprintf(
            "%02d:%02d:%02d,%03d",
            $local / 3600,
            $local / 60 % 60,
            $local % 60,
            (1000 * $local) % 1000
        );
    }

    // pyannote 跟 whisperx 各自獨立切分，用時間重疊最多的 pyannote 片段推斷該句的發言者。
    // 兩邊都已依時間遞增排序，用單調指標往前掃描即可，不需要每句都整個 pyannote 重找一次。
    function ivod_ai_transcript_best_speaker($pyannote, &$p_floor, $p_count, $w_start, $w_end) {
        while ($p_floor < $p_count && $pyannote[$p_floor]->end < $w_start) {
            $p_floor++;
        }
        $best = null;
        $best_overlap = 0;
        $i = $p_floor;
        while ($i < $p_count && $pyannote[$i]->start < $w_end) {
            $overlap = min($w_end, $pyannote[$i]->end) - max($w_start, $pyannote[$i]->start);
            if ($overlap > $best_overlap) {
                $best_overlap = $overlap;
                $best = $pyannote[$i];
            }
            $i++;
        }
        return $best;
    }

    $video_start_dt = new DateTime($this->data->data->開始時間);
    $video_start_epoch = (float) $video_start_dt->format('U.u');
    $tz_offset = $video_start_dt->getOffset();

    $pyannote = $this->data->data->transcript->pyannote ?? [];
    $p_count = count($pyannote);
    $p_floor = 0;

    $ai_transcript = [];
    foreach ($this->data->data->transcript->whisperx as $idx => $segment) {
        $content = new stdClass();
        $content->time = ivod_ai_transcript_format_abs_time($video_start_epoch + $segment->start, $tz_offset);
        $speaker_seg = ivod_ai_transcript_best_speaker($pyannote, $p_floor, $p_count, $segment->start, $segment->end);
        if ($speaker_seg) {
            $content->speaker = $this->escape($speaker_seg->speaker_name ?? $speaker_seg->speaker);
            $content->speaker_score = isset($speaker_seg->speaker_score) ? round($speaker_seg->speaker_score * 100) : null;
        } else {
            $content->speaker = '—';
            $content->speaker_score = null;
        }
        $content->text = $this->escape($segment->text);
        $ai_transcript[] = $content;
    }
    $subtitles = json_encode($this->data->data->transcript->whisperx);
?>
<link rel="stylesheet" href="/static/css/ivod/custom_ai-transcript.css">
<div class="card my-3 border-left-danger">
  <div class="card-body">
    本逐字稿內容由 AI 自動生成，可能包含錯誤、遺漏或誤譯之處。請使用者務必與原始影片音訊內容交叉比對，以確保資訊正確性。另可參考立法院日後釋出的正式公報以取得最終權威版本。
  </div>
</div>
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
                                <th>時間</th>
                                <th>發言者</th>
                                <th>發言內容</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ai_transcript as $idx => $segment): ?>
                                <tr id="s-<?= $idx ?>">
                                    <td><?= $segment->time ?></td>
                                    <td>
                                        <?= $segment->speaker ?>
                                        <?php if ($segment->speaker_score !== null): ?>
                                            <span class="text-muted small" title="AI 判別發言者的信心度">(<?= $segment->speaker_score ?>%)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $segment->text ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-12 col-lg-6">
                <video id="video" controls width="100%"></video>
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="font-weight-bold">擷取片段下載</h6>
                        <div class="form-row align-items-center">
                            <div class="col-auto mb-2">
                                <label class="mb-0 mr-1">開始</label>
                                <input type="text" id="clip-start" class="form-control form-control-sm d-inline-block" style="width:120px" placeholder="00:00:00,000">
                                <button type="button" id="clip-start-now" class="btn btn-sm btn-outline-secondary">用目前時間</button>
                            </div>
                            <div class="col-auto mb-2">
                                <label class="mb-0 mr-1">結束</label>
                                <input type="text" id="clip-end" class="form-control form-control-sm d-inline-block" style="width:120px" placeholder="00:00:00,000">
                                <button type="button" id="clip-end-now" class="btn btn-sm btn-outline-secondary">用目前時間</button>
                            </div>
                            <div class="col-auto mb-2">
                                <button type="button" id="clip-download" class="btn btn-sm btn-primary">擷取並下載</button>
                            </div>
                        </div>
                        <div id="clip-status" class="small text-muted mt-1"></div>
                    </div>
                </div>
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
    <?php if ($time) { ?>
    const startAt = <?= $time ?>;
    <?php } ?>
    window.IVOD_VIDEO_URL = <?= json_encode($this->escape($this->data->data->video_url)) ?>;
    window.IVOD_ID = <?= json_encode($this->escape($this->id)) ?>;
</script>
<script src="/static/js/ivod/custom_ai-transcript.js"></script>
<script type="module" src="/static/js/ivod/clip.js"></script>
