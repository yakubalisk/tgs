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

    // ── Multi-upload ───────────────────────────────────────────
    if ($action === 'add') {
        $uploaded = 0;
        $errors   = [];

        // Support multiple files via images[]
        $files  = $_FILES['images'] ?? [];
        $names  = $_POST['company_names'] ?? [];   // parallel array of company names
        $orders = $_POST['sort_orders']   ?? [];

        if (!empty($files['name'][0])) {
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                // rebuild single-file structure for handleImageUpload
                $_FILES['_single_'] = [
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ];
                try {
                    $imgPath = handleImageUpload('_single_', 'clients');
                    if ($imgPath) {
                        $pdo->prepare("INSERT INTO client_logos (image,alt_text,sort_order) VALUES (?,?,?)")
                            ->execute([$imgPath, trim($names[$i] ?? ''), (int)($orders[$i] ?? 0)]);
                        $uploaded++;
                    }
                } catch (Exception $e) {
                    $errors[] = "File " . ($i+1) . ": " . $e->getMessage();
                }
            }
        }

        if ($uploaded) $msg = "$uploaded logo(s) uploaded successfully.";
        if ($errors)   $err = implode('<br>', $errors);
    }

    // ── Edit company name / order ──────────────────────────────
    if ($action === 'edit') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE client_logos SET alt_text=?, sort_order=? WHERE id=?")
            ->execute([trim($_POST['alt_text'] ?? ''), (int)($_POST['sort_order'] ?? 0), $id]);
        $msg = "Logo updated.";
    }

    // ── Toggle active ──────────────────────────────────────────
    if ($action === 'toggle') {
        $pdo->prepare("UPDATE client_logos SET is_active=1-is_active WHERE id=?")->execute([(int)$_POST['id']]);
        $msg = "Visibility toggled.";
    }

    // ── Delete ─────────────────────────────────────────────────
    if ($action === 'delete') {
        $id  = (int)$_POST['id'];
        $cur = $pdo->prepare("SELECT image FROM client_logos WHERE id=?");
        $cur->execute([$id]);
        $cur = $cur->fetch(PDO::FETCH_ASSOC);
        if ($cur) deleteUploadedFile($cur['image']);
        $pdo->prepare("DELETE FROM client_logos WHERE id=?")->execute([$id]);
        $msg = "Logo deleted.";
    }
}

$logos = $pdo->query("SELECT * FROM client_logos ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);

// Edit mode
$editLogo = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM client_logos WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $editLogo = $s->fetch(PDO::FETCH_ASSOC);
}
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:1.4rem;font-weight:700;">Client / Partner Logos</h1>
</div>

<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
<?php if ($err):  ?><div class="alert alert-danger"><?= $err  ?></div><?php endif; ?>

<!-- ── MULTI-UPLOAD FORM ─────────────────────────────────────── -->
<div class="admin-card" style="margin-bottom:28px;">
  <div class="admin-card-header">
    <h2>Upload Partner Logos</h2>
    <small style="color:#64748b;font-size:.78rem;">You can upload multiple logos at once</small>
  </div>
  <div class="admin-card-body">
    <form method="POST" enctype="multipart/form-data" id="uploadForm">
      <input type="hidden" name="action" value="add">

      <!-- Dynamic rows: each row = one logo -->
      <div id="logoRows">
        <!-- Row template (row 0) -->
        <div class="logo-row" style="display:grid;grid-template-columns:1fr 1fr 100px 36px;gap:12px;align-items:end;margin-bottom:12px;" id="row0">
          <div class="form-group" style="margin:0;">
            <label class="form-label">Logo Image</label>
            <input type="file" name="images[]" class="form-control" accept="image/*" required>
          </div>
          <div class="form-group" style="margin:0;">
            <label class="form-label">Company / Partner Name</label>
            <input type="text" name="company_names[]" class="form-control" placeholder="e.g. Infosys Ltd">
          </div>
          <div class="form-group" style="margin:0;">
            <label class="form-label">Order</label>
            <input type="number" name="sort_orders[]" class="form-control" value="0">
          </div>
          <div style="padding-bottom:2px;">
            <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;" title="Remove">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:12px;margin-top:4px;">
        <button type="button" onclick="addRow()" class="btn btn-outline">
          <i class="fas fa-plus"></i> Add Another Logo
        </button>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-upload"></i> Upload All
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ── EDIT FORM (shown when ?edit=id) ──────────────────────── -->
<?php if ($editLogo): ?>
<div class="admin-card" style="margin-bottom:28px;border:2px solid #3b82f6;">
  <div class="admin-card-header" style="background:#eff6ff;">
    <h2 style="color:#1d4ed8;">Edit Logo Details</h2>
  </div>
  <div class="admin-card-body">
    <form method="POST" style="display:flex;gap:16px;align-items:end;flex-wrap:wrap;">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" value="<?= $editLogo['id'] ?>">
      <img src="../<?= htmlspecialchars($editLogo['image']) ?>" style="height:56px;border-radius:8px;border:1px solid #e2e8f0;object-fit:contain;background:#f8fafc;padding:4px;">
      <div class="form-group" style="margin:0;flex:1;min-width:200px;">
        <label class="form-label">Company / Partner Name</label>
        <input type="text" name="alt_text" class="form-control" value="<?= htmlspecialchars($editLogo['alt_text']) ?>">
      </div>
      <div class="form-group" style="margin:0;width:120px;">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" class="form-control" value="<?= $editLogo['sort_order'] ?>">
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
      <a href="client_logos.php" class="btn btn-outline">Cancel</a>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- ── LOGOS GRID ────────────────────────────────────────────── -->
<div class="admin-card">
  <div class="admin-card-header">
    <h2>All Partners (<?= count($logos) ?>)</h2>
    <span style="font-size:.78rem;color:#64748b;"><?= count(array_filter($logos, fn($l) => $l['is_active'])) ?> visible on homepage</span>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <?php if (empty($logos)): ?>
      <p style="padding:32px;color:#64748b;text-align:center;">No logos yet. Upload your first partner logo above.</p>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th style="width:60px;">#</th>
            <th style="width:100px;">Preview</th>
            <th>Company Name</th>
            <th style="width:80px;">Order</th>
            <th style="width:90px;">Status</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($logos as $logo): ?>
          <tr>
            <td style="color:#94a3b8;"><?= $logo['id'] ?></td>
            <td>
              <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:6px;display:inline-flex;align-items:center;justify-content:center;width:80px;height:48px;">
                <img src="../<?= htmlspecialchars($logo['image']) ?>"
                     alt="<?= htmlspecialchars($logo['alt_text']) ?>"
                     style="max-height:36px;max-width:68px;object-fit:contain;<?= !$logo['is_active'] ? 'opacity:.4;filter:grayscale(1);' : '' ?>">
              </div>
            </td>
            <td>
              <strong style="<?= !$logo['is_active'] ? 'color:#94a3b8;' : '' ?>">
                <?= htmlspecialchars($logo['alt_text'] ?: '—') ?>
              </strong>
            </td>
            <td style="color:#64748b;"><?= $logo['sort_order'] ?></td>
            <td>
              <span class="badge <?= $logo['is_active'] ? 'badge-green' : 'badge-red' ?>">
                <?= $logo['is_active'] ? 'Visible' : 'Hidden' ?>
              </span>
            </td>
            <td style="text-align:right;">
              <a href="?edit=<?= $logo['id'] ?>" class="btn btn-warning btn-sm" title="Edit name/order">
                <i class="fas fa-edit"></i>
              </a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= $logo['id'] ?>">
                <button type="submit" class="btn btn-outline btn-sm" title="<?= $logo['is_active'] ? 'Hide' : 'Show' ?>">
                  <i class="fas fa-<?= $logo['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                </button>
              </form>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this logo permanently?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $logo['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
let rowCount = 1;

function addRow() {
    const container = document.getElementById('logoRows');
    const div = document.createElement('div');
    div.className = 'logo-row';
    div.style.cssText = 'display:grid;grid-template-columns:1fr 1fr 100px 36px;gap:12px;align-items:end;margin-bottom:12px;';
    div.id = 'row' + rowCount;
    div.innerHTML = `
      <div class="form-group" style="margin:0;">
        <input type="file" name="images[]" class="form-control" accept="image/*" required>
      </div>
      <div class="form-group" style="margin:0;">
        <input type="text" name="company_names[]" class="form-control" placeholder="Company / Partner Name">
      </div>
      <div class="form-group" style="margin:0;">
        <input type="number" name="sort_orders[]" class="form-control" value="${rowCount}">
      </div>
      <div>
        <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm"
                style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-minus"></i>
        </button>
      </div>
    `;
    container.appendChild(div);
    rowCount++;
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.logo-row');
    if (rows.length > 1) {
        btn.closest('.logo-row').remove();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
