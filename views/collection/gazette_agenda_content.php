<?php
$agenda = $this->data->data;

//agenda content
$parsed_doc_urls = $agenda->處理後公報網址 ?? [];
$tikahtml_doc = array_filter($parsed_doc_urls, function($doc) {
    return $doc->type == 'tikahtml';
});
$tikahtml_url = array_shift($tikahtml_doc)->url;
$tikahtml_content = file_get_contents($tikahtml_url);
if ($tikahtml_content !== false) {
    $allowedTags = '<p><b><i><ul><ol><li><br><div><span><h1><h2><h3><h4><h5><h6>';
    $tikahtml_content = strip_tags($tikahtml_content, $allowedTags);
    $tikahtml_content = preg_replace('/(on\w+|style)="[^"]*"/i', '', $tikahtml_content);
}
?>
<style>
  #html-content p {
    margin: 2px !important;
  }
  #html-content p:has(b) {
    margin: 10px !important;
  }
</style>
<?php if ($tikahtml_content !== false) { ?>
  <div class="card shadow mt-3 mb-3">
    <div id="html-content" class="card-body">
      <?= $tikahtml_content ?>
    </div>
  </div>
<?php } else { ?>
  <div class="mt-2 card border-left-danger">
    <div class="card-body">
      無關係文書
    </div>
  </div>
<?php } ?>
