let timer;
let isRunning = false;
let timeLeft;
let initialTime;
let pausedTime = 0; // Neue Variable für die gespeicherte Zeit, wenn der Timer pausiert wird
let defaultMinutes = 25;
let defaultSeconds = 0;

const timeDisplay = document.getElementById('timeDisplay');
const startPauseButton = document.getElementById('startPauseButton');
const stopButton = document.getElementById('stopButton');
const setTimeButton = document.getElementById('setTimeButton'); // Der neue Bestätigungsbutton
const minutesInput = document.getElementById('minutesInput');
const secondsInput = document.getElementById('secondsInput');
const timerSound = document.getElementById('timerSound');
const notification = document.getElementById('notification');
const closeNotificationButton = document.getElementById('closeNotification');

// Bestätigungsbutton-Funktion
function confirmTime() {
  const minutes = parseInt(minutesInput.value) || 0;
  const seconds = parseInt(secondsInput.value) || 0;

  // Bestätigte Zeit setzen
  timeLeft = (minutes * 60) + seconds;
  initialTime = timeLeft;

  // Display aktualisieren
  updateDisplay();
}

// Event-Listener für den Bestätigungsbutton
setTimeButton.addEventListener('click', confirmTime);

// Event-Listener für das Drücken der Enter-Taste
document.addEventListener('keydown', function(event) {
  if (event.key === 'Enter') {
    confirmTime(); // Bestätigt die Zeit, wenn Enter gedrückt wird
  }
});

function startPauseTimer() {
  if (isRunning) {
    // Timer pausieren
    clearInterval(timer);
    isRunning = false;
    pausedTime = timeLeft; // Speichert die aktuelle Zeit, wenn der Timer pausiert wird
    startPauseButton.textContent = 'Resume'; // Text auf Resume ändern
  } else {
    // Timer starten oder fortsetzen
    if (pausedTime !== 0) {
      timeLeft = pausedTime; // Setzt timeLeft auf die pausierte Zeit
      pausedTime = 0; // Setzt pausedTime zurück
    }

    // Startet den Timer
    timer = setInterval(updateTime, 1000);
    isRunning = true;
    startPauseButton.textContent = 'Pause'; // Text auf Pause ändern
  }
}

function stopTimer() {
  clearInterval(timer);
  isRunning = false;
  timeLeft = initialTime = (defaultMinutes * 60) + defaultSeconds;
  updateDisplay();
  startPauseButton.textContent = 'Start'; // Text auf Start ändern
}

function updateTime() {
  if (timeLeft > 0) {
    timeLeft--;
    updateDisplay();
  } else {
    clearInterval(timer);
    playSound();
    showNotification();
  }
}

function playSound() {
  if (timerSound) {
    timerSound.currentTime = 0;
    timerSound.play().catch((error) => {
      console.error('Fehler beim Abspielen des Sounds:', error);
    });
  }
}

function showNotification() {
  notification.style.display = 'block';
}

function closeNotification() {
  notification.style.display = 'none';
  timerSound.pause();
  timerSound.currentTime = 0;
  stopTimer();
}

function updateDisplay() {
  const minutes = Math.floor(timeLeft / 60);
  const seconds = timeLeft % 60;
  timeDisplay.textContent = `${minutes < 10 ? '0' : ''}${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
}

window.onload = function() {
  timeLeft = initialTime = (defaultMinutes * 60) + defaultSeconds;
  minutesInput.value = defaultMinutes;
  secondsInput.value = defaultSeconds;
  updateDisplay();
};

startPauseButton.addEventListener('click', startPauseTimer);
stopButton.addEventListener('click', stopTimer);
closeNotificationButton.addEventListener('click', closeNotification);

updateDisplay();
