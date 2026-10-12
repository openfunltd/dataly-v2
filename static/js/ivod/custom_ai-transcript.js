window.onload = function(){
  const table = $('#subtitleTable').DataTable({
    ordering: false,
    scrollCollapse: true,
    scrollY: '300px',
    paging: false,
    fixedHeader: true,
  });

  $('tr[id^="s-"]').on('click', function(event) {
    if (window.getSelection().toString().length === 0) {
      const idx = parseInt($(this).attr('id').slice(2), 10);
      video.currentTime = subtitles[idx].start;
      video.play();
      video.focus();
    } else {
      event.preventDefault();
    }
  });

  // 時間欄顯示的是當天絕對時間，截取片段下載要的是影片相對秒數，
  // 因此兩個欄位都從 subtitles（相對秒數）取值並用 clip.js 的格式化邏輯顯示。
  // 不呼叫 stopPropagation，讓點擊事件照常冒泡到上面的 row handler 跳轉影片。
  $('tr[id^="s-"] td:nth-child(1)').on('click', function(event) {
    if (window.getSelection().toString().length === 0) {
      const idx = parseInt($(this).parent().attr('id').slice(2), 10);
      $('#clip-start').val(window.IvodClip.formatTimecode(subtitles[idx].start));
    }
  });

  $('tr[id^="s-"] td:nth-child(3)').on('click', function(event) {
    if (window.getSelection().toString().length === 0) {
      const idx = parseInt($(this).parent().attr('id').slice(2), 10);
      $('#clip-end').val(window.IvodClip.formatTimecode(subtitles[idx].end));
    }
  });

  timeCache = 0;
  d_g = $('#s-0').offset().top;
  video.addEventListener("timeupdate", function(event) {
    time = this.currentTime;
    if (Math.abs(timeCache - time) < 0.1) {
      return;
    }
    timeCache = time;
    desiredIndex = timecodeBinarySearch(subtitles, time);
    if (desiredIndex == -1) {
      return;
    }
    desiredTr = $('#s-' + desiredIndex);
    selectedTr = $('tr.selected');
    if (desiredTr.is(selectedTr)) {
      return;
    }
    if (selectedTr.length > 0) {
      selectedTr.removeClass('selected');
    }
    desiredTr.addClass('selected');
    scroll_pos = $('.dataTables_scrollBody').scrollTop();
    d_x = desiredTr.offset().top;
    $('.dataTables_scrollBody').scrollTop(scroll_pos + d_x - d_g);
  });

  if (typeof startAt !== 'undefined') {
    const segmentId = getSegmentId(startAt);
    if (segmentId !== false) {
      $(`#${segmentId}`).click();
      video.currentTime = startAt;
    }
  }
};

function timecodeBinarySearch(subtitles, time) {
  left = 0;
  right = subtitles.length;
  middle = Math.floor((left + right)/2);
  result = compareTimeCode(subtitles[middle], time);
  cnt = 0
  while (left <= right && ! (left == right && right == left)) {
    if (left == right && middle == right) {
      break;
    }
    if (right - left == 1) {
      isLeft = (compareTimeCode(subtitles[left], time) == 1) ? true :  false;
      isRight = (compareTimeCode(subtitles[right], time) == 1) ? true :  false;
      if (! isLeft && ! isRight) {
        break;
      }
    }
    middle = Math.floor((left + right)/2);
    result = compareTimeCode(subtitles[middle], time);
    if (cnt > 2000) {
      break;
    }
    if (result === 0) {
      right = middle;
    } else if (result === 2) {
      left = middle;
    } else {
      return middle;
    }
    cnt++;
  }
  return -1;
}

function compareTimeCode(data, desiredTime) {
  startTime = data['start'];
  endTime = data['end'];
  if (startTime > desiredTime) {
    return 0;
  }
  if (desiredTime > endTime) {
    return 2;
  }
  return 1;
}

function getSegmentId(startAt) {
    for (let i = 0; i < subtitles.length ;i++) {
        const segmentStart = subtitles[i].start;
        const segmentEnd = subtitles[i].end;
        if (segmentStart <= startAt && startAt <= segmentEnd) {
            return `s-${i}`;
        }
    }
    return false;
}
