(function () {
  'use strict';

  /* ===== LIVE CLOCK ===== */
  function updateClock() {
    var now = new Date();
    var h = String(now.getHours()).padStart(2, '0');
    var m = String(now.getMinutes()).padStart(2, '0');
    var s = String(now.getSeconds()).padStart(2, '0');
    var el = document.getElementById('ecClockTime');
    if (el) el.textContent = h + ':' + m + ':' + s;

    var dateEl = document.getElementById('ecClockDate');
    if (dateEl) {
      var opts = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
      dateEl.textContent = now.toLocaleDateString('en-US', opts);
    }
  }

  updateClock();
  setInterval(updateClock, 1000);

  /* ===== SCHEDULER ===== */
  window.openScheduler = function (day, month, year) {
    var dateInput = document.getElementById('ecInputDate');
    if (dateInput) {
      var mm = String(month).padStart(2, '0');
      var dd = String(day).padStart(2, '0');
      dateInput.value = year + '-' + mm + '-' + dd;
    }

    var titleInput = document.getElementById('ecInputTitle');
    var formTitle = document.getElementById('ecFormTitle');
    if (titleInput && formTitle) {
      titleInput.value = '';
      formTitle.textContent = 'Add Event — ' + day + ' ' + dateInput.value;
    }

    var card = document.getElementById('ecFormCard');
    if (card) card.scrollIntoView({ behavior: 'smooth', block: 'center' });
  };
})();
