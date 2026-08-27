window.onload = function(){
  const table = $('#subtitleTable').DataTable({
    ordering: false,
    scrollCollapse: true,
    scrollY: '300px',
    paging: false,
    fixedHeader: true,
  });

  if (!hasTimeline) {
    return;
  }

  $('tr[id^="s-"]').on('click', function(event) {
    const idx = parseInt($(this).attr('id').slice(2), 10);
    if (subtitles[idx].start === null) {
      return;
    }
    if (window.getSelection().toString().length === 0) {
      video.currentTime = subtitles[idx].start;
      video.play();
      video.focus();
    } else {
      event.preventDefault();
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
  if (data.start === null) {
    return 2;
  }
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
