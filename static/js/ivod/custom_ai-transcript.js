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
      startTime = $(this).find('td:first').text();
      video.currentTime = timeStringToSeconds(startTime);
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
    if (Math.abs(timeCache - time) < 0.5) {
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

// H:i:s.u to s
function timeStringToSeconds(timeString) {
    const parts = timeString.split(/[:,]/).map(parseFloat);
    let totalSeconds = 0;

    // Convert hours to seconds
    if (parts.length === 4) {
        totalSeconds += parts[0] * 3600; // 1 hour = 3600 seconds
        totalSeconds += parts[1] * 60;   // 1 minute = 60 seconds
        totalSeconds += parts[2];         // seconds
        totalSeconds += parts[3] / 1000;  // milliseconds
    }
    // Convert minutes to seconds
    else if (parts.length === 3) {
        totalSeconds += parts[0] * 60;   // 1 minute = 60 seconds
        totalSeconds += parts[1];         // seconds
        totalSeconds += parts[2] / 1000;  // milliseconds
    }
    // Only seconds provided
    else if (parts.length === 2) {
        totalSeconds += parts[0];         // seconds
        totalSeconds += parts[1] / 1000;  // milliseconds
    }

    return totalSeconds;
}
