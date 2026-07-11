<?php
require_once '../middleware.php';
require_once '../function.php';
include 'includes/header.php';

$db  = new Database();
$pdo = $db->con;
$msg = '';
$err = '';

// Active page & block ID for editing
$pageId = isset($_GET['page_id']) ? (int)$_GET['page_id'] : (isset($_POST['page_id']) ? (int)$_POST['page_id'] : null);
$blockId = isset($_GET['block_id']) ? (int)$_GET['block_id'] : (isset($_POST['block_id']) ? (int)$_POST['block_id'] : null);

// ════════════════════════════════════════════════════════════════
// POST ACTIONS HANDLERS
// ════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── PAGE Actions ───────────────────────────────────────────
    if ($action === 'add_page' || $action === 'edit_page') {
        $title = trim($_POST['title'] ?? '');
        // slug: lowercase, replace spaces with dashes, filter alphanumeric + dashes
        $slug  = trim($_POST['slug'] ?? '');
        $slug  = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $slug)));
        $meta  = trim($_POST['meta_description'] ?? '');

        if ($title === '' || $slug === '') {
            $err = "Title and slug are required.";
        } else {
            // Check unique slug
            $check = $pdo->prepare("SELECT id FROM custom_pages WHERE slug = ? AND id != ?");
            $check->execute([$slug, $pageId ?: 0]);
            if ($check->fetchColumn()) {
                $err = "A page with this slug already exists.";
            } else {
                if ($action === 'add_page') {
                    $stmt = $pdo->prepare("INSERT INTO custom_pages (title, slug, meta_description) VALUES (?, ?, ?)");
                    $stmt->execute([$title, $slug, $meta]);
                    $pageId = $pdo->lastInsertId();
                    $msg = "Page created successfully! Now you can add content blocks below.";
                } else {
                    $stmt = $pdo->prepare("UPDATE custom_pages SET title = ?, slug = ?, meta_description = ? WHERE id = ?");
                    $stmt->execute([$title, $slug, $meta, $pageId]);
                    $msg = "Page details updated.";
                }
            }
        }
    }

    if ($action === 'del_page') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM custom_pages WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Page and all its layout blocks deleted successfully.";
        $pageId = null;
    }

    if ($action === 'tog_page') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE custom_pages SET is_active = 1 - is_active WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Page status toggled.";
    }

    // ── BLOCK Actions ──────────────────────────────────────────
    if ($action === 'add_block' || $action === 'edit_block') {
        $btype = $_POST['block_type'] ?? 'text';
        $heading = trim($_POST['heading'] ?? '');
        $subtext = trim($_POST['subtext'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $btn_lbl = trim($_POST['button_label'] ?? '');
        $btn_lnk = trim($_POST['button_link'] ?? '');
        $layout  = $_POST['layout_option'] ?? '';
        $sort    = (int)($_POST['sort_order'] ?? 0);
        $image   = '';

        // Handle image upload if provided
        if (isset($_FILES['block_image']) && $_FILES['block_image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['block_image']['tmp_name']);
            finfo_close($finfo);

            if (in_array($mime, $allowed)) {
                $ext = strtolower(pathinfo($_FILES['block_image']['name'], PATHINFO_EXTENSION));
                $fname = 'block_' . uniqid() . '.' . $ext;
                $dest = __DIR__ . '/../assets/uploads/' . $fname;
                
                // Ensure uploads directory exists
                if (!is_dir(__DIR__ . '/../assets/uploads/')) {
                    mkdir(__DIR__ . '/../assets/uploads/', 0777, true);
                }

                if (move_uploaded_file($_FILES['block_image']['tmp_name'], $dest)) {
                    $image = 'assets/uploads/' . $fname;
                }
            }
        }

        // If editing and no new image, retain old image
        if ($action === 'edit_block' && $image === '') {
            $oldImgStmt = $pdo->prepare("SELECT image_path FROM page_blocks WHERE id = ?");
            $oldImgStmt->execute([$blockId]);
            $image = $oldImgStmt->fetchColumn() ?: '';
        }

        if ($action === 'add_block') {
            $stmt = $pdo->prepare("INSERT INTO page_blocks (page_id, block_type, heading, subtext, content, image_path, button_label, button_link, layout_option, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$pageId, $btype, $heading, $subtext ?: null, $content ?: null, $image ?: null, $btn_lbl ?: null, $btn_lnk ?: null, $layout, $sort]);
            $msg = "Layout block added successfully.";
        } else {
            $stmt = $pdo->prepare("UPDATE page_blocks SET block_type=?, heading=?, subtext=?, content=?, image_path=?, button_label=?, button_link=?, layout_option=?, sort_order=? WHERE id=?");
            $stmt->execute([$btype, $heading, $subtext ?: null, $content ?: null, $image ?: null, $btn_lbl ?: null, $btn_lnk ?: null, $layout, $sort, $blockId]);
            $msg = "Layout block updated.";
            $blockId = null; // Exit edit mode for block
        }
    }

    if ($action === 'del_block') {
        $bid = (int)$_POST['id'];
        
        // Remove uploaded image if exists
        $imgStmt = $pdo->prepare("SELECT image_path FROM page_blocks WHERE id = ?");
        $imgStmt->execute([$bid]);
        $imgPath = $imgStmt->fetchColumn();
        if ($imgPath && file_exists(__DIR__ . '/../' . $imgPath)) {
            unlink(__DIR__ . '/../' . $imgPath);
        }

        $stmt = $pdo->prepare("DELETE FROM page_blocks WHERE id = ?");
        $stmt->execute([$bid]);
        $msg = "Layout block deleted.";
    }
}

// ── Fetch custom pages list ─────────────────────────────────────
$pagesList = $pdo->query("SELECT p.*, COUNT(b.id) as block_count 
                          FROM custom_pages p 
                          LEFT JOIN page_blocks b ON p.id = b.page_id 
                          GROUP BY p.id 
                          ORDER BY p.title ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch current page details if editing
$pageData = null;
$pageBlocks = [];
if ($pageId) {
    $stmt = $pdo->prepare("SELECT * FROM custom_pages WHERE id = ?");
    $stmt->execute([$pageId]);
    $pageData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch blocks for this page
    $blockStmt = $pdo->prepare("SELECT * FROM page_blocks WHERE page_id = ? ORDER BY sort_order ASC, id ASC");
    $blockStmt->execute([$pageId]);
    $pageBlocks = $blockStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch current block details if editing a block
$blockData = null;
if ($blockId) {
    $stmt = $pdo->prepare("SELECT * FROM page_blocks WHERE id = ?");
    $stmt->execute([$blockId]);
    $blockData = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <div>
    <h1 style="font-size:1.4rem;font-weight:700;">Custom Pages & Content Builder</h1>
    <p style="font-size:.8rem;color:#64748b;margin-top:2px;">Build new pages, design layouts using customizable elements, and add content</p>
  </div>
  <?php if ($pageData): ?>
    <a href="../page.php?slug=<?= htmlspecialchars($pageData['slug']) ?>" target="_blank" class="btn btn-outline btn-sm">
      <i class="fas fa-external-link-alt"></i> Preview Page: <?= htmlspecialchars($pageData['title']) ?>
    </a>
  <?php endif; ?>
</div>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<!-- ── Case 1: Page List (Default) ── -->
<?php if (!$pageId): ?>
  <div style="display:grid;grid-template-columns:320px 1fr;gap:24px;align-items:start;">
    
    <!-- Card: Create Page -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2>Create Custom Page</h2>
      </div>
      <div class="admin-card-body">
        <form method="POST">
          <input type="hidden" name="action" value="add_page">
          <div class="form-group">
            <label class="form-label">Page Title *</label>
            <input type="text" name="title" class="form-control" placeholder="e.g. CSR Initiatives" required id="pageTitleInput" onkeyup="generateSlug(this.value)">
          </div>
          <div class="form-group">
            <label class="form-label">Page URL Slug *</label>
            <input type="text" name="slug" class="form-control" placeholder="e.g. csr-initiatives" required id="pageSlugInput">
            <small style="color:#64748b;font-size:.7rem;margin-top:4px;display:block;">Alphanumeric and dashes only. Access at <code>page.php?slug=slug-name</code></small>
          </div>
          <div class="form-group">
            <label class="form-label">Meta Description</label>
            <textarea name="meta_description" class="form-control" placeholder="SEO page description..."></textarea>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;">
            <i class="fas fa-plus"></i> Create & Edit Content
          </button>
        </form>
      </div>
    </div>

    <!-- Table: Existing custom pages -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2>Custom Layout Pages</h2>
      </div>
      <div class="admin-card-body" style="padding:0;">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Title</th>
                <th>Target URL</th>
                <th style="text-align:center;">Content Blocks</th>
                <th style="text-align:center;">Status</th>
                <th style="text-align:right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pagesList as $p): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
                  <td><code>page.php?slug=<?= htmlspecialchars($p['slug']) ?></code></td>
                  <td style="text-align:center;">
                    <span class="badge" style="background:#f1f5f9;color:#475569;"><?= (int)$p['block_count'] ?> sections</span>
                  </td>
                  <td style="text-align:center;">
                    <span class="badge <?= $p['is_active'] ? 'badge-green' : 'badge-red' ?>">
                      <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                  </td>
                  <td style="text-align:right;">
                    <div style="display:flex;gap:4px;justify-content:flex-end;">
                      <a href="?page_id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit Blocks / Layout
                      </a>
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="tog_page">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-outline btn-sm" title="Toggle active">
                          <i class="fas fa-eye<?= $p['is_active'] ? '-slash' : '' ?>"></i>
                        </button>
                      </form>
                      <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this custom page and all its content blocks?');">
                        <input type="hidden" name="action" value="del_page">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" title="Delete Page">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($pagesList)): ?>
                <tr>
                  <td colspan="5" style="text-align:center;padding:32px;color:#94a3b8;">No custom layout pages created yet.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

<!-- ── Case 2: Edit Custom Page Layout blocks ── -->
<?php else: ?>

  <div style="margin-bottom:20px;">
    <a href="pages_manage.php" class="btn btn-outline btn-sm">
      <i class="fas fa-arrow-left"></i> Back to Custom Pages
    </a>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start;">

    <!-- Left Panel: Content Layout Blocks List -->
    <div>
      
      <!-- Card: Page details -->
      <div class="admin-card" style="margin-bottom:24px;">
        <div class="admin-card-header">
          <h2>Page Settings & Metadata</h2>
        </div>
        <div class="admin-card-body">
          <form method="POST">
            <input type="hidden" name="action" value="edit_page">
            <input type="hidden" name="page_id" value="<?= $pageId ?>">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
              <div class="form-group">
                <label class="form-label">Page Title *</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($pageData['title']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">URL Slug *</label>
                <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($pageData['slug']) ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">SEO Meta Description</label>
              <textarea name="meta_description" class="form-control" rows="2"><?= htmlspecialchars($pageData['meta_description'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-success">
              <i class="fas fa-save"></i> Save Page Settings
            </button>
          </form>
        </div>
      </div>

      <!-- Card: Blocks list -->
      <div class="admin-card">
        <div class="admin-card-header">
          <h2>Page Section Layout Order</h2>
        </div>
        <div class="admin-card-body" style="padding:0;">
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th style="width:60px;">Sort</th>
                  <th>Block Type</th>
                  <th>Heading / Title</th>
                  <th style="width:120px;text-align:right;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pageBlocks as $b): ?>
                  <tr style="<?= $blockId && $blockId == $b['id'] ? 'background:#fefce8;' : '' ?>">
                    <td><strong><?= (int)$b['sort_order'] ?></strong></td>
                    <td>
                      <span class="badge" style="background:#eff6ff;color:#1e40af;font-size:.7rem;text-transform:uppercase;">
                        <?= htmlspecialchars($b['block_type']) ?>
                      </span>
                    </td>
                    <td>
                      <strong><?= htmlspecialchars($b['heading'] ?: '(no heading)') ?></strong>
                      <?php if ($b['subtext']): ?>
                        <br><small style="color:#64748b;"><?= htmlspecialchars(substr($b['subtext'], 0, 60)) ?>...</small>
                      <?php endif; ?>
                    </td>
                    <td style="text-align:right;">
                      <div style="display:flex;gap:4px;justify-content:flex-end;">
                        <a href="?page_id=<?= $pageId ?>&block_id=<?= $b['id'] ?>" class="btn btn-warning btn-sm" title="Edit Block">
                          <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this content block?');">
                          <input type="hidden" name="action" value="del_block">
                          <input type="hidden" name="page_id" value="<?= $pageId ?>">
                          <input type="hidden" name="id" value="<?= $b['id'] ?>">
                          <button type="submit" class="btn btn-danger btn-sm" title="Delete Block">
                            <i class="fas fa-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($pageBlocks)): ?>
                  <tr>
                    <td colspan="4" style="text-align:center;padding:32px;color:#94a3b8;">No layout blocks added to this page yet. Use the panel on the right to design content.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

    <!-- Right Panel: Add / Edit Block Form -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2><?= $blockData ? 'Edit Block Settings' : 'Add Content / Layout Element' ?></h2>
      </div>
      <div class="admin-card-body">
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="<?= $blockData ? 'edit_block' : 'add_block' ?>">
          <input type="hidden" name="page_id" value="<?= $pageId ?>">
          <?php if ($blockData): ?>
            <input type="hidden" name="block_id" value="<?= $blockId ?>">
          <?php endif; ?>

          <div class="form-group">
            <label class="form-label">Element Type (Block Type) *</label>
            <select name="block_type" id="blockTypeSelect" class="form-control" onchange="adjustBlockForm()" required>
              <option value="hero" <?= ($blockData['block_type']??'')==='hero'?'selected':'' ?>>🌟 Hero Banner Section</option>
              <option value="text" <?= ($blockData['block_type']??'text')==='text'?'selected':'' ?>>📝 Rich Text / Editorial Section</option>
              <option value="image_text" <?= ($blockData['block_type']??'')==='image_text'?'selected':'' ?>>🖼️ Image & Text Layout</option>
              <option value="features" <?= ($blockData['block_type']??'')==='features'?'selected':'' ?>>🗂️ Cards / Features Grid</option>
              <option value="cta" <?= ($blockData['block_type']??'')==='cta'?'selected':'' ?>>⚡ Call to Action (CTA) Strip</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Heading / Title</label>
            <input type="text" name="heading" class="form-control" placeholder="e.g. Bridging Global Boundaries" value="<?= htmlspecialchars($blockData['heading'] ?? '') ?>">
          </div>

          <div class="form-group" id="subtextGroup">
            <label class="form-label" id="subtextLabel">Subtext / Caption</label>
            <textarea name="subtext" class="form-control" rows="2" placeholder="e.g. Empowering candidates across countries..."><?= htmlspecialchars($blockData['subtext'] ?? '') ?></textarea>
          </div>

          <div class="form-group" id="contentGroup">
            <label class="form-label" id="contentLabel">Content / Rich Paragraphs</label>
            <textarea name="content" class="form-control" rows="8" placeholder="Detailed content paragraphs..."><?= htmlspecialchars($blockData['content'] ?? '') ?></textarea>
            <small id="contentHint" style="color:#64748b;font-size:.7rem;margin-top:4px;display:block;"></small>
          </div>

          <div class="form-group" id="imageGroup">
            <label class="form-label">Upload Section Image</label>
            <input type="file" name="block_image" class="form-control" accept="image/*">
            <?php if ($blockData && $blockData['image_path']): ?>
              <div style="margin-top:8px;">
                <img src="../<?= htmlspecialchars($blockData['image_path']) ?>" style="height:80px;border-radius:8px;border:1px solid #e2e8f0;">
              </div>
            <?php endif; ?>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;" id="buttonGroup">
            <div class="form-group">
              <label class="form-label">Button Text</label>
              <input type="text" name="button_label" class="form-control" placeholder="e.g. Learn More" value="<?= htmlspecialchars($blockData['button_label'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Button Link Target</label>
              <input type="text" name="button_link" class="form-control" placeholder="e.g. contact.php" value="<?= htmlspecialchars($blockData['button_link'] ?? '') ?>">
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="form-group" id="layoutGroup">
              <label class="form-label">Layout / Theme Option</label>
              <select name="layout_option" id="layoutSelect" class="form-control">
                <!-- choices populated dynamically in script -->
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Section Sort Order</label>
              <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($blockData['sort_order'] ?? 0) ?>">
            </div>
          </div>

          <div style="display:flex;gap:8px;margin-top:24px;">
            <button type="submit" class="btn btn-primary" style="flex:1;">
              <i class="fas fa-save"></i> <?= $blockData ? 'Save Block Changes' : 'Insert Element' ?>
            </button>
            <?php if ($blockData): ?>
              <a href="?page_id=<?= $pageId ?>" class="btn btn-outline">Cancel Edit</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>

  </div>

<?php endif; ?>

<script>
function generateSlug(text) {
  const slugInput = document.getElementById('pageSlugInput');
  if (slugInput) {
    slugInput.value = text.toLowerCase()
                          .replace(/[^a-z0-9\s\-]/g, '')
                          .replace(/\s+/g, '-')
                          .replace(/-+/g, '-');
  }
}

function adjustBlockForm() {
  const type = document.getElementById('blockTypeSelect').value;
  const subtextGroup = document.getElementById('subtextGroup');
  const subtextLabel = document.getElementById('subtextLabel');
  const contentGroup = document.getElementById('contentGroup');
  const contentLabel = document.getElementById('contentLabel');
  const contentHint  = document.getElementById('contentHint');
  const imageGroup   = document.getElementById('imageGroup');
  const buttonGroup  = document.getElementById('buttonGroup');
  const layoutSelect = document.getElementById('layoutSelect');
  
  // Default show status
  subtextGroup.style.display = '';
  contentGroup.style.display = '';
  imageGroup.style.display   = 'none';
  buttonGroup.style.display  = '';
  contentHint.textContent    = '';

  // Options configuration
  let layoutOpts = [];
  const currentSelected = '<?= $blockData['layout_option'] ?? '' ?>';

  if (type === 'hero') {
    subtextLabel.textContent = 'Banner Subtext';
    contentGroup.style.display = 'none'; // Hero uses title and subtext
    layoutOpts = [
      {val: 'bg_gradient', label: '🔵 Blue Gradient'},
      {val: 'bg_dark', label: '⚫ Dark Slate Background'}
    ];
  } else if (type === 'text') {
    subtextLabel.textContent = 'Abstract / Subtext (italicized)';
    contentLabel.textContent = 'Rich Text Paragraphs';
    buttonGroup.style.display = 'none';
    layoutOpts = [
      {val: 'plain', label: '⚪ Standard White Layout'}
    ];
  } else if (type === 'image_text') {
    subtextLabel.textContent = 'Section Accent (e.g. "Since 2015")';
    contentLabel.textContent = 'Side Description Text';
    imageGroup.style.display = '';
    layoutOpts = [
      {val: 'right_image', label: '🖼️ Image on the Right'},
      {val: 'left_image', label: '🖼️ Image on the Left'}
    ];
  } else if (type === 'features') {
    subtextLabel.textContent = 'Grid Subtext / Abstract';
    contentLabel.textContent = 'Features / Grid Items (one per line)';
    buttonGroup.style.display = 'none';
    contentHint.textContent = 'Enter each item on a new line. They will be formatted as card nodes in the grid.';
    layoutOpts = [
      {val: 'cols_3', label: '🗂️ 3 Column Layout'},
      {val: 'cols_4', label: '🗂️ 4 Column Layout'}
    ];
  } else if (type === 'cta') {
    subtextLabel.textContent = 'Action Description text';
    contentGroup.style.display = 'none';
    layoutOpts = [
      {val: 'bg_gradient', label: '🔵 Blue Gradient Bar'},
      {val: 'bg_blue', label: '🔵 Royal Blue solid'},
      {val: 'bg_gray', label: '⚪ Light Gray bar'},
      {val: 'bg_white', label: '⚪ White bar'}
    ];
  }

  // Populate layout choices
  layoutSelect.innerHTML = '';
  layoutOpts.forEach(o => {
    const opt = document.createElement('option');
    opt.value = o.val;
    opt.textContent = o.label;
    if (o.val === currentSelected) {
      opt.selected = true;
    }
    layoutSelect.appendChild(opt);
  });
}

// Initialise form layout
document.addEventListener('DOMContentLoaded', adjustBlockForm);
</script>

<?php include 'includes/footer.php'; ?>
