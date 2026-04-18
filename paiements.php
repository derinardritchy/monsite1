<?php
require_once '../includes/config.php';
if(!isAdmin()) redirect('../login.php');
$db = getDB();

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$uid = (int)($_GET['uid'] ?? 0);

if($id && $action) {
    if($action === 'confirme') {
        $db->query("UPDATE paiements SET statut='confirme', date_confirmation=NOW() WHERE id=$id");
        if($uid) $db->query("UPDATE users SET acces_complet=1 WHERE id=$uid AND role='user'");
        setFlash('success','✅ Depo konfime! Aksè bay itilizatè a.');
    } elseif($action === 'rejete') {
        $db->query("UPDATE paiements SET statut='rejete' WHERE id=$id");
        setFlash('danger','❌ Depo rejte.');
    }
    redirect('paiements.php');
}

$flash = getFlash();
$filter = $_GET['filter'] ?? 'en_attente';
$where = $filter !== 'tout' ? "WHERE p.statut='$filter'" : '';
$pays = $db->query("SELECT p.*, u.nom, u.prenom, u.email, u.telephone FROM paiements p JOIN users u ON p.user_id=u.id $where ORDER BY p.date_depot DESC");
?>
<!DOCTYPE html>
<html lang="ht" data-theme="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Paiemen — Admin BiroTech</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
  <a href="../index.php" class="navbar-brand">BiroTech</a>
  <ul class="navbar-links"><li><a href="../logout.php">Dekonekte</a></li><button class="theme-toggle" id="themeToggle">🌙</button></ul>
</nav>
<div class="admin-sidebar">
  <div style="padding:1rem 1.5rem;border-bottom:1px solid var(--border);margin-bottom:0.5rem;">
    <div style="font-family:'Cinzel Decorative',serif;color:var(--gold);font-size:1rem;">⚙️ Admin Panel</div>
  </div>
  <a href="index.php" class="sidebar-link"><span class="sidebar-icon">📊</span> Tablo Bò</a>
  <a href="users.php" class="sidebar-link"><span class="sidebar-icon">👥</span> Itilizatè yo</a>
  <a href="paiements.php" class="sidebar-link active"><span class="sidebar-icon">💰</span> Depo / Paiemen</a>
  <a href="messages.php" class="sidebar-link"><span class="sidebar-icon">✉️</span> Mesaj Prive</a>
  <a href="cours_admin.php" class="sidebar-link"><span class="sidebar-icon">📚</span> Jere Kou yo</a>
  <a href="../index.php" class="sidebar-link"><span class="sidebar-icon">🌐</span> Wè Sit la</a>
</div>
<div class="admin-main">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
    <h2 style="font-family:'Cinzel Decorative',serif;color:var(--gold);">💰 Depo / Paiemen</h2>
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
      <a href="?filter=en_attente" class="btn btn-sm <?= $filter==='en_attente' ? 'btn-gold' : 'btn-outline' ?>">⏳ An Atant</a>
      <a href="?filter=confirme" class="btn btn-sm <?= $filter==='confirme' ? 'btn-gold' : 'btn-outline' ?>">✅ Konfime</a>
      <a href="?filter=rejete" class="btn btn-sm <?= $filter==='rejete' ? 'btn-gold' : 'btn-outline' ?>">❌ Rejte</a>
      <a href="?filter=tout" class="btn btn-sm <?= $filter==='tout' ? 'btn-gold' : 'btn-outline' ?>">Tout</a>
    </div>
  </div>

  <?php if($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><?= $flash['msg'] ?></div><?php endif; ?>

  <div class="card" style="overflow-x:auto;">
    <table class="admin-table">
      <tr><th>Itilizatè</th><th>Montant</th><th>Nimewo Ref.</th><th>Nòt</th><th>Statut</th><th>Dat Depo</th><th>Aksyon</th></tr>
      <?php while($p = $pays->fetch_assoc()): ?>
      <tr>
        <td>
          <strong><?= htmlspecialchars($p['prenom'].' '.$p['nom']) ?></strong><br>
          <span style="font-size:0.8rem;color:var(--text-muted)"><?= htmlspecialchars($p['email']) ?></span><br>
          <span style="font-size:0.8rem;color:var(--blue-light)"><?= htmlspecialchars($p['telephone']) ?></span>
        </td>
        <td style="color:var(--gold);font-weight:700;"><?= number_format($p['montant'],0) ?> G</td>
        <td><code style="color:var(--blue-glow);font-size:0.85rem"><?= htmlspecialchars($p['reference']) ?></code></td>
        <td style="font-size:0.85rem;color:var(--text-muted);max-width:150px;"><?= htmlspecialchars($p['note'] ?? '-') ?></td>
        <td>
          <span class="badge <?= $p['statut']==='confirme' ? 'badge-green' : ($p['statut']==='rejete' ? 'badge-red' : 'badge-gold') ?>">
            <?= $p['statut'] ?>
          </span>
        </td>
        <td style="font-size:0.8rem;color:var(--text-muted)"><?= date('d/m/Y H:i', strtotime($p['date_depot'])) ?></td>
        <td>
          <?php if($p['statut']==='en_attente'): ?>
            <a href="?action=confirme&id=<?= $p['id'] ?>&uid=<?= $p['user_id'] ?>" class="btn btn-sm" style="background:var(--success);color:#000;padding:0.3rem 0.7rem;" onclick="return confirm('Konfime depo sa a?')">✅ Konfime</a>
            <a href="?action=rejete&id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" style="padding:0.3rem 0.7rem;" onclick="return confirm('Rejte?')">❌</a>
          <?php else: ?>
            <span style="color:var(--text-muted);font-size:0.85rem;">—</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>
</div>
<script src="../js/main.js"></script>
</body></html>
