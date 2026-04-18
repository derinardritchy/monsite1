<?php
require_once 'includes/config.php';
$db = getDB();
$user = getCurrentUser();

$q = trim($_GET['q'] ?? '');
$results = [];

if($q) {
    $qE = escape($q);
    $sql = "SELECT c.*, cat.nom as cat_nom, cat.slug, cat.logo, cat.type as cat_type
            FROM cours c JOIN categories cat ON c.categorie_id=cat.id
            WHERE c.titre LIKE '%$qE%' OR c.titre_en LIKE '%$qE%' OR c.contenu LIKE '%$qE%'
            ORDER BY c.est_gratuit DESC, cat.ordre, c.numero_cours LIMIT 30";
    $r = $db->query($sql);
    while($row = $r->fetch_assoc()) $results[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ht" data-theme="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Rechèch — BiroTech</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <img src="assets/img/logo.svg" alt="BiroTech" style="height:36px;vertical-align:middle;">
  </a>
  <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
  <ul class="navbar-links" id="navLinks">
    <li><a href="cours.php">Kou yo</a></li>
    <li><a href="apropos.php">À Propos</a></li>
    <?php if(isLoggedIn()):?><li><a href="dashboard.php">Tablo Bò</a></li><li><a href="ai_assistant.php">🤖 AI</a></li><li><a href="logout.php">Dekonekte</a></li><?php else:?><li><a href="login.php">Konekte</a></li><li><a href="register.php">Enskri</a></li><?php endif;?>
    <?php if(isAdmin()):?><li><a href="admin/index.php" style="color:var(--gold)">⚙️ Admin</a></li><?php endif;?>
    <button class="theme-toggle" id="themeToggle">🌙</button>
  </ul>
</nav>

<div style="padding-top:68px;max-width:900px;margin:0 auto;padding-left:1.5rem;padding-right:1.5rem;padding-bottom:4rem;">
  <div style="padding:2rem 0 1.5rem;">
    <h1 style="font-family:'Cinzel Decorative',serif;font-size:1.6rem;color:var(--gold);margin-bottom:1.5rem;">🔍 Rechèch Kou</h1>

    <!-- Search form -->
    <form method="GET" action="recherche.php">
      <div style="display:flex;gap:.7rem;">
        <input type="text" name="q" class="form-control" placeholder="Chèche yon kou... (Word, Excel, formule, tableau...)"
          value="<?= htmlspecialchars($q) ?>" autofocus style="flex:1;">
        <button type="submit" class="btn btn-gold" style="flex-shrink:0;">🔍 Chèche</button>
      </div>
    </form>

    <!-- Quick suggestions -->
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:1rem;">
      <?php foreach(['tableau','formule','image','mise en page','Excel','calque','marge','PDF'] as $sug): ?>
      <a href="?q=<?= urlencode($sug) ?>" style="background:rgba(255,215,0,.08);border:1px solid rgba(255,215,0,.2);color:var(--gold);padding:.3rem .8rem;border-radius:50px;font-size:.8rem;text-decoration:none;transition:var(--transition);"><?= $sug ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Results -->
  <?php if($q): ?>
  <div style="margin-bottom:1rem;">
    <?php if(empty($results)): ?>
      <div class="alert alert-info">
        😕 Pa gen rezilta pou "<strong><?= htmlspecialchars($q) ?></strong>". Eseye yon lòt mo.
      </div>
    <?php else: ?>
      <p style="color:var(--text-muted);font-size:.9rem;margin-bottom:1.5rem;">
        <strong style="color:var(--gold)"><?= count($results) ?></strong> rezilta jwenn pou "<strong style="color:var(--white)"><?= htmlspecialchars($q) ?></strong>"
      </p>
      <div class="cours-grid">
        <?php foreach($results as $c):
          $canAccess = $c['est_gratuit'] || ($user && ($user['acces_complet'] || $user['role']==='admin'));
          // Highlight search term in title
          $titleHL = preg_replace('/('.preg_quote($q,'/').')/i', '<mark style="background:rgba(255,215,0,.2);color:var(--gold);border-radius:3px;padding:0 2px;">$1</mark>', htmlspecialchars($c['titre']));
        ?>
        <a href="<?= $canAccess || $c['est_gratuit'] ? 'cours_view.php?id='.$c['id'] : (isLoggedIn()?'dashboard.php#paiement':'register.php') ?>" class="cours-item" style="text-decoration:none;">
          <div class="cours-num <?= $c['est_gratuit']?'gratuit':'cn-blue' ?>"><?= $c['numero_cours'] ?></div>
          <div class="cours-info">
            <div class="cours-titre"><?= $titleHL ?></div>
            <div class="cours-meta"><?= $c['logo'] ?> <?= htmlspecialchars($c['cat_nom']) ?> · <?= $c['cat_type']==='video'?'🎬 Vidéo':'📖 Lekti' ?></div>
          </div>
          <span class="cours-status <?= $c['est_gratuit']?'status-gratuit':'status-paye' ?>">
            <?= $c['est_gratuit']?'✨ Gratis':'💎 Premium' ?>
          </span>
        </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  <?php else: ?>
  <div style="text-align:center;padding:3rem;color:var(--text-muted);">
    <div style="font-size:4rem;margin-bottom:1rem;">🔍</div>
    <p>Tape yon mo pou chèche nan tout kou yo...</p>
  </div>
  <?php endif; ?>
</div>

<script src="js/main.js"></script>
</body>
</html>
