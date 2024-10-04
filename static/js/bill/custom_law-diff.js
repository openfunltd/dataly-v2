window.onload = function(){
  //generate text-diff
  let diffResult = {};
  for (const [law, comparison] of Object.entries(diffData)) {
    let current = comparison['current'];
    let commit = comparison['commit'];
    diffResult[law] = {};
    if (current === null || current == '') {
      diffResult[law].current = null;
      diffResult[law].commit = commit;
      continue;
    }
    const diff = new Diff();
    const textDiff = diff.main(current, commit);
    const diff_in_html = diff.prettyHtml(textDiff);
    diffResult[law].current = current.replaceAll("\n", "<br>");
    diffResult[law].commit = diff_in_html.replaceAll("\n", "<br>");
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

    //commit
    var commitText = diff.commit;
    console.log(commitText);
    var commitTr = $('<tr>').append(
      $('<td>', {
        text: (currentText === null) ? '增訂條文' : '修正條文',
      }),
      $('<td>', {
        html: commitText,
      }),
    );

    diffTableBody.append(currentTr);
    diffTableBody.append(commitTr);
    diffTable.append(diffTableBody);

  });
}
