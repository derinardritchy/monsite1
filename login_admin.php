<?php
require_once '../includes/config.php';

// Si deja admin, redirect dirèk
if (isAdmin()) redirect('index.php');

// Kòd sekrè admin — CHANJE SA!
define('ADMIN_SECRET_CODE', 'BIRO@Admin2025!');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email  = trim($_POST['email'] ?? '');
    $pass   = $_POST['password'] ?? '';
    $secret = trim($_POST['secret_code'] ?? '');

    if (empty($email) || empty($pass) || empty($secret)) {
        $error = '⚠️ Ranpli tout champ yo.';
    } elseif ($secret !== ADMIN_SECRET_CODE) {
        // Kòd sekrè mal — anpeche aksè menm si imel ak modpas bon
        sleep(2); // Anti-brute force
        $error = '🚫 Kòd sekrè Admin pa bon.';
    } else {
        $db    = getDB();
        $emailE = escape($email);
        $r     = $db->query("SELECT * FROM users WHERE email='$emailE' AND role='admin' LIMIT 1");
        if ($r && $r->num_rows > 0) {
            $user = $r->fetch_assoc();
            if (password_verify($pass, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role']    = 'admin';
                $_SESSION['nom']     = $user['nom'];
                $db->query("UPDATE users SET derniere_connexion=NOW() WHERE id={$user['id']}");
                redirect('index.php');
            } else {
                sleep(1);
                $error = '❌ Imel oswa modpas pa bon.';
            }
        } else {
            sleep(1);
            $error = '❌ Kont Admin pa jwenn.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ht" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — BiroTech</title>
<link rel="stylesheet" href="../css/style.css">
<!-- Bloke indexasyon paj sa a -->
<meta name="robots" content="noindex, nofollow">
<style>
body { background: #050510; }
.login-wrap {
  min-height: 100vh;
  display: flex; align-items: center; justify-content: center;
  padding: 2rem 1rem;
  background:
    radial-gradient(ellipse 60% 50% at 50% 0%, rgba(21,101,192,.2), transparent),
    radial-gradient(ellipse 40% 30% at 80% 80%, rgba(255,215,0,.06), transparent);
}
.login-box {
  width: 100%; max-width: 420px;
  background: var(--bg-card);
  border: 1px solid rgba(255,215,0,.2);
  border-radius: var(--radius);
  padding: 2.5rem 2rem;
  box-shadow: 0 20px 60px rgba(0,0,0,.6), 0 0 40px rgba(255,215,0,.08);
}
.lock-icon {
  width: 70px; height: 70px; border-radius: 50%;
  background: linear-gradient(135deg, #7f0000, var(--danger));
  display: flex; align-items: center; justify-content: center;
  font-size: 2rem; margin: 0 auto 1.5rem;
  box-shadow: 0 0 30px rgba(255,23,68,.3);
}
</style>
</head>
<body>
<div class="login-wrap">
  <div class="login-box">
    <div class="lock-icon">🔐</div>
    <h1 style="font-family:'Cinzel Decorative',serif;text-align:center;font-size:1.3rem;background:linear-gradient(135deg,var(--gold),var(--white));-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:.4rem;">
      Koneksyon Admin
    </h1>
    <p style="color:var(--text-muted);text-align:center;font-size:.82rem;margin-bottom:1.5rem;">
      Aksè prive — Sèl Derinard Ritchy
    </p>

    <?php if($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label">📧 Imel Admin</label>
        <input type="email" name="email" class="form-control"
          placeholder="admin@birotech.ht"
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
          required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">🔒 Modpas</label>
        <div style="position:relative;">
          <input type="password" name="password" class="form-control"
            placeholder="Modpas admin ou" id="passF" required>
          <button type="button" onclick="var f=document.getElementById('passF');f.type=f.type==='password'?'text':'password';"
            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1rem;">👁️</button>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">🔑 Kòd Sekrè Admin</label>
        <input type="password" name="secret_code" class="form-control"
          placeholder="Kòd sekrè espesyal admin" required
          autocomplete="off">
        <p style="color:var(--text-muted);font-size:.75rem;margin-top:.3rem;">
          Kòd sa a diferan ak modpas ou. Se yon kòd espesyal pou admin sèlman.
        </p>
      </div>
      <button type="submit" class="btn btn-gold w-100" style="font-size:1rem;padding:1rem;margin-top:.5rem;">
        🔐 Antre nan Admin Panel
      </button>
    </form>

    <div style="text-align:center;margin-top:1.5rem;">
      <a href="../index.php" style="color:var(--text-muted);font-size:.82rem;text-decoration:none;">← Tounen nan sit la</a>
    </div>
  </div>
</div>
<script src="../js/main.js"></script>
</body>
</html>
