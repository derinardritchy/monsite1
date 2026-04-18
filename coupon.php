<?php
require_once 'includes/config.php';

// Table coupons (kreye si pa egziste)
$db = getDB();
$db->query("CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    description VARCHAR(200),
    type ENUM('acces_gratuit','reduction') DEFAULT 'acces_gratuit',
    valeur INT DEFAULT 100,
    max_utilisations INT DEFAULT 1,
    utilisations INT DEFAULT 0,
    actif TINYINT(1) DEFAULT 1,
    date_expiration DATE DEFAULT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$db->query("CREATE TABLE IF NOT EXISTS coupon_utilisations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT NOT NULL,
    user_id INT NOT NULL,
    date_utilisation DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk(coupon_id, user_id)
)");

if(!isLoggedIn()) redirect('login.php');
$user = getCurrentUser();
$uid = $user['id'];

$msg = '';
$msgType = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    if(empty($code)) {
        $msg = '❌ Ou dwe antre yon kòd koupon.';
        $msgType = 'danger';
    } else {
        $codeE = escape($code);
        $today = date('Y-m-d');
        $coupon = $db->query("SELECT * FROM coupons WHERE code='$codeE' AND actif=1 AND (date_expiration IS NULL OR date_expiration >= '$today') LIMIT 1")->fetch_assoc();

        if(!$coupon) {
            $msg = '❌ Kòd koupon sa a pa valid oswa li ekspire.';
            $msgType = 'danger';
        } elseif($coupon['utilisations'] >= $coupon['max_utilisations']) {
            $msg = '⚠️ Kòd koupon sa a deja itilize maksimòm fwa li.';
            $msgType = 'warning';
        } else {
            // Check si deja itilize pa user sa
            $alreadyUsed = $db->query("SELECT id FROM coupon_utilisations WHERE coupon_id={$coupon['id']} AND user_id=$uid LIMIT 1")->fetch_assoc();
            if($alreadyUsed) {
                $msg = '⚠️ Ou deja itilize kòd koupon sa a.';
                $msgType = 'warning';
            } else {
                // Aplike koupon
                $db->query("INSERT INTO coupon_utilisations (coupon_id, user_id) VALUES ({$coupon['id']}, $uid)");
                $db->query("UPDATE coupons SET utilisations=utilisations+1 WHERE id={$coupon['id']}");
                if($coupon['type'] === 'acces_gratuit') {
                    $db->query("UPDATE users SET acces_complet=1 WHERE id=$uid");
                    $msg = '🎉 Felisitasyon! Kòd koupon aksepte — ou gen aksè gratis pou tout kou yo!';
                } else {
                    $reduction = $coupon['valeur'];
                    $msg = "✅ Kòd koupon valid! Ou gen yon reduksyon de {$reduction}% sou pri a.";
                }
                $msgType = 'success';
                $user = getCurrentUser(); // Reload
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ht" data-theme="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Koupon — BiroTech</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="index.php" class="navbar-brand"><img src="assets/img/logo.svg" alt="BiroTech" style="height:36px;vertical-align:middle;"></a>
  <ul class="navbar-links">
    <li><a href="cours.php">Kou yo</a></li>
    <li><a href="dashboard.php">Tablo Bò</a></li>
    <li><a href="logout.php">Dekonekte</a></li>
    <button class="theme-toggle" id="themeToggle">🌙</button>
  </ul>
</nav>

<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:5rem 1rem 2rem;">
  <div style="width:100%;max-width:480px;">

    <div style="text-align:center;margin-bottom:2rem;">
      <div style="font-size:4rem;margin-bottom:.8rem;">🎟️</div>
      <h1 style="font-family:'Cinzel Decorative',serif;font-size:1.8rem;background:linear-gradient(135deg,var(--gold),var(--white));-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Kòd Koupon</h1>
      <p style="color:var(--text-muted);margin-top:.5rem;">Antre kòd koupon ou pou deblouke aksè</p>
    </div>

    <?php if($user['acces_complet']): ?>
      <div class="alert alert-success" style="text-align:center;padding:2rem;">
        <div style="font-size:3rem;margin-bottom:.8rem;">✅</div>
        <h3 style="color:var(--success);margin-bottom:.5rem;">Ou deja gen Aksè Konplè!</h3>
        <p style="color:var(--text-muted);">Tout kou yo disponib pou ou.</p>
        <a href="cours.php" class="btn btn-gold" style="margin-top:1rem;">📚 Ale nan Kou yo</a>
      </div>
    <?php else: ?>

    <?php if($msg): ?><div class="alert alert-<?= $msgType ?>"><?= $msg ?></div><?php endif; ?>

    <div class="card">
      <form method="POST">
        <div class="form-group">
          <label class="form-label">🎟️ Kòd Koupon ou *</label>
          <input type="text" name="code" class="form-control"
            placeholder="Egzanp: BIRO2025FREE"
            value="<?= htmlspecialchars(strtoupper($_POST['code'] ?? '')) ?>"
            style="text-transform:uppercase;font-size:1.1rem;letter-spacing:2px;font-weight:700;text-align:center;"
            maxlength="30" required>
          <p style="color:var(--text-muted);font-size:.8rem;margin-top:.5rem;text-align:center;">Kòd la pa sansib ak majiskil/miniskil</p>
        </div>
        <button type="submit" class="btn btn-gold w-100" style="font-size:1rem;padding:1rem;">
          🎁 Aplike Koupon
        </button>
      </form>

      <div style="margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--border);text-align:center;">
        <p style="color:var(--text-muted);font-size:.85rem;">Pa gen koupon? Fè yon depo NatCash 1,500 Goud pou aksè konplè.</p>
        <a href="dashboard.php#paiement" class="btn btn-outline btn-sm" style="margin-top:.8rem;">💰 Fè Depo</a>
      </div>
    </div>

    <?php endif; ?>

  </div>
</div>
<script src="js/main.js"></script>
</body>
</html>
