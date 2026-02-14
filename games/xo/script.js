const boardEl=document.getElementById('board'); const statusEl=document.getElementById('status'); const modeBtn=document.getElementById('mode');
let board=Array(9).fill(''); let turn='X'; let ai=true;
const wins=[[0,1,2],[3,4,5],[6,7,8],[0,3,6],[1,4,7],[2,5,8],[0,4,8],[2,4,6]];
function winner(b){for(const [a,c,d] of wins){if(b[a]&&b[a]===b[c]&&b[c]===b[d]) return b[a];} return b.includes('')?null:'D';}
function draw(){boardEl.innerHTML='';board.forEach((v,i)=>{const c=document.createElement('button');c.className='cell';c.textContent=v;c.onclick=()=>play(i);boardEl.appendChild(c);}); const w=winner(board); statusEl.textContent=w?(w==='D'?'تعادل':`الفائز ${w}`):`الدور ${turn}`;}
function aiMove(){if(winner(board)) return; const idx=board.findIndex(v=>!v); if(idx>-1){board[idx]='O'; turn='X'; draw();}}
function play(i){if(board[i]||winner(board)) return; board[i]=turn; turn=turn==='X'?'O':'X'; draw(); if(ai&&turn==='O') setTimeout(aiMove,250);}
modeBtn.onclick=()=>{ai=!ai;modeBtn.textContent=`تبديل الوضع: ${ai?'AI':'2P'}`; board=Array(9).fill(''); turn='X'; draw();};
draw();
