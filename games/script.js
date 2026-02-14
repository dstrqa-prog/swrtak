const boardEl = document.getElementById('xo-board');
const statusEl = document.getElementById('xo-status');
const modeEl = document.getElementById('mode');
const resetBtn = document.getElementById('reset');

let board = Array(9).fill('');
let current = 'X';

const wins = [
  [0, 1, 2], [3, 4, 5], [6, 7, 8],
  [0, 3, 6], [1, 4, 7], [2, 5, 8],
  [0, 4, 8], [2, 4, 6],
];

function winner(state) {
  for (const [a, b, c] of wins) {
    if (state[a] && state[a] === state[b] && state[a] === state[c]) return state[a];
  }
  return state.includes('') ? null : 'draw';
}

function minimax(state, isMax) {
  const result = winner(state);
  if (result === 'O') return 1;
  if (result === 'X') return -1;
  if (result === 'draw') return 0;

  let best = isMax ? -Infinity : Infinity;
  for (let i = 0; i < 9; i += 1) {
    if (state[i]) continue;
    state[i] = isMax ? 'O' : 'X';
    const score = minimax(state, !isMax);
    state[i] = '';
    best = isMax ? Math.max(best, score) : Math.min(best, score);
  }
  return best;
}

function aiMove() {
  let bestScore = -Infinity;
  let move = -1;
  for (let i = 0; i < 9; i += 1) {
    if (board[i]) continue;
    board[i] = 'O';
    const score = minimax(board, false);
    board[i] = '';
    if (score > bestScore) {
      bestScore = score;
      move = i;
    }
  }
  if (move !== -1) board[move] = 'O';
}

function render() {
  boardEl.innerHTML = '';
  board.forEach((value, idx) => {
    const btn = document.createElement('button');
    btn.className = 'cell';
    btn.textContent = value;
    btn.addEventListener('click', () => play(idx));
    boardEl.appendChild(btn);
  });

  const result = winner(board);
  if (result === 'draw') statusEl.textContent = 'تعادل';
  else if (result) statusEl.textContent = `الفائز: ${result}`;
  else statusEl.textContent = `الدور الحالي: ${current}`;
}

function play(idx) {
  if (board[idx] || winner(board)) return;
  board[idx] = current;

  if (modeEl.value === 'ai' && !winner(board)) {
    aiMove();
    current = 'X';
  } else {
    current = current === 'X' ? 'O' : 'X';
  }

  render();
}

resetBtn.addEventListener('click', () => {
  board = Array(9).fill('');
  current = 'X';
  render();
});

render();
