const tiktokPlayer = document.getElementById('tiktok-player');
const prevTt = document.getElementById('prev-tt');
const nextTt = document.getElementById('next-tt');
const playlistSelect = document.getElementById('playlist-select');
const ytPlayer = document.getElementById('yt-player');
const videoList = document.getElementById('video-list');
const searchInput = document.getElementById('search');

let tiktokVideos = [];
let tiktokIndex = 0;
let youtubePlaylists = {};

function fileNameFromPath(path) {
  return decodeURIComponent(path.split('/').pop());
}

function renderYoutubeVideos() {
  const selected = playlistSelect.value;
  const query = searchInput.value.trim().toLowerCase();
  const videos = (youtubePlaylists[selected] || []).filter((path) => {
    const name = fileNameFromPath(path).toLowerCase();
    return name.includes(query) || selected.toLowerCase().includes(query);
  });

  videoList.innerHTML = '';
  videos.forEach((path, idx) => {
    const li = document.createElement('li');
    li.textContent = `${idx + 1}. ${fileNameFromPath(path)}`;
    li.addEventListener('click', () => {
      ytPlayer.src = path;
      ytPlayer.play();
    });
    videoList.appendChild(li);
  });

  if (videos.length) {
    ytPlayer.src = videos[0];
  }
}

function loadTikTok(index) {
  if (!tiktokVideos.length) {
    tiktokPlayer.removeAttribute('src');
    return;
  }
  tiktokIndex = (index + tiktokVideos.length) % tiktokVideos.length;
  tiktokPlayer.src = tiktokVideos[tiktokIndex];
  tiktokPlayer.play();
}

prevTt.addEventListener('click', () => loadTikTok(tiktokIndex - 1));
nextTt.addEventListener('click', () => loadTikTok(tiktokIndex + 1));
playlistSelect.addEventListener('change', renderYoutubeVideos);
searchInput.addEventListener('input', renderYoutubeVideos);

(async function bootstrap() {
  const ttRes = await fetch('/api/videos/tiktok');
  const ttData = await ttRes.json();
  tiktokVideos = ttData.videos;
  loadTikTok(0);

  const ytRes = await fetch('/api/videos/youtube');
  const ytData = await ytRes.json();
  youtubePlaylists = ytData.playlists;

  Object.keys(youtubePlaylists).forEach((name) => {
    const option = document.createElement('option');
    option.value = name;
    option.textContent = `قائمة: ${name}`;
    playlistSelect.appendChild(option);
  });

  renderYoutubeVideos();
})();
