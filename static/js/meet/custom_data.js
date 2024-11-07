window.onload = function(){
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
