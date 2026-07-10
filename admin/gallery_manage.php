<?php
require_once '../middleware.php';
require_once '../function.php';
require_once 'upload_helper.php';
include 'includes/header.php';

$db  = new Database();
$pdo = $db->con;
$msg = '';
$err = '';

// ── Fetch distinct categories ──────────────────────────────────
$allCategories = $pdo->query("SELECT DISTINCT category FROM gallery WHERE category != '' ORDER BY category")
                     ->fetchAll(PDO::FETCH_COLUMN);

// ── POST HANDLERS ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Multi-upload
    if ($action === 'add') {
        $uploaded = 0;
        $errors   = [];
        $files    = $_FILES['images'] ?? [];
        $titles   = $_POST['titles']     ?? [];
        $cats     = $_POST['categories'] ?? [];
        $orders   = $_POST['sort_orders'] ?? [];

        if (!empty($files['name'][0])) {
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                $_FILES['_gimg_'] = [
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ];
                try {
                    $imgPath = handleImageUpload('_gimg_', 'gallery');
                    if ($imgPath) {
                        $pdo->prepare("INSERT INTO gallery (image,title,category,sort_order) VALUES (?,?,?,?)")
                            ->execute([
                                $imgPath,
                                trim($titles[$i]   ?? ''),
                                trim($cats[$i]     ?? 'General'),
                                (int)($orders[$i]  ?? 0),
                            ]);
                        $uploaded++;
                    }
                } catch (Exception $e) {
                    $errors[] = "File " . ($i+1) . ": " . $e->getMessage();
                }
            }
        }
        if ($uploaded) $msg = "$uploaded image(s) uploaded.";
        if ($errors)   $err = implode('<br>', $errors);
    }

    // Edit title/category/order
    if ($action === 'edit') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE gallery SET title=?, category=?, sort_order=? WHERE id=?")
            ->execute([
                trim($_POST['title']      ?? ''),
                trim($_POST['category']   ?? 'General'),
                (int)($_POST['sort_order'] ?? 0),
                $id,
            ]);
        $msg = "Image updated.";
    }

    // Toggle visibility
    if ($action === 'toggle') {
        $pdo->prepare("UPDATE gallery SET is_active=1-is_active WHERE id=?")->execute([(int)$_POST['id']]);
        $msg = "Visibility toggled.";
    }

    // Delete
    if ($action === 'delete') {
        $id  = (int)$_POST['id'];
        $cur = $pdo->prepare("SELECT image FROM gallery WHERE id=?");
        $cur->execute([$id]);
        $cur = $cur->fetch(PDO::FETCH_ASSOC);
        if ($cur) deleteUploadedFile($cur['image']);
        $pdo->prepare("DELETE FROM gallery WHERE id=?")->execute([$id]);
        $msg = "Image deleted.";
    }
}

$images  = $pdo->query("SELECT * FROM gallery ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
$editImg = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM gallery WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $editImg = $s->fetch(PDO::FETCH_ASSOC);
}

$totalActive = count(array_filter($images, fn($i) => $i['is_active']));
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:1.4rem;font-weight:700;">Gallery</h1>
  <span style="font-size:.82rem;color:#64748b;"><?= $totalActive ?> visible · <?= count($images) ?> total</span>
</div>

<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
<?php if ($err):  ?><div class="alert alert-danger" ><?= $err  ?></div><?php endif; ?>

<!-- ── UPLOAD FORM ──────────────────────────────────────────── -->
<div class="admin-card" style="margin-bottom:28px;">
  <div class="admin-card-header">
    <h2>Upload Gallery Images</h2>
    <small style="color:#64748b;font-size:.78rem;">Upload multiple images at once</small>
  </div>
  <div class="admin-card-body">
    <form method="POST" enctype="multipart/form-data" id="uploadForm">
      <input type="hidden" name="action" value="add">

      <div id="galleryRows">
        <div class="gal-row" style="display:grid;grid-template-columns:1.5fr 1fr 1fr 90px 36px;gap:12px;align-items:end;margin-bottom:10px;">
          <div class="form-group" style="margin:0;">
            <label class="form-label">Image</label>
            <input type="file" name="images[]" class="form-control" accept="image/*" required>
          </div>
          <div class="form-group" style="margin:0;">
            <label class="form-label">Caption / Title</label>
            <input type="text" name="titles[]" class="form-control" placeholder="Optional caption">
          </div>
          <div class="form-group" style="margin:0;">
            <label class="form-label">Category</label>
            <input type="text" name="categories[]" class="form-control" placeholder="e.g. Events" value="General" list="catList">
            <datalist id="catList">
              <?php foreach ($allCategories as $c): ?>
              <option value="<?= htmlspecialchars($c) ?>">
              <?php endforeach; ?>
              <option value="Events">
              <option value="Team">
              <option value="Office">
              <option value="Projects">
              <option value="General">
            </datalist>
          </div>
          <div class="form-group" style="margin:0;">
            <label class="form-label">Order</label>
            <input type="number" name="sort_orders[]" class="form-control" value="0">
          </div>
          <div style="padding-bottom:2px;">
            <button type="button" onclick="removeGalRow(this)" class="btn btn-danger btn-sm"
                    style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;" title="Remove">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:12px;margin-top:8px;">
        <button type="button" onclick="addGalRow()" class="btn btn-outline">
          <i class="fas fa-plus"></i> Add Another Image
        </button>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-upload"></i> Upload All
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ── EDIT FORM ────────────────────────────────────────────── -->
<?php if ($editImg): ?>
<div class="admin-card" style="margin-bottom:28px;border:2px solid #3b82f6;">
  <div class="admin-card-header" style="background:#eff6ff;">
    <h2 style="color:#1d4ed8;">Edit Image Details</h2>
  </div>
  <div class="admin-card-body">
    <form method="POST" style="display:flex;gap:16px;align-items:end;flex-wrap:wrap;">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" value="<?= $editImg['id'] ?>">
      <img src="../<?= htmlspecialchars($editImg['image']) ?>"
           style="height:70px;width:100px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;">
      <div class="form-group" style="margin:0;flex:1;min-width:180px;">
        <label class="form-label">Caption / Title</label>
        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editImg['title']) ?>">
      </div>
      <div class="form-group" style="margin:0;min-width:160px;">
        <label class="form-label">Category</label>
        <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($editImg['category']) ?>" list="catList2">
        <datalist id="catList2">
          <?php foreach ($allCategories as $c): ?>
          <option value="<?= htmlspecialchars($c) ?>">
          <?php endforeach; ?>
        </datalist>
      </div>
      <div class="form-group" style="margin:0;width:110px;">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" class="form-control" value="<?= $editImg['sort_order'] ?>">
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
      <a href="gallery_manage.php" class="btn btn-outline">Cancel</a>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- ── FILTER TABS ───────────────────────────────────────────── -->
<?php
$filterCat = $_GET['cat'] ?? 'all';
$cats = ['all' => 'All'];
foreach ($images as $img) {
    $c = $img['category'] ?: 'General';
    $cats[$c] = $c;
}
?>
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
  <?php foreach ($cats as $key => $label): ?>
    <a href="?cat=<?= urlencode($key) ?>"
       class="btn btn-sm <?= $filterCat === $key ? 'btn-primary' : 'btn-outline' ?>">
      <?= htmlspecialchars($label) ?>
    </a>
  <?php endforeach; ?>
</div>

<!-- ── IMAGE GRID ────────────────────────────────────────────── -->
<div class="admin-card">
  <div class="admin-card-header">
    <h2>Gallery Images (<?= count($images) ?>)</h2>
  </div>
  <div class="admin-card-body">
    <?php
    $filtered = array_filter($images, function($img) use ($filterCat) {
        return $filterCat === 'all' || $img['category'] === $filterCat;
    });
    ?>
    <?php if (empty($filtered)): ?>
      <p style="color:#64748b;text-align:center;padding:32px;">No images yet. Upload your first gallery image above.</p>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;">
      <?php foreach ($filtered as $img): ?>
      <div style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;background:#f8fafc;position:relative;display:flex;flex-direction:column;">
        <!-- Thumbnail -->
        <div style="position:relative;overflow:hidden;height:130px;background:#f1f5f9;">
          <img src="../<?= htmlspecialchars($img['image']) ?>"
               style="width:100%;height:100%;object-fit:cover;<?= !$img['is_active'] ? 'opacity:.4;filter:grayscale(1);' : '' ?>"
               alt="<?= htmlspecialchars($img['title']) ?>">
          <!-- Category badge -->
          <span style="position:absolute;top:6px;left:6px;background:rgba(29,78,216,.85);color:#fff;font-size:.65rem;font-weight:700;padding:2px 8px;border-radius:20px;backdrop-filter:blur(4px);">
            <?= htmlspecialchars($img['category'] ?: 'General') ?>
          </span>
          <?php if (!$img['is_active']): ?>
          <span style="position:absolute;top:6px;right:6px;background:#ef4444;color:#fff;font-size:.6rem;font-weight:700;padding:2px 6px;border-radius:4px;">HIDDEN</span>
          <?php endif; ?>
        </div>
        <!-- Info -->
        <div style="padding:8px 10px;flex:1;">
          <p style="font-size:.78rem;font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
            <?= htmlspecialchars($img['title'] ?: 'Untitled') ?>
          </p>
          <p style="font-size:.68rem;color:#94a3b8;">Order: <?= $img['sort_order'] ?></p>
        </div>
        <!-- Actions -->
        <div style="padding:6px 10px 10px;display:flex;gap:6px;">
          <a href="?edit=<?= $img['id'] ?><?= $filterCat !== 'all' ? '&cat='.urlencode($filterCat) : '' ?>"
             class="btn btn-warning btn-sm" style="flex:1;justify-content:center;" title="Edit">
            <i class="fas fa-edit"></i>
          </a>
          <form method="POST" style="flex:1;">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= $img['id'] ?>">
            <button type="submit" class="btn btn-outline btn-sm" style="width:100%;" title="Toggle">
              <i class="fas fa-<?= $img['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
            </button>
          </form>
          <form method="POST" style="flex:1;" onsubmit="return confirm('Delete this image?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $img['id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm" style="width:100%;" title="Delete">
              <i class="fas fa-trash"></i>
            </button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
let galRowCount = 1;
function addGalRow() {
    const container = document.getElementById('galleryRows');
    const div = document.createElement('div');
    div.className = 'gal-row';
    div.style.cssText = 'display:grid;grid-template-columns:1.5fr 1fr 1fr 90px 36px;gap:12px;align-items:end;margin-bottom:10px;';
    div.innerHTML = `
      <div class="form-group" style="margin:0;">
        <input type="file" name="images[]" class="form-control" accept="image/*" required>
      </div>
      <div class="form-group" style="margin:0;">
        <input type="text" name="titles[]" class="form-control" placeholder="Caption">
      </div>
      <div class="form-group" style="margin:0;">
        <input type="text" name="categories[]" class="form-control" placeholder="Category" value="General" list="catList">
      </div>
      <div class="form-group" style="margin:0;">
        <input type="number" name="sort_orders[]" class="form-control" value="${galRowCount}">
      </div>
      <div>
        <button type="button" onclick="removeGalRow(this)" class="btn btn-danger btn-sm"
                style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-minus"></i>
        </button>
      </div>
    `;
    container.appendChild(div);
    galRowCount++;
}
function removeGalRow(btn) {
    const rows = document.querySelectorAll('.gal-row');
    if (rows.length > 1) btn.closest('.gal-row').remove();
}
</script>

<?php include 'includes/footer.php'; ?>
