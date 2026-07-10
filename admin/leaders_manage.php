<?php
require_once '../middleware.php';
require_once '../function.php';
require_once 'upload_helper.php';
include 'includes/header.php';

$db  = new Database();
$pdo = $db->con;
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        try {
            $expertiseRaw = trim($_POST['expertise'] ?? '');
            $expLines     = array_filter(array_map('trim', explode("\n", $expertiseRaw)));
            $expJson      = json_encode(array_values($expLines));

            if ($action === 'add') {
                $imgPath = handleImageUpload('image', 'leaders');
                $stmt = $pdo->prepare("INSERT INTO leaders (name,position,image,description,expertise,sort_order) VALUES (?,?,?,?,?,?)");
                $stmt->execute([
                    trim($_POST['name']     ?? ''),
                    trim($_POST['position'] ?? ''),
                    $imgPath ?? '',
                    trim($_POST['description'] ?? ''),
                    $expJson,
                    (int)($_POST['sort_order'] ?? 0),
                ]);
                $msg = "Leader added.";
            } else {
                $id  = (int)$_POST['id'];
                $cur = $pdo->prepare("SELECT image FROM leaders WHERE id=?");
                $cur->execute([$id]);
                $cur = $cur->fetch(PDO::FETCH_ASSOC);
                $imgPath = handleImageUpload('image', 'leaders');
                if (!$imgPath) $imgPath = $cur['image']; else deleteUploadedFile($cur['image']);
                $stmt = $pdo->prepare("UPDATE leaders SET name=?,position=?,image=?,description=?,expertise=?,sort_order=? WHERE id=?");
                $stmt->execute([
                    trim($_POST['name']     ?? ''),
                    trim($_POST['position'] ?? ''),
                    $imgPath,
                    trim($_POST['description'] ?? ''),
                    $expJson,
                    (int)($_POST['sort_order'] ?? 0),
                    $id,
                ]);
                $msg = "Leader updated.";
            }
        } catch (Exception $e) { $err = $e->getMessage(); }
    }

    if ($action === 'toggle') {
        $pdo->prepare("UPDATE leaders SET is_active=1-is_active WHERE id=?")->execute([(int)$_POST['id']]);
        $msg = "Status toggled.";
    }

    if ($action === 'delete') {
        $id  = (int)$_POST['id'];
        $cur = $pdo->prepare("SELECT image FROM leaders WHERE id=?");
        $cur->execute([$id]);
        $cur = $cur->fetch(PDO::FETCH_ASSOC);
        if ($cur) deleteUploadedFile($cur['image']);
        $pdo->prepare("DELETE FROM leaders WHERE id=?")->execute([$id]);
        $msg = "Leader deleted.";
    }
}

$leaders = $pdo->query("SELECT * FROM leaders ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
$editLdr  = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM leaders WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $editLdr = $s->fetch(PDO::FETCH_ASSOC);
}
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:1.4rem;font-weight:700;">Leadership Team</h1>
  <a href="leaders_manage.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Leader</a>
</div>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<!-- FORM -->
<div class="admin-card" style="margin-bottom:28px;">
  <div class="admin-card-header"><h2><?= $editLdr ? 'Edit Leader' : 'Add Leader' ?></h2></div>
  <div class="admin-card-body">
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="<?= $editLdr ? 'edit' : 'add' ?>">
      <?php if ($editLdr): ?><input type="hidden" name="id" value="<?= $editLdr['id'] ?>"><?php endif; ?>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="form-group">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editLdr['name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Position / Title</label>
          <input type="text" name="position" class="form-control" value="<?= htmlspecialchars($editLdr['position'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Photo <?= $editLdr ? '(leave blank to keep current)' : '' ?></label>
          <input type="file" name="image" class="form-control" accept="image/*">
          <?php if ($editLdr && $editLdr['image']): ?>
            <img src="../<?= htmlspecialchars($editLdr['image']) ?>" style="height:72px;width:72px;object-fit:cover;border-radius:50%;margin-top:8px;border:2px solid #3b82f6;">
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($editLdr['sort_order'] ?? 0) ?>">
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Description / Bio</label>
          <textarea name="description" class="form-control" style="min-height:100px;"><?= htmlspecialchars($editLdr['description'] ?? '') ?></textarea>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Expertise Tags <small style="color:#64748b;">(one per line)</small></label>
          <textarea name="expertise" class="form-control"><?php
            if ($editLdr) echo htmlspecialchars(implode("\n", json_decode($editLdr['expertise'], true) ?? []));
          ?></textarea>
        </div>
      </div>

      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $editLdr ? 'Update' : 'Add' ?> Leader</button>
      <?php if ($editLdr): ?><a href="leaders_manage.php" class="btn btn-outline" style="margin-left:8px;">Cancel</a><?php endif; ?>
    </form>
  </div>
</div>

<!-- LIST -->
<div class="admin-card">
  <div class="admin-card-header"><h2>All Leaders (<?= count($leaders) ?>)</h2></div>
  <div class="admin-card-body" style="padding:0;">
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>Photo</th><th>Name</th><th>Position</th><th>Expertise</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
        <tbody>
          <?php foreach ($leaders as $ldr): ?>
          <tr>
            <td><?= $ldr['sort_order'] ?></td>
            <td>
              <?php if ($ldr['image']): ?>
                <img src="../<?= htmlspecialchars($ldr['image']) ?>" style="width:44px;height:44px;object-fit:cover;border-radius:50%;border:2px solid #dbeafe;">
              <?php else: ?>
                <div style="width:44px;height:44px;background:#dbeafe;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#1d4ed8;font-weight:700;"><?= strtoupper(substr($ldr['name'],0,1)) ?></div>
              <?php endif; ?>
            </td>
            <td><strong><?= htmlspecialchars($ldr['name']) ?></strong></td>
            <td><?= htmlspecialchars($ldr['position']) ?></td>
            <td><?php $exp = json_decode($ldr['expertise'],true) ?: []; echo '<span style="font-size:.72rem;color:#64748b;">'.count($exp).' tags</span>'; ?></td>
            <td><span class="badge <?= $ldr['is_active'] ? 'badge-green' : 'badge-red' ?>"><?= $ldr['is_active'] ? 'Active' : 'Hidden' ?></span></td>
            <td style="text-align:right;">
              <a href="?edit=<?= $ldr['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $ldr['id'] ?>">
                <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-<?= $ldr['is_active'] ? 'eye-slash' : 'eye' ?>"></i></button>
              </form>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this leader?');">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $ldr['id'] ?>">
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
