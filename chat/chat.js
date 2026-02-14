const user = localStorage.getItem('local_app_user') || 'guest';
const publicBox = document.getElementById('public-messages');
const privateBox = document.getElementById('private-messages');

const publicForm = document.getElementById('public-form');
const publicInput = document.getElementById('public-input');
const publicAudio = document.getElementById('public-audio');

const privateForm = document.getElementById('private-form');
const privateTarget = document.getElementById('private-target');
const privateInput = document.getElementById('private-input');
const privateAudio = document.getElementById('private-audio');

function fmt(msg) {
  const audio = msg.audio ? `<audio controls src="${msg.audio}"></audio>` : '';
  return `<div class="msg"><strong>${msg.user}</strong>: ${msg.text || ''}<br>${audio}</div>`;
}

async function uploadAudio(file) {
  if (!file) return null;
  const form = new FormData();
  form.append('audio', file);
  const res = await fetch('/api/chat/audio', { method: 'POST', body: form });
  if (!res.ok) return null;
  const data = await res.json();
  return data.path;
}

async function refreshPublic() {
  const res = await fetch('/api/chat/public');
  const data = await res.json();
  publicBox.innerHTML = data.messages.map(fmt).join('');
}

async function refreshPrivate() {
  const target = privateTarget.value.trim();
  if (!target) return;
  const res = await fetch(`/api/chat/private?user=${encodeURIComponent(user)}&target=${encodeURIComponent(target)}`);
  const data = await res.json();
  privateBox.innerHTML = data.messages.map(fmt).join('');
}

publicForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const audio = await uploadAudio(publicAudio.files[0]);
  await fetch('/api/chat/public', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ user, text: publicInput.value.trim(), audio }),
  });
  publicInput.value = '';
  publicAudio.value = '';
  refreshPublic();
});

privateForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const target = privateTarget.value.trim();
  const audio = await uploadAudio(privateAudio.files[0]);
  await fetch('/api/chat/private', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ user, target, text: privateInput.value.trim(), audio }),
  });
  privateInput.value = '';
  privateAudio.value = '';
  refreshPrivate();
});

privateTarget.addEventListener('change', refreshPrivate);
setInterval(() => {
  refreshPublic();
  refreshPrivate();
}, 3000);

refreshPublic();
