<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="ht" data-theme="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>À Propos — BiroTech</title>
<link rel="stylesheet" href="css/style.css">
<style>
.about-hero{padding:7rem 2rem 4rem;text-align:center;position:relative;overflow:hidden;}
.about-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(21,101,192,.25),transparent 70%);}
.creator-img{width:180px;height:180px;border-radius:50%;border:4px solid var(--gold);box-shadow:0 0 40px rgba(255,215,0,.3);margin:0 auto 1.5rem;display:block;object-fit:cover;animation:float 4s ease infinite;}
.skill-bar{background:rgba(255,255,255,.08);border-radius:50px;height:8px;overflow:hidden;margin-top:.5rem;}
.skill-fill{height:100%;border-radius:50px;background:linear-gradient(90deg,var(--blue-primary),var(--gold));transition:width 1.5s ease;}
.timeline{position:relative;padding-left:2rem;}
.timeline::before{content:'';position:absolute;left:.5rem;top:0;bottom:0;width:2px;background:linear-gradient(180deg,var(--gold),var(--blue-primary));}
.tl-item{position:relative;margin-bottom:2rem;}
.tl-dot{position:absolute;left:-1.7rem;top:.3rem;width:12px;height:12px;border-radius:50%;background:var(--gold);box-shadow:0 0 10px rgba(255,215,0,.5);}
.value-card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem;text-align:center;transition:var(--transition);}
.value-card:hover{transform:translateY(-4px);border-color:var(--gold);}
.value-icon{font-size:2.5rem;margin-bottom:.8rem;}
</style>
</head>
<body>
<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <img src="assets/img/logo.svg" alt="BiroTech" style="height:38px;vertical-align:middle;">
  </a>
  <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
  <ul class="navbar-links" id="navLinks">
    <li><a href="index.php">Akèy</a></li>
    <li><a href="cours.php">Kou yo</a></li>
    <li><a href="apropos.php" style="color:var(--gold)">À Propos</a></li>
    <?php if(isLoggedIn()): ?>
      <li><a href="dashboard.php">Tablo Bò</a></li>
      <li><a href="ai_assistant.php">🤖 AI</a></li>
      <li><a href="logout.php">Dekonekte</a></li>
    <?php else: ?>
      <li><a href="login.php">Konekte</a></li>
      <li><a href="register.php">Enskri</a></li>
    <?php endif; ?>
    <?php if(isAdmin()):?><li><a href="admin/index.php" style="color:var(--gold)">⚙️ Admin</a></li><?php endif;?>
    <button class="theme-toggle" id="themeToggle">🌙</button>
  </ul>
</nav>

<!-- HERO -->
<div class="about-hero">
  <img src="assets/img/creator.svg" alt="Derinard Ritchy" class="creator-img">
  <div style="position:relative;z-index:1;">
    <span style="display:inline-block;background:rgba(255,215,0,.1);border:1px solid rgba(255,215,0,.3);color:var(--gold);padding:.4rem 1.2rem;border-radius:50px;font-size:.85rem;font-weight:700;letter-spacing:1px;margin-bottom:1rem;">👨‍💻 Kreateur BiroTech</span>
    <h1 style="font-family:'Cinzel Decorative',serif;font-size:clamp(1.8rem,4vw,3rem);background:linear-gradient(135deg,var(--gold),var(--white));-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:.5rem;">Derinard Ritchy</h1>
    <p style="color:var(--blue-light);font-size:1.1rem;font-weight:600;margin-bottom:1rem;">Ingénieur en Sciences Informatiques</p>
    <p style="color:var(--text-muted);max-width:600px;margin:0 auto;line-height:1.8;">Étudiant à l'Université Antenor Firmin (UNAF), Faculté des Sciences Informatiques. Passionné par l'éducation technologique et le développement de solutions innovantes pour Haïti.</p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;margin-top:1.5rem;">
      <a href="cours.php" class="btn btn-gold">🚀 Kòmanse Aprann</a>
      <a href="register.php" class="btn btn-outline">📝 Kreye Kont Gratis</a>
    </div>
  </div>
</div>

<!-- MISSION -->
<section style="background:var(--bg-card);border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
<div class="section" style="padding:3rem 2rem;max-width:900px;margin:0 auto;text-align:center;">
  <h2 style="font-family:'Cinzel Decorative',serif;font-size:1.8rem;background:linear-gradient(135deg,var(--white),var(--gold));-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:1rem;">🎯 Misyon BiroTech</h2>
  <div style="width:80px;height:3px;background:linear-gradient(90deg,transparent,var(--gold),transparent);margin:0 auto 1.5rem;"></div>
  <p style="color:var(--text-muted);font-size:1.05rem;line-height:1.9;max-width:700px;margin:0 auto;">
    BiroTech se yon platfòm aprantisaj enfòmatik ki kreye espesyalman pou <strong style="color:var(--white)">pèp ayisyen an</strong>. Objektif nou se bay chak moun opòtinite pou aprann <strong style="color:var(--gold)">enfòmatik biwotik</strong> nan yon langaj ke li konprann — kreyòl ak français — avèk kou ki pratik, egzamen reyèl, ak sipò dirèk.
  </p>
  <p style="color:var(--text-muted);font-size:1rem;line-height:1.9;max-width:700px;margin:1rem auto 0;">
    Mwen kwè ke <strong style="color:var(--white)">teknoloji dwe aksesib pou tout moun</strong>, kit ou nan kapital la oswa nan pwovens yo. Avèk sèlman <strong style="color:var(--gold)">1,500 Goud</strong>, ou jwenn aksè pèmanan ak tout kou yo.
  </p>
</div>
</section>

<!-- COMPÉTENCES -->
<div class="section" style="max-width:900px;margin:0 auto;padding:4rem 2rem;">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:start;flex-wrap:wrap;">
    <div>
      <h2 style="font-family:'Cinzel Decorative',serif;font-size:1.4rem;color:var(--gold);margin-bottom:1.5rem;">⚡ Konpetans Teknik</h2>
      <?php
      $skills = [
        ['PHP / MySQL', 92], ['HTML / CSS / JavaScript', 90],
        ['Java (Console & GUI)', 85], ['C# Windows Forms', 80],
        ['Flutter / Dart', 75], ['PostgreSQL / pgAdmin', 88],
        ['Microsoft Office Suite', 95], ['Adobe Photoshop', 78],
      ];
      foreach($skills as $s): ?>
      <div style="margin-bottom:1rem;">
        <div style="display:flex;justify-content:space-between;margin-bottom:.4rem;">
          <span style="font-weight:600;font-size:.9rem;"><?= $s[0] ?></span>
          <span style="color:var(--gold);font-weight:700;font-size:.85rem;"><?= $s[1] ?>%</span>
        </div>
        <div class="skill-bar"><div class="skill-fill" style="width:<?= $s[1] ?>%"></div></div>
      </div>
      <?php endforeach; ?>
    </div>
    <div>
      <h2 style="font-family:'Cinzel Decorative',serif;font-size:1.4rem;color:var(--gold);margin-bottom:1.5rem;">🏆 Parcours</h2>
      <div class="timeline">
        <?php $events = [
          ['2024-2025', 'Inivèsite Antenor Firmin (UNAF)', 'Etidyan Injeniyeri Syans Enfòmatik — Semès 2'],
          ['2024', 'BiroTech v1.0', 'Kreye platfòm aprantisaj enfòmatik biwotik an kreyòl'],
          ['2024', 'Pwojè MiniBank Java', 'Aplikasyon console jesyon kont bank — UNAF'],
          ['2023', 'Pwojè School Management', 'Sistèm jesyon lekòl konplè PHP/MySQL'],
          ['2023', 'Pwojè MicroCrédit C#', 'Sistèm mikrokrèdi Windows Forms .NET'],
          ['2022', 'Kòmansman Pwogramason', 'Premye pa nan HTML, CSS ak Python'],
        ];
        foreach($events as $e): ?>
        <div class="tl-item">
          <div class="tl-dot"></div>
          <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:1rem;">
            <span style="color:var(--gold);font-size:.78rem;font-weight:700;"><?= $e[0] ?></span>
            <h4 style="color:var(--white);margin:.2rem 0 .3rem;font-size:.95rem;"><?= $e[1] ?></h4>
            <p style="color:var(--text-muted);font-size:.85rem;"><?= $e[2] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- VALEURS -->
<div style="background:var(--bg-card);border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
<div class="section" style="max-width:1100px;margin:0 auto;padding:4rem 2rem;">
  <div style="text-align:center;margin-bottom:2.5rem;">
    <h2 style="font-family:'Cinzel Decorative',serif;font-size:1.6rem;background:linear-gradient(135deg,var(--white),var(--gold));-webkit-background-clip:text;-webkit-text-fill-color:transparent;">💎 Valè BiroTech</h2>
    <div style="width:80px;height:3px;background:linear-gradient(90deg,transparent,var(--gold),transparent);margin:1rem auto;"></div>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem;">
    <?php $values = [
      ['🎓','Kalite','Kou yo detaye, egzamen reyèl, sètifika pwofesyonèl pou chak matyè.'],
      ['🌍','Aksesibilite','Ansèyman an kreyòl ak français pou tout ayisyen jwenn aksè.'],
      ['💰','Abòdab','1,500 Goud sèlman pou aksè pèmanan — pa gen abònman mensyèl.'],
      ['🤝','Sipò','Derinard Ritchy disponib pou reponn tout kesyon via mesaj prive.'],
      ['🚀','Inovasyon','AI intègre pou ede elèv yo jwenn repons rapid ak egzat.'],
      ['📜','Sètifika','Chak elèv ki pase egzamen an resevwa yon sètifika ofisyèl PDF.'],
    ];
    foreach($values as $v): ?>
    <div class="value-card">
      <div class="value-icon"><?= $v[0] ?></div>
      <h3 style="font-family:'Cinzel Decorative',serif;font-size:1rem;color:var(--gold);margin-bottom:.5rem;"><?= $v[1] ?></h3>
      <p style="color:var(--text-muted);font-size:.85rem;line-height:1.6;"><?= $v[2] ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</div>

<!-- STATS -->
<div class="section" style="max-width:900px;margin:0 auto;padding:4rem 2rem;text-align:center;">
  <h2 style="font-family:'Cinzel Decorative',serif;font-size:1.6rem;background:linear-gradient(135deg,var(--white),var(--gold));-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:2rem;">📊 BiroTech an Chif</h2>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1.5rem;">
    <?php
    $db = getDB();
    $nbUsers = $db->query("SELECT COUNT(*) as n FROM users WHERE role='user'")->fetch_assoc()['n'];
    $nbCours = $db->query("SELECT COUNT(*) as n FROM cours")->fetch_assoc()['n'];
    $stats = [
      ['🎓', $nbUsers, 'Elèv Enskriye'],
      ['📚', $nbCours.'+', 'Kou Disponib'],
      ['📝', '40+', 'Egzamen'],
      ['🏆', '4', 'Matyè'],
      ['🤖', '1', 'AI Intègre'],
      ['💰', '1,500G', 'Prix Aksè'],
    ];
    foreach($stats as $s): ?>
    <div style="padding:1.5rem;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);">
      <div style="font-size:2rem;"><?= $s[0] ?></div>
      <div style="font-family:'Cinzel Decorative',serif;font-size:1.8rem;background:linear-gradient(135deg,var(--gold),var(--blue-glow));-webkit-background-clip:text;-webkit-text-fill-color:transparent;"><?= $s[1] ?></div>
      <div style="color:var(--text-muted);font-size:.8rem;"><?= $s[2] ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <div style="margin-top:2.5rem;">
    <a href="register.php" class="btn btn-gold" style="font-size:1.1rem;padding:1rem 2.5rem;">🚀 Rejwenn BiroTech Gratis</a>
  </div>
</div>

<footer style="background:var(--bg-card);border-top:1px solid var(--border);padding:2rem;text-align:center;color:var(--text-muted);font-size:.85rem;">
  <div style="font-family:'Cinzel Decorative',serif;background:linear-gradient(135deg,var(--gold),var(--blue-glow));-webkit-background-clip:text;-webkit-text-fill-color:transparent;font-size:1.1rem;margin-bottom:.5rem;">BiroTech</div>
  <p>✨ Kreye ak ❤️ pa <strong style="color:var(--gold)">Ingénieur Derinard Ritchy</strong> · Tout dwa rezève <?= date('Y') ?></p>
</footer>

<script src="js/main.js"></script>
</body>
</html>
