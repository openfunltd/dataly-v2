<?php
    echo $this->escape($this->data->data->video_url) . "<br>";
?>
<div>
  <video id="video" controls width="100%"></video>
</div>
<script src="/static/js/hls.js"></script>
<script>
    if(Hls.isSupported()) {
        var video = document.getElementById('video');
        var hls = new Hls();

        // working
        hls.loadSource('https://h264media01.ly.gov.tw:443/vod_1/_definst_/mp4:1M/cbbd4ed0b5fcd3a07e9323a110d342f509b1d301fcc61009ab4dc72a1a8924cd5905afd99abfec235ea18f28b6918d91.mp4/playlist.m3u8');

        // not work
        //hls.loadSource(<?= $this->escape($this->data->data->video_url) ?>);

        hls.attachMedia(video);
        hls.on(Hls.Events.MANIFEST_PARSED,function() {
            video.play();
        });
    }
</script>
