window.onload = function(){
  //generate text-diff
  let diffResult = {};
  for (const [law, comparison] of Object.entries(diffData)) {
    diffResult[law] = {};
    let current = comparison['current'];
    let commits = comparison['commits'];
    if (current === null || current == '') {
      diffResult[law].current = current;
      diffResult[law].commits = comparison['commits'];
      for (const [bill_idx, commit] of Object.entries(commits)) {
        diffResult[law].commits[bill_idx] = commit.replaceAll("\n", "<br>");
      }
      continue;
    }
    diffResult[law].current = current.replaceAll("\n", "<br>");
    diffResult[law].commits = {};
    for (const [bill_idx, commit] of Object.entries(commits)) {
      const diff = new Diff();
      const textDiff = diff.main(current, commit);
      const diff_in_html = diff.prettyHtml(textDiff);
      diffResult[law].commits[bill_idx] = diff_in_html;
    }
  }

  //render law-idx-list
  $.each(diffResult, function(lawIdx, diff) {
    var anchor = $('<a>', {
      class: 'law-idx ' + lawIdx,
      href: '#' + lawIdx,
      text: lawIdx,
      css: {
        display: 'block',
      }
    });
    $('.law-idx-a-list').append(anchor);
  });

  //render diff-tables
  $.each(diffResult, function(lawIdx, diff) {
    //main div
    var diffTableDiv = $('<div>', {
      id: lawIdx,
      class: 'diff-comparison ' + lawIdx + ' card shadow mb-4',
    });
    $('.diff-tables').append(diffTableDiv);

    //card-header-div
    var diffTableHeaderDiv = $('<div>', {
      class: 'card-header py-3'
    }).append(
      $('<h6>', {
        class: 'm-0 font-weight-bold text-primary',
        text: lawIdx
      })
    );
    diffTableDiv.append(diffTableHeaderDiv);

    //card-body-div
    var diffTableBodyDiv = $('<div>', {
      class: 'card-body'
    });

    //card-body-table
    var diffTable = $('<table>', {
      class: 'table table-bordered table-sm nowrap'
    });
    diffTableBodyDiv.append(diffTable);
    diffTableDiv.append(diffTableBodyDiv);

    //thead
    var diffTableHead = $('<thead>').append(
      $('<th>', {
          text: '版本名稱',
          css: { width: '20%' }
      }),
      $('<th>', {
          text: '條文內容'
      })
    );
    diffTable.append(diffTableHead);

    //tbody
    var diffTableBody = $('<tbody>');

    //current
    var currentText = diff.current;
    var currentTr = $('<tr>').append(
      $('<td>', {
        text: '現行條文'
      }),
      $('<td>', {
        html: currentText || '本條新增無現行版本'
      }),
    );
    diffTableBody.append(currentTr);
    diffTable.append(diffTableBody);

    //diff of relatedBills
    $.each(relatedBills, function(billIdx, bill) {
      diffBillTr = $('<tr>', {
        class: 'diff ' + billIdx
      }).append(
        $('<td>', {
          text: bill.version_name
        }),
        $('<td>', {
          html: diff.commits[billIdx] || '無'
        }),
      )
      diffTableBody.append(diffBillTr);
    });
  });

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
