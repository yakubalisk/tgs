<?php
require_once __DIR__ . '/../function.php';
$db = new Database();
$pdo = $db->con;

if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['admin_user'])) {
    header("Location: login.php");
    exit;
}

require_once 'upload_helper.php';

$msg = '';
$err = '';
$tab = $_GET['tab'] ?? 'qb'; // 'qb' or 'categories'

// Helper for Question Bank Document upload
function handleQBDocumentUpload($fileKey) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Document upload error code: " . $_FILES[$fileKey]['error']);
    }

    $allowedMimes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES[$fileKey]['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMimes)) {
        throw new Exception("Invalid document type. Allowed: PDF, Word, Excel.");
    }
    if ($_FILES[$fileKey]['size'] > 15 * 1024 * 1024) {
        throw new Exception("Document too large. Max 15 MB.");
    }

    $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
    $fname = uniqid('qb_', true) . '.' . $ext;
    $dir = __DIR__ . '/../assets/uploads/qb/';
    $dest = $dir . $fname;

    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (!move_uploaded_file($_FILES[$fileKey]['tmp_name'], $dest)) {
        throw new Exception("Failed to save uploaded document.");
    }

    return 'assets/uploads/qb/' . $fname;
}

// Handle POST operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        // --- CATEGORIES CRUD ---
        if ($action === 'add_cat') {
            $name = trim($_POST['name']);
            $sort = (int)($_POST['sort_order'] ?? 0);
            if (empty($name)) throw new Exception("Category name is required.");

            $stmt = $pdo->prepare("INSERT INTO qb_categories (name, sort_order) VALUES (?, ?)");
            $stmt->execute([$name, $sort]);
            $msg = "Category added successfully!";
        }
        elseif ($action === 'edit_cat') {
            $id = (int)$_POST['id'];
            $name = trim($_POST['name']);
            $sort = (int)($_POST['sort_order'] ?? 0);
            $active = isset($_POST['is_active']) ? 1 : 0;
            if (empty($name)) throw new Exception("Category name is required.");

            $stmt = $pdo->prepare("UPDATE qb_categories SET name = ?, sort_order = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $sort, $active, $id]);
            $msg = "Category updated successfully!";
        }
        elseif ($action === 'delete_cat') {
            $id = (int)$_POST['id'];
            // Delete associated file attachments
            $qbs = $pdo->prepare("SELECT file_path FROM question_banks WHERE category_id = ?");
            $qbs->execute([$id]);
            $files = $qbs->fetchAll(PDO::FETCH_ASSOC);
            foreach ($files as $f) {
                if ($f['file_path']) deleteUploadedFile($f['file_path']);
            }
            
            $stmt = $pdo->prepare("DELETE FROM qb_categories WHERE id = ?");
            $stmt->execute([$id]);
            $msg = "Category and its question bank items deleted successfully!";
        }

        // --- QUESTION BANK ITEMS CRUD ---
        elseif ($action === 'add_qb') {
            $catId = (int)$_POST['category_id'];
            $title = trim($_POST['title']);
            $itemType = $_POST['item_type'] ?? 'link';
            $linkUrl = trim($_POST['link_url'] ?? '');
            $sort = (int)($_POST['sort_order'] ?? 0);

            if (empty($title)) throw new Exception("Title is required.");
            if ($catId <= 0) throw new Exception("Select a valid category.");

            $filePath = handleQBDocumentUpload('file_doc');

            $stmt = $pdo->prepare("INSERT INTO question_banks (category_id, title, item_type, file_path, link_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$catId, $title, $itemType, $filePath, $linkUrl, $sort]);
            $msg = "Question Bank item added successfully!";
        }
        elseif ($action === 'edit_qb') {
            $id = (int)$_POST['id'];
            $catId = (int)$_POST['category_id'];
            $title = trim($_POST['title']);
            $itemType = $_POST['item_type'] ?? 'link';
            $linkUrl = trim($_POST['link_url'] ?? '');
            $sort = (int)($_POST['sort_order'] ?? 0);
            $active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($title)) throw new Exception("Title is required.");
            if ($catId <= 0) throw new Exception("Select a valid category.");

            $curr = $pdo->prepare("SELECT file_path FROM question_banks WHERE id = ?");
            $curr->execute([$id]);
            $c = $curr->fetch(PDO::FETCH_ASSOC);

            $filePath = $c['file_path'];
            $newFile = handleQBDocumentUpload('file_doc');
            if ($newFile) {
                if ($c['file_path']) deleteUploadedFile($c['file_path']);
                $filePath = $newFile;
            }

            $stmt = $pdo->prepare("UPDATE question_banks SET category_id = ?, title = ?, item_type = ?, file_path = ?, link_url = ?, sort_order = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$catId, $title, $itemType, $filePath, $linkUrl, $sort, $active, $id]);
            $msg = "Question Bank item updated successfully!";
        }
        elseif ($action === 'delete_qb') {
            $id = (int)$_POST['id'];
            $curr = $pdo->prepare("SELECT file_path FROM question_banks WHERE id = ?");
            $curr->execute([$id]);
            $c = $curr->fetch(PDO::FETCH_ASSOC);

            if ($c['file_path']) deleteUploadedFile($c['file_path']);

            $stmt = $pdo->prepare("DELETE FROM question_banks WHERE id = ?");
            $stmt->execute([$id]);
            $msg = "Question Bank item deleted successfully!";
        }

    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}

// Fetch records
$categories = $pdo->query("SELECT * FROM qb_categories ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
$qbs = $pdo->query("SELECT q.*, cat.name as category_name FROM question_banks q JOIN qb_categories cat ON q.category_id = cat.id ORDER BY cat.sort_order, q.category_id, q.sort_order, q.id")->fetchAll(PDO::FETCH_ASSOC);

// Editing states
$editCat = null;
if (isset($_GET['edit_cat'])) {
    $id = (int)$_GET['edit_cat'];
    $stmt = $pdo->prepare("SELECT * FROM qb_categories WHERE id = ?");
    $stmt->execute([$id]);
    $editCat = $stmt->fetch(PDO::FETCH_ASSOC);
}

$editQB = null;
if (isset($_GET['edit_qb'])) {
    $id = (int)$_GET['edit_qb'];
    $stmt = $pdo->prepare("SELECT * FROM question_banks WHERE id = ?");
    $stmt->execute([$id]);
    $editQB = $stmt->fetch(PDO::FETCH_ASSOC);
}

include 'includes/header.php';
?>

<!-- Tab Selector -->
<div style="display:flex;gap:12px;margin-bottom:20px;border-bottom:1px solid #e2e8f0;padding-bottom:10px;">
  <a href="?tab=qb" class="btn <?= $tab === 'qb' ? 'btn-primary' : 'btn-outline' ?>">
    <i class="fas fa-circle-question"></i> Manage Question Papers (<?= count($qbs) ?>)
  </a>
  <a href="?tab=categories" class="btn <?= $tab === 'categories' ? 'btn-primary' : 'btn-outline' ?>">
    <i class="fas fa-tags"></i> Manage Categories (<?= count($categories) ?>)
  </a>
</div>

<?php if ($msg): ?>
  <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($err): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<!-- ========================================== -->
<!-- SUB-TAB: QUESTION PAPERS                   -->
<!-- ========================================== -->
<?php if ($tab === 'qb'): ?>
<div style="display:grid;grid-template-columns: 1fr 2fr;gap:24px;">

  <!-- Form Column -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h2><?= $editQB ? 'Edit QP / Document' : 'Add QP / Document' ?></h2>
      <?php if ($editQB): ?>
        <a href="?tab=qb" class="btn btn-outline btn-sm">Add New Instead</a>
      <?php endif; ?>
    </div>
    <div class="admin-card-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?= $editQB ? 'edit_qb' : 'add_qb' ?>">
        <?php if ($editQB): ?>
          <input type="hidden" name="id" value="<?= $editQB['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label">Category *</label>
          <select name="category_id" class="form-control" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= ($editQB && $editQB['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Item Title *</label>
          <input type="text" name="title" value="<?= $editQB ? htmlspecialchars($editQB['title']) : '' ?>" class="form-control" placeholder="e.g. Model Question Paper - Level 3" required>
        </div>

        <div class="form-group">
          <label class="form-label">Display Mode *</label>
          <select name="item_type" class="form-control" required>
            <option value="link" <?= ($editQB && $editQB['item_type'] === 'link') ? 'selected' : '' ?>>Simple List Link</option>
            <option value="card" <?= ($editQB && $editQB['item_type'] === 'card') ? 'selected' : '' ?>>Card Grid Box</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Link URL (Redirect / '#' if none)</label>
          <input type="text" name="link_url" value="<?= $editQB ? htmlspecialchars($editQB['link_url']) : '' ?>" class="form-control" placeholder="e.g. #">
        </div>

        <div class="form-group">
          <label class="form-label">Upload Question Paper File (PDF/Word/Excel)</label>
          <input type="file" name="file_doc" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">
          <?php if ($editQB && $editQB['file_path']): ?>
            <div style="margin-top:6px;font-size:0.75rem;">
              Current file: <a href="../<?= $editQB['file_path'] ?>" target="_blank" class="text-blue-600"><?= basename($editQB['file_path']) ?></a>
            </div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" value="<?= $editQB ? (int)$editQB['sort_order'] : 0 ?>" class="form-control">
        </div>

        <?php if ($editQB): ?>
          <div class="form-group" style="display:flex;align-items:center;gap:8px;">
            <input type="checkbox" name="is_active" id="qb_active" value="1" <?= $editQB['is_active'] ? 'checked' : '' ?>>
            <label for="qb_active" class="form-label" style="margin-bottom:0;cursor:pointer;">Active / Visible</label>
          </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary" style="width:100%;">
          <?= $editQB ? 'Update Item' : 'Add Item' ?>
        </button>
      </form>
    </div>
  </div>

  <!-- List Column -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h2>Empanelled Question Papers</h2>
    </div>
    <div class="admin-card-body table-wrap">
      <table>
        <thead>
          <tr>
            <th>Sort</th>
            <th>Type</th>
            <th>Category</th>
            <th>Title</th>
            <th>Status</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($qbs as $q): ?>
            <tr>
              <td><?= $q['sort_order'] ?></td>
              <td>
                <span class="badge <?= $q['item_type'] === 'card' ? 'badge-green' : 'badge-red' ?>">
                  <?= $q['item_type'] === 'card' ? 'Card' : 'Link' ?>
                </span>
              </td>
              <td>
                <span class="badge badge-green"><?= htmlspecialchars($q['category_name']) ?></span>
              </td>
              <td>
                <strong><?= htmlspecialchars($q['title']) ?></strong>
                <?php if ($q['file_path']): ?>
                  <div style="font-size:0.75rem;margin-top:4px;">
                    <a href="../<?= $q['file_path'] ?>" target="_blank" style="color:#2563eb;text-decoration:underline;">
                      <i class="far fa-file-pdf"></i> Download (<?= pathinfo($q['file_path'], PATHINFO_EXTENSION) ?>)
                    </a>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge <?= $q['is_active'] ? 'badge-green' : 'badge-red' ?>">
                  <?= $q['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
              </td>
              <td style="text-align:right;">
                <div style="display:inline-flex;gap:4px;">
                  <a href="?tab=qb&edit_qb=<?= $q['id'] ?>" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form method="POST" onsubmit="return confirm('Are you sure you want to delete this item?');" style="display:inline;">
                    <input type="hidden" name="action" value="delete_qb">
                    <input type="hidden" name="id" value="<?= $q['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($qbs)): ?>
            <tr>
              <td colspan="6" style="text-align:center;padding:24px;color:#94a3b8;">No Question Bank entries created yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
<?php endif; ?>

<!-- ========================================== -->
<!-- SUB-TAB: CATEGORIES                        -->
<!-- ========================================== -->
<?php if ($tab === 'categories'): ?>
<div style="display:grid;grid-template-columns: 1fr 2fr;gap:24px;">

  <!-- Form Column -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h2><?= $editCat ? 'Edit Category' : 'Add Category' ?></h2>
      <?php if ($editCat): ?>
        <a href="?tab=categories" class="btn btn-outline btn-sm">Add New Instead</a>
      <?php endif; ?>
    </div>
    <div class="admin-card-body">
      <form method="POST">
        <input type="hidden" name="action" value="<?= $editCat ? 'edit_cat' : 'add_cat' ?>">
        <?php if ($editCat): ?>
          <input type="hidden" name="id" value="<?= $editCat['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label">Category Name *</label>
          <input type="text" name="name" value="<?= $editCat ? htmlspecialchars($editCat['name']) : '' ?>" class="form-control" placeholder="e.g. Model QPs" required>
        </div>

        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" value="<?= $editCat ? (int)$editCat['sort_order'] : 0 ?>" class="form-control">
        </div>

        <?php if ($editCat): ?>
          <div class="form-group" style="display:flex;align-items:center;gap:8px;">
            <input type="checkbox" name="is_active" id="cat_active" value="1" <?= $editCat['is_active'] ? 'checked' : '' ?>>
            <label for="cat_active" class="form-label" style="margin-bottom:0;cursor:pointer;">Active / Visible</label>
          </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary" style="width:100%;">
          <?= $editCat ? 'Update Category' : 'Add Category' ?>
        </button>
      </form>
    </div>
  </div>

  <!-- List Column -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h2>Question Bank Categories</h2>
    </div>
    <div class="admin-card-body table-wrap">
      <table>
        <thead>
          <tr>
            <th>Sort</th>
            <th>Category Name</th>
            <th>Status</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $cat): ?>
            <tr>
              <td><?= $cat['sort_order'] ?></td>
              <td>
                <strong><?= htmlspecialchars($cat['name']) ?></strong>
              </td>
              <td>
                <span class="badge <?= $cat['is_active'] ? 'badge-green' : 'badge-red' ?>">
                  <?= $cat['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
              </td>
              <td style="text-align:right;">
                <div style="display:inline-flex;gap:4px;">
                  <a href="?tab=categories&edit_cat=<?= $cat['id'] ?>" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form method="POST" onsubmit="return confirm('Are you sure you want to delete this category? This will delete all question papers inside it!');" style="display:inline;">
                    <input type="hidden" name="action" value="delete_cat">
                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($categories)): ?>
            <tr>
              <td colspan="4" style="text-align:center;padding:24px;color:#94a3b8;">No categories created yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
