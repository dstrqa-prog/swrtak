const form = document.getElementById('story-form');
const fileInput = document.getElementById('story-file');
const storiesBox = document.getElementById('stories');
const user = localStorage.getItem('local_app_user') || 'guest';

function renderStory(story) {
  const ext = story.url.split('.').pop().toLowerCase();
  const media = ['mp4', 'webm', 'mov', 'mkv'].includes(ext)
    ? `<video controls src="${story.url}"></video>`
    : `<img src="${story.url}" alt="story" />`;

  return `<div class="story-card">${media}<p><strong>${story.user}</strong></p></div>`;
}

async function refreshStories() {
  const res = await fetch('/api/stories');
  const data = await res.json();
  storiesBox.innerHTML = data.stories.map(renderStory).join('') || '<p>لا يوجد ستوري حالياً.</p>';
}

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = new FormData();
  formData.append('user', user);
  formData.append('story', fileInput.files[0]);
  const res = await fetch('/api/stories', { method: 'POST', body: formData });
  if (res.ok) {
    form.reset();
    refreshStories();
  }
});

refreshStories();
setInterval(refreshStories, 10000);
