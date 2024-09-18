window.onload = function(){
  $('input[type=checkbox]').on("change", function() {
      const bill_indexes = $('input[type=checkbox]:checked').map(function() {
          return $(this).val();
      }).get();
      const law_indexes = getDesiredLawIndexes(bill_indexes, bill_n_law_idx_mapping);
      toggleDisplayLawIndexes(law_indexes);
      toggleDisplayLawDiff(law_indexes, bill_indexes);
  });
  
  $('input[type="checkbox"][value="0"]').prop('checked', true).change();
}

function getDesiredLawIndexes(bill_indexes, bill_n_law_idx_mapping) {
    const filtered_mapping = bill_n_law_idx_mapping.filter(function (mapping) {
        return bill_indexes.includes(mapping.bill_idx.toString());
    });
    desiredLawIndexes = filtered_mapping.reduce(function (acc, curr) {
        return [...new Set([...acc, ...curr.law_indexes])];
    }, []);
    return desiredLawIndexes;
}

function toggleDisplayLawIndexes(law_indexes) {
    $('a.law-idx').each(function() {
        ele = $(this);
        current_display = ele.css('display');
        law_index = ele.attr('class').split(' ')[1];
        next_display = (law_indexes.includes(law_index)) ? 'block' : 'none';
        if (current_display != next_display) {
            ele.css('display', next_display);
        }
    });
}

function toggleDisplayLawDiff(law_indexes, bill_indexes) {
    $('div.diff-comparison').each(function() {
        ele = $(this);
        current_display = ele.css('display');
        law_index = ele.attr('class').split(' ')[1];
        next_display = (law_indexes.includes(law_index)) ? 'block' : 'none';
        if (current_display != next_display) {
            ele.css('display', next_display);
        }
    });
    $('tr.diff').each(function() {
        ele = $(this);
        current_display = ele.css('display');
        bill_index = ele.attr('class').split(' ')[1];
        next_display = (bill_indexes.includes(bill_index)) ? 'table-row' : 'none';
        if (current_display != next_display) {
            ele.css('display', next_display);
        }
    });
}
