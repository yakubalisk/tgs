<?php
require_once '../middleware.php';
require_once '../function.php';
require_once 'upload_helper.php';
include 'includes/header.php';

$db  = new Database();
$pdo = $db->con;
$msg = '';
$err = '';

// ── Handle POST actions ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD new slide
    if ($action === 'add') {
        try {
            $imgPath = handleImageUpload('image', 'banners');
            if (!$imgPath) throw new Exception("Please choose a banner image.");
            $stmt = $pdo->prepare("INSERT INTO hero_slides (image,title,subtitle,btn1_label,btn1_link,btn2_label,btn2_link,sort_order,is_active)
                                   VALUES (?,?,?,?,?,?,?,?,1)");
            $stmt->execute([
                $imgPath,
                trim($_POST['title']    ?? ''),
                trim($_POST['subtitle'] ?? ''),
                trim($_POST['btn1_label'] ?? ''),
                trim($_POST['btn1_link']  ?? ''),
                trim($_POST['btn2_label'] ?? ''),
                trim($_POST['btn2_link']  ?? ''),
                (int)($_POST['sort_order'] ?? 0),
            ]);
            $msg = "Slide added successfully.";
        } catch (Exception $e) { $err = $e->getMessage(); }
    }

    // EDIT existing slide
    if ($action === 'edit') {
        $id = (int)$_POST['id'];
        try {
            $current = $pdo->prepare("SELECT image FROM hero_slides WHERE id=?");
            $current->execute([$id]);
            $row = $current->fetch(PDO::FETCH_ASSOC);
            $imgPath = handleImageUpload('image', 'banners');
            if (!$imgPath) $imgPath = $row['image']; else deleteUploadedFile($row['image']);
            $stmt = $pdo->prepare("UPDATE hero_slides SET image=?,title=?,subtitle=?,btn1_label=?,btn1_link=?,btn2_label=?,btn2_link=?,sort_order=? WHERE id=?");
            $stmt->execute([
                $imgPath,
                trim($_POST['title']    ?? ''),
                trim($_POST['subtitle'] ?? ''),
                trim($_POST['btn1_label'] ?? ''),
                trim($_POST['btn1_link']  ?? ''),
                trim($_POST['btn2_label'] ?? ''),
                trim($_POST['btn2_link']  ?? ''),
                (int)($_POST['sort_order'] ?? 0),
                $id,
            ]);
            $msg = "Slide updated successfully.";
        } catch (Exception $e) { $err = $e->getMessage(); }
    }

    // TOGGLE active
    if ($action === 'toggle') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE hero_slides SET is_active = 1-is_active WHERE id=?")->execute([$id]);
        $msg = "Slide status toggled.";
    }

    // DELETE slide
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $row = $pdo->prepare("SELECT image FROM hero_slides WHERE id=?");
        $row->execute([$id]);
        $row = $row->fetch(PDO::FETCH_ASSOC);
        if ($row) deleteUploadedFile($row['image']);
        $pdo->prepare("DELETE FROM hero_slides WHERE id=?")->execute([$id]);
        $msg = "Slide deleted.";
    }
}

// ── Fetch slides ────────────────────────────────────────────────
$slides = $pdo->query("SELECT * FROM hero_slides ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Edit mode?
$editSlide = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM hero_slides WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $editSlide = $s->fetch(PDO::FETCH_ASSOC);
}
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <h1 style="font-size:1.4rem;font-weight:700;">Hero Slides</h1>
  <a href="hero_slides.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Slide</a>
</div>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<!-- ADD / EDIT FORM -->
<div class="admin-card" style="margin-bottom:28px;">
  <div class="admin-card-header">
    <h2><?= $editSlide ? 'Edit Slide' : 'Add New Slide' ?></h2>
  </div>
  <div class="admin-card-body">
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="<?= $editSlide ? 'edit' : 'add' ?>">
      <?php if ($editSlide): ?><input type="hidden" name="id" value="<?= $editSlide['id'] ?>"><?php endif; ?>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Banner Image <?= $editSlide ? '(leave blank to keep current)' : '(required)' ?></label>
          <input type="file" name="image" class="form-control" accept="image/*">
          <?php if ($editSlide && $editSlide['image']): ?>
            <img src="../<?= htmlspecialchars($editSlide['image']) ?>" style="height:80px;border-radius:6px;margin-top:8px;">
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" placeholder="e.g. Global Talent, Local Expertise" value="<?= htmlspecialchars($editSlide['title'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($editSlide['sort_order'] ?? 0) ?>">
        </div>
        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Subtitle / Description</label>
          <textarea name="subtitle" class="form-control"><?= htmlspecialchars($editSlide['subtitle'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Button 1 Label</label>
          <input type="text" name="btn1_label" class="form-control" placeholder="Explore Services" value="<?= htmlspecialchars($editSlide['btn1_label'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Button 1 Link</label>
          <input type="text" name="btn1_link" class="form-control" placeholder="#services" value="<?= htmlspecialchars($editSlide['btn1_link'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Button 2 Label</label>
          <input type="text" name="btn2_label" class="form-control" placeholder="Get Consultation" value="<?= htmlspecialchars($editSlide['btn2_label'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Button 2 Link</label>
          <input type="text" name="btn2_link" class="form-control" placeholder="contact.php" value="<?= htmlspecialchars($editSlide['btn2_link'] ?? '') ?>">
        </div>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> <?= $editSlide ? 'Update Slide' : 'Add Slide' ?>
      </button>
      <?php if ($editSlide): ?>
        <a href="hero_slides.php" class="btn btn-outline" style="margin-left:8px;">Cancel</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<!-- SLIDES LIST -->
<div class="admin-card">
  <div class="admin-card-header"><h2>All Slides (<?= count($slides) ?>)</h2></div>
  <div class="admin-card-body" style="padding:0;">
    <?php if (empty($slides)): ?>
      <p style="padding:24px;color:#64748b;">No slides yet. Add your first banner above.</p>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Order</th><th>Preview</th><th>Title</th><th>Subtitle</th><th>Buttons</th><th>Status</th><th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($slides as $slide): ?>
          <tr>
            <td><?= $slide['sort_order'] ?></td>
            <td>
              <img src="../<?= htmlspecialchars($slide['image']) ?>" class="thumb" alt="">
            </td>
            <td><?= htmlspecialchars($slide['title'] ?: '—') ?></td>
            <td style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($slide['subtitle'] ?: '—') ?></td>
            <td>
              <?php if ($slide['btn1_label']): ?>
                <span style="font-size:.72rem;background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:4px;"><?= htmlspecialchars($slide['btn1_label']) ?></span>
              <?php endif; ?>
              <?php if ($slide['btn2_label']): ?>
                <span style="font-size:.72rem;background:#f3e8ff;color:#7e22ce;padding:2px 8px;border-radius:4px;"><?= htmlspecialchars($slide['btn2_label']) ?></span>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge <?= $slide['is_active'] ? 'badge-green' : 'badge-red' ?>">
                <?= $slide['is_active'] ? 'Active' : 'Hidden' ?>
              </span>
            </td>
            <td style="text-align:right;">
              <a href="?edit=<?= $slide['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= $slide['id'] ?>">
                <button type="submit" class="btn btn-outline btn-sm" title="Toggle">
                  <i class="fas fa-<?= $slide['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                </button>
              </form>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this slide?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $slide['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
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

<?php include 'includes/footer.php'; ?>
