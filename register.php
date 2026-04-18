<?php
require_once 'includes/config.php';

// Si deja konekte, redirect
if(isLoggedIn()) {
    redirect('cours.php');
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom'] ?? '');
    $prenom   = trim($_POST['prenom'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $tel      = trim($_POST['telephone'] ?? '');
    $pass     = $_POST['password'] ?? '';
    $pass2    = $_POST['password2'] ?? '';

    // Validasyon
    if(empty($nom) || empty($prenom) || empty($email) || empty($tel) || empty($pass) || empty($pass2)) {
        $error = '⚠️ Ou dwe ranpli tout champ yo san eksepsyon.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '❌ Adrès imel ou a pa valid.';
    } elseif(strlen($pass) < 8) {
        $error = '🔒 Modpas ou dwe gen omwen 8 karaktè.';
    } elseif($pass !== $pass2) {
        $error = '❌ De modpas yo pa menm. Verifye yo.';
    } else {
        $db = getDB();
        $emailEsc = escape($email);
        $check = $db->query("SELECT id FROM users WHERE email='$emailEsc' LIMIT 1");
        if($check->num_rows > 0) {
            $error = '⚠️ Imel sa a deja itilize. <a href="login.php" style="color:var(--gold)">Konekte</a>';
        } else {
            $hash    = password_hash($pass, PASSWORD_DEFAULT);
            $nomE    = escape($nom);
            $prenomE = escape($prenom);
            $telE    = escape($tel);
            $db->query("INSERT INTO users (nom, prenom, email, telephone, password) VALUES ('$nomE','$prenomE','$emailEsc','$telE','$hash')");
            $newId = $db->insert_id;

            // Konekte otomatikman
            $_SESSION['user_id'] = $newId;
            $_SESSION['role'] = 'user';
            $_SESSION['nom'] = $nom;

            redirect('cours.php?welcome=1');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ht" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enskri — BiroTech</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="index.php" class="navbar-brand">BiroTech</a>
  <ul class="navbar-links">
    <li><a href="login.php">Deja gen kont? Konekte</a></li>
    <button class="theme-toggle" id="themeToggle">🌙</button>
  </ul>
</nav>

<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:5rem 1rem 2rem;">
  <div style="width:100%;max-width:520px;">

    <div style="text-align:center;margin-bottom:2rem;">
      <div style="font-size:3rem;margin-bottom:0.5rem;">🎓</div>
      <h1 style="font-family:'Cinzel Decorative',serif;font-size:1.8rem;background:linear-gradient(135deg,var(--gold),var(--white));-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
        Kreye Kont Ou
      </h1>
      <p style="color:var(--text-muted);margin-top:0.5rem;">Antre kòm etidyan BiroTech jodi a — Gratis!</p>
    </div>

    <?php if($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card">
      <form method="POST" id="registerForm">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
          <div class="form-group">
            <label class="form-label">👤 Non Fanmi</label>
            <input type="text" name="nom" class="form-control" placeholder="Ritchy" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">👤 Prenon</label>
            <input type="text" name="prenom" class="form-control" placeholder="Derinard" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">📧 Adrès Imel</label>
          <input type="email" name="email" class="form-control" placeholder="ou@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label">📱 Nimewo Telefòn</label>
          <input type="tel" name="telephone" class="form-control" placeholder="+509 3000-0000" value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label">🔒 Modpas</label>
          <div style="position:relative;">
            <input type="password" name="password" class="form-control" placeholder="Omwen 8 karaktè" id="pass1" required>
            <button type="button" onclick="togglePass('pass1',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1.1rem;">👁️</button>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">🔒 Konfime Modpas</label>
          <div style="position:relative;">
            <input type="password" name="password2" class="form-control" placeholder="Repete modpas ou" id="pass2" required>
            <button type="button" onclick="togglePass('pass2',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:1.1rem;">👁️</button>
          </div>
          <div id="passMatch" style="font-size:0.8rem;margin-top:0.3rem;"></div>
        </div>

        <button type="submit" class="btn btn-gold w-100" style="font-size:1rem;padding:1rem;">
          🚀 Kreye Kont & Kòmanse Gratis
        </button>

        <p style="text-align:center;margin-top:1rem;color:var(--text-muted);font-size:0.9rem;">
          Deja gen kont? <a href="login.php" style="color:var(--gold);font-weight:700;">Konekte la a</a>
        </p>
      </form>
    </div>

  </div>
</div>

<script src="js/main.js"></script>
<script>
// Password match indicator
const p1 = document.getElementById('pass1');
const p2 = document.getElementById('pass2');
const msg = document.getElementById('passMatch');
p2.addEventListener('input', () => {
  if(p2.value === '') { msg.textContent = ''; return; }
  if(p1.value === p2.value) {
    msg.textContent = '✅ Modpas yo match!'; msg.style.color = 'var(--success)';
  } else {
    msg.textContent = '❌ Modpas yo pa menm.'; msg.style.color = 'var(--danger)';
  }
});
function togglePass(id, btn) {
  const inp = document.getElementById(id);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.textContent = inp.type === 'password' ? '👁️' : '🙈';
}
</script>
</body>
</html>
