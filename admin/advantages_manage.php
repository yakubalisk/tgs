<?php
require_once '../middleware.php';
require_once '../function.php';
include 'includes/header.php';

$db  = new Database();
$pdo = $db->con;
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        try {
            $data = [
                trim($_POST['icon']        ?? 'fa-star'),
                trim($_POST['title']       ?? ''),
                trim($_POST['description'] ?? ''),
                trim($_POST['stats']       ?? ''),
                (int)($_POST['sort_order'] ?? 0),
            ];
            if ($action === 'add') {
                $pdo->prepare("INSERT INTO advantages (icon,title,description,stats,sort_order) VALUES (?,?,?,?,?)")->execute($data);
                $msg = "Advantage added.";
            } else {
                $data[] = (int)$_POST['id'];
                $pdo->prepare("UPDATE advantages SET icon=?,title=?,description=?,stats=?,sort_order=? WHERE id=?")->execute($data);
                $msg = "Advantage updated.";
            }
        } catch (Exception $e) { $err = $e->getMessage(); }
    }

    if ($action === 'toggle') {
        $pdo->prepare("UPDATE advantages SET is_active=1-is_active WHERE id=?")->execute([(int)$_POST['id']]);
        $msg = "Status toggled.";
    }
    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM advantages WHERE id=?")->execute([(int)$_POST['id']]);
        $msg = "Advantage deleted.";
    }
}

$advantages = $pdo->query("SELECT * FROM advantages ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
$editAdv = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM advantages WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $editAdv = $s->fetch(PDO::FETCH_ASSOC);
}
$faIcons = ['fa-award','fa-globe','fa-users','fa-star','fa-trophy','fa-shield','fa-bolt','fa-handshake','fa-chart-line','fa-medal'];
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:1.4rem;font-weight:700;">Why Choose Us</h1>
  <a href="advantages_manage.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Advantage</a>
</div>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<!-- FORM -->
<div class="admin-card" style="margin-bottom:28px;">
  <div class="admin-card-header"><h2><?= $editAdv ? 'Edit Advantage' : 'Add Advantage' ?></h2></div>
  <div class="admin-card-body">
    <form method="POST">
      <input type="hidden" name="action" value="<?= $editAdv ? 'edit' : 'add' ?>">
      <?php if ($editAdv): ?><input type="hidden" name="id" value="<?= $editAdv['id'] ?>"><?php endif; ?>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="form-group">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($editAdv['title'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Stats Badge Text</label>
          <input type="text" name="stats" class="form-control" placeholder="e.g. Since 2015" value="<?= htmlspecialchars($editAdv['stats'] ?? '') ?>">
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control"><?= htmlspecialchars($editAdv['description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Icon (Font Awesome class)</label>
          <div style="display:flex;gap:8px;align-items:center;">
            <input type="text" name="icon" id="advIconInput" class="form-control" value="<?= htmlspecialchars($editAdv['icon'] ?? 'fa-star') ?>">
            <i id="advIconPreview" class="fas <?= htmlspecialchars($editAdv['icon'] ?? 'fa-star') ?>" style="font-size:1.4rem;color:#1d4ed8;min-width:24px;"></i>
          </div>
          <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:8px;">
            <?php foreach ($faIcons as $ic): ?>
              <button type="button" onclick="document.getElementById('advIconInput').value='<?=$ic?>';document.getElementById('advIconPreview').className='fas <?=$ic?>';"
                      style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:6px 8px;cursor:pointer;" title="<?=$ic?>">
                <i class="fas <?=$ic?>"></i>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($editAdv['sort_order'] ?? 0) ?>">
        </div>
      </div>

      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $editAdv ? 'Update' : 'Add' ?></button>
      <?php if ($editAdv): ?><a href="advantages_manage.php" class="btn btn-outline" style="margin-left:8px;">Cancel</a><?php endif; ?>
    </form>
  </div>
</div>

<!-- LIST -->
<div class="admin-card">
  <div class="admin-card-header"><h2>All Advantages (<?= count($advantages) ?>)</h2></div>
  <div class="admin-card-body" style="padding:0;">
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>Icon</th><th>Title</th><th>Stats Badge</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
        <tbody>
          <?php foreach ($advantages as $adv): ?>
          <tr>
            <td><?= $adv['sort_order'] ?></td>
            <td><i class="fas <?= htmlspecialchars($adv['icon']) ?>" style="font-size:1.2rem;color:#1d4ed8;"></i></td>
            <td><strong><?= htmlspecialchars($adv['title']) ?></strong></td>
            <td><span style="background:#dbeafe;color:#1e40af;font-size:.72rem;padding:2px 8px;border-radius:20px;"><?= htmlspecialchars($adv['stats']) ?></span></td>
            <td><span class="badge <?= $adv['is_active'] ? 'badge-green' : 'badge-red' ?>"><?= $adv['is_active'] ? 'Active' : 'Hidden' ?></span></td>
            <td style="text-align:right;">
              <a href="?edit=<?= $adv['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $adv['id'] ?>">
                <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-<?= $adv['is_active'] ? 'eye-slash' : 'eye' ?>"></i></button>
              </form>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $adv['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
