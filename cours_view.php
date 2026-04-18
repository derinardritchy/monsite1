<?php
require_once 'includes/config.php';
if(!isLoggedIn()) { setFlash('warning','⚠️ Ou dwe konekte.'); redirect('login.php'); }

$db = getDB();
$user = getCurrentUser();
$id = (int)($_GET['id'] ?? 0);
if(!$id) redirect('cours.php');

$lang = ($_GET['lang'] ?? 'fr') === 'en' ? 'en' : 'fr';

// ---- Build SELECT selon kolonn ki egziste ----
$hasCatNomEn  = columnExists('categories','nom_en');
$hasTitreEn   = columnExists('cours','titre_en');
$hasContenuEn = columnExists('cours','contenu_en');
$hasDuree     = columnExists('cours','duree_minutes');

$catNomEnSel  = $hasCatNomEn  ? ", cat.nom_en as cat_nom_en" : "";
$titreEnSel   = $hasTitreEn   ? ", c.titre_en" : "";
$contenuEnSel = $hasContenuEn ? ", c.contenu_en" : "";
$dureeSel     = $hasDuree     ? ", c.duree_minutes" : "";

$sql = "SELECT c.id, c.categorie_id, c.titre, c.contenu, c.video_url,
               c.numero_cours, c.est_gratuit, c.ordre
               $titreEnSel $contenuEnSel $dureeSel,
               cat.nom as cat_nom $catNomEnSel,
               cat.slug, cat.type as cat_type, cat.logo as cat_logo
        FROM cours c
        JOIN categories cat ON c.categorie_id = cat.id
        WHERE c.id = $id LIMIT 1";

$r = $db->query($sql);
if(!$r || $r->num_rows === 0) redirect('cours.php');
$cours = $r->fetch_assoc();

// Defaults si kolonn pa la
if(!isset($cours['titre_en']))    $cours['titre_en']    = '';
if(!isset($cours['contenu_en']))  $cours['contenu_en']  = '';
if(!isset($cours['cat_nom_en']))  $cours['cat_nom_en']  = '';
if(!isset($cours['duree_minutes'])) $cours['duree_minutes'] = 30;

// ---- Access check ----
$canAccess = $cours['est_gratuit'] || $user['acces_complet'] || $user['role'] === 'admin';
if(!$canAccess) {
    setFlash('warning','💰 Ou bezwen aksè konplè pou kou sa a. Fè yon depo.');
    redirect('cours.php');
}

// ---- Previous course blocked? ----
if($cours['numero_cours'] > 1 && !$user['acces_complet'] && $user['role'] !== 'admin') {
    $prevNum = (int)$cours['numero_cours'] - 1;
    $catId   = (int)$cours['categorie_id'];
    $prevR   = $db->query("SELECT id FROM cours WHERE categorie_id=$catId AND numero_cours=$prevNum LIMIT 1");
    if($prevR && $prevR->num_rows > 0) {
        $prevC    = $prevR->fetch_assoc();
        $prevProg = $db->query("SELECT statut FROM progression WHERE user_id={$user['id']} AND cours_id={$prevC['id']} LIMIT 1");
        $prevData = ($prevProg && $prevProg->num_rows > 0) ? $prevProg->fetch_assoc() : null;
        if(!$prevData || $prevData['statut'] !== 'termine') {
            setFlash('warning','🔒 Ou dwe fin kou ki anvan an epi pase egzamen li anvan ou ka kontinye.');
            redirect('cours.php?categorie='.$cours['slug']);
        }
    }
}

// ---- Progression ----
$uid   = (int)$user['id'];
$progR = $db->query("SELECT * FROM progression WHERE user_id=$uid AND cours_id=$id LIMIT 1");
$prog  = ($progR && $progR->num_rows > 0) ? $progR->fetch_assoc() : null;

if(!$prog) {
    $db->query("INSERT INTO progression (user_id,cours_id,statut,date_debut) VALUES ($uid,$id,'en_cours',NOW())");
    $prog = $db->query("SELECT * FROM progression WHERE user_id=$uid AND cours_id=$id LIMIT 1")->fetch_assoc();
} elseif($prog['statut'] === 'non_commence') {
    $db->query("UPDATE progression SET statut='en_cours',date_debut=NOW() WHERE user_id=$uid AND cours_id=$id");
    $prog['statut'] = 'en_cours';
}

// ---- Exam ----
$hasExamTitreEn = columnExists('examens','titre_en');
$hasQuestionEn  = columnExists('questions','texte_en');

$examSql = "SELECT id, cours_id, titre, score_minimum" . ($hasExamTitreEn ? ", titre_en" : "") . " FROM examens WHERE cours_id=$id LIMIT 1";
$examR = $db->query($examSql);
$exam = ($examR && $examR->num_rows > 0) ? $examR->fetch_assoc() : null;
if($exam && !isset($exam['titre_en'])) $exam['titre_en'] = '';

$questions = [];
if($exam) {
    $qSel = "id, examen_id, texte, option_a, option_b, option_c, option_d, bonne_reponse, points";
    if($hasQuestionEn) $qSel .= ", texte_en, option_a_en, option_b_en, option_c_en, option_d_en";
    $qr = $db->query("SELECT $qSel FROM questions WHERE examen_id={$exam['id']} ORDER BY id");
    if($qr) while($q = $qr->fetch_assoc()) {
        if(!isset($q['texte_en'])) { $q['texte_en']=''; $q['option_a_en']=''; $q['option_b_en']=''; $q['option_c_en']=''; $q['option_d_en']=''; }
        $questions[] = $q;
    }
}

// ---- Exam submission ----
$examResult = null;
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_exam']) && $exam) {
    $score = 0; $total = count($questions) * 10; $details = [];
    foreach($questions as $q) {
        $ans     = $_POST['q'.$q['id']] ?? '';
        $correct = ($ans === $q['bonne_reponse']);
        if($correct) $score += (int)$q['points'];
        $details[] = ['q' => $q, 'ans' => $ans, 'correct' => $correct];
    }
    $pct    = $total > 0 ? round($score / $total * 100) : 0;
    $passed = $pct >= (int)$exam['score_minimum'];
    $statut = $passed ? 'termine' : 'echoue';
    $tent   = (int)($prog['tentatives'] ?? 0) + 1;
    $db->query("UPDATE progression SET statut='$statut', score_examen=$pct, tentatives=$tent, date_fin=NOW() WHERE user_id=$uid AND cours_id=$id");
    $examResult = ['score' => $pct, 'passed' => $passed, 'details' => $details, 'tent' => $tent];
    $prog = $db->query("SELECT * FROM progression WHERE user_id=$uid AND cours_id=$id LIMIT 1")->fetch_assoc();
}

// Mark video/no-exam complete
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_done']) && !$exam) {
    $db->query("UPDATE progression SET statut='termine', date_fin=NOW() WHERE user_id=$uid AND cours_id=$id");
    redirect("cours_view.php?id=$id");
}

// ---- Next course ----
$nextR = $db->query("SELECT id FROM cours WHERE categorie_id={$cours['categorie_id']} AND numero_cours=".((int)$cours['numero_cours']+1)." LIMIT 1");
$nextCours = ($nextR && $nextR->num_rows > 0) ? $nextR->fetch_assoc() : null;

// ---- Display fields ----
$titre   = ($lang === 'en' && $cours['titre_en'])   ? $cours['titre_en']   : $cours['titre'];
$contenu = ($lang === 'en' && $cours['contenu_en']) ? $cours['contenu_en'] : $cours['contenu'];
$catNom  = ($lang === 'en' && $cours['cat_nom_en']) ? $cours['cat_nom_en'] : $cours['cat_nom'];
$examTitre = $exam ? (($lang === 'en' && $exam['titre_en']) ? $exam['titre_en'] : $exam['titre']) : '';
?>
<!DOCTYPE html>
<html lang="<?= $lang === 'en' ? 'en' : 'fr' ?>" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($titre) ?> — BiroTech</title>
<link rel="stylesheet" href="css/style.css">
<style>
.cours-content h2{font-family:'Cinzel Decorative',serif;color:var(--gold);margin:1.5rem 0 .8rem;font-size:1.1rem;}
.cours-content h3{color:var(--blue-light);margin:1.2rem 0 .5rem;}
.cours-content p{margin-bottom:.8rem;color:var(--text-main);line-height:1.85;}
.cours-content ul,.cours-content ol{padding-left:1.5rem;margin-bottom:.8rem;}
.cours-content li{margin-bottom:.5rem;line-height:1.7;}
.cours-content strong{color:var(--gold);}
.cours-content em{color:var(--blue-light);}
.cours-content code{background:rgba(0,176,255,.1);padding:.1rem .4rem;border-radius:4px;font-family:'Courier New',monospace;font-size:.9em;color:var(--blue-glow);}
.cours-content table{width:100%;border-collapse:collapse;margin:1rem 0;font-size:.9rem;}
.cours-content th{background:rgba(255,215,0,.1);padding:.7rem;border:1px solid rgba(255,255,255,.1);color:var(--gold);text-align:left;}
.cours-content td{padding:.6rem .7rem;border:1px solid rgba(255,255,255,.07);}
.cours-content tr:hover td{background:rgba(255,255,255,.02);}
.cours-content div[style*="border-left"]{margin-top:1rem;}
</style>
</head>
<body>
<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <img src="assets/img/logo.svg" alt="BiroTech" style="height:34px;vertical-align:middle;">
  </a>
  <ul class="navbar-links">
    <li><a href="cours.php?categorie=<?= $cours['slug'] ?>">← <?= $cours['cat_logo'] ?> <?= htmlspecialchars($catNom) ?></a></li>
    <li><a href="dashboard.php">Tablo Bò</a></li>
    <li><a href="ai_assistant.php">🤖 AI</a></li>
    <li><a href="logout.php">Dekonekte</a></li>
    <button class="theme-toggle" id="themeToggle">🌙</button>
  </ul>
</nav>

<div style="padding-top:68px;max-width:900px;margin:0 auto;padding-left:1.5rem;padding-right:1.5rem;padding-bottom:4rem;">

  <!-- Breadcrumb -->
  <div style="padding:1.5rem 0 0;color:var(--text-muted);font-size:.85rem;flex-wrap:wrap;display:flex;gap:.3rem;align-items:center;">
    <a href="cours.php" style="color:var(--text-muted);text-decoration:none;">Kou yo</a>
    <span>→</span>
    <a href="cours.php?categorie=<?= $cours['slug'] ?>" style="color:var(--text-muted);text-decoration:none;"><?= htmlspecialchars($catNom) ?></a>
    <span>→</span>
    <span style="color:var(--gold);">Kousan <?= $cours['numero_cours'] ?></span>
  </div>

  <!-- Header -->
  <div style="margin:1.2rem 0 1.8rem;">
    <div style="display:flex;align-items:flex-start;gap:1rem;flex-wrap:wrap;justify-content:space-between;">
      <div style="flex:1;min-width:0;">
        <h1 style="font-family:'Cinzel Decorative',serif;font-size:clamp(1rem,3vw,1.6rem);margin-bottom:.8rem;word-break:break-word;">
          <?= $cours['cat_logo'] ?> <?= htmlspecialchars($titre) ?>
        </h1>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
          <span class="badge <?= $cours['est_gratuit'] ? 'badge-gold' : 'badge-blue' ?>">
            <?= $cours['est_gratuit'] ? '✨ Gratis' : '💎 Premium' ?>
          </span>
          <span class="badge badge-blue">⏱️ ~<?= $cours['duree_minutes'] ?> min</span>
          <?php if($prog): ?>
            <span class="badge <?= $prog['statut']==='termine'?'badge-green':($prog['statut']==='echoue'?'badge-red':'badge-gold') ?>">
              <?= $prog['statut']==='termine'?'✅ Termine':($prog['statut']==='echoue'?'❌ Echoue':'⏳ An Kou') ?>
            </span>
          <?php endif; ?>
        </div>
      </div>
      <!-- Language toggle -->
      <?php if($cours['titre_en'] || $cours['contenu_en']): ?>
      <div style="display:flex;gap:.3rem;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:50px;padding:.25rem;flex-shrink:0;">
        <a href="?id=<?= $id ?>&lang=fr" style="padding:.3rem .8rem;border-radius:50px;font-size:.8rem;font-weight:700;text-decoration:none;background:<?= $lang==='fr'?'linear-gradient(135deg,var(--gold-dark),var(--gold))':'none' ?>;color:<?= $lang==='fr'?'#000':'var(--text-muted)' ?>;">🇫🇷 FR</a>
        <a href="?id=<?= $id ?>&lang=en" style="padding:.3rem .8rem;border-radius:50px;font-size:.8rem;font-weight:700;text-decoration:none;background:<?= $lang==='en'?'linear-gradient(135deg,var(--gold-dark),var(--gold))':'none' ?>;color:<?= $lang==='en'?'#000':'var(--text-muted)' ?>;">🇺🇸 EN</a>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- VIDEO -->
  <?php if($cours['cat_type'] === 'video' && $cours['video_url']): ?>
  <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:var(--radius);background:#000;margin-bottom:2rem;border:1px solid var(--border);">
    <iframe src="<?= htmlspecialchars($cours['video_url']) ?>"
      style="position:absolute;inset:0;width:100%;height:100%;border:none;"
      allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture"
      allowfullscreen></iframe>
  </div>
  <?php if($prog && $prog['statut'] !== 'termine'): ?>
  <form method="POST" style="margin-bottom:2rem;">
    <button type="submit" name="mark_done" value="1" class="btn btn-gold">✅ Mwen Fin Gade Vidéo a</button>
  </form>
  <?php endif; ?>

  <!-- TEXTE COURS -->
  <?php else: ?>
  <div class="card" style="margin-bottom:2rem;">
    <div class="cours-content"><?= $contenu ?></div>
  </div>
  <?php endif; ?>

  <!-- ===== BOUTON LANSE EGZAMEN ===== -->
  <?php if($exam && !$examResult && $prog && $prog['statut'] !== 'termine'): ?>
  <div style="margin-bottom:2rem;">
    <?php if(!isset($_GET['start_exam'])): ?>
    <!-- Bouton klè pou kòmanse egzamen -->
    <div style="background:var(--bg-card);border:2px solid rgba(255,215,0,.3);border-radius:var(--radius);padding:2rem;text-align:center;">
      <div style="font-size:3rem;margin-bottom:1rem;">📝</div>
      <h3 style="font-family:'Cinzel Decorative',serif;color:var(--gold);margin-bottom:.8rem;">
        <?= $lang==='en' ? 'Ready to take the exam?' : 'Ou fin li kou a?' ?>
      </h3>
      <p style="color:var(--text-muted);max-width:450px;margin:0 auto 1.5rem;line-height:1.7;">
        <?= $lang==='en'
          ? "Click the button below to start the exam. You need at least <strong style='color:var(--gold)'>{$exam['score_minimum']}/100</strong> to pass."
          : "Klike sou bouton anba a pou konpoze egzamen an. Ou bezwen omwen <strong style='color:var(--gold)'>{$exam['score_minimum']}/100</strong> pou pase epi kontinye." ?>
      </p>
      <?php if((int)($prog['tentatives'] ?? 0) > 0): ?>
        <div class="alert alert-warning" style="max-width:380px;margin:0 auto 1rem;">
          <?= $lang==='en'?'Last score':'Dènye nòt ou' ?>: <strong><?= $prog['score_examen'] ?>/100</strong>
        </div>
      <?php endif; ?>
      <a href="?id=<?= $id ?>&lang=<?= $lang ?>&start_exam=1"
         class="btn btn-gold"
         style="font-size:1.1rem;padding:1rem 2.5rem;display:inline-flex;align-items:center;gap:.5rem;">
        ✏️ <?= $lang==='en' ? 'Start the Exam' : 'Klike pou Konpoze Egzamen' ?>
      </a>
    </div>

    <?php else: ?>
    <!-- EXAM FORM -->
    <div class="card" style="border-color:rgba(255,215,0,.3);">
      <div style="text-align:center;margin-bottom:1.5rem;padding-bottom:1.2rem;border-bottom:1px solid var(--border);">
        <h2 style="font-family:'Cinzel Decorative',serif;color:var(--gold);margin-bottom:.5rem;font-size:1.1rem;">📝 <?= htmlspecialchars($examTitre) ?></h2>
        <p style="color:var(--text-muted);font-size:.85rem;">
          <?= count($questions) ?> <?= $lang==='en'?'questions':'kesyon' ?> &nbsp;·&nbsp;
          <?= $lang==='en'?'Min':'Min' ?> <strong style="color:var(--gold)"><?= $exam['score_minimum'] ?>/100</strong>
        </p>
      </div>
      <form method="POST" id="examForm">
        <?php foreach($questions as $i => $q):
          $qTxt = ($lang==='en' && $q['texte_en']) ? $q['texte_en'] : $q['texte'];
        ?>
        <div class="question-card" style="margin-bottom:1.2rem;">
          <p class="question-text"><?= ($i+1) ?>. <?= htmlspecialchars($qTxt) ?></p>
          <?php foreach(['a','b','c','d'] as $opt):
            $optKey = 'option_'.$opt.($lang==='en'&&$q['option_'.$opt.'_en']?'_en':'');
            $optTxt = ($lang==='en' && $q['option_'.$opt.'_en']) ? $q['option_'.$opt.'_en'] : $q['option_'.$opt];
          ?>
          <button type="button" class="option-btn"
            onclick="selectOption(this,'<?= $q['id'] ?>','<?= $opt ?>')">
            <span class="option-letter"><?= strtoupper($opt) ?></span>
            <?= htmlspecialchars($optTxt) ?>
            <input type="radio" name="q<?= $q['id'] ?>" value="<?= $opt ?>"
              style="display:none;" id="r<?= $q['id'] ?>_<?= $opt ?>">
          </button>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
        <div style="text-align:center;margin-top:1.5rem;">
          <button type="submit" name="submit_exam" value="1" class="btn btn-gold"
            id="submitExam" disabled
            style="font-size:1rem;padding:1rem 2.5rem;opacity:.5;cursor:not-allowed;">
            📤 <?= $lang==='en'?'Submit Exam':'Soumèt Egzamen' ?>
          </button>
          <p style="color:var(--text-muted);font-size:.8rem;margin-top:.7rem;">
            <?= $lang==='en'?'Answer all questions first.':'Reponn tout kesyon yo anvan ou soumèt.' ?>
          </p>
        </div>
      </form>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- EXAM RESULT -->
  <?php if($examResult): ?>
  <div class="card" style="margin-bottom:2rem;text-align:center;border-color:<?= $examResult['passed']?'var(--success)':'var(--danger)' ?>;">
    <div class="score-circle" style="border-color:<?= $examResult['passed']?'var(--success)':'var(--danger)' ?>;box-shadow:0 0 40px <?= $examResult['passed']?'rgba(0,230,118,.3)':'rgba(255,23,68,.3)' ?>;">
      <span class="score-num" style="color:<?= $examResult['passed']?'var(--success)':'var(--danger)' ?>;"><?= $examResult['score'] ?></span>
      <span class="score-label">/ 100</span>
    </div>
    <?php if($examResult['passed']): ?>
      <h3 style="color:var(--success);font-size:1.4rem;margin-bottom:.5rem;">🎉 <?= $lang==='en'?'You Passed!':'Bravo! Ou Pase!' ?></h3>
      <p style="color:var(--text-muted);margin-bottom:1.5rem;"><?= $lang==='en'?'Score':'Nòt ou' ?>: <strong><?= $examResult['score'] ?>/100</strong></p>
      <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
        <?php if($nextCours): ?>
          <a href="cours_view.php?id=<?= $nextCours['id'] ?>&lang=<?= $lang ?>" class="btn btn-gold"><?= $lang==='en'?'Next Lesson →':'Kou Swivan →' ?></a>
        <?php else: ?>
          <a href="certificat.php?categorie=<?= $cours['slug'] ?>" class="btn btn-gold">🏆 <?= $lang==='en'?'Get Certificate':'Telechaje Sètifika' ?></a>
        <?php endif; ?>
        <a href="ai_assistant.php" class="btn btn-blue">🤖 Poze AI yon Kesyon</a>
      </div>
    <?php else: ?>
      <h3 style="color:var(--danger);font-size:1.4rem;margin-bottom:.5rem;">😔 <?= $lang==='en'?'Not Passed':'Ou Pa Pase' ?></h3>
      <p style="color:var(--text-muted);margin-bottom:1.5rem;"><?= $lang==='en'?'You need at least':'Ou bezwen omwen' ?> <strong style="color:var(--gold)"><?= $exam['score_minimum'] ?>/100</strong></p>
      <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
        <a href="?id=<?= $id ?>&lang=<?= $lang ?>" class="btn btn-blue">📖 <?= $lang==='en'?'Review Lesson':'Relire Kou a' ?></a>
        <a href="ai_assistant.php" class="btn btn-outline">🤖 Mande AI ede ou</a>
      </div>
    <?php endif; ?>

    <!-- Detay repons -->
    <div style="margin-top:2rem;text-align:left;">
      <h4 style="color:var(--gold);margin-bottom:1rem;">📋 <?= $lang==='en'?'Answer Details':'Detay Repons' ?></h4>
      <?php foreach($examResult['details'] as $i => $d):
        $qTxt = ($lang==='en' && $d['q']['texte_en']) ? $d['q']['texte_en'] : $d['q']['texte'];
      ?>
      <div style="margin-bottom:1rem;padding:1rem;background:<?= $d['correct']?'rgba(0,230,118,.05)':'rgba(255,23,68,.05)' ?>;border:1px solid <?= $d['correct']?'rgba(0,230,118,.2)':'rgba(255,23,68,.2)' ?>;border-radius:var(--radius-sm);">
        <p style="font-weight:700;margin-bottom:.5rem;font-size:.9rem;"><?= ($i+1) ?>. <?= htmlspecialchars($qTxt) ?></p>
        <?php foreach(['a','b','c','d'] as $opt):
          $optTxt = ($lang==='en' && $d['q']['option_'.$opt.'_en']) ? $d['q']['option_'.$opt.'_en'] : $d['q']['option_'.$opt];
          $isCorrect = $opt === $d['q']['bonne_reponse'];
          $isWrong   = $opt === $d['ans'] && !$isCorrect;
        ?>
        <div style="padding:.3rem .7rem;margin:.2rem 0;border-radius:4px;font-size:.85rem;
          <?= $isCorrect?'background:rgba(0,230,118,.15);color:var(--success);font-weight:700;':($isWrong?'background:rgba(255,23,68,.1);color:var(--danger);':'color:var(--text-muted);') ?>">
          <?= strtoupper($opt) ?>. <?= htmlspecialchars($optTxt) ?>
          <?= $isCorrect?' ✅':($isWrong?' ❌':'') ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Nav bottom -->
  <div style="display:flex;gap:1rem;margin-top:2rem;flex-wrap:wrap;">
    <a href="cours.php?categorie=<?= $cours['slug'] ?>" class="btn btn-outline">← <?= $lang==='en'?'Back':'Tounen' ?></a>
    <?php if($prog && $prog['statut'] === 'termine' && $nextCours): ?>
      <a href="cours_view.php?id=<?= $nextCours['id'] ?>&lang=<?= $lang ?>" class="btn btn-gold">Kou Swivan →</a>
    <?php endif; ?>
    <?php if($prog && $prog['statut'] === 'termine' && !$nextCours): ?>
      <a href="certificat.php?categorie=<?= $cours['slug'] ?>" class="btn btn-gold">🏆 Sètifika</a>
    <?php endif; ?>
  </div>

</div>

<script src="js/main.js"></script>
<script>
const answered = {};
const totalQ = <?= count($questions) ?>;

function selectOption(btn, qId, opt) {
  btn.closest('.question-card').querySelectorAll('.option-btn').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');
  document.getElementById('r' + qId + '_' + opt).checked = true;
  answered[qId] = opt;
  if(Object.keys(answered).length === totalQ) {
    const sb = document.getElementById('submitExam');
    if(sb) {
      sb.disabled = false;
      sb.style.opacity = '1';
      sb.style.cursor = 'pointer';
      sb.style.animation = 'pulse-gold 1.5s infinite';
    }
  }
}
</script>
</body>
</html>
