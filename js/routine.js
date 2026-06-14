(function () {
  'use strict';

  function highlightActiveTask() {
    var slots = document.querySelectorAll('.routine-slot');
    if (!slots.length) return;

    var now = new Date();
    var currentMinutes = now.getHours() * 60 + now.getMinutes();
    var activeFound = false;

    slots.forEach(function (slot) {
      var timeEl = slot.querySelector('.routine-slot-time');
      if (!timeEl) return;
      var timeText = timeEl.textContent.trim();
      var parsed = parseTimeToMinutes(timeText);
      if (parsed === null) return;

      slot.classList.remove('active');

      var next = slot.nextElementSibling;
      var nextParsed = null;
      if (next) {
        var nextTimeEl = next.querySelector('.routine-slot-time');
        if (nextTimeEl) nextParsed = parseTimeToMinutes(nextTimeEl.textContent.trim());
      }

      if (!activeFound && parsed !== null && currentMinutes >= parsed) {
        if (!nextParsed || currentMinutes < nextParsed) {
          slot.classList.add('active');
          activeFound = true;
        }
      }
    });
  }

  function parseTimeToMinutes(str) {
    str = str.trim().toUpperCase();
    var match = str.match(/^(\d{1,2}):?(\d{2})?\s*(AM|PM)$/);
    if (!match) return null;
    var h = parseInt(match[1], 10);
    var m = match[2] ? parseInt(match[2], 10) : 0;
    var period = match[3];
    if (period === 'PM' && h !== 12) h += 12;
    if (period === 'AM' && h === 12) h = 0;
    return h * 60 + m;
  }

  var initTimer = null;
  function init() {
    highlightActiveTask();
    if (initTimer) clearInterval(initTimer);
    initTimer = setInterval(highlightActiveTask, 60000);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
