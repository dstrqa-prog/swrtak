let currentUser = "";

async function refreshUsers() {
  const res = await fetch('/api/users');
  const data = await res.json();
  document.getElementById('online-count').textContent = `المتصلون الآن: ${data.online_count}`;
}

async function loadTikTok() {
  const res = await fetch('/api/videos/tiktok');
  const data = await res.json();
  const root = document.getElementById('tiktok-feed');
  root.innerHTML = '';
  data.videos.forEach((v) => {
    const el = document.createElement('video');
    el.src = v;
    el.controls = true;
    el.loop = true;
    el.autoplay = true;
    el.muted = true;
    root.appendChild(el);
  });
  if (!data.videos.length) root.textContent = 'لا توجد فيديوهات بعد في videos/tiktok';
}

async function loadYoutube() {
  const res = await fetch('/api/videos/youtube');
  const data = await res.json();
  const search = (document.getElementById('yt-search').value || '').toLowerCase();
  const root = document.getElementById('yt-playlists');
  root.innerHTML = '';

  data.playlists.forEach((p) => {
    const vids = p.videos.filter((v) => v.toLowerCase().includes(search));
    if (!vids.length && search) return;

    const box = document.createElement('div');
    box.className = 'story-item';
    box.innerHTML = `<h3>${p.playlist}</h3>`;
    vids.forEach((v) => {
      const video = document.createElement('video');
      video.src = v;
      video.controls = true;
      box.appendChild(video);
    });
    root.appendChild(box);
  });
}

async function loadStories() {
  const res = await fetch('/api/stories');
  const data = await res.json();
  const root = document.getElementById('stories');
  root.innerHTML = '';

  data.stories.forEach((s) => {
    const item = document.createElement('div');
    item.className = 'story-item';
    item.innerHTML = `<strong>${s.username}</strong>`;
    if (s.media_type === 'video') {
      const v = document.createElement('video');
      v.src = s.media_url;
      v.controls = true;
      item.appendChild(v);
    } else {
      const img = document.createElement('img');
      img.src = s.media_url;
      item.appendChild(img);
    }
    root.appendChild(item);
  });
}

document.getElementById('login-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const username = document.getElementById('username').value;
  const res = await fetch('/api/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username })
  });
  const data = await res.json();
  if (res.ok) {
    currentUser = data.username;
    document.getElementById('welcome').textContent = `مرحباً ${currentUser}`;
    refreshUsers();
  }
});

document.getElementById('yt-search').addEventListener('input', loadYoutube);

document.getElementById('story-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const media_url = document.getElementById('story-url').value;
  const media_type = document.getElementById('story-type').value;
  await fetch('/api/stories', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username: currentUser || 'anonymous', media_url, media_type })
  });
  e.target.reset();
  loadStories();
});

refreshUsers();
loadTikTok();
loadYoutube();
loadStories();
setInterval(refreshUsers, 10000);
setInterval(loadStories, 30000);
