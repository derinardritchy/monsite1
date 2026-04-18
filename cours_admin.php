<?php
require_once '../includes/config.php';
if(!isAdmin()) redirect('../login.php');
$db = getDB();

$flash = getFlash();
$cats = $db->query("SELECT * FROM categories ORDER BY ordre");
$selCat = (int)($_GET['cat'] ?? 0);
$coursList = $selCat ? $db->query("SELECT c.*, cat.nom as cat_nom FROM cours c JOIN categories cat ON c.categorie_id=cat.id WHERE c.categorie_id=$selCat ORDER BY c.ordre, c.numero_cours") : null;
?>
<!DOCTYPE html>
<html lang="ht" data-theme="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Kou yo — Admin BiroTech</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="../index.php" class="navbar-brand">BiroTech</a>
  <ul class="navbar-links"><li><a href="../logout.php">Dekonekte</a></li><button class="theme-toggle" id="themeToggle">🌙</button></ul>
</nav>
<div class="admin-sidebar">
  <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border);margin-bottom:0.5rem;">
    <div style="font-family:'Cinzel Decorative',serif;color:var(--gold);font-size:1rem;">⚙️ Admin Panel</div>
  </div>
  <a href="index.php" class="sidebar-link"><span class="sidebar-icon">📊</span> Tablo Bò</a>
  <a href="users.php" class="sidebar-link"><span class="sidebar-icon">👥</span> Itilizatè yo</a>
  <a href="paiements.php" class="sidebar-link"><span class="sidebar-icon">💰</span> Depo / Paiemen</a>
  <a href="messages.php" class="sidebar-link"><span class="sidebar-icon">✉️</span> Mesaj Prive</a>
  <a href="cours_admin.php" class="sidebar-link active"><span class="sidebar-icon">📚</span> Jere Kou yo</a>
  <a href="../index.php" class="sidebar-link"><span class="sidebar-icon">🌐</span> Wè Sit la</a>
</div>
<div class="admin-main">
  <h2 style="font-family:'Cinzel Decorative',serif;color:var(--gold);margin-bottom:1.5rem;">📚 Jere Kou yo</h2>
  <?php if($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><?= $flash['msg'] ?></div><?php endif; ?>

  <!-- Category filter -->
  <div style="display:flex;gap:0.7rem;flex-wrap:wrap;margin-bottom:2rem;">
    <a href="cours_admin.php" class="btn btn-sm <?= !$selCat ? 'btn-gold' : 'btn-outline' ?>">Tout</a>
    <?php $cats->data_seek(0); while($c = $cats->fetch_assoc()): ?>
    <a href="?cat=<?= $c['id'] ?>" class="btn btn-sm <?= $selCat==$c['id'] ? 'btn-gold' : 'btn-outline' ?>"><?= $c['logo'] ?> <?= $c['nom'] ?></a>
    <?php endwhile; ?>
  </div>

  <?php if($coursList): ?>
  <div class="card" style="overflow-x:auto;">
    <table class="admin-table">
      <tr><th>#</th><th>Titre</th><th>Matyè</th><th>Gratis?</th><th>Tip</th></tr>
      <?php while($c = $coursList->fetch_assoc()): ?>
      <tr>
        <td style="color:var(--text-muted)"><?= $c['numero_cours'] ?></td>
        <td><strong><?= htmlspecialchars($c['titre']) ?></strong></td>
        <td><?= htmlspecialchars($c['cat_nom']) ?></td>
        <td><span class="badge <?= $c['est_gratuit'] ? 'badge-green' : 'badge-red' ?>"><?= $c['est_gratuit'] ? '✨ Gratis' : '🔐 Peyan' ?></span></td>
        <td><?= $c['video_url'] ? '🎬 Vidéo' : '📖 Tèks' ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>
  <?php else: ?>
  <div class="alert alert-info">Chwazi yon matyè anwo pou wè kou yo.</div>
  <?php endif; ?>

  <div class="alert alert-info" style="margin-top:1.5rem;">
    💡 <strong>Pou ajoute kou:</strong> Edite fichye <code>database.sql</code> ou fè INSERT dirèkteman nan baz done ou a.
    Fonksyonalite CRUD konplè disponib nan vèsyon pwochèn.
  </div>
</div>
<script src="../js/main.js"></script>
</body></html>
