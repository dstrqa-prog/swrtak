const usernameInput = document.getElementById('username');
const loginForm = document.getElementById('login-form');
const loginStatus = document.getElementById('login-status');
const onlinePill = document.getElementById('online-pill');
const storiesPreview = document.getElementById('stories-preview');

function setUser(username) {
  localStorage.setItem('local_app_user', username);
}

function getUser() {
  return localStorage.getItem('local_app_user') || '';
}

async function refreshStatus() {
  const res = await fetch('/api/users/online');
  const data = await res.json();
  onlinePill.textContent = `المتصلون الآن: ${data.count}`;
}

async function refreshStories() {
  const res = await fetch('/api/stories');
  const data = await res.json();
  storiesPreview.innerHTML = '';
  if (!data.stories.length) {
    storiesPreview.innerHTML = '<p>لا يوجد ستوري حالياً.</p>';
    return;
  }

  data.stories.slice(0, 6).forEach((story) => {
    const div = document.createElement('div');
    div.className = 'story-chip';
    div.innerHTML = `<strong>${story.user}</strong><br><small>تنتهي خلال 24 ساعة</small>`;
    storiesPreview.appendChild(div);
  });
}

if (loginForm) {
  loginForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const username = usernameInput.value.trim();
    if (!username) return;

    const res = await fetch('/api/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username }),
    });

    if (!res.ok) {
      loginStatus.textContent = 'تعذر تسجيل الدخول.';
      return;
    }

    setUser(username);
    loginStatus.textContent = `مرحباً ${username}`;
    await refreshStatus();
  });

  const existing = getUser();
  if (existing) {
    usernameInput.value = existing;
    loginStatus.textContent = `مرحباً ${existing}`;
  }
}

refreshStatus();
refreshStories();
setInterval(refreshStatus, 10000);
