<?php
    //echo $this->escape($this->data->data->video_url) . "<br>";
    var_dump($data);
?>
<div id="ai-transcript" class="card shadow mb-4">
    <div class="card-header py-3">
        <h1 class="h3 mb-0 text-gray-800">
            <?= $this->escape($this->data->data->委員名稱) ?> @
            <?= $this->escape($this->data->data->會議資料->標題) ?>
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
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-12 col-lg-6">
                <video id="video" controls width="100%"></video>
            </div>
        </div>
    </div>
<div>
  <video id="video" controls width="100%"></video>
</div>
<script src="/static/js/hls.js"></script>
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
