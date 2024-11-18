<link href="/static/css/tooltip.css" rel="stylesheet">
<div class="tooltip-container">
  <div class="row">
    <div class="col-7">
      <p>
        <a class="tooltip-name" href="/" class="tooltip-name font-weight-bold"></a>
        <span>立法委員</span>
      </p>
      <hr>
      <p class="font-weight-bold">選區</p>
      <p class="tooltip-area"></p>
      <hr>
      <p class="font-weight-bold">所屬委員會</p>
      <p class="tooltip-committee"></p>
    </div>
    <div class="col-5">
      <img class="tooltip-img img-thumbnail" src="">
    </div>
  </div>
</div>
<script>
  const ly_api_url = 'https://' + '<?= $this->escape(getenv('LYAPI_HOST')) ?>';
</script>
<script src="/static/js/tooltip.js"></script>
