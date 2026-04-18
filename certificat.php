<?php
require_once 'includes/config.php';
if(!isLoggedIn()) redirect('login.php');

$db = getDB();
$user = getCurrentUser();
$catSlug = $_GET['categorie'] ?? '';

if(!$catSlug) redirect('cours.php');

$slugE = escape($catSlug);
$cat = $db->query("SELECT * FROM categories WHERE slug='$slugE' LIMIT 1")->fetch_assoc();
if(!$cat) redirect('cours.php');

$uid = $user['id'];

// Verifye si tout kou yo termine
$totalCours = $db->query("SELECT COUNT(*) as n FROM cours WHERE categorie_id={$cat['id']}")->fetch_assoc()['n'];
$termineCount = $db->query("SELECT COUNT(*) as n FROM progression p JOIN cours c ON p.cours_id=c.id WHERE p.user_id=$uid AND c.categorie_id={$cat['id']} AND p.statut='termine'")->fetch_assoc()['n'];

if($termineCount < $totalCours) {
    setFlash('warning', '⚠️ Ou dwe fin tout kou yo avan ou resevwa sètifika a.');
    redirect('cours.php?categorie='.$catSlug);
}

$dateAjod = date('d F Y');
$certNum = 'BT-'.strtoupper(substr($catSlug,0,2)).'-'.date('Y').'-'.str_pad($uid, 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Sètifika — <?= htmlspecialchars($cat['nom']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700;900&family=Exo+2:wght@400;600;700&family=Great+Vibes&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{background:#0a0a1a;font-family:'Exo 2',sans-serif;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;padding:2rem;}
.cert-wrap{background:#fff;width:100%;max-width:860px;aspect-ratio:1.414/1;position:relative;overflow:hidden;border-radius:4px;box-shadow:0 20px 80px rgba(0,0,0,0.8);}
.cert-bg{position:absolute;inset:0;background:linear-gradient(135deg,#0a0620 0%,#0d1b3e 40%,#0a1628 100%);}
/* Gold borders */
.border-outer{position:absolute;inset:12px;border:3px solid #C8A400;}
.border-inner{position:absolute;inset:20px;border:1px solid rgba(200,164,0,.4);}
/* Corner ornaments */
.corner{position:absolute;width:60px;height:60px;}
.corner svg{width:100%;height:100%;}
.corner-tl{top:8px;left:8px;}
.corner-tr{top:8px;right:8px;transform:scaleX(-1);}
.corner-bl{bottom:8px;left:8px;transform:scaleY(-1);}
.corner-br{bottom:8px;right:8px;transform:scale(-1);}
.cert-content{position:relative;z-index:2;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:3rem 4rem;text-align:center;}
.cert-logo{font-size:2.5rem;margin-bottom:.5rem;}
.cert-brand{font-family:'Cinzel Decorative',serif;font-size:2rem;background:linear-gradient(135deg,#C8A400,#FFD700,#FFE57F);-webkit-background-clip:text;-webkit-text-fill-color:transparent;letter-spacing:3px;margin-bottom:.3rem;}
.cert-subtitle{color:rgba(200,164,0,.7);font-size:.75rem;letter-spacing:4px;text-transform:uppercase;margin-bottom:1.5rem;}
.cert-title{font-family:'Cinzel Decorative',serif;font-size:1.3rem;color:#FFD700;letter-spacing:2px;margin-bottom:.8rem;}
.cert-presents{color:rgba(255,255,255,.5);font-size:.8rem;letter-spacing:2px;text-transform:uppercase;margin-bottom:.5rem;}
.cert-name{font-family:'Great Vibes',cursive;font-size:3.5rem;color:#fff;line-height:1.1;margin-bottom:.5rem;text-shadow:0 0 30px rgba(255,215,0,.3);}
.cert-completed{color:rgba(255,255,255,.6);font-size:.85rem;margin-bottom:.4rem;}
.cert-course{font-family:'Cinzel Decorative',serif;font-size:1.2rem;color:#FFD700;margin-bottom:.3rem;}
.cert-detail{color:rgba(255,255,255,.45);font-size:.75rem;margin-bottom:1.5rem;}
.cert-line{width:200px;height:1px;background:linear-gradient(90deg,transparent,#FFD700,transparent);margin:0 auto 1.5rem;}
.cert-footer{display:flex;justify-content:space-between;align-items:flex-end;width:100%;gap:2rem;}
.cert-sign{text-align:center;flex:1;}
.sign-line{width:140px;height:1px;background:rgba(200,164,0,.6);margin:0 auto .3rem;}
.sign-name{font-family:'Great Vibes',cursive;font-size:1.4rem;color:#FFD700;}
.sign-title{color:rgba(255,255,255,.5);font-size:.65rem;letter-spacing:1px;text-transform:uppercase;}
.cert-num{color:rgba(200,164,0,.5);font-size:.65rem;letter-spacing:1px;}
.cert-stamp{width:70px;height:70px;border:2px solid rgba(200,164,0,.5);border-radius:50%;display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;}
/* Stars decoration */
.stars{position:absolute;inset:0;pointer-events:none;z-index:1;}
/* Print styles */
@media print{
  body{background:#fff;padding:0;}
  .cert-wrap{box-shadow:none;max-width:100%;}
  .no-print{display:none!important;}
}
</style>
</head>
<body>

<!-- Actions -->
<div class="no-print" style="margin-bottom:1.5rem;display:flex;gap:1rem;flex-wrap:wrap;justify-content:center;">
  <button onclick="window.print()" style="background:linear-gradient(135deg,#C8A400,#FFD700);color:#000;border:none;padding:.8rem 2rem;border-radius:50px;font-weight:700;cursor:pointer;font-size:.95rem;font-family:'Exo 2',sans-serif;">
    🖨️ Enprime / Sove PDF
  </button>
  <a href="cours.php" style="background:transparent;border:2px solid #FFD700;color:#FFD700;padding:.8rem 2rem;border-radius:50px;font-weight:700;text-decoration:none;font-size:.95rem;font-family:'Exo 2',sans-serif;">
    ← Tounen
  </a>
</div>

<!-- CERTIFICATE -->
<div class="cert-wrap" id="certificate">
  <div class="cert-bg"></div>

  <!-- Stars background -->
  <div class="stars" id="stars"></div>

  <!-- Decorative corners -->
  <div class="corner corner-tl"><svg viewBox="0 0 60 60"><path d="M5,5 L55,5 M5,5 L5,55 M5,5 L25,25" stroke="#C8A400" stroke-width="1.5" fill="none"/><circle cx="5" cy="5" r="4" fill="#FFD700"/><path d="M15,5 Q5,15 5,15" stroke="#FFD700" stroke-width="1" fill="none"/></svg></div>
  <div class="corner corner-tr"><svg viewBox="0 0 60 60"><path d="M5,5 L55,5 M5,5 L5,55 M5,5 L25,25" stroke="#C8A400" stroke-width="1.5" fill="none"/><circle cx="5" cy="5" r="4" fill="#FFD700"/><path d="M15,5 Q5,15 5,15" stroke="#FFD700" stroke-width="1" fill="none"/></svg></div>
  <div class="corner corner-bl"><svg viewBox="0 0 60 60"><path d="M5,5 L55,5 M5,5 L5,55 M5,5 L25,25" stroke="#C8A400" stroke-width="1.5" fill="none"/><circle cx="5" cy="5" r="4" fill="#FFD700"/><path d="M15,5 Q5,15 5,15" stroke="#FFD700" stroke-width="1" fill="none"/></svg></div>
  <div class="corner corner-br"><svg viewBox="0 0 60 60"><path d="M5,5 L55,5 M5,5 L5,55 M5,5 L25,25" stroke="#C8A400" stroke-width="1.5" fill="none"/><circle cx="5" cy="5" r="4" fill="#FFD700"/><path d="M15,5 Q5,15 5,15" stroke="#FFD700" stroke-width="1" fill="none"/></svg></div>

  <div class="border-outer"></div>
  <div class="border-inner"></div>

  <div class="cert-content">
    <div class="cert-logo"><?= $cat['logo'] ?></div>
    <div class="cert-brand">BiroTech</div>
    <div class="cert-subtitle">Platfòm Aprantisaj Enfòmatik · Haïti</div>

    <div class="cert-title">🏆 SÈTIFIKA REYISIT</div>
    <div class="cert-presents">Sètifye ke:</div>

    <div class="cert-name"><?= htmlspecialchars($user['prenom'].' '.$user['nom']) ?></div>

    <div class="cert-completed">te konplete avèk siksè tout kou nan</div>
    <div class="cert-course"><?= htmlspecialchars($cat['nom']) ?></div>
    <div class="cert-detail"><?= $totalCours ?> kou · Egzamen pase · <?= $dateAjod ?></div>

    <div class="cert-line"></div>

    <div class="cert-footer">
      <div class="cert-sign">
        <div class="sign-line"></div>
        <div class="sign-name">Derinard Ritchy</div>
        <div class="sign-title">Fondatè & Direktè · BiroTech</div>
        <div class="sign-title">Ingénieur en Sciences Informatiques</div>
      </div>
      <div class="cert-stamp">
        <div style="font-size:1.8rem;">🏅</div>
        <div style="color:rgba(200,164,0,.7);font-size:.5rem;letter-spacing:1px;text-transform:uppercase;margin-top:.2rem;">Verified</div>
      </div>
      <div style="flex:1;text-align:right;">
        <div class="cert-num">N° <?= $certNum ?></div>
        <div style="color:rgba(200,164,0,.5);font-size:.65rem;margin-top:.3rem;">birotech.ht</div>
        <div style="color:rgba(255,255,255,.3);font-size:.6rem;margin-top:.2rem;">Sètifika ofisyèl BiroTech</div>
      </div>
    </div>
  </div>
</div>

<script>
// Generate stars
const starsDiv = document.getElementById('stars');
for(let i=0; i<60; i++){
  const s = document.createElement('div');
  const size = Math.random()*2+1;
  s.style.cssText = `position:absolute;width:${size}px;height:${size}px;background:#FFD700;border-radius:50%;left:${Math.random()*100}%;top:${Math.random()*100}%;opacity:${Math.random()*0.4+0.1};`;
  starsDiv.appendChild(s);
}
</script>
</body>
</html>
