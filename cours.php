<?php
require_once 'includes/config.php';
$db = getDB();
$user = getCurrentUser();

// Welcome message
$welcome = isset($_GET['welcome']) && isLoggedIn();

// Filter par categorie
$slug = trim($_GET['categorie'] ?? '');
$catCurrent = null;
if($slug) {
    $slugE = escape($slug);
    $r = $db->query("SELECT * FROM categories WHERE slug='$slugE' LIMIT 1");
    $catCurrent = $r ? $r->fetch_assoc() : null;
}

// Toutes les categories
$cats = $db->query("SELECT * FROM categories ORDER BY ordre");
$categories = [];
while($c = $cats->fetch_assoc()) $categories[] = $c;
?>
<!DOCTYPE html>
<html lang="ht" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kou yo — BiroTech</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="index.php" class="navbar-brand">BiroTech</a>
  <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
  <ul class="navbar-links" id="navLinks">
    <li><a href="index.php">Akèy</a></li>
    <li><a href="cours.php">Kou yo</a></li>
    <?php if(isLoggedIn()): ?>
      <li><a href="dashboard.php">Tablo Bò</a></li>
      <li><a href="logout.php">Dekonekte</a></li>
    <?php else: ?>
      <li><a href="login.php">Konekte</a></li>
      <li><a href="register.php">Enskri</a></li>
    <?php endif; ?>
    <?php if(isAdmin()): ?><li><a href="admin/index.php" style="color:var(--gold)">⚙️ Admin</a></li><?php endif; ?>
    <button class="theme-toggle" id="themeToggle">🌙</button>
  </ul>
</nav>

<div style="padding-top:68px;min-height:100vh;">

<?php if($welcome): ?>
<div style="background:linear-gradient(135deg,rgba(255,215,0,0.1),rgba(21,101,192,0.1));border-bottom:1px solid var(--border);padding:1.5rem 2rem;text-align:center;">
  <h2 style="font-family:'Cinzel Decorative',serif;color:var(--gold);margin-bottom:0.5rem;">🎉 Byenvini sou BiroTech, <?= htmlspecialchars($user['nom'] ?? '') ?>!</h2>
  <p style="color:var(--text-muted);">Kont ou kreye avèk siksè. Eseye 1er kou yo GRATIS — pa bezwen peye pou kòmanse!</p>
</div>
<?php endif; ?>

<div class="section">

  <!-- Header -->
  <div class="section-header">
    <h1 class="section-title">
      <?= $catCurrent ? $catCurrent['logo'].' '.$catCurrent['nom'] : '📚 Tout Kou yo' ?>
    </h1>
    <div class="gold-line"></div>
    <?php if(!isLoggedIn()): ?>
    <div class="alert alert-info" style="max-width:600px;margin:1rem auto 0;">
      💡 <strong>Kreye yon kont gratis</strong> pou swiv kou yo ak pase egzamen yo.
      <a href="register.php" class="btn btn-gold btn-sm" style="margin-left:1rem;">Enskri Gratis</a>
    </div>
    <?php endif; ?>
  </div>

  <!-- Category tabs -->
  <div style="display:flex;gap:0.7rem;flex-wrap:wrap;margin-bottom:2.5rem;justify-content:center;">
    <a href="cours.php" class="btn btn-sm <?= !$catCurrent ? 'btn-gold' : 'btn-outline' ?>">Tout</a>
    <?php foreach($categories as $c): ?>
    <a href="cours.php?categorie=<?= $c['slug'] ?>" class="btn btn-sm <?= ($catCurrent && $catCurrent['id']==$c['id']) ? 'btn-gold' : 'btn-outline' ?>">
      <?= $c['logo'] ?> <?= htmlspecialchars($c['nom']) ?>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Cours par categorie -->
  <?php
  $displayCats = $catCurrent ? [$catCurrent] : $categories;
  foreach($displayCats as $cat):
    $cid = $cat['id'];
    $coursList = $db->query("SELECT * FROM cours WHERE categorie_id=$cid ORDER BY ordre, numero_cours");
    $total = $db->query("SELECT COUNT(*) as n FROM cours WHERE categorie_id=$cid")->fetch_assoc()['n'];
  ?>
  <div style="margin-bottom:3rem;">
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid var(--border);">
      <span style="font-size:2.5rem;"><?= $cat['logo'] ?></span>
      <div>
        <h2 style="font-family:'Cinzel Decorative',serif;font-size:1.4rem;color:var(--gold);"><?= htmlspecialchars($cat['nom']) ?></h2>
        <p style="color:var(--text-muted);font-size:0.85rem;"><?= $cat['description'] ?> · <strong><?= $total ?> kou</strong></p>
      </div>
      <?php if($cat['type']==='video'): ?>
        <span class="badge badge-red" style="margin-left:auto;">🎬 Vidéo</span>
      <?php endif; ?>
    </div>

    <div class="cours-grid">
    <?php while($c = $coursList->fetch_assoc()):
      // Check progression si connecte
      $prog = null;
      $canAccess = $c['est_gratuit'];
      if($user) {
        $uid = $user['id'];
        $cid2 = $c['id'];
        $pr = $db->query("SELECT * FROM progression WHERE user_id=$uid AND cours_id=$cid2 LIMIT 1");
        $prog = $pr ? $pr->fetch_assoc() : null;
        if($user['acces_complet'] || $user['role']==='admin') $canAccess = true;
      }
      // Check si bloque (cours precedent pas termine)
      $prevBlocked = false;
      if($c['numero_cours'] > 1 && $user && !$user['acces_complet']) {
        $prevNum = $c['numero_cours'] - 1;
        $prevCours = $db->query("SELECT id FROM cours WHERE categorie_id={$cat['id']} AND numero_cours=$prevNum LIMIT 1")->fetch_assoc();
        if($prevCours) {
          $prevProg = $db->query("SELECT statut FROM progression WHERE user_id={$user['id']} AND cours_id={$prevCours['id']} LIMIT 1")->fetch_assoc();
          if(!$prevProg || $prevProg['statut'] !== 'termine') $prevBlocked = true;
        }
      }
    ?>
    <div class="cours-item" style="<?= $prevBlocked ? 'opacity:0.6;' : '' ?>"
         onclick="handleCours(<?= $c['id'] ?>, <?= $c['est_gratuit'] ?>, <?= $canAccess ? 1 : 0 ?>, <?= $prevBlocked ? 1 : 0 ?>, '<?= addslashes($c['titre']) ?>')">
      <div class="cours-num <?= $c['est_gratuit'] ? 'gratuit' : '' ?>"><?= $c['numero_cours'] ?></div>
      <div class="cours-info">
        <div class="cours-titre"><?= htmlspecialchars($c['titre']) ?></div>
        <div class="cours-meta">
          <?= $cat['type']==='video' ? '🎬 Vidéo' : '📖 Lekti' ?>
          <?php if($prog): ?>
            · <span style="color:<?= $prog['statut']==='termine' ? 'var(--success)' : ($prog['statut']==='echoue' ? 'var(--danger)' : 'var(--warning)') ?>">
              <?= $prog['statut']==='termine' ? '✅ Termine' : ($prog['statut']==='echoue' ? '❌ Echoue' : '⏳ An Kou') ?>
            </span>
          <?php endif; ?>
        </div>
      </div>
      <div>
        <?php if($prevBlocked): ?>
          <span class="cours-status status-bloque">🔒 Bloke</span>
        <?php elseif($c['est_gratuit']): ?>
          <span class="cours-status status-gratuit">✨ Gratis</span>
        <?php elseif($canAccess): ?>
          <span class="cours-status status-paye">🔓 Aksè</span>
        <?php else: ?>
          <span class="cours-status" style="background:rgba(255,23,68,0.1);color:var(--danger);border:1px solid rgba(255,23,68,0.3);">🔐 Peye</span>
        <?php endif; ?>
      </div>
    </div>
    <?php endwhile; ?>
    </div>

    <?php if(!isLoggedIn()): ?>
    <div style="text-align:center;margin-top:1.5rem;padding:1.5rem;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);">
      <p style="color:var(--text-muted);margin-bottom:1rem;">👆 Pou swiv kou yo, ou bezwen kreye yon kont gratis.</p>
      <a href="register.php" class="btn btn-gold">🚀 Kreye Kont Gratis</a>
    </div>
    <?php elseif($user && !$user['acces_complet']): ?>
    <div style="margin-top:1rem;padding:1rem 1.5rem;background:rgba(255,215,0,0.05);border:1px solid rgba(255,215,0,0.2);border-radius:var(--radius);display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
      <span>💎 Ou ka swiv sèlman kou gratis yo. Pou <strong>tout kou yo</strong>, fè yon depo 1500 Goud.</span>
      <a href="dashboard.php#paiement" class="btn btn-gold btn-sm">💰 Fè Depo</a>
    </div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>

</div>
</div>

<!-- Modal: Cours bloke, pa peye -->
<div class="modal-overlay" id="lockModal">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">🔐 Aksè Restriksyon</span>
      <button class="modal-close" onclick="closeModal('lockModal')">✕</button>
    </div>
    <div id="lockModalContent"></div>
  </div>
</div>

<script src="js/main.js"></script>
<script>
function handleCours(id, isGratuit, canAccess, prevBlocked, titre) {
  if(prevBlocked) {
    document.getElementById('lockModalContent').innerHTML = `
      <div style="text-align:center;padding:1rem;">
        <div style="font-size:3rem;margin-bottom:1rem;">🔒</div>
        <h3 style="color:var(--warning);margin-bottom:0.8rem;">Kou a Bloke</h3>
        <p style="color:var(--text-muted);">Ou dwe <strong style="color:var(--white)">fin kou ki anvan an</strong> epi pase egzamen li <strong style="color:var(--gold)">avèk omwen 70/100</strong> anvan ou ka kontinye.</p>
      </div>`;
    openModal('lockModal');
    return;
  }
  if(!canAccess && !isGratuit) {
    <?php if(!isLoggedIn()): ?>
    document.getElementById('lockModalContent').innerHTML = `
      <div style="text-align:center;padding:1rem;">
        <div style="font-size:3rem;margin-bottom:1rem;">🎓</div>
        <h3 style="color:var(--gold);margin-bottom:0.8rem;">Kreye yon Kont</h3>
        <p style="color:var(--text-muted);margin-bottom:1.5rem;">Ou bezwen yon kont pou swiv kou yo.</p>
        <a href="register.php" class="btn btn-gold">Enskri Gratis</a>
      </div>`;
    <?php else: ?>
    document.getElementById('lockModalContent').innerHTML = `
      <div style="text-align:center;padding:1rem;">
        <div style="font-size:3rem;margin-bottom:1rem;">💰</div>
        <h3 style="color:var(--gold);margin-bottom:0.8rem;">Kou Peyan</h3>
        <p style="color:var(--text-muted);margin-bottom:0.5rem;">Kou sa a se yon kou peyan.</p>
        <p style="color:var(--text-muted);margin-bottom:1.5rem;">Pou jwenn aksè, <strong style="color:var(--white)">ekri Derinard Ritchy</strong> ak yon mesaj epi fè yon depo <strong style="color:var(--gold)">1500 Goud</strong> via NatCash. Depi li konfime depo ou, lap ba ou aksè pou tout kou yo.</p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
          <a href="dashboard.php#message" class="btn btn-blue">✉️ Ekri Mesaj</a>
          <a href="dashboard.php#paiement" class="btn btn-gold">💰 Fè Depo</a>
        </div>
      </div>`;
    <?php endif; ?>
    openModal('lockModal');
    return;
  }
  // Aksè OK — ale nan kou a
  window.location.href = 'cours_view.php?id=' + id;
}

function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
document.getElementById('lockModal').addEventListener('click', function(e) {
  if(e.target === this) closeModal('lockModal');
});
</script>
</body>
</html>
