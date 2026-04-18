<?php
require_once 'includes/config.php';
if (!isLoggedIn()) { setFlash('warning', '⚠️ Konekte ou premye.'); redirect('login.php'); }
$db   = getDB();
$user = getCurrentUser();
$uid  = (int)$user['id'];

$flashPay = $flashMsg = null;

// Depo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_paiement'])) {
    $note = trim($_POST['note'] ?? '');
    if (empty($note)) {
        $flashPay = ['type'=>'danger','msg'=>'❌ Ou dwe ekri yon ti mesaj.'];
    } else {
        $db->query("INSERT INTO paiements (user_id,montant,methode,note) VALUES ($uid,1500,'NatCash','".escape($note)."')");
        $flashPay = ['type'=>'success','msg'=>'✅ Depo anrejistre! Derinard Ritchy ap verifye epi ba ou aksè trèvit.'];
    }
}

// Mesaj — netwaye champ apre voye
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_message'])) {
    $contenu = trim($_POST['contenu'] ?? '');
    if (empty($contenu)) {
        $flashMsg = ['type'=>'danger','msg'=>'❌ Mesaj vid.'];
    } else {
        $db->query("INSERT INTO messages_prives (user_id,contenu) VALUES ($uid,'".escape($contenu)."')");
        // Netwaye: redirect pou evite double-submit epi efase champ nan
        setFlash('success', '✅ Mesaj voye bay Derinard Ritchy! Li ap reponn ou trèvit.');
        redirect('dashboard.php#message');
    }
}

$flash = getFlash();
$totalCours = (int)$db->query("SELECT COUNT(*) as n FROM cours")->fetch_assoc()['n'];
$termine    = (int)$db->query("SELECT COUNT(*) as n FROM progression WHERE user_id=$uid AND statut='termine'")->fetch_assoc()['n'];
$enCours    = (int)$db->query("SELECT COUNT(*) as n FROM progression WHERE user_id=$uid AND statut='en_cours'")->fetch_assoc()['n'];
$pct        = $totalCours > 0 ? round($termine/$totalCours*100) : 0;
$paiement   = $db->query("SELECT * FROM paiements WHERE user_id=$uid ORDER BY date_depot DESC LIMIT 1")->fetch_assoc();
$msgs       = [];
$mr = $db->query("SELECT * FROM messages_prives WHERE user_id=$uid ORDER BY date_envoi DESC LIMIT 10");
while ($m = $mr->fetch_assoc()) $msgs[] = $m;
$cats = $db->query("SELECT * FROM categories ORDER BY ordre");
?>
<!DOCTYPE html>
<html lang="ht" data-theme="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Tablo Bò — BiroTech</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <img src="assets/img/logo.svg" alt="BiroTech" style="height:34px;vertical-align:middle;">
  </a>
  <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
  <ul class="navbar-links" id="navLinks">
    <li><a href="cours.php">Kou yo</a></li>
    <li><a href="dashboard.php" style="color:var(--gold)">Tablo Bò</a></li>
    <li><a href="ai_assistant.php">🤖 AI</a></li>
    <li><a href="coupon.php">🎟️</a></li>
    <li><a href="logout.php">Dekonekte</a></li>
    <button class="theme-toggle" id="themeToggle">🌙</button>
  </ul>
</nav>

<div style="padding-top:68px;max-width:1000px;margin:0 auto;padding-left:1.5rem;padding-right:1.5rem;padding-bottom:4rem;">

  <div style="margin:2rem 0 1.5rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
    <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,var(--blue-primary),var(--gold));display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:900;color:#000;flex-shrink:0;"><?= strtoupper(substr($user['prenom'],0,1)) ?></div>
    <div>
      <h1 style="font-family:'Cinzel Decorative',serif;font-size:1.3rem;">Bonjou, <?= htmlspecialchars($user['prenom'].' '.$user['nom']) ?>!</h1>
      <p style="color:var(--text-muted);font-size:.88rem;"><?= $user['acces_complet'] ? '<span style="color:var(--success)">✅ Aksè Konplè — Tout kou yo disponib!</span>' : '<span style="color:var(--warning)">⏳ Aksè Gratis sèlman.</span>' ?></p>
    </div>
  </div>

  <!-- Flash global -->
  <?php if($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><?= $flash['msg'] ?></div><?php endif; ?>

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat-card"><div class="stat-card-icon">📚</div><div class="stat-card-num"><?= $termine ?></div><div class="stat-card-label">Kou Fini</div></div>
    <div class="stat-card"><div class="stat-card-icon">⏳</div><div class="stat-card-num"><?= $enCours ?></div><div class="stat-card-label">An Kou</div></div>
    <div class="stat-card"><div class="stat-card-icon">📊</div><div class="stat-card-num"><?= $pct ?>%</div><div class="stat-card-label">Pwogresyon</div></div>
    <div class="stat-card"><div class="stat-card-icon"><?= $user['acces_complet']?'✅':'🔐' ?></div><div class="stat-card-num" style="font-size:1.1rem;"><?= $user['acces_complet']?'Total':'Gratis' ?></div><div class="stat-card-label">Nivo Aksè</div></div>
  </div>

  <!-- Progresyon -->
  <div class="card mb-3">
    <h3 style="color:var(--gold);margin-bottom:1.2rem;">📈 Pwogresyon pa Matyè</h3>
    <?php $cats->data_seek(0); while ($cat = $cats->fetch_assoc()):
      $cT = (int)$db->query("SELECT COUNT(*) as n FROM cours WHERE categorie_id={$cat['id']}")->fetch_assoc()['n'];
      $cF = (int)$db->query("SELECT COUNT(*) as n FROM progression p JOIN cours c ON p.cours_id=c.id WHERE p.user_id=$uid AND c.categorie_id={$cat['id']} AND p.statut='termine'")->fetch_assoc()['n'];
      $cp = $cT > 0 ? round($cF/$cT*100) : 0;
    ?>
    <div style="margin-bottom:1rem;">
      <div style="display:flex;justify-content:space-between;margin-bottom:.4rem;"><span><?= $cat['logo'] ?> <strong><?= htmlspecialchars($cat['nom']) ?></strong></span><span style="color:var(--gold);font-weight:700;"><?= $cF ?>/<?= $cT ?></span></div>
      <div class="progress-wrap"><div class="progress-bar" style="width:<?= $cp ?>%"></div></div>
      <?php if($cF >= $cT && $cT > 0): ?>
      <div style="margin-top:.4rem;"><a href="certificat.php?categorie=<?= $cat['slug'] ?>" style="color:var(--gold);font-size:.78rem;text-decoration:none;">🏆 Telechaje Sètifika →</a></div>
      <?php endif; ?>
    </div>
    <?php endwhile; ?>
    <div style="display:flex;gap:.8rem;flex-wrap:wrap;margin-top:1rem;">
      <a href="cours.php" class="btn btn-blue btn-sm">📚 Kou yo</a>
      <a href="ai_assistant.php" class="btn btn-outline btn-sm">🤖 AI</a>
    </div>
  </div>

  <!-- Paiement -->
  <div class="card mb-3" id="paiement">
    <h3 style="color:var(--gold);margin-bottom:1rem;">💰 Depo NatCash — 1,500 Goud</h3>
    <?php if($user['acces_complet']): ?>
      <div class="alert alert-success">✅ Ou gen aksè konplè! Mèsi.</div>
    <?php else: ?>
      <?php if($flashPay): ?><div class="alert alert-<?= $flashPay['type'] ?>"><?= $flashPay['msg'] ?></div><?php endif; ?>
      <?php if($paiement): ?>
      <div class="alert alert-<?= $paiement['statut']==='en_attente'?'warning':($paiement['statut']==='confirme'?'success':'danger') ?>" style="margin-bottom:1rem;">
        <?= $paiement['statut']==='en_attente'?'⏳ Depo an atant...':($paiement['statut']==='confirme'?'✅ Depo konfime!':'❌ Depo rejte.') ?>
      </div>
      <?php endif; ?>
      <div style="background:rgba(255,215,0,.05);border:1px solid rgba(255,215,0,.2);border-radius:var(--radius);padding:1.2rem;margin-bottom:1.2rem;">
        <h4 style="color:var(--gold);margin-bottom:.8rem;">📱 Kijan fè depo</h4>
        <ol style="color:var(--text-muted);padding-left:1.5rem;line-height:2.2;">
          <li>Ouvri <strong style="color:#fff">NatCash</strong> → "Envoyer de l'argent"</li>
          <li>Nimewo: <strong style="color:var(--gold);font-size:1.1rem;">+509 3000-0000</strong></li>
          <li>Montan: <strong style="color:var(--gold)">1,500 Goud</strong></li>
          <li>Voye depo a, apre sa ranpli fòm anba a</li>
        </ol>
      </div>
      <form method="POST">
        <div class="form-group">
          <label class="form-label">💬 Di nou ou fè depo a *</label>
          <textarea name="note" class="form-control" rows="3" placeholder="Egzanp: Mwen fè yon depo 1500 goud NatCash. Mèsi!" required></textarea>
        </div>
        <button type="submit" name="submit_paiement" value="1" class="btn btn-gold">💰 Konfime Depo</button>
      </form>
    <?php endif; ?>
  </div>

  <!-- Mesaj Prive — CHAMP NETWAYE APRè VOYE -->
  <div class="card" id="message">
    <h3 style="color:var(--gold);margin-bottom:.5rem;">✉️ Mesaj Prive bay Derinard Ritchy</h3>
    <p style="color:var(--text-muted);font-size:.82rem;margin-bottom:1.2rem;">
      🔒 <strong>100% Prive</strong> — Sèl ou ak Derinard Ritchy ka wè mesaj sa yo. Lòt moun pa wè yo.
    </p>

    <?php if($flashMsg): ?><div class="alert alert-<?= $flashMsg['type'] ?>"><?= $flashMsg['msg'] ?></div><?php endif; ?>

    <!-- Fòm — champ vid toujou (pa gen value=) -->
    <form method="POST" style="margin-bottom:1.5rem;" id="msgForm">
      <div class="form-group">
        <label class="form-label">💬 Ekri mesaj ou *</label>
        <textarea name="contenu" class="form-control" rows="4"
          placeholder="Ekri sa ou vle di a... (depo, pwoblèm aksè, kesyon, etc.)"
          id="msgTextarea" required></textarea>
      </div>
      <button type="submit" name="submit_message" value="1" class="btn btn-blue">📤 Voye Mesaj Prive</button>
    </form>

    <!-- Istwa mesaj -->
    <?php if (!empty($msgs)): ?>
    <h4 style="color:var(--text-muted);font-size:.8rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:.8rem;">📬 Istwa Mesaj yo</h4>
    <div style="display:grid;gap:.7rem;">
      <?php foreach($msgs as $m): ?>
      <div style="padding:.9rem;background:rgba(255,255,255,.02);border:1px solid var(--border);border-radius:var(--radius-sm);">
        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:.4rem;margin-bottom:.5rem;">
          <span class="badge badge-blue">Ou</span>
          <span style="color:var(--text-muted);font-size:.75rem;"><?= date('d/m/Y H:i',strtotime($m['date_envoi'])) ?></span>
        </div>
        <p style="color:var(--text-muted);font-size:.88rem;margin-bottom:.4rem;"><?= nl2br(htmlspecialchars($m['contenu'])) ?></p>
        <?php if($m['reponse']): ?>
        <div style="margin-top:.6rem;padding:.7rem;background:rgba(0,176,255,.05);border-left:3px solid var(--blue-glow);border-radius:0 6px 6px 0;">
          <span style="color:var(--blue-glow);font-size:.75rem;font-weight:700;">💬 Derinard Ritchy — <?= date('d/m/Y H:i',strtotime($m['date_reponse'])) ?></span>
          <p style="color:var(--text-main);font-size:.88rem;margin-top:.3rem;"><?= nl2br(htmlspecialchars($m['reponse'])) ?></p>
        </div>
        <?php else: ?>
        <span style="color:var(--text-muted);font-size:.75rem;">⏳ An atant repons...</span>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

</div>
<script src="js/main.js"></script>
</body>
</html>
