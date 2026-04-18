<?php
require_once 'includes/config.php';
if(!isLoggedIn()) { setFlash('warning','⚠️ Konekte ou premye.'); redirect('login.php'); }
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ht" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🤖 BiroTech AI</title>
<link rel="stylesheet" href="css/style.css">
<style>
.ai-wrap{
  max-width:800px;margin:0 auto;
  padding:68px 1rem 1rem;
  height:100vh;
  display:flex;flex-direction:column;
}
.ai-header{
  text-align:center;padding:1.2rem 0 .8rem;flex-shrink:0;
}
.chat-box{
  flex:1;overflow-y:auto;
  background:var(--bg-card);
  border:1px solid var(--border);
  border-radius:var(--radius);
  padding:1rem;
  display:flex;flex-direction:column;gap:.8rem;
  margin-bottom:.8rem;
  min-height:0;
}
.msg{display:flex;gap:.6rem;align-items:flex-start;animation:fadeInUp .25s ease;}
.msg.user{flex-direction:row-reverse;}
.av{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.av-ai{background:linear-gradient(135deg,var(--blue-primary),var(--gold));}
.av-u{background:linear-gradient(135deg,var(--gold),#C8A400);color:#000;font-weight:900;font-size:.85rem;}
.bubble{max-width:78%;padding:.75rem 1rem;border-radius:14px;font-size:.88rem;line-height:1.65;word-break:break-word;}
.bubble-ai{background:rgba(255,255,255,.04);border:1px solid var(--border);color:var(--text-main);}
.bubble-ai code{background:rgba(0,176,255,.12);padding:.1rem .35rem;border-radius:4px;font-family:monospace;color:var(--blue-glow);font-size:.85em;}
.bubble-ai strong{color:var(--gold);}
.bubble-ai ul,.bubble-ai ol{padding-left:1.2rem;margin:.4rem 0;}
.bubble-ai li{margin:.2rem 0;}
.bubble-u{background:linear-gradient(135deg,var(--blue-primary),var(--blue-glow));color:#fff;}
.input-zone{display:flex;gap:.6rem;align-items:flex-end;flex-shrink:0;}
.ai-input{
  flex:1;background:rgba(255,255,255,.04);
  border:1.5px solid var(--border);border-radius:12px;
  color:var(--text-main);padding:.75rem .9rem;
  font-family:'Exo 2',sans-serif;font-size:.9rem;
  resize:none;min-height:44px;max-height:120px;
  transition:var(--transition);outline:none;
  -webkit-appearance:none;
}
.ai-input:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(255,215,0,.08);}
.send-btn{
  width:44px;height:44px;border-radius:50%;
  background:linear-gradient(135deg,var(--gold-dark),var(--gold));
  border:none;cursor:pointer;font-size:1.1rem;
  display:flex;align-items:center;justify-content:center;
  transition:var(--transition);flex-shrink:0;
}
.send-btn:hover{transform:scale(1.08);}
.send-btn:disabled{opacity:.4;cursor:not-allowed;transform:none;}
.typing-dots{display:flex;gap:.3rem;padding:.4rem .2rem;align-items:center;}
.dot{width:7px;height:7px;border-radius:50%;background:var(--gold);animation:typeDot 1.2s ease infinite;}
.dot:nth-child(2){animation-delay:.2s;}.dot:nth-child(3){animation-delay:.4s;}
@keyframes typeDot{0%,60%,100%{opacity:.3;transform:scale(.8)}30%{opacity:1;transform:scale(1)}}
.sugs{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:.6rem;flex-shrink:0;}
.sug{background:rgba(255,215,0,.07);border:1px solid rgba(255,215,0,.2);color:var(--gold);
  padding:.3rem .75rem;border-radius:50px;font-size:.75rem;cursor:pointer;transition:.2s;
  font-family:'Exo 2',sans-serif;-webkit-tap-highlight-color:transparent;}
.sug:hover,.sug:active{background:rgba(255,215,0,.15);}
.err-banner{background:rgba(255,23,68,.08);border:1px solid rgba(255,23,68,.3);color:var(--danger);
  padding:.6rem 1rem;border-radius:8px;font-size:.82rem;margin-top:.4rem;}
</style>
</head>
<body>
<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <img src="assets/img/logo.svg" alt="BiroTech" style="height:34px;vertical-align:middle;">
  </a>
  <ul class="navbar-links">
    <li><a href="cours.php">Kou yo</a></li>
    <li><a href="dashboard.php">Tablo Bò</a></li>
    <li><a href="logout.php">Dekonekte</a></li>
    <button class="theme-toggle" id="themeToggle">🌙</button>
  </ul>
</nav>

<div class="ai-wrap">
  <div class="ai-header">
    <div style="font-size:2.2rem;margin-bottom:.3rem;">🤖</div>
    <h1 style="font-family:'Cinzel Decorative',serif;font-size:1.3rem;background:linear-gradient(135deg,var(--gold),var(--blue-glow));-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:.2rem;">BiroTech AI</h1>
    <p style="color:var(--text-muted);font-size:.8rem;">Poze kesyon an kreyòl, français oswa anglais</p>
  </div>

  <!-- Suggestions -->
  <div class="sugs" id="sugsZone">
    <button class="sug" onclick="useSug(this)">Comment créer un tableau Word?</button>
    <button class="sug" onclick="useSug(this)">Kisa =SOMME() fè nan Excel?</button>
    <button class="sug" onclick="useSug(this)">Comment exporter en PDF?</button>
    <button class="sug" onclick="useSug(this)">C'est quoi un calque Photoshop?</button>
    <button class="sug" onclick="useSug(this)">Koman pou mete lèt gra nan Word?</button>
  </div>

  <!-- Chat -->
  <div class="chat-box" id="chatBox">
    <div class="msg">
      <div class="av av-ai">🤖</div>
      <div class="bubble bubble-ai">
        Bonjou <strong><?= htmlspecialchars($user['prenom']) ?></strong>! 👋<br><br>
        Mwen se <strong>BiroTech AI</strong>. Mwen ka ede ou ak tout kesyon sou
        <strong>Word, Excel, Publisher</strong> ak <strong>Adobe Photoshop</strong>.<br><br>
        Ekri kesyon ou an <strong>français, kreyòl oswa anglais</strong> — mwen reponn imedyatman! ✨
      </div>
    </div>
  </div>

  <!-- Error zone -->
  <div id="errZone" style="display:none;"></div>

  <!-- Input -->
  <div class="input-zone">
    <textarea class="ai-input" id="inp"
      placeholder="Ekri kesyon ou la a..."
      rows="1"
      onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();send();}"></textarea>
    <button class="send-btn" id="sendBtn" onclick="send()" title="Voye (Enter)">➤</button>
  </div>
  <p style="color:var(--text-muted);font-size:.72rem;margin-top:.4rem;text-align:center;">Enter = voye · Shift+Enter = nouvo liy</p>
</div>

<script src="js/main.js"></script>
<script>
const chatBox = document.getElementById('chatBox');
const inp     = document.getElementById('inp');
const sendBtn = document.getElementById('sendBtn');
const errZone = document.getElementById('errZone');
let history   = [];
let busy      = false;

function addMsg(role, html) {
  const wrap = document.createElement('div');
  wrap.className = 'msg' + (role==='user' ? ' user' : '');
  const av = document.createElement('div');
  av.className = 'av ' + (role==='user' ? 'av-u' : 'av-ai');
  av.textContent = role==='user' ? '<?= strtoupper(substr($user['prenom'],0,1)) ?>' : '🤖';
  const bub = document.createElement('div');
  bub.className = 'bubble ' + (role==='user' ? 'bubble-u' : 'bubble-ai');
  bub.innerHTML = html;
  wrap.appendChild(av); wrap.appendChild(bub);
  chatBox.appendChild(wrap);
  chatBox.scrollTop = chatBox.scrollHeight;
  return bub;
}

function showTyping() {
  const d = document.createElement('div');
  d.className = 'msg'; d.id = 'typing';
  d.innerHTML = '<div class="av av-ai">🤖</div><div class="bubble bubble-ai"><div class="typing-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div></div>';
  chatBox.appendChild(d);
  chatBox.scrollTop = chatBox.scrollHeight;
}

function hideTyping() {
  const t = document.getElementById('typing');
  if(t) t.remove();
}

function formatReply(text) {
  return text
    .replace(/```[\w]*\n?([\s\S]*?)```/g, '<pre style="background:rgba(0,176,255,.08);padding:.7rem;border-radius:8px;overflow-x:auto;margin:.4rem 0;font-size:.82em;"><code style="color:var(--blue-glow);">$1</code></pre>')
    .replace(/`([^`\n]+)`/g, '<code>$1</code>')
    .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
    .replace(/\*([^*\n]+)\*/g, '<em>$1</em>')
    .replace(/^### (.+)$/gm, '<h4 style="color:var(--blue-light);margin:.5rem 0 .3rem;font-size:.9rem;">$1</h4>')
    .replace(/^## (.+)$/gm, '<h3 style="color:var(--gold);margin:.5rem 0 .3rem;">$1</h3>')
    .replace(/^[-•] (.+)$/gm, '<li>$1</li>')
    .replace(/(<li>[\s\S]*?<\/li>)/g, '<ul style="padding-left:1.1rem;margin:.3rem 0;">$1</ul>')
    .replace(/\n\n/g, '<br><br>')
    .replace(/\n/g, '<br>');
}

async function send() {
  const text = inp.value.trim();
  if(!text || busy) return;

  document.getElementById('sugsZone').style.display = 'none';
  errZone.style.display = 'none';
  addMsg('user', text.replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>'));
  history.push({role:'user', content: text});
  inp.value = ''; inp.style.height = 'auto';
  busy = true; sendBtn.disabled = true;
  showTyping();

  try {
    const res = await fetch('ai_proxy.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({messages: history})
    });

    const data = await res.json();
    hideTyping();

    if(data.error) {
      errZone.innerHTML = '<div class="err-banner">⚠️ ' + data.error + '</div>';
      errZone.style.display = 'block';
      history.pop(); // retire mesaj ki echwe
    } else if(data.reply) {
      history.push({role:'assistant', content: data.reply});
      addMsg('assistant', formatReply(data.reply));
    }
  } catch(e) {
    hideTyping();
    errZone.innerHTML = '<div class="err-banner">❌ Pa ka joinn AI a. Verifye koneksyon entènèt ou epi eseye ankò.</div>';
    errZone.style.display = 'block';
    history.pop();
  }

  busy = false; sendBtn.disabled = false;
  inp.focus();
}

function useSug(btn) {
  inp.value = btn.textContent;
  send();
}

// Auto-resize
inp.addEventListener('input', function() {
  this.style.height = 'auto';
  this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});
</script>
</body>
</html>
