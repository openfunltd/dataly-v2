window.onload = function(){
  $('.meet-reason').click(function(){
    selectionLength = window.getSelection().toString().length;
    if (selectionLength === 0) {
      $(this).toggleClass('truncate-2');
    }
  });

  if ($("#ivod-table").length) {
    const table = $('#ivod-table').DataTable({
      fixedHeader: true,
    });
  }
  
  if ($("#related-doc-table").length) {
    const table = $('#related-doc-table').DataTable({
      fixedHeader: true,
    });
  }
}
