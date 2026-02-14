async function loadPublic() {
  const res = await fetch('/api/chat/public');
  const data = await res.json();
  document.getElementById('public-box').innerHTML = data.messages.map(m => `<p><b>${m.username}:</b> ${m.text || '[voice note]'}</p>`).join('');
}

async function loadPrivate() {
  const me = document.getElementById('me').value;
  const peer = document.getElementById('peer').value;
  if (!me || !peer) return;
  const res = await fetch(`/api/chat/private?me=${encodeURIComponent(me)}&peer=${encodeURIComponent(peer)}`);
  const data = await res.json();
  document.getElementById('private-box').innerHTML = data.messages.map(m => `<p><b>${m.from}→${m.to}:</b> ${m.text || '[voice note]'}</p>`).join('');
}

document.getElementById('send-public').addEventListener('click', async () => {
  const username = document.getElementById('me').value || 'anonymous';
  const text = document.getElementById('public-text').value;
  await fetch('/api/chat/public', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({username,text})});
  document.getElementById('public-text').value = '';
  loadPublic();
});

document.getElementById('send-private').addEventListener('click', async () => {
  const from = document.getElementById('me').value;
  const to = document.getElementById('peer').value;
  const text = document.getElementById('private-text').value;
  await fetch('/api/chat/private', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({from,to,text})});
  document.getElementById('private-text').value = '';
  loadPrivate();
});

setInterval(() => { loadPublic(); loadPrivate(); }, 2000);
loadPublic();
