<?php
require_once '../includes/config.php';
if(!isAdmin()) redirect('../login.php');
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
if(!$id) redirect('users.php');

$u = $db->query("SELECT * FROM users WHERE id=$id AND role='user' LIMIT 1")->fetch_assoc();
if(!$u) redirect('users.php');

$flash = getFlash();

// Stats
$totalCours = $db->query("SELECT COUNT(*) as n FROM cours")->fetch_assoc()['n'];
$termU = $db->query("SELECT COUNT(*) as n FROM progression WHERE user_id=$id AND statut='termine'")->fetch_assoc()['n'];
$echoueU = $db->query("SELECT COUNT(*) as n FROM progression WHERE user_id=$id AND statut='echoue'")->fetch_assoc()['n'];
$progPct = $totalCours>0?round($termU/$totalCours*100):0;

// Progression detail par categorie
$cats = $db->query("SELECT * FROM categories ORDER BY ordre");

// Payments
$payments = $db->query("SELECT * FROM paiements WHERE user_id=$id ORDER BY date_depot DESC");

// Messages
$messages = $db->query("SELECT * FROM messages_prives WHERE user_id=$id ORDER BY date_envoi DESC");
?>
<!DOCTYPE html>
<html lang="ht" data-theme="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Detay Kont — Admin BiroTech</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="../index.php" class="navbar-brand"><img src="../assets/img/logo.svg" alt="BiroTech" style="height:34px;vertical-align:middle;"></a>
  <ul class="navbar-links">
    <li><a href="../logout.php">Dekonekte</a></li>
    <button class="theme-toggle" id="themeToggle">🌙</button>
  </ul>
</nav>
<div class="admin-sidebar">
  <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border);margin-bottom:.5rem;">
    <div style="font-family:'Cinzel Decorative',serif;color:var(--gold);font-size:.95rem;">⚙️ Admin Panel</div>
  </div>
  <a href="index.php" class="sidebar-link"><span class="sidebar-icon">📊</span> Tablo Bò</a>
  <a href="users.php" class="sidebar-link active"><span class="sidebar-icon">👥</span> Itilizatè yo</a>
  <a href="paiements.php" class="sidebar-link"><span class="sidebar-icon">💰</span> Depo / Paiemen</a>
  <a href="messages.php" class="sidebar-link"><span class="sidebar-icon">✉️</span> Mesaj Prive</a>
  <a href="cours_admin.php" class="sidebar-link"><span class="sidebar-icon">📚</span> Jere Kou yo</a>
  <a href="../index.php" class="sidebar-link"><span class="sidebar-icon">🌐</span> Wè Sit la</a>
</div>
<div class="admin-main">

  <div style="margin-bottom:1.5rem;">
    <a href="users.php" style="color:var(--text-muted);text-decoration:none;font-size:.9rem;">← Tounen nan lis itilizatè yo</a>
  </div>

  <?php if($flash):?><div class="alert alert-<?= $flash['type'] ?>"><?= $flash['msg'] ?></div><?php endif;?>

  <!-- Profile header -->
  <div class="card" style="margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;">
      <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--blue-primary),var(--gold));display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:900;color:#000;flex-shrink:0;">
        <?= strtoupper(substr($u['prenom'],0,1)) ?>
      </div>
      <div style="flex:1;">
        <h2 style="font-family:'Cinzel Decorative',serif;font-size:1.3rem;color:var(--white);"><?= htmlspecialchars($u['prenom'].' '.$u['nom']) ?></h2>
        <p style="color:var(--text-muted);font-size:.9rem;">📧 <?= htmlspecialchars($u['email']) ?> · 📱 <?= htmlspecialchars($u['telephone']) ?></p>
        <p style="color:var(--text-muted);font-size:.85rem;">📅 Enskripsyon: <?= date('d/m/Y à H:i', strtotime($u['date_inscription'])) ?></p>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.5rem;">
          <span class="badge <?= $u['acces_complet']?'badge-green':'badge-red' ?>"><?= $u['acces_complet']?'✅ Aksè Konplè':'🔐 San Aksè' ?></span>
          <span class="badge <?= $u['statut']==='actif'?'badge-blue':($u['statut']==='bloque'?'badge-red':'badge-gold') ?>"><?= $u['statut'] ?></span>
        </div>
      </div>

      <!-- BIG ACTION BUTTONS -->
      <div style="display:flex;flex-direction:column;gap:.7rem;min-width:160px;">
        <?php if(!$u['acces_complet']): ?>
        <a href="users.php?action=acces&id=<?= $id ?>"
           style="display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.8rem 1.5rem;background:linear-gradient(135deg,#1B5E20,var(--success));color:#fff;border-radius:50px;font-weight:700;text-decoration:none;font-size:.9rem;box-shadow:0 4px 20px rgba(0,230,118,.3);transition:.2s;"
           onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'"
           onclick="return confirm('✅ Bay aksè konplè pou <?= htmlspecialchars($u['prenom'].' '.$u['nom']) ?>?')">
          ✅ Bay Aksè Konplè
        </a>
        <?php else: ?>
        <a href="users.php?action=retire_acces&id=<?= $id ?>"
           style="display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.8rem 1.5rem;background:linear-gradient(135deg,#B71C1C,var(--danger));color:#fff;border-radius:50px;font-weight:700;text-decoration:none;font-size:.9rem;transition:.2s;"
           onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'"
           onclick="return confirm('❌ Retire aksè pou <?= htmlspecialchars($u['prenom']) ?>?')">
          ❌ Retire Aksè
        </a>
        <?php endif; ?>
        <?php if($u['statut']!=='bloque'): ?>
        <a href="users.php?action=bloke&id=<?= $id ?>"
           style="display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.6rem 1.2rem;background:rgba(255,145,0,.1);border:1.5px solid rgba(255,145,0,.4);color:var(--warning);border-radius:50px;font-weight:700;text-decoration:none;font-size:.85rem;transition:.2s;"
           onclick="return confirm('🚫 Bloke kont <?= htmlspecialchars($u['prenom']) ?>?')">
          🚫 Bloke Kont
        </a>
        <?php else: ?>
        <a href="users.php?action=debloke&id=<?= $id ?>"
           style="display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.6rem 1.2rem;background:rgba(21,101,192,.15);border:1.5px solid rgba(21,101,192,.4);color:var(--blue-light);border-radius:50px;font-weight:700;text-decoration:none;font-size:.85rem;transition:.2s;"
           onclick="return confirm('🔓 Debloke kont <?= htmlspecialchars($u['prenom']) ?>?')">
          🔓 Debloke Kont
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Stats row -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <div class="stat-card"><div class="stat-card-icon">✅</div><div class="stat-card-num"><?= $termU ?></div><div class="stat-card-label">Kou Fini</div></div>
    <div class="stat-card"><div class="stat-card-icon">❌</div><div class="stat-card-num"><?= $echoueU ?></div><div class="stat-card-label">Egzamen Echwe</div></div>
    <div class="stat-card"><div class="stat-card-icon">📊</div><div class="stat-card-num"><?= $progPct ?>%</div><div class="stat-card-label">Pwogresyon</div></div>
    <div class="stat-card"><div class="stat-card-icon">💰</div><div class="stat-card-num"><?= $db->query("SELECT COUNT(*) as n FROM paiements WHERE user_id=$id")->fetch_assoc()['n'] ?></div><div class="stat-card-label">Depo</div></div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;flex-wrap:wrap;">

    <!-- Progression par categorie -->
    <div class="card">
      <h3 style="color:var(--gold);margin-bottom:1.2rem;">📈 Pwogresyon pa Matyè</h3>
      <?php $cats->data_seek(0); while($cat=$cats->fetch_assoc()):
        $catTotal=$db->query("SELECT COUNT(*) as n FROM cours WHERE categorie_id={$cat['id']}")->fetch_assoc()['n'];
        $catFini=$db->query("SELECT COUNT(*) as n FROM progression p JOIN cours c ON p.cours_id=c.id WHERE p.user_id=$id AND c.categorie_id={$cat['id']} AND p.statut='termine'")->fetch_assoc()['n'];
        $catPct=$catTotal>0?round($catFini/$catTotal*100):0;
        // Certificate available?
        $canCert = $catFini >= $catTotal && $catTotal > 0;
      ?>
      <div style="margin-bottom:1rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem;">
          <span style="font-size:.9rem;"><?= $cat['logo'] ?> <strong><?= htmlspecialchars($cat['nom']) ?></strong></span>
          <div style="display:flex;align-items:center;gap:.5rem;">
            <span style="color:var(--gold);font-weight:700;font-size:.85rem;"><?= $catFini ?>/<?= $catTotal ?></span>
            <?php if($canCert): ?><span class="badge badge-green" style="font-size:.65rem;">🏆 Sètifika</span><?php endif; ?>
          </div>
        </div>
        <div class="progress-wrap"><div class="progress-bar" style="width:<?= $catPct ?>%"></div></div>
      </div>
      <?php endwhile; ?>
    </div>

    <!-- Payment history -->
    <div class="card">
      <h3 style="color:var(--gold);margin-bottom:1.2rem;">💰 Istwa Depo yo</h3>
      <?php if($payments->num_rows === 0): ?>
        <p style="color:var(--text-muted);">Pa gen depo anrejistre.</p>
      <?php else: ?>
      <div style="display:grid;gap:.7rem;">
        <?php while($p=$payments->fetch_assoc()): ?>
        <div style="padding:.8rem;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;">
          <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:.3rem;">
            <span style="font-weight:700;color:var(--gold);"><?= number_format($p['montant'],0) ?> G</span>
            <span class="badge <?= $p['statut']==='confirme'?'badge-green':($p['statut']==='en_attente'?'badge-gold':'badge-red') ?>"><?= $p['statut'] ?></span>
          </div>
          <?php if($p['note']): ?><p style="font-size:.8rem;color:var(--text-muted);margin:.3rem 0;"><?= htmlspecialchars(substr($p['note'],0,80)) ?></p><?php endif; ?>
          <span style="font-size:.75rem;color:var(--text-muted);"><?= date('d/m/Y H:i',strtotime($p['date_depot'])) ?></span>
          <?php if($p['statut']==='en_attente'): ?>
          <div style="margin-top:.5rem;display:flex;gap:.4rem;">
            <a href="../admin/paiements.php?action=confirme&id=<?= $p['id'] ?>&uid=<?= $id ?>" class="btn btn-sm" style="background:var(--success);color:#000;padding:.3rem .7rem;font-size:.75rem;" onclick="return confirm('Konfime depo a?')">✅ Konfime</a>
            <a href="../admin/paiements.php?action=rejete&id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" style="padding:.3rem .7rem;font-size:.75rem;" onclick="return confirm('Rejte depo a?')">❌</a>
          </div>
          <?php endif; ?>
        </div>
        <?php endwhile; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Messages -->
  <div class="card" style="margin-top:1.5rem;">
    <h3 style="color:var(--gold);margin-bottom:1.2rem;">✉️ Mesaj Prive</h3>
    <?php if($messages->num_rows === 0): ?>
      <p style="color:var(--text-muted);">Pa gen mesaj.</p>
    <?php else: ?>
    <div style="display:grid;gap:.7rem;">
      <?php while($m=$messages->fetch_assoc()): ?>
      <div style="padding:.8rem 1rem;background:rgba(255,255,255,.02);border:1px solid var(--border);border-radius:8px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:.4rem;">
          <span class="badge badge-blue">Itilizatè</span>
          <span style="font-size:.75rem;color:var(--text-muted);"><?= date('d/m/Y H:i',strtotime($m['date_envoi'])) ?></span>
        </div>
        <p style="color:var(--text-muted);font-size:.88rem;"><?= nl2br(htmlspecialchars($m['contenu'])) ?></p>
        <?php if($m['reponse']): ?>
        <div style="margin-top:.5rem;padding:.6rem;background:rgba(0,176,255,.05);border-left:2px solid var(--blue-glow);border-radius:0 6px 6px 0;font-size:.85rem;">
          <span style="color:var(--blue-glow);font-weight:700;">Repons admin:</span> <?= nl2br(htmlspecialchars($m['reponse'])) ?>
        </div>
        <?php endif; ?>
        <?php if(!$m['reponse']): ?>
        <a href="messages.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-blue" style="margin-top:.5rem;">💬 Reponn</a>
        <?php endif; ?>
      </div>
      <?php endwhile; ?>
    </div>
    <?php endif; ?>
  </div>

</div>
<script src="../js/main.js"></script>
</body>
</html>
