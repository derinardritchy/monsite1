<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="ht" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BiroTech — Aprann Enfòmatik Biwotik</title>
<link rel="stylesheet" href="style.css">
<style>
/* Admin secret button - discret nan footer */
.admin-secret {
  display: inline-block;
  color: transparent;
  font-size: .5px;
  user-select: none;
  cursor: default;
  position: relative;
}
.admin-secret::after {
  content: '⚙';
  color: rgba(255,255,255,.08);
  font-size: 14px;
  cursor: pointer;
  transition: .3s;
}
.admin-secret:hover::after { color: var(--gold); }
</style>
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <img src="assets/img/logo.svg" alt="BiroTech" style="height:38px;vertical-align:middle;">
  </a>
  <form action="recherche.php" method="GET" style="flex:1;max-width:320px;margin:0 1.2rem;display:flex;gap:.3rem;" id="searchDesktop">
    <input type="text" name="q" placeholder="🔍 Chèche yon kou..." class="form-control" style="height:36px;font-size:.83rem;">
    <button type="submit" class="btn btn-gold btn-sm" style="height:36px;flex-shrink:0;">OK</button>
  </form>
  <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
  <ul class="navbar-links" id="navLinks">
    <li><a href="cours.php">Kou yo</a></li>
    <li><a href="apropos.php">À Propos</a></li>
    <?php if(isLoggedIn()): ?>
      <li><a href="dashboard.php">Tablo Bò</a></li>
      <li><a href="ai_assistant.php">🤖 AI</a></li>
      <li><a href="coupon.php">🎟️</a></li>
      <li><a href="logout.php">Dekonekte</a></li>
      <?php if(isAdmin()): ?>
      <li><a href="admin/index.php" style="color:var(--gold);font-weight:700;">⚙️ Admin</a></li>
      <?php endif; ?>
    <?php else: ?>
      <li><a href="login.php">Konekte</a></li>
      <li><a href="register.php" style="color:var(--gold)">Enskri</a></li>
    <?php endif; ?>
    <button class="theme-toggle" id="themeToggle">🌙</button>
  </ul>
</nav>

<!-- HERO -->
<section class="hero" id="home">
  <div class="hero-bg"></div>
  <canvas id="particles"></canvas>
  <div class="hero-content">
    <div class="hero-badge">🎓 Platfòm #1 Aprantisaj Enfòmatik an Kreyòl</div>
    <h1 class="hero-title">Aprann<br><span class="highlight">Enfòmatik Biwotik</span><br>ak Konfyans</h1>
    <p class="hero-subtitle">Kou pwofesyonèl nan <strong>Word, Excel, Publisher</strong> ak <strong>Adobe Photoshop</strong> — ann Kreyòl Ayisyen</p>
    <p class="hero-creator">✨ Konsepsyon pa <span>Ingénieur Derinard Ritchy</span> — pou ede ou avanse</p>
    <form action="recherche.php" method="GET" style="display:flex;gap:.5rem;max-width:500px;margin:0 auto 1.5rem;">
      <input type="text" name="q" placeholder="🔍 Chèche yon kou..." class="form-control" style="height:44px;">
      <button type="submit" class="btn btn-blue" style="height:44px;flex-shrink:0;">Chèche</button>
    </form>
    <div class="hero-btns">
      <a href="cours.php" class="btn btn-gold" id="startBtn">🚀 Kòmanse Gratis</a>
      <a href="apropos.php" class="btn btn-outline">👤 À Propos</a>
    </div>
    <div class="hero-stats">
      <div class="stat-item"><div class="stat-num" data-target="135">0</div><div class="stat-label">Kou Total</div></div>
      <div class="stat-item"><div class="stat-num" data-target="4">0</div><div class="stat-label">Matyè</div></div>
      <div class="stat-item"><div class="stat-num" data-target="1500">0</div><div class="stat-label">Goud Sèlman</div></div>
      <div class="stat-item"><div class="stat-num">∞</div><div class="stat-label">Aksè Pèmanan</div></div>
    </div>
  </div>
</section>

<!-- MATIERES -->
<section class="section" id="matieres">
  <div class="section-header">
    <h2 class="section-title">📚 Matyè yo</h2>
    <div class="gold-line"></div>
    <p class="section-subtitle">1er kou chak matyè GRATIS!</p>
  </div>
  <div class="matieres-grid">
    <?php $db=getDB(); $cats=$db->query("SELECT * FROM categories ORDER BY ordre"); while($cat=$cats->fetch_assoc()): ?>
    <a href="cours.php?categorie=<?=$cat['slug']?>" class="matiere-card">
      <span class="matiere-logo"><?=$cat['logo']?></span>
      <div class="matiere-name"><?=htmlspecialchars($cat['nom'])?></div>
      <div class="matiere-desc"><?=htmlspecialchars($cat['description'])?></div>
      <span class="matiere-badge"><?=$cat['total_cours']?>+ Kou</span>
      <?php if($cat['type']==='video'):?><br><span class="badge badge-red" style="margin-top:.4rem;display:inline-block;">🎬 Vidéo</span><?php endif;?>
    </a>
    <?php endwhile; ?>
  </div>
</section>

<!-- FEATURES -->
<div style="background:var(--bg-card);border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
<div class="section" style="padding:4rem 2rem;">
  <div class="section-header"><h2 class="section-title">⚡ Fonksyonalite</h2><div class="gold-line"></div></div>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.2rem;">
    <?php foreach([
      ['🤖','AI Assistant','Poze kesyon an kreyòl, français oswa anglais','ai_assistant.php'],
      ['🏆','Sètifika PDF','Pase egzamen, telechaje sètifika ofisyèl','cours.php'],
      ['🔍','Rechèch Kou','Jwenn yon kou rapid nan tout matyè yo','recherche.php'],
      ['🎟️','Kòd Koupon','Gen yon koupon? Antre li pou aksè gratis','coupon.php'],
    ] as $f): ?>
    <div class="card" style="text-align:center;">
      <div style="font-size:2.2rem;margin-bottom:.7rem;"><?=$f[0]?></div>
      <h3 style="font-family:'Cinzel Decorative',serif;color:var(--gold);font-size:.95rem;margin-bottom:.4rem;"><?=$f[1]?></h3>
      <p style="color:var(--text-muted);font-size:.82rem;margin-bottom:.9rem;"><?=$f[2]?></p>
      <a href="<?=isLoggedIn()?$f[3]:'register.php'?>" class="btn btn-outline btn-sm"><?=$f[0]?></a>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</div>

<!-- CREATOR -->
<section class="section" style="max-width:900px;margin:0 auto;padding:4rem 2rem;">
  <div style="display:grid;grid-template-columns:auto 1fr;gap:2.5rem;align-items:center;flex-wrap:wrap;">
    <div style="text-align:center;">
      <img src="assets/img/creator.svg" alt="Derinard Ritchy" style="width:160px;height:160px;border-radius:50%;border:3px solid var(--gold);box-shadow:0 0 40px rgba(255,215,0,.3);animation:float 4s ease infinite;">
      <div style="margin-top:.8rem;font-family:'Cinzel Decorative',serif;color:var(--gold);font-size:.85rem;">Ingénieur</div>
      <div style="font-weight:700;color:var(--white);font-size:.95rem;">Derinard Ritchy</div>
    </div>
    <div>
      <h2 style="font-family:'Cinzel Decorative',serif;font-size:1.6rem;background:linear-gradient(135deg,var(--white),var(--gold));-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:1rem;">👨‍💻 Kreateur BiroTech</h2>
      <p style="color:var(--text-muted);line-height:1.9;margin-bottom:1.5rem;">Etidyan Injeniyeri Syans Enfòmatik nan <strong style="color:var(--white)">Université Antenor Firmin (UNAF)</strong>. BiroTech kreye pou rann aprantisaj enfòmatik aksesib pou tout ayisyen ak sèlman <strong style="color:var(--gold)">1,500 Goud</strong> pou aksè pèmanan.</p>
      <a href="apropos.php" class="btn btn-gold">👤 Aprann Pi Plis</a>
    </div>
  </div>
</section>

<!-- PRICING -->
<div style="background:var(--bg-card);border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
<section class="section" style="max-width:560px;margin:0 auto;padding:4rem 2rem;">
  <div class="section-header"><h2 class="section-title">💎 Pri Aksè</h2><div class="gold-line"></div></div>
  <div class="card" style="text-align:center;border-color:var(--gold);box-shadow:var(--shadow-gold);padding:2.5rem;">
    <div style="font-family:'Cinzel Decorative',serif;font-size:3rem;color:var(--gold);">1,500</div>
    <div style="color:var(--text-muted);margin-bottom:1.5rem;">Goud — Yon sèl fwa pou tout</div>
    <ul style="list-style:none;text-align:left;margin-bottom:2rem;display:grid;gap:.6rem;font-size:.92rem;">
      <li>✅ Tout <strong>135+ kou</strong> nèt</li>
      <li>✅ Word, Excel, Publisher + Photoshop</li>
      <li>✅ Egzamen + <strong>Sètifika PDF</strong></li>
      <li>✅ <strong>🤖 AI Assistant</strong> intègre</li>
      <li>✅ Aksè <strong>pèmanan</strong></li>
    </ul>
    <div style="display:flex;gap:.8rem;flex-wrap:wrap;justify-content:center;">
      <?php if(isLoggedIn()):?>
        <a href="dashboard.php#paiement" class="btn btn-gold">💰 Fè Depo</a>
        <a href="coupon.php" class="btn btn-outline">🎟️ Koupon</a>
      <?php else:?>
        <a href="register.php" class="btn btn-gold">🚀 Kòmanse Gratis</a>
      <?php endif;?>
    </div>
  </div>
</section>
</div>

<!-- FOOTER -->
<footer>
  <div style="max-width:600px;margin:0 auto;">
    <img src="assets/img/logo.svg" alt="BiroTech" style="height:38px;margin-bottom:.8rem;">
    <p>Platfòm Aprantisaj Enfòmatik Biwotik ann Kreyòl</p>
    <p style="margin-top:.5rem;">✨ Kreye pa <strong style="color:var(--gold)">Ingénieur Derinard Ritchy</strong> · <?=date('Y')?></p>
    <div style="display:flex;gap:1.5rem;justify-content:center;margin-top:1rem;flex-wrap:wrap;">
      <a href="cours.php" style="color:var(--text-muted);text-decoration:none;font-size:.83rem;">Kou yo</a>
      <a href="apropos.php" style="color:var(--text-muted);text-decoration:none;font-size:.83rem;">À Propos</a>
      <a href="recherche.php" style="color:var(--text-muted);text-decoration:none;font-size:.83rem;">Rechèch</a>
      <a href="coupon.php" style="color:var(--text-muted);text-decoration:none;font-size:.83rem;">Koupon</a>
      <?php if(isLoggedIn()):?><a href="ai_assistant.php" style="color:var(--text-muted);text-decoration:none;font-size:.83rem;">🤖 AI</a><?php endif;?>
      <!-- Bouton Admin sekrè — sèl admin ka wè li (discret) -->
      <a href="admin/login_admin.php" class="admin-secret" title="Admin" style="color:rgba(255,255,255,.15);text-decoration:none;font-size:.83rem;">⚙</a>
    </div>
  </div>
</footer>

<script src="js/main.js"></script>
<script src="js/particles.js"></script>
</body>
</html>
