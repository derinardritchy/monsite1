<?php
require_once '../includes/config.php';
if (!isAdmin()) redirect('../login.php');
$db = getDB();

// Reply to message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    $mid = (int)$_POST['msg_id'];
    $rep = escape(trim($_POST['reponse'] ?? ''));
    if ($mid && $rep) {
        $db->query("UPDATE messages_prives SET lu=1, reponse='$rep', date_reponse=NOW() WHERE id=$mid");
        setFlash('success', '✅ Repons voye!');
    }
    redirect('messages.php?id='.$mid);
}

// Quick access action from messages page
$qa  = $_GET['qa'] ?? '';
$qau = (int)($_GET['u'] ?? 0);
if ($qau && in_array($qa, ['bay','retire'])) {
    $chk = $db->query("SELECT role FROM users WHERE id=$qau LIMIT 1")->fetch_assoc();
    if ($chk && $chk['role'] === 'user') {
        if ($qa === 'bay')    $db->query("UPDATE users SET acces_complet=1 WHERE id=$qau");
        if ($qa === 'retire') $db->query("UPDATE users SET acces_complet=0 WHERE id=$qau");
        setFlash($qa==='bay'?'success':'warning', $qa==='bay'?'✅ Aksè bay!':'⚠️ Aksè retire.');
    }
    redirect('messages.php');
}

$flash   = getFlash();
$viewId  = (int)($_GET['id'] ?? 0);
$viewMsg = null;

if ($viewId) {
    $db->query("UPDATE messages_prives SET lu=1 WHERE id=$viewId");
    $r = $db->query("SELECT m.*, u.nom, u.prenom, u.email, u.telephone, u.acces_complet, u.statut, u.id as uid FROM messages_prives m JOIN users u ON m.user_id=u.id WHERE m.id=$viewId LIMIT 1");
    $viewMsg = ($r && $r->num_rows > 0) ? $r->fetch_assoc() : null;
}

$msgs = $db->query("SELECT m.*, u.nom, u.prenom, u.acces_complet FROM messages_prives m JOIN users u ON m.user_id=u.id ORDER BY m.lu ASC, m.date_envoi DESC LIMIT 40");
$nonLus = (int)$db->query("SELECT COUNT(*) as n FROM messages_prives WHERE lu=0")->fetch_assoc()['n'];
?>
<!DOCTYPE html>
<html lang="ht" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mesaj Prive — Admin BiroTech</title>
<link rel="stylesheet" href="../css/style.css">
<style>
.msg-list { display: grid; gap: .6rem; }
.msg-item {
  padding: .85rem 1rem;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 10px;
  cursor: pointer;
  text-decoration: none;
  color: inherit;
  display: block;
  transition: .2s;
  border-left: 3px solid transparent;
}
.msg-item:hover { border-color: rgba(255,215,0,.3); }
.msg-item.unread { border-left-color: var(--gold); background: rgba(255,215,0,.03); }
.msg-item.active { border-left-color: var(--blue-glow); background: rgba(0,176,255,.04); }

.msg-panel {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.5rem;
}

/* Access buttons inside message view */
.qacc-give {
  display: inline-flex; align-items: center; gap: .3rem;
  padding: .5rem 1.1rem; border-radius: 50px;
  background: linear-gradient(135deg, #1B5E20, var(--success));
  color: #fff; font-weight: 700; font-size: .82rem;
  text-decoration: none; transition: .2s;
}
.qacc-give:hover { transform: scale(1.04); }
.qacc-take {
  display: inline-flex; align-items: center; gap: .3rem;
  padding: .5rem 1.1rem; border-radius: 50px;
  background: rgba(255,23,68,.12);
  border: 1.5px solid rgba(255,23,68,.35);
  color: var(--danger); font-weight: 700; font-size: .82rem;
  text-decoration: none; transition: .2s;
}
.qacc-take:hover { background: rgba(255,23,68,.2); }

@media(max-width:700px){
  .msg-layout { flex-direction: column !important; }
  .msg-sidebar { width:100% !important; max-height:220px; overflow-y:auto; }
}
</style>
</head>
<body>
<nav class="navbar">
  <a href="../index.php" class="navbar-brand">
    <img src="../assets/img/logo.svg" alt="BiroTech" style="height:34px;vertical-align:middle;">
  </a>
  <ul class="navbar-links">
    <li><a href="index.php">📊 Dashboard</a></li>
    <li><a href="aksè.php" style="color:var(--gold)">🔑 Aksè</a></li>
    <li><a href="../logout.php" style="color:var(--danger)">Dekonekte</a></li>
    <button class="theme-toggle" id="themeToggle">🌙</button>
  </ul>
</nav>

<div class="admin-sidebar">
  <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border);margin-bottom:.5rem;">
    <div style="font-family:'Cinzel Decorative',serif;color:var(--gold);font-size:.9rem;">⚙️ Admin</div>
  </div>
  <a href="index.php" class="sidebar-link"><span class="sidebar-icon">📊</span> Tablo Bò</a>
  <a href="aksè.php" class="sidebar-link" style="color:var(--gold);font-weight:700;"><span class="sidebar-icon">🔑</span> Jere Aksè</a>
  <a href="paiements.php" class="sidebar-link"><span class="sidebar-icon">💰</span> Depo</a>
  <a href="messages.php" class="sidebar-link active"><span class="sidebar-icon">✉️</span> Mesaj</a>
  <a href="cours_admin.php" class="sidebar-link"><span class="sidebar-icon">📚</span> Kou yo</a>
</div>

<div class="admin-main">
  <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;">
    <h2 style="font-family:'Cinzel Decorative',serif;color:var(--gold);">✉️ Mesaj Prive</h2>
    <?php if($nonLus>0):?>
    <span class="badge badge-gold"><?=$nonLus?> nouvo</span>
    <?php endif;?>
    <a href="aksè.php" class="btn btn-gold btn-sm" style="margin-left:auto;">🔑 Jere Aksè Itilizatè</a>
  </div>

  <?php if($flash):?><div class="alert alert-<?=$flash['type']?>" style="margin-bottom:1rem;"><?=$flash['msg']?></div><?php endif;?>

  <div class="msg-layout" style="display:flex;gap:1.5rem;align-items:flex-start;">

    <!-- List -->
    <div class="msg-sidebar" style="width:320px;flex-shrink:0;">
      <div class="msg-list">
        <?php if ($msgs->num_rows === 0): ?>
          <p style="color:var(--text-muted);text-align:center;padding:2rem;">Pa gen mesaj anko.</p>
        <?php else: while($m=$msgs->fetch_assoc()): ?>
        <a href="messages.php?id=<?=$m['id']?>"
           class="msg-item <?=!$m['lu']?'unread':''?> <?=$viewId==$m['id']?'active':''?>">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.3rem;margin-bottom:.2rem;">
            <strong style="font-size:.88rem;color:<?=!$m['lu']?'var(--gold)':'var(--text-main)'?>;">
              <?=htmlspecialchars($m['prenom'].' '.$m['nom'])?>
            </strong>
            <div style="display:flex;gap:.3rem;align-items:center;flex-shrink:0;">
              <?php if(!$m['lu']):?><span style="width:7px;height:7px;border-radius:50%;background:var(--gold);display:inline-block;"></span><?php endif;?>
              <span class="badge <?=$m['acces_complet']?'badge-green':'badge-red'?>" style="font-size:.65rem;"><?=$m['acces_complet']?'✅':'🔐'?></span>
            </div>
          </div>
          <p style="font-size:.78rem;color:var(--text-muted);overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">
            <?=htmlspecialchars(mb_substr($m['contenu'],0,55))?>...
          </p>
        </a>
        <?php endwhile; endif; ?>
      </div>
    </div>

    <!-- View + Reply -->
    <div style="flex:1;min-width:0;">
      <?php if($viewMsg): ?>
      <div class="msg-panel">

        <!-- User info + ACCESS BUTTONS RIGHT HERE -->
        <div style="background:rgba(255,215,0,.04);border:1px solid rgba(255,215,0,.15);border-radius:var(--radius-sm);padding:1rem;margin-bottom:1.2rem;">
          <div style="display:flex;align-items:center;gap:.8rem;flex-wrap:wrap;">
            <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--blue-primary),var(--gold));display:flex;align-items:center;justify-content:center;font-weight:900;color:#000;flex-shrink:0;">
              <?=strtoupper(substr($viewMsg['prenom'],0,1))?>
            </div>
            <div style="flex:1;min-width:120px;">
              <div style="font-weight:700;"><?=htmlspecialchars($viewMsg['prenom'].' '.$viewMsg['nom'])?></div>
              <div style="font-size:.78rem;color:var(--text-muted);"><?=htmlspecialchars($viewMsg['email'])?> · <?=htmlspecialchars($viewMsg['telephone'])?></div>
            </div>
            <!-- ===== BOUTON AKSÈ DIRÈK ===== -->
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
              <?php if(!$viewMsg['acces_complet']): ?>
              <a href="messages.php?qa=bay&u=<?=$viewMsg['uid']?>&id=<?=$viewMsg['id']?>"
                 class="qacc-give"
                 onclick="return confirm('✅ Bay aksè konplè pou <?=htmlspecialchars(addslashes($viewMsg['prenom'].' '.$viewMsg['nom']))?>?')">
                ✅ Bay Aksè
              </a>
              <?php else: ?>
              <span style="background:rgba(0,230,118,.1);border:1px solid rgba(0,230,118,.3);color:var(--success);padding:.4rem .9rem;border-radius:50px;font-size:.8rem;font-weight:700;">✅ Gen Aksè</span>
              <a href="messages.php?qa=retire&u=<?=$viewMsg['uid']?>&id=<?=$viewMsg['id']?>"
                 class="qacc-take"
                 onclick="return confirm('Retire aksè pou <?=htmlspecialchars(addslashes($viewMsg['prenom']))?>?')">
                ❌ Retire
              </a>
              <?php endif; ?>
              <a href="user_detail.php?id=<?=$viewMsg['uid']?>" style="color:var(--text-muted);font-size:.78rem;text-decoration:none;">👁️ Wè kont</a>
            </div>
          </div>
        </div>

        <!-- Message content -->
        <div style="margin-bottom:1.2rem;padding-bottom:1.2rem;border-bottom:1px solid var(--border);">
          <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:.4rem;margin-bottom:.7rem;">
            <span style="font-weight:700;color:var(--text-main);">Mesaj:</span>
            <span style="color:var(--text-muted);font-size:.78rem;"><?=date('d/m/Y H:i',strtotime($viewMsg['date_envoi']))?></span>
          </div>
          <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:var(--radius-sm);padding:1rem;line-height:1.7;color:var(--text-main);">
            <?=nl2br(htmlspecialchars($viewMsg['contenu']))?>
          </div>
        </div>

        <!-- Previous reply -->
        <?php if($viewMsg['reponse']): ?>
        <div style="margin-bottom:1.2rem;padding-bottom:1.2rem;border-bottom:1px solid var(--border);">
          <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:.4rem;margin-bottom:.7rem;">
            <span style="color:var(--blue-glow);font-weight:700;">Repons ou:</span>
            <span style="color:var(--text-muted);font-size:.78rem;"><?=date('d/m/Y H:i',strtotime($viewMsg['date_reponse']))?></span>
          </div>
          <div style="background:rgba(0,176,255,.05);border-left:3px solid var(--blue-glow);padding:.9rem;border-radius:0 8px 8px 0;line-height:1.7;">
            <?=nl2br(htmlspecialchars($viewMsg['reponse']))?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Reply form -->
        <form method="POST">
          <input type="hidden" name="msg_id" value="<?=$viewMsg['id']?>">
          <div class="form-group">
            <label class="form-label">💬 <?=$viewMsg['reponse']?'Modifye repons ou':'Ekri repons ou'?></label>
            <textarea name="reponse" class="form-control" rows="4"
              placeholder="Ekri repons ou pou <?=htmlspecialchars($viewMsg['prenom'])?>..." required><?=htmlspecialchars($viewMsg['reponse']??'')?></textarea>
          </div>
          <div style="display:flex;gap:.7rem;flex-wrap:wrap;">
            <button type="submit" name="reply" value="1" class="btn btn-gold">📤 Voye Repons</button>
            <?php if(!$viewMsg['acces_complet']): ?>
            <a href="messages.php?qa=bay&u=<?=$viewMsg['uid']?>&id=<?=$viewMsg['id']?>"
               class="btn" style="background:linear-gradient(135deg,#1B5E20,var(--success));color:#fff;"
               onclick="return confirm('Bay aksè epi reponn apre?')">✅ Bay Aksè</a>
            <?php endif; ?>
          </div>
        </form>

      </div>
      <?php else: ?>
      <div style="display:flex;align-items:center;justify-content:center;height:300px;color:var(--text-muted);text-align:center;">
        <div>
          <div style="font-size:3rem;margin-bottom:1rem;">✉️</div>
          <p>Chwazi yon mesaj pou li epi reponn</p>
          <a href="aksè.php" class="btn btn-gold btn-sm" style="margin-top:1rem;">🔑 Jere Aksè Itilizatè</a>
        </div>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script src="../js/main.js"></script>
</body>
</html>
