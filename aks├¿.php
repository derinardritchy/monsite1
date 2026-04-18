<?php
// =====================================================
// GESTION ACCÈS UTILISATEURS — ADMIN SÈLMAN
// URL: localhost/birotech/admin/aksè.php
// =====================================================
require_once '../includes/config.php';

// 🔒 BLOKAJ TOTAL — sèl admin ka antre
if (!isAdmin()) {
    http_response_code(403);
    die('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>403</title>
    <style>body{background:#050510;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;text-align:center;}
    h1{color:#ff4757;font-size:3rem;}a{color:#FFD700;}</style></head>
    <body><div><h1>🚫</h1><h2>Aksè Refize</h2><p>Sèl admin ka wè paj sa a.</p><a href="../login.php">Konekte</a></div></body></html>');
}

$db = getDB();

// ===== ACTIONS AKSÈ =====
$action = $_GET['a'] ?? '';
$uid    = (int)($_GET['u'] ?? 0);

if ($uid > 0 && in_array($action, ['bay','retire','bloke','debloke'])) {
    // Asire pa janm touche kont admin
    $check = $db->query("SELECT role FROM users WHERE id=$uid LIMIT 1")->fetch_assoc();
    if ($check && $check['role'] === 'user') {
        match($action) {
            'bay'     => $db->query("UPDATE users SET acces_complet=1 WHERE id=$uid"),
            'retire'  => $db->query("UPDATE users SET acces_complet=0 WHERE id=$uid"),
            'bloke'   => $db->query("UPDATE users SET statut='bloque' WHERE id=$uid"),
            'debloke' => $db->query("UPDATE users SET statut='actif' WHERE id=$uid"),
        };
        $labels = ['bay'=>'✅ Aksè bay!','retire'=>'⚠️ Aksè retire.','bloke'=>'🚫 Moun bloke.','debloke'=>'✅ Moun debloke.'];
        setFlash(in_array($action,['bay','debloke'])?'success':($action==='retire'?'warning':'danger'), $labels[$action]);
    }
    $q = $_GET['q'] ?? '';
    redirect('aksè.php'.($q?"?q=".urlencode($q):''));
}

// ===== FILTRES & RECHERCHE =====
$search = trim($_GET['q'] ?? '');
$filtre = $_GET['f'] ?? 'tous'; // tous | avec | sans | bloques

$where = "WHERE role='user'";
if ($search) {
    $s = escape($search);
    $where .= " AND (nom LIKE '%$s%' OR prenom LIKE '%$s%' OR email LIKE '%$s%' OR telephone LIKE '%$s%')";
}
if ($filtre === 'avec')   $where .= " AND acces_complet=1 AND statut='actif'";
if ($filtre === 'sans')   $where .= " AND acces_complet=0 AND statut='actif'";
if ($filtre === 'bloques') $where .= " AND statut='bloque'";

$users = $db->query("SELECT * FROM users $where ORDER BY acces_complet ASC, date_inscription DESC");

// Compteurs
$total   = $db->query("SELECT COUNT(*) as n FROM users WHERE role='user'")->fetch_assoc()['n'];
$avec    = $db->query("SELECT COUNT(*) as n FROM users WHERE role='user' AND acces_complet=1")->fetch_assoc()['n'];
$sans    = $db->query("SELECT COUNT(*) as n FROM users WHERE role='user' AND acces_complet=0 AND statut!='bloque'")->fetch_assoc()['n'];
$bloques = $db->query("SELECT COUNT(*) as n FROM users WHERE role='user' AND statut='bloque'")->fetch_assoc()['n'];

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="ht" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>🔐 Jere Aksè Itilizatè — Admin BiroTech</title>
<link rel="stylesheet" href="../css/style.css">
<style>
/* ======= PAGE WRAPPER ======= */
body { overflow-x: hidden; }
.page {
  max-width: 960px;
  margin: 0 auto;
  padding: 80px 1rem 3rem;
  min-height: 100vh;
}

/* ======= PAGE HEADER ======= */
.page-header {
  background: linear-gradient(135deg, rgba(21,101,192,.15), rgba(255,215,0,.08));
  border: 1px solid rgba(255,215,0,.2);
  border-radius: var(--radius);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  text-align: center;
}
.page-title {
  font-family: 'Cinzel Decorative', serif;
  font-size: clamp(1.1rem, 3vw, 1.6rem);
  background: linear-gradient(135deg, var(--gold), var(--white));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-bottom: .4rem;
}
.private-badge {
  display: inline-flex; align-items: center; gap: .4rem;
  background: rgba(255,23,68,.12);
  border: 1px solid rgba(255,23,68,.35);
  color: var(--danger);
  padding: .3rem .9rem;
  border-radius: 50px;
  font-size: .78rem;
  font-weight: 700;
  letter-spacing: .5px;
}

/* ======= COUNTER PILLS ======= */
.counters {
  display: flex; gap: .5rem; flex-wrap: wrap;
  margin-bottom: 1.2rem;
}
.cpill {
  display: flex; align-items: center; gap: .4rem;
  padding: .4rem .9rem; border-radius: 50px;
  font-size: .82rem; font-weight: 700;
  cursor: pointer; text-decoration: none;
  border: 1.5px solid;
  transition: .2s;
}
.cpill:hover { transform: translateY(-1px); }
.cpill.active { box-shadow: 0 0 0 2px rgba(255,215,0,.4); }
.cp-all   { background: rgba(255,255,255,.05); border-color: var(--border); color: var(--text-main); }
.cp-avec  { background: rgba(0,230,118,.08);  border-color: rgba(0,230,118,.3); color: var(--success); }
.cp-sans  { background: rgba(255,23,68,.08);  border-color: rgba(255,23,68,.3); color: var(--danger); }
.cp-blk   { background: rgba(255,145,0,.08);  border-color: rgba(255,145,0,.3); color: var(--warning); }

/* ======= SEARCH ======= */
.search-bar {
  display: flex; gap: .5rem;
  margin-bottom: 1.2rem;
}
.search-bar input {
  flex: 1; height: 44px;
  background: rgba(255,255,255,.04);
  border: 1.5px solid var(--border);
  border-radius: 10px; color: var(--text-main);
  padding: 0 1rem; font-family: 'Exo 2', sans-serif;
  font-size: .9rem; outline: none;
  transition: var(--transition);
  -webkit-appearance: none;
}
.search-bar input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(255,215,0,.08); }
.search-bar button {
  height: 44px; padding: 0 1.2rem;
  background: linear-gradient(135deg, var(--blue-primary), var(--blue-glow));
  border: none; border-radius: 10px;
  color: #fff; font-weight: 700; cursor: pointer;
  font-family: 'Exo 2', sans-serif; font-size: .9rem;
  transition: .2s; flex-shrink: 0;
}
.search-bar button:hover { transform: scale(1.04); }

/* ======= USER CARDS ======= */
.ulist { display: grid; gap: .8rem; }

.ucard {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 14px;
  overflow: hidden;
  transition: var(--transition);
}
.ucard:hover { box-shadow: 0 4px 24px rgba(0,0,0,.45); }

/* Colored left strip */
.ucard::before {
  content: '';
  display: block;
  height: 3px;
  width: 100%;
}
.ucard.has-acces::before { background: linear-gradient(90deg, var(--success), #00BCD4); }
.ucard.no-acces::before  { background: linear-gradient(90deg, var(--danger), var(--warning)); }
.ucard.bloque::before    { background: linear-gradient(90deg, var(--warning), #FF6D00); }

.ucard-body {
  padding: 1rem 1.1rem;
  display: flex; align-items: flex-start; gap: .9rem;
  flex-wrap: wrap;
}

/* Avatar */
.av {
  width: 46px; height: 46px; border-radius: 50%;
  background: linear-gradient(135deg, var(--blue-primary), var(--gold));
  display: flex; align-items: center; justify-content: center;
  font-weight: 900; font-size: 1.1rem; color: #000;
  flex-shrink: 0;
}

/* Info block */
.uinfo { flex: 1; min-width: 160px; }
.uname { font-weight: 700; font-size: .98rem; color: var(--white); margin-bottom: .2rem; }
.umeta { font-size: .78rem; color: var(--text-muted); line-height: 1.7; }
.umeta strong { color: var(--text-main); }

/* Middle: badges + progress */
.umiddle { flex: 1; min-width: 140px; }
.ubadges { display: flex; gap: .3rem; flex-wrap: wrap; margin-bottom: .5rem; }
.uprog-label { font-size: .72rem; color: var(--text-muted); margin-bottom: .25rem; }
.uprog-bar { background: rgba(255,255,255,.08); border-radius: 50px; height: 6px; overflow: hidden; }
.uprog-fill { height: 100%; border-radius: 50px; background: linear-gradient(90deg, var(--blue-primary), var(--gold)); }

/* Last payment */
.upay { font-size: .72rem; margin-top: .4rem; display: flex; align-items: center; gap: .3rem; }

/* ======= ACTION BUTTONS ======= */
.uactions {
  display: flex; flex-direction: column; gap: .4rem;
  flex-shrink: 0; min-width: 130px;
}

.abtn {
  display: flex; align-items: center; justify-content: center; gap: .35rem;
  padding: .6rem .9rem; border-radius: 50px;
  font-size: .8rem; font-weight: 700;
  cursor: pointer; border: none; text-decoration: none;
  font-family: 'Exo 2', sans-serif;
  transition: .2s; white-space: nowrap;
  -webkit-tap-highlight-color: transparent;
}
.abtn:hover { transform: scale(1.04); }
.abtn:active { transform: scale(.97); }

/* BAY AKSÈ — vèt, pwomivan */
.abtn-give {
  background: linear-gradient(135deg, #1B5E20, #2E7D32, var(--success));
  color: #fff;
  box-shadow: 0 3px 14px rgba(0,230,118,.25);
  font-size: .85rem;
  padding: .65rem 1rem;
}
.abtn-give:hover { box-shadow: 0 5px 20px rgba(0,230,118,.45); }

/* RETIRE AKSÈ — wouj */
.abtn-take {
  background: linear-gradient(135deg, #7f0000, #B71C1C);
  color: #fff;
  box-shadow: 0 3px 14px rgba(255,23,68,.2);
}

/* BLOKE */
.abtn-block {
  background: rgba(255,145,0,.1);
  border: 1.5px solid rgba(255,145,0,.4);
  color: var(--warning);
}
/* DEBLOKE */
.abtn-unblock {
  background: rgba(21,101,192,.12);
  border: 1.5px solid rgba(21,101,192,.4);
  color: var(--blue-light);
}
/* DETAY */
.abtn-detail {
  background: rgba(255,255,255,.04);
  border: 1px solid var(--border);
  color: var(--text-muted);
  font-size: .75rem;
}

/* ======= EMPTY STATE ======= */
.empty {
  text-align: center; padding: 3rem 1rem;
  color: var(--text-muted);
}
.empty-icon { font-size: 3rem; margin-bottom: .8rem; }

/* ======= RESPONSIVE ======= */
@media (max-width: 600px) {
  .ucard-body { gap: .7rem; }
  .uactions {
    flex-direction: row; flex-wrap: wrap;
    width: 100%; min-width: unset;
  }
  .abtn { flex: 1; min-width: calc(50% - .2rem); }
  .abtn-detail { flex: 1 1 100%; }
}
</style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar">
  <a href="../index.php" class="navbar-brand" style="display:flex;align-items:center;gap:.5rem;">
    <img src="../assets/img/logo.svg" alt="BiroTech" style="height:34px;">
  </a>
  <ul class="navbar-links">
    <li><a href="index.php">📊 Dashboard</a></li>
    <li><a href="messages.php">✉️ Mesaj</a></li>
    <li><a href="paiements.php">💰 Depo</a></li>
    <li><a href="../logout.php" style="color:var(--danger)">Dekonekte</a></li>
    <button class="theme-toggle" id="themeToggle">🌙</button>
  </ul>
</nav>

<div class="page">

  <!-- ===== PAGE HEADER ===== -->
  <div class="page-header">
    <span class="private-badge">🔒 Paj Prive — Admin Sèlman</span>
    <h1 class="page-title" style="margin-top:.8rem;">👥 Jere Aksè Itilizatè yo</h1>
    <p style="color:var(--text-muted);font-size:.85rem;">
      Sèl ou — <strong style="color:var(--gold)">Derinard Ritchy</strong> — ki ka wè epi modifye paj sa a.
      Bay oswa retire aksè pou chak itilizatè endividyèlman.
    </p>
  </div>

  <!-- ===== FLASH ===== -->
  <?php if($flash): ?>
  <div class="alert alert-<?= $flash['type'] ?>" style="margin-bottom:1rem;">
    <?= $flash['msg'] ?>
  </div>
  <?php endif; ?>

  <!-- ===== COUNTER PILLS ===== -->
  <div class="counters">
    <a href="aksè.php<?= $search?"?q=".urlencode($search):'' ?>"
       class="cpill cp-all <?= $filtre==='tous'?'active':'' ?>">
      👥 Tout <strong style="margin-left:.2rem;"><?= $total ?></strong>
    </a>
    <a href="aksè.php?f=avec<?= $search?"&q=".urlencode($search):'' ?>"
       class="cpill cp-avec <?= $filtre==='avec'?'active':'' ?>">
      ✅ Avèk aksè <strong style="margin-left:.2rem;"><?= $avec ?></strong>
    </a>
    <a href="aksè.php?f=sans<?= $search?"&q=".urlencode($search):'' ?>"
       class="cpill cp-sans <?= $filtre==='sans'?'active':'' ?>">
      🔐 San aksè <strong style="margin-left:.2rem;"><?= $sans ?></strong>
    </a>
    <a href="aksè.php?f=bloques<?= $search?"&q=".urlencode($search):'' ?>"
       class="cpill cp-blk <?= $filtre==='bloques'?'active':'' ?>">
      🚫 Bloke <strong style="margin-left:.2rem;"><?= $bloques ?></strong>
    </a>
  </div>

  <!-- ===== SEARCH ===== -->
  <form method="GET" class="search-bar">
    <?php if($filtre !== 'tous'): ?>
    <input type="hidden" name="f" value="<?= htmlspecialchars($filtre) ?>">
    <?php endif; ?>
    <input type="text" name="q"
      placeholder="🔍 Chèche pa non, imel oswa telefòn..."
      value="<?= htmlspecialchars($search) ?>"
      autocomplete="off">
    <button type="submit">Chèche</button>
    <?php if($search): ?>
    <a href="aksè.php<?= $filtre!=='tous'?"?f=$filtre":'' ?>"
       style="height:44px;padding:0 .9rem;display:flex;align-items:center;background:rgba(255,23,68,.12);border:1.5px solid rgba(255,23,68,.3);border-radius:10px;color:var(--danger);font-weight:700;font-size:.85rem;text-decoration:none;">
      ✕
    </a>
    <?php endif; ?>
  </form>

  <!-- ===== RESULT COUNT ===== -->
  <?php if($search): ?>
  <p style="color:var(--text-muted);font-size:.82rem;margin-bottom:.8rem;">
    <?= $users->num_rows ?> rezilta pou "<strong style="color:var(--white)"><?= htmlspecialchars($search) ?></strong>"
  </p>
  <?php endif; ?>

  <!-- ===== USERS LIST ===== -->
  <?php if($users->num_rows === 0): ?>
  <div class="empty">
    <div class="empty-icon">🔍</div>
    <p>Pa gen itilizatè ki koresponn.</p>
  </div>

  <?php else: ?>
  <div class="ulist">
  <?php while($u = $users->fetch_assoc()):
    $uid2   = (int)$u['id'];
    $totC   = (int)$db->query("SELECT COUNT(*) as n FROM cours")->fetch_assoc()['n'];
    $termU  = (int)$db->query("SELECT COUNT(*) as n FROM progression WHERE user_id=$uid2 AND statut='termine'")->fetch_assoc()['n'];
    $pct    = $totC > 0 ? round($termU / $totC * 100) : 0;
    $lastPR = $db->query("SELECT statut, montant, date_depot FROM paiements WHERE user_id=$uid2 ORDER BY date_depot DESC LIMIT 1");
    $lastP  = ($lastPR && $lastPR->num_rows > 0) ? $lastPR->fetch_assoc() : null;
    $cls    = $u['statut']==='bloque' ? 'bloque' : ($u['acces_complet'] ? 'has-acces' : 'no-acces');
    $qPart  = $search ? '&q='.urlencode($search) : '';
    $fPart  = $filtre !== 'tous' ? '&f='.$filtre : '';
  ?>
  <div class="ucard <?= $cls ?>">
    <div class="ucard-body">

      <!-- Avatar -->
      <div class="av"><?= strtoupper(substr($u['prenom'], 0, 1)) ?></div>

      <!-- Info -->
      <div class="uinfo">
        <div class="uname"><?= htmlspecialchars($u['prenom'].' '.$u['nom']) ?></div>
        <div class="umeta">
          📧 <?= htmlspecialchars($u['email']) ?><br>
          📱 <?= htmlspecialchars($u['telephone']) ?><br>
          📅 Enskri: <strong><?= date('d/m/Y', strtotime($u['date_inscription'])) ?></strong>
        </div>
      </div>

      <!-- Status + Progress -->
      <div class="umiddle">
        <div class="ubadges">
          <span class="badge <?= $u['acces_complet'] ? 'badge-green' : 'badge-red' ?>">
            <?= $u['acces_complet'] ? '✅ Aksè' : '🔐 San aksè' ?>
          </span>
          <span class="badge <?= $u['statut']==='actif'?'badge-blue':($u['statut']==='bloque'?'badge-red':'badge-gold') ?>">
            <?= $u['statut'] ?>
          </span>
        </div>

        <!-- Progress bar -->
        <div class="uprog-label">📚 <?= $termU ?>/<?= $totC ?> kou (<?= $pct ?>%)</div>
        <div class="uprog-bar">
          <div class="uprog-fill" style="width:<?= $pct ?>%"></div>
        </div>

        <!-- Last payment -->
        <?php if($lastP): ?>
        <div class="upay">
          💰 Dènye depo:
          <span class="badge <?= $lastP['statut']==='confirme'?'badge-green':($lastP['statut']==='en_attente'?'badge-gold':'badge-red') ?>" style="font-size:.65rem;">
            <?= $lastP['statut'] ?>
          </span>
          <span style="color:var(--text-muted);">(<?= date('d/m', strtotime($lastP['date_depot'])) ?>)</span>
        </div>
        <?php else: ?>
        <div class="upay" style="color:var(--text-muted);">💰 Pa gen depo</div>
        <?php endif; ?>
      </div>

      <!-- ===== BOUTON AKSÈ ===== -->
      <div class="uactions">

        <?php if(!$u['acces_complet']): ?>
        <!-- ✅ BAY AKSÈ (bouton vèt pwomivan) -->
        <a href="aksè.php?a=bay&u=<?= $u['id'] ?><?= $qPart.$fPart ?>"
           class="abtn abtn-give"
           onclick="return confirm('✅ Bay aksè konplè pou:\n<?= htmlspecialchars(addslashes($u['prenom'].' '.$u['nom'])) ?>\n\nSa ap deblouke tout kou yo imedyatman.')">
          ✅ Bay Aksè
        </a>
        <?php else: ?>
        <!-- ❌ RETIRE AKSÈ -->
        <a href="aksè.php?a=retire&u=<?= $u['id'] ?><?= $qPart.$fPart ?>"
           class="abtn abtn-take"
           onclick="return confirm('❌ Retire aksè pou:\n<?= htmlspecialchars(addslashes($u['prenom'].' '.$u['nom'])) ?>')">
          ❌ Retire Aksè
        </a>
        <?php endif; ?>

        <?php if($u['statut'] !== 'bloque'): ?>
        <!-- 🚫 BLOKE -->
        <a href="aksè.php?a=bloke&u=<?= $u['id'] ?><?= $qPart.$fPart ?>"
           class="abtn abtn-block"
           onclick="return confirm('🚫 Bloke kont:\n<?= htmlspecialchars(addslashes($u['prenom'])) ?>')">
          🚫 Bloke Kont
        </a>
        <?php else: ?>
        <!-- 🔓 DEBLOKE -->
        <a href="aksè.php?a=debloke&u=<?= $u['id'] ?><?= $qPart.$fPart ?>"
           class="abtn abtn-unblock"
           onclick="return confirm('🔓 Debloke kont:\n<?= htmlspecialchars(addslashes($u['prenom'])) ?>')">
          🔓 Debloke
        </a>
        <?php endif; ?>

        <!-- 👁️ WÈ DETAY -->
        <a href="user_detail.php?id=<?= $u['id'] ?>" class="abtn abtn-detail">
          👁️ Wè Detay Kont
        </a>

      </div>
    </div>
  </div>
  <?php endwhile; ?>
  </div>
  <?php endif; ?>

  <!-- Back button -->
  <div style="margin-top:2rem;text-align:center;">
    <a href="index.php" style="color:var(--text-muted);text-decoration:none;font-size:.85rem;">← Tounen nan Tablo Bò Admin</a>
  </div>

</div><!-- /page -->

<script src="../js/main.js"></script>
</body>
</html>
