const video = document.getElementById('video');
const playBtn = document.getElementById('play-btn');
const progress = document.getElementById('progress');
const timeDisplay = document.getElementById('time');
const muteBtn = document.getElementById('mute-btn');
const volume = document.getElementById('volume');
const speedBtn = document.getElementById('speed-btn');
const fullscreenBtn = document.getElementById('fullscreen-btn');
const viewsDisplay = document.getElementById('views');
const heatmap = document.getElementById('heatmap');

// Analytics Data
let analytics = {
  views: localStorage.getItem('videoViews') || 0,
  watchedSegments: JSON.parse(localStorage.getItem('watchedSegments')) || Array(20).fill(0)
};

// Initialize
updateViews();
renderHeatmap();

// Play/Pause
playBtn.addEventListener('click', togglePlay);
video.addEventListener('click', togglePlay);

function togglePlay() {
  if (video.paused) {
    video.play();
    playBtn.textContent = '⏸';
    analytics.views++;
    localStorage.setItem('videoViews', analytics.views);
    updateViews();
  } else {
    video.pause();
    playBtn.textContent = '▶';
  }
}

// Progress Bar
video.addEventListener('timeupdate', updateProgress);

function updateProgress() {
  progress.value = (video.currentTime / video.duration) * 100;
  timeDisplay.textContent = `${formatTime(video.currentTime)} / ${formatTime(video.duration)}`;
  
  // Update heatmap (track watched segments)
  const segment = Math.floor((video.currentTime / video.duration) * 20);
  analytics.watchedSegments[segment]++;
  localStorage.setItem('watchedSegments', JSON.stringify(analytics.watchedSegments));
  renderHeatmap();
}

progress.addEventListener('input', () => {
  video.currentTime = (progress.value / 100) * video.duration;
});

// Volume Control
volume.addEventListener('input', () => {
  video.volume = volume.value / 100;
  muteBtn.textContent = video.volume === 0 ? '🔇' : '🔊';
});

muteBtn.addEventListener('click', () => {
  video.volume = video.volume === 0 ? 1 : 0;
  volume.value = video.volume * 100;
  muteBtn.textContent = video.volume === 0 ? '🔇' : '🔊';
});

// Speed Control
speedBtn.addEventListener('click', () => {
  const speeds = [0.5, 1, 1.5, 2];
  const currentSpeed = video.playbackRate;
  const newSpeed = speeds[(speeds.indexOf(currentSpeed) + 1) % speeds.length];
  video.playbackRate = newSpeed;
  speedBtn.textContent = `${newSpeed}x`;
});

// Fullscreen
fullscreenBtn.addEventListener('click', () => {
  if (!document.fullscreenElement) {
    video.requestFullscreen();
  } else {
    document.exitFullscreen();
  }
});

// Keyboard Shortcuts
document.addEventListener('keydown', (e) => {
  if (e.code === 'Space') togglePlay();
  if (e.code === 'ArrowLeft') video.currentTime -= 5;
  if (e.code === 'ArrowRight') video.currentTime += 5;
  if (e.code === 'KeyF') fullscreenBtn.click();
});

// Helper Functions
function formatTime(seconds) {
  const mins = Math.floor(seconds / 60);
  const secs = Math.floor(seconds % 60);
  return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
}

function updateViews() {
  viewsDisplay.textContent = analytics.views;
}

function renderHeatmap() {
  heatmap.innerHTML = '';
  const maxWatched = Math.max(...analytics.watchedSegments);
  
  analytics.watchedSegments.forEach((count) => {
    const segment = document.createElement('div');
    segment.className = 'heatmap-segment';
    segment.style.background = `hsl(${120 - (count / maxWatched) * 120}, 100%, 50%)`;
    heatmap.appendChild(segment);
  });
}