<?php
require_once __DIR__ . '/../function.php';
$db = new Database();
$pdo = $db->con;

// Authenticate session if needed (assuming admin session is verified)
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

// Active sub-tab
$tab = $_GET['tab'] ?? 'courses'; // 'courses' or 'categories'

// Helper for Document upload
function handleDocumentUpload($fileKey) {
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
        throw new Exception("Invalid document type. Allowed: PDF, Word (doc/docx), Excel (xls/xlsx).");
    }
    if ($_FILES[$fileKey]['size'] > 15 * 1024 * 1024) {
        throw new Exception("Document too large. Max 15 MB.");
    }

    $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
    $fname = uniqid('doc_', true) . '.' . $ext;
    $dir = __DIR__ . '/../assets/uploads/courses/';
    $dest = $dir . $fname;

    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (!move_uploaded_file($_FILES[$fileKey]['tmp_name'], $dest)) {
        throw new Exception("Failed to save uploaded document.");
    }

    return 'assets/uploads/courses/' . $fname;
}

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        // --- CATEGORIES CRUD ---
        if ($action === 'add_cat') {
            $name = trim($_POST['name']);
            $sort = (int)($_POST['sort_order'] ?? 0);
            if (empty($name)) throw new Exception("Category name is required.");

            $stmt = $pdo->prepare("INSERT INTO course_categories (name, sort_order) VALUES (?, ?)");
            $stmt->execute([$name, $sort]);
            $msg = "Category added successfully!";
        }
        elseif ($action === 'edit_cat') {
            $id = (int)$_POST['id'];
            $name = trim($_POST['name']);
            $sort = (int)($_POST['sort_order'] ?? 0);
            $active = isset($_POST['is_active']) ? 1 : 0;
            if (empty($name)) throw new Exception("Category name is required.");

            $stmt = $pdo->prepare("UPDATE course_categories SET name = ?, sort_order = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$name, $sort, $active, $id]);
            $msg = "Category updated successfully!";
        }
        elseif ($action === 'delete_cat') {
            $id = (int)$_POST['id'];
            // Also delete associated course files first
            $courses = $pdo->prepare("SELECT file_path, image_path FROM courses WHERE category_id = ?");
            $courses->execute([$id]);
            $files = $courses->fetchAll(PDO::FETCH_ASSOC);
            foreach ($files as $f) {
                if ($f['file_path']) deleteUploadedFile($f['file_path']);
                if ($f['image_path']) deleteUploadedFile($f['image_path']);
            }
            
            $stmt = $pdo->prepare("DELETE FROM course_categories WHERE id = ?");
            $stmt->execute([$id]);
            $msg = "Category and its courses deleted successfully!";
        }

        // --- COURSES CRUD ---
        elseif ($action === 'add_course') {
            $catId = (int)$_POST['category_id'];
            $title = trim($_POST['title']);
            $itemType = $_POST['item_type'] ?? 'card';
            $linkUrl = trim($_POST['link_url'] ?? '');
            $sort = (int)($_POST['sort_order'] ?? 0);

            if (empty($title)) throw new Exception("Course title is required.");
            if ($catId <= 0) throw new Exception("Please select a valid category.");

            // Upload PDF Document
            $filePath = handleDocumentUpload('file_doc');
            
            // Upload Custom Thumbnail Image
            $imgPath = handleImageUpload('file_img', 'courses_thumbs');

            $stmt = $pdo->prepare("INSERT INTO courses (category_id, title, item_type, file_path, link_url, image_path, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$catId, $title, $itemType, $filePath, $linkUrl, $imgPath, $sort]);
            $msg = "Course item added successfully!";
        }
        elseif ($action === 'edit_course') {
            $id = (int)$_POST['id'];
            $catId = (int)$_POST['category_id'];
            $title = trim($_POST['title']);
            $itemType = $_POST['item_type'] ?? 'card';
            $linkUrl = trim($_POST['link_url'] ?? '');
            $sort = (int)($_POST['sort_order'] ?? 0);
            $active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($title)) throw new Exception("Course title is required.");
            if ($catId <= 0) throw new Exception("Please select a valid category.");

            // Get current files to replace if new ones are uploaded
            $curr = $pdo->prepare("SELECT file_path, image_path FROM courses WHERE id = ?");
            $curr->execute([$id]);
            $c = $curr->fetch(PDO::FETCH_ASSOC);

            $filePath = $c['file_path'];
            $imgPath = $c['image_path'];

            $newFile = handleDocumentUpload('file_doc');
            if ($newFile) {
                if ($c['file_path']) deleteUploadedFile($c['file_path']);
                $filePath = $newFile;
            }

            $newImg = handleImageUpload('file_img', 'courses_thumbs');
            if ($newImg) {
                if ($c['image_path']) deleteUploadedFile($c['image_path']);
                $imgPath = $newImg;
            }

            $stmt = $pdo->prepare("UPDATE courses SET category_id = ?, title = ?, item_type = ?, file_path = ?, link_url = ?, image_path = ?, sort_order = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$catId, $title, $itemType, $filePath, $linkUrl, $imgPath, $sort, $active, $id]);
            $msg = "Course item updated successfully!";
        }
        elseif ($action === 'delete_course') {
            $id = (int)$_POST['id'];
            $curr = $pdo->prepare("SELECT file_path, image_path FROM courses WHERE id = ?");
            $curr->execute([$id]);
            $c = $curr->fetch(PDO::FETCH_ASSOC);

            if ($c['file_path']) deleteUploadedFile($c['file_path']);
            if ($c['image_path']) deleteUploadedFile($c['image_path']);

            $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->execute([$id]);
            $msg = "Course item deleted successfully!";
        }

    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}

// Fetch lists for rendering
$categories = $pdo->query("SELECT * FROM course_categories ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
$courses = $pdo->query("SELECT c.*, cat.name as category_name FROM courses c JOIN course_categories cat ON c.category_id = cat.id ORDER BY cat.sort_order, c.category_id, c.sort_order, c.id")->fetchAll(PDO::FETCH_ASSOC);

// Editing states
$editCat = null;
if (isset($_GET['edit_cat'])) {
    $id = (int)$_GET['edit_cat'];
    $stmt = $pdo->prepare("SELECT * FROM course_categories WHERE id = ?");
    $stmt->execute([$id]);
    $editCat = $stmt->fetch(PDO::FETCH_ASSOC);
}

$editCourse = null;
if (isset($_GET['edit_course'])) {
    $id = (int)$_GET['edit_course'];
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    $editCourse = $stmt->fetch(PDO::FETCH_ASSOC);
}

include 'includes/header.php';
?>

<!-- Tab Selector Navigation -->
<div style="display:flex;gap:12px;margin-bottom:20px;border-bottom:1px solid #e2e8f0;padding-bottom:10px;">
  <a href="?tab=courses" class="btn <?= $tab === 'courses' ? 'btn-primary' : 'btn-outline' ?>">
    <i class="fas fa-graduation-cap"></i> Manage Course Items (<?= count($courses) ?>)
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
<!-- SUB-TAB: COURSE ITEMS                      -->
<!-- ========================================== -->
<?php if ($tab === 'courses'): ?>
<div style="display:grid;grid-template-columns: 1fr 2fr;gap:24px;">

  <!-- Form Column -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h2><?= $editCourse ? 'Edit Course Item' : 'Add Course Item' ?></h2>
      <?php if ($editCourse): ?>
        <a href="?tab=courses" class="btn btn-outline btn-sm">Add New Instead</a>
      <?php endif; ?>
    </div>
    <div class="admin-card-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?= $editCourse ? 'edit_course' : 'add_course' ?>">
        <?php if ($editCourse): ?>
          <input type="hidden" name="id" value="<?= $editCourse['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label">Category *</label>
          <select name="category_id" class="form-control" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= ($editCourse && $editCourse['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Course Title *</label>
          <input type="text" name="title" value="<?= $editCourse ? htmlspecialchars($editCourse['title']) : '' ?>" class="form-control" placeholder="e.g. Circular Knitting Machine Operator" required>
        </div>

        <div class="form-group">
          <label class="form-label">Display Mode *</label>
          <select name="item_type" class="form-control" required id="itemTypeSelect">
            <option value="card" <?= ($editCourse && $editCourse['item_type'] === 'card') ? 'selected' : '' ?>>Card (Grid box with thumbnail)</option>
            <option value="link" <?= ($editCourse && $editCourse['item_type'] === 'link') ? 'selected' : '' ?>>Simple Link (Text link layout)</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Link URL (External Website / Redirect)</label>
          <input type="text" name="link_url" value="<?= $editCourse ? htmlspecialchars($editCourse['link_url']) : '' ?>" class="form-control" placeholder="e.g. https://example.com/syllabus (Leave '#' if none)">
        </div>

        <div class="form-group">
          <label class="form-label">Upload File (PDF / Word / Excel)</label>
          <input type="file" name="file_doc" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">
          <?php if ($editCourse && $editCourse['file_path']): ?>
            <div style="margin-top:6px;font-size:0.75rem;">
              Current file: <a href="../<?= $editCourse['file_path'] ?>" target="_blank" class="text-blue-600"><?= basename($editCourse['file_path']) ?></a>
            </div>
          <?php endif; ?>
        </div>

        <div class="form-group" id="thumbGroup">
          <label class="form-label">Custom Thumbnail Image (For Cards)</label>
          <input type="file" name="file_img" class="form-control" accept="image/*">
          <?php if ($editCourse && $editCourse['image_path']): ?>
            <div style="margin-top:6px;">
              <img src="../<?= $editCourse['image_path'] ?>" class="thumb" alt="current thumb">
            </div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" value="<?= $editCourse ? (int)$editCourse['sort_order'] : 0 ?>" class="form-control">
        </div>

        <?php if ($editCourse): ?>
          <div class="form-group" style="display:flex;align-items:center;gap:8px;">
            <input type="checkbox" name="is_active" id="active_check" value="1" <?= $editCourse['is_active'] ? 'checked' : '' ?>>
            <label for="active_check" class="form-label" style="margin-bottom:0;cursor:pointer;">Active / Visible</label>
          </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary" style="width:100%;">
          <?= $editCourse ? 'Update Item' : 'Add Item' ?>
        </button>
      </form>
    </div>
  </div>

  <!-- List Column -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h2>Empanelled Courses & Downloads</h2>
    </div>
    <div class="admin-card-body table-wrap">
      <table>
        <thead>
          <tr>
            <th>Sort</th>
            <th>Thumbnail / Type</th>
            <th>Category</th>
            <th>Course Title</th>
            <th>Status</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($courses as $c): ?>
            <tr>
              <td><?= $c['sort_order'] ?></td>
              <td>
                <?php if ($c['item_type'] === 'card'): ?>
                  <?php if ($c['image_path']): ?>
                    <img src="../<?= $c['image_path'] ?>" class="thumb" alt="course thumbnail">
                  <?php else: ?>
                    <span class="badge badge-green">Card Placeholder</span>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="badge badge-red"><i class="fas fa-link"></i> Link Only</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge badge-green"><?= htmlspecialchars($c['category_name']) ?></span>
              </td>
              <td>
                <strong><?= htmlspecialchars($c['title']) ?></strong>
                <?php if ($c['file_path']): ?>
                  <div style="font-size:0.75rem;margin-top:4px;">
                    <a href="../<?= $c['file_path'] ?>" target="_blank" style="color:#2563eb;text-decoration:underline;">
                      <i class="far fa-file-pdf"></i> Download (<?= pathinfo($c['file_path'], PATHINFO_EXTENSION) ?>)
                    </a>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge <?= $c['is_active'] ? 'badge-green' : 'badge-red' ?>">
                  <?= $c['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
              </td>
              <td style="text-align:right;">
                <div style="display:inline-flex;gap:4px;">
                  <a href="?tab=courses&edit_course=<?= $c['id'] ?>" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form method="POST" onsubmit="return confirm('Are you sure you want to delete this course item?');" style="display:inline;">
                    <input type="hidden" name="action" value="delete_course">
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($courses)): ?>
            <tr>
              <td colspan="6" style="text-align:center;padding:24px;color:#94a3b8;">No course items created yet.</td>
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
          <input type="text" name="name" value="<?= $editCat ? htmlspecialchars($editCat['name']) : '' ?>" class="form-control" placeholder="e.g. Soft skills" required>
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
      <h2>Course Categories</h2>
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
                  <form method="POST" onsubmit="return confirm('Are you sure you want to delete this category? This will delete all course items inside it as well!');" style="display:inline;">
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

<script>
// Conditional layout options
const itemType = document.getElementById('itemTypeSelect');
const thumbGroup = document.getElementById('thumbGroup');

if (itemType && thumbGroup) {
  const toggleThumb = () => {
    thumbGroup.style.display = itemType.value === 'card' ? '' : 'none';
  };
  itemType.addEventListener('change', toggleThumb);
  toggleThumb();
}
</script>

<?php include 'includes/footer.php'; ?>
