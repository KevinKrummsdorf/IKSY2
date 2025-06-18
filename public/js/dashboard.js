// dashboard.js

document.addEventListener('DOMContentLoaded', function () {
  // Collapse icon toggles
  document.querySelectorAll('.toggle-collapse-icon').forEach(btn => {
    const icon = btn.querySelector('.collapse-icon');
    const target = document.querySelector(btn.dataset.bsTarget);
    if (!target || !icon) return;

    const updateIcon = (expanded) => {
      icon.textContent = expanded ? 'expand_less' : 'expand_more';
    };

    updateIcon(target.classList.contains('show'));

    target.addEventListener('show.bs.collapse', () => updateIcon(true));
    target.addEventListener('hide.bs.collapse', () => updateIcon(false));
  });

  // Lern-Timer
  const durationInput = document.getElementById('timerDuration');
  const startBtn = document.getElementById('startTimerBtn');
  const modalEl = document.getElementById('timerModal');
  const timerDisplay = document.getElementById('timerDisplay');
  const timerMessage = document.getElementById('timerMessage');
  const closeBtn = document.getElementById('closeTimerBtn');

  if (!durationInput || !startBtn || !modalEl) {
    return;
  }

  const modal = new bootstrap.Modal(modalEl);
  const alarmSound = new Audio(baseUrl + '/assets/alarm.mp3');
  let countdown;
  let remaining = 0;
  let timerRunning = false;

  startBtn.addEventListener('click', () => {
    const minutes = parseInt(durationInput.value, 10);
    remaining = (!isNaN(minutes) && minutes > 0 ? minutes : 30) * 60;
    timerDisplay.textContent = formatTime(remaining);
    timerMessage.textContent = '';
    closeBtn.classList.add('d-none');
    modal.show();
    timerRunning = true;

    clearInterval(countdown);
    countdown = setInterval(() => {
      remaining--;
      if (remaining <= 0) {
        clearInterval(countdown);
        timerDisplay.textContent = formatTime(0);
        timerMessage.textContent = 'Zeit f\u00fcr eine Pause';
        closeBtn.classList.remove('d-none');
        alarmSound.play().catch(() => {});
        timerRunning = false;
      } else {
        timerDisplay.textContent = formatTime(remaining);
      }
    }, 1000);
  });

  modalEl.addEventListener('hide.bs.modal', (e) => {
    if (timerRunning && remaining > 0) {
      if (!confirm('Willst du den Timer wirklich abbrechen?')) {
        e.preventDefault();
        return;
      }
      clearInterval(countdown);
    }
    reset();
  });

  function reset() {
    timerRunning = false;
    remaining = 0;
    durationInput.value = 30;
    timerMessage.textContent = '';
  }

  function formatTime(totalSeconds) {
    const h = Math.floor(totalSeconds / 3600);
    const m = Math.floor((totalSeconds % 3600) / 60);
    const s = totalSeconds % 60;
    const parts = [];
    if (h > 0) parts.push(String(h).padStart(2, '0'));
    parts.push(String(m).padStart(2, '0'));
    parts.push(String(s).padStart(2, '0'));
    return parts.join(':');
  }
});
