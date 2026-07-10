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
            $featuresRaw = trim($_POST['features'] ?? '');
            $featLines   = array_filter(array_map('trim', explode("\n", $featuresRaw)));
            $featJson    = json_encode(array_values($featLines));

            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO services (icon,title,description,features,sort_order) VALUES (?,?,?,?,?)");
                $stmt->execute([
                    trim($_POST['icon'] ?? 'fa-briefcase'),
                    trim($_POST['title'] ?? ''),
                    trim($_POST['description'] ?? ''),
                    $featJson,
                    (int)($_POST['sort_order'] ?? 0),
                ]);
                $msg = "Service added.";
            } else {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("UPDATE services SET icon=?,title=?,description=?,features=?,sort_order=? WHERE id=?");
                $stmt->execute([
                    trim($_POST['icon'] ?? 'fa-briefcase'),
                    trim($_POST['title'] ?? ''),
                    trim($_POST['description'] ?? ''),
                    $featJson,
                    (int)($_POST['sort_order'] ?? 0),
                    $id,
                ]);
                $msg = "Service updated.";
            }
        } catch (Exception $e) { $err = $e->getMessage(); }
    }

    if ($action === 'toggle') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE services SET is_active=1-is_active WHERE id=?")->execute([$id]);
        $msg = "Status toggled.";
    }

    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM services WHERE id=?")->execute([(int)$_POST['id']]);
        $msg = "Service deleted.";
    }
}

$services = $pdo->query("SELECT * FROM services ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);

$editSvc = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM services WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $editSvc = $s->fetch(PDO::FETCH_ASSOC);
}

$faIcons = ['fa-user','fa-file','fa-briefcase','fa-shield','fa-graduation-cap','fa-globe','fa-star','fa-heart','fa-cogs','fa-chart-bar','fa-handshake','fa-laptop'];
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:1.4rem;font-weight:700;">Services</h1>
  <a href="services_manage.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Service</a>
</div>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<!-- FORM -->
<div class="admin-card" style="margin-bottom:28px;">
  <div class="admin-card-header"><h2><?= $editSvc ? 'Edit Service' : 'Add New Service' ?></h2></div>
  <div class="admin-card-body">
    <form method="POST">
      <input type="hidden" name="action" value="<?= $editSvc ? 'edit' : 'add' ?>">
      <?php if ($editSvc): ?><input type="hidden" name="id" value="<?= $editSvc['id'] ?>"><?php endif; ?>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="form-group">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($editSvc['title'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Icon (Font Awesome class)</label>
          <div style="display:flex;gap:8px;align-items:center;">
            <input type="text" name="icon" id="iconInput" class="form-control" placeholder="fa-briefcase" value="<?= htmlspecialchars($editSvc['icon'] ?? 'fa-briefcase') ?>">
            <i id="iconPreview" class="fas <?= htmlspecialchars($editSvc['icon'] ?? 'fa-briefcase') ?>" style="font-size:1.4rem;color:#1d4ed8;min-width:24px;"></i>
          </div>
          <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:8px;">
            <?php foreach ($faIcons as $ic): ?>
              <button type="button" onclick="document.getElementById('iconInput').value='<?=$ic?>';document.getElementById('iconPreview').className='fas <?=$ic?>';"
                      style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:6px 8px;cursor:pointer;font-size:.9rem;" title="<?=$ic?>">
                <i class="fas <?=$ic?>"></i>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control"><?= htmlspecialchars($editSvc['description'] ?? '') ?></textarea>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Features <small style="color:#64748b;">(one per line)</small></label>
          <textarea name="features" class="form-control" style="min-height:110px;"><?php
            if ($editSvc) echo htmlspecialchars(implode("\n", json_decode($editSvc['features'], true) ?? []));
          ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($editSvc['sort_order'] ?? 0) ?>">
        </div>
      </div>

      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $editSvc ? 'Update' : 'Add' ?> Service</button>
      <?php if ($editSvc): ?><a href="services_manage.php" class="btn btn-outline" style="margin-left:8px;">Cancel</a><?php endif; ?>
    </form>
  </div>
</div>

<!-- LIST -->
<div class="admin-card">
  <div class="admin-card-header"><h2>All Services (<?= count($services) ?>)</h2></div>
  <div class="admin-card-body" style="padding:0;">
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>Icon</th><th>Title</th><th>Description</th><th>Features</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
        <tbody>
          <?php foreach ($services as $svc): ?>
          <tr>
            <td><?= $svc['sort_order'] ?></td>
            <td><i class="fas <?= htmlspecialchars($svc['icon']) ?>" style="font-size:1.2rem;color:#1d4ed8;"></i></td>
            <td><strong><?= htmlspecialchars($svc['title']) ?></strong></td>
            <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($svc['description']) ?></td>
            <td>
              <?php $feats = json_decode($svc['features'], true) ?: []; ?>
              <span style="font-size:.72rem;color:#64748b;"><?= count($feats) ?> items</span>
            </td>
            <td><span class="badge <?= $svc['is_active'] ? 'badge-green' : 'badge-red' ?>"><?= $svc['is_active'] ? 'Active' : 'Hidden' ?></span></td>
            <td style="text-align:right;">
              <a href="?edit=<?= $svc['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $svc['id'] ?>">
                <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-<?= $svc['is_active'] ? 'eye-slash' : 'eye' ?>"></i></button>
              </form>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this service?');">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $svc['id'] ?>">
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
