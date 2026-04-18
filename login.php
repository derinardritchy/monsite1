<?php
require_once 'includes/config.php';
if(isLoggedIn()) redirect('cours.php');

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if(empty($email) || empty($pass)) {
        $error = '⚠️ Ou dwe ranpli tout champ yo.';
    } else {
        $db = getDB();
        $emailE = escape($email);
        $r = $db->query("SELECT * FROM users WHERE email='$emailE' LIMIT 1");
        if($r->num_rows === 0) {
            $error = '❌ Pa gen kont avèk imel sa a. <a href="register.php" style="color:var(--gold)">Kreye yon kont</a>';
        } else {
            $user = $r->fetch_assoc();
            if($user['statut'] === 'bloque') {
                $error = '🚫 Kont ou a bloke. Kontakte admin.';
            } elseif(!password_verify($pass, $user['password'])) {
                $error = '❌ Modpas ou pa bon. Eseye ankò.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nom'] = $user['nom'];
                // Update last login
                $db->query("UPDATE users SET derniere_connexion=NOW() WHERE id={$user['id']}");
                if($user['role'] === 'admin') redirect('admin/index.php');
                else redirect('cours.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ht" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Konekte — BiroTech</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="index.php" class="navbar-brand">BiroTech</a>
  <ul class="navbar-links">
    <li><a href="register.php">Pa gen kont? Enskri</a></li>
    <button class="theme-toggle" id="themeToggle">🌙</button>
  </ul>
</nav>

<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:5rem 1rem 2rem;">
  <div style="width:100%;max-width:440px;">

    <div style="text-align:center;margin-bottom:2rem;">
      <div style="font-size:3rem;margin-bottom:0.5rem;">🔐</div>
      <h1 style="font-family:'Cinzel Decorative',serif;font-size:1.8rem;background:linear-gradient(135deg,var(--gold),var(--white));-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
        Konekte ou
      </h1>
      <p style="color:var(--text-muted);margin-top:0.5rem;">Byenvini tounen nan BiroTech!</p>
    </div>

    <?php if($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php $flash = getFlash(); if($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><?= $flash['msg'] ?></div>
    <?php endif; ?>

    <div class="card">
      <form method="POST">
        <div class="form-group">
          <label class="form-label">📧 Adrès Imel</label>
          <input type="email" name="email" class="form-control" placeholder="ou@email.com"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
        </div>
        <div class="form-group">
          <label class="form-label">🔒 Modpas</label>
          <div style="position:relative;">
            <input type="password" name="password" class="form-control" placeholder="Modpas ou" id="passField" required>
            <button type="button" onclick="togglePass()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1.1rem;" id="eyeBtn">👁️</button>
          </div>
        </div>
        <button type="submit" class="btn btn-gold w-100" style="font-size:1rem;padding:1rem;margin-top:0.5rem;">
          🔑 Konekte
        </button>
        <p style="text-align:center;margin-top:1rem;color:var(--text-muted);font-size:0.9rem;">
          Pa gen kont? <a href="register.php" style="color:var(--gold);font-weight:700;">Enskri gratis</a>
        </p>
      </form>
    </div>

  </div>
</div>

<script src="js/main.js"></script>
<script>
function togglePass() {
  const f = document.getElementById('passField');
  const b = document.getElementById('eyeBtn');
  f.type = f.type === 'password' ? 'text' : 'password';
  b.textContent = f.type === 'password' ? '👁️' : '🙈';
}
</script>
</body>
</html>
