<?php
require_once '../middleware.php';
require_once '../function.php';
include 'includes/header.php';

$db  = new Database();
$pdo = $db->con;
$msg = '';
$err = '';

// ── Handle Form Submissions ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Add / Edit Item
    if ($action === 'add' || $action === 'edit') {
        $title      = trim($_POST['title'] ?? '');
        $url        = trim($_POST['url'] ?? '');
        $parent_id  = $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
        $icon       = trim($_POST['icon'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);

        if ($title === '') {
            $err = "Title is required.";
        } else {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO menu_items (title, url, parent_id, icon, sort_order) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $url, $parent_id, $icon ?: null, $sort_order]);
                $msg = "Menu item added successfully.";
            } else {
                $id = (int)$_POST['id'];
                // Prevent setting itself as parent
                if ($parent_id === $id) {
                    $err = "A menu item cannot be its own parent.";
                } else {
                    $stmt = $pdo->prepare("UPDATE menu_items SET title = ?, url = ?, parent_id = ?, icon = ?, sort_order = ? WHERE id = ?");
                    $stmt->execute([$title, $url, $parent_id, $icon ?: null, $sort_order, $id]);
                    $msg = "Menu item updated successfully.";
                }
            }
        }
    }

    // Delete Item
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Menu item deleted successfully.";
    }

    // Toggle Active Status
    if ($action === 'toggle') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE menu_items SET is_active = 1 - is_active WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Menu item status updated.";
    }
}

// ── Fetch Menu Items ───────────────────────────────────────────
// Load all items sorted
$menuRows = $pdo->query("SELECT m.*, p.title as parent_title 
                         FROM menu_items m 
                         LEFT JOIN menu_items p ON m.parent_id = p.id 
                         ORDER BY m.sort_order ASC, m.id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Load parent items for dropdown choice (items where parent_id is null)
$parents = $pdo->query("SELECT id, title FROM menu_items WHERE parent_id IS NULL ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);

// Edit Fetch
$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editItem = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
  <div>
    <h1 style="font-size:1.4rem;font-weight:700;">Header Menu Links</h1>
    <p style="font-size:.8rem;color:#64748b;margin-top:2px;">Manage main navigation and dropdown submenus dynamically</p>
  </div>
  <a href="../index.php" target="_blank" class="btn btn-outline btn-sm">
    <i class="fas fa-external-link-alt"></i> View Site
  </a>
</div>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:350px 1fr;gap:24px;align-items:start;">

  <!-- Form: Add/Edit Item -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h2><?= $editItem ? 'Edit Menu Item' : 'Add Menu Item' ?></h2>
    </div>
    <div class="admin-card-body">
      <form method="POST">
        <input type="hidden" name="action" value="<?= $editItem ? 'edit' : 'add' ?>">
        <?php if ($editItem): ?>
          <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label">Link Title / Label *</label>
          <input type="text" name="title" class="form-control" placeholder="e.g. Home" required value="<?= htmlspecialchars($editItem['title'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">URL / Link Target</label>
          <input type="text" name="url" class="form-control" placeholder="e.g. index.php or # for dropdown" value="<?= htmlspecialchars($editItem['url'] ?? '') ?>">
          <small style="color:#64748b;font-size:.7rem;margin-top:4px;display:block;">Use <code>#</code> if this item is a placeholder parent that opens a dropdown.</small>
        </div>

        <div class="form-group">
          <label class="form-label">Parent Menu (Dropdown Group)</label>
          <select name="parent_id" class="form-control">
            <option value="">[ None - Main Menu Link ]</option>
            <?php foreach ($parents as $p): ?>
              <?php 
                // Skip self in edit mode
                if ($editItem && $p['id'] == $editItem['id']) continue; 
                $selected = ($editItem && $editItem['parent_id'] == $p['id']) ? 'selected' : '';
              ?>
              <option value="<?= $p['id'] ?>" <?= $selected ?>><?= htmlspecialchars($p['title']) ?></option>
            <?php endforeach; ?>
          </select>
          <small style="color:#64748b;font-size:.7rem;margin-top:4px;display:block;">Select a parent link to turn this item into a dropdown sub-menu item.</small>
        </div>

        <div class="form-group">
          <label class="form-label">Icon Class (Optional)</label>
          <input type="text" name="icon" class="form-control" placeholder="e.g. fa-briefcase" value="<?= htmlspecialchars($editItem['icon'] ?? '') ?>">
          <small style="color:#64748b;font-size:.7rem;margin-top:4px;display:block;">FontAwesome icon class name. Useful mainly for dropdown children.</small>
        </div>

        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($editItem['sort_order'] ?? 0) ?>">
        </div>

        <div style="display:flex;gap:8px;margin-top:20px;">
          <button type="submit" class="btn btn-primary" style="flex:1;">
            <i class="fas fa-save"></i> <?= $editItem ? 'Save Changes' : 'Add Item' ?>
          </button>
          <?php if ($editItem): ?>
            <a href="menu_manage.php" class="btn btn-outline">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- Table list of items -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h2>All Header Menu Links</h2>
    </div>
    <div class="admin-card-body" style="padding:0;">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width:70px;">Sort</th>
              <th>Menu Label</th>
              <th>Target URL</th>
              <th>Parent Group</th>
              <th style="width:100px;text-align:center;">Status</th>
              <th style="width:130px;text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($menuRows as $row): ?>
              <tr>
                <td><strong><?= (int)$row['sort_order'] ?></strong></td>
                <td>
                  <span style="<?= $row['parent_id'] ? 'padding-left: 20px; font-style: italic; color:#64748b;' : 'font-weight:600;' ?>">
                    <?php if ($row['parent_id']): ?>
                      <i class="fas fa-angle-right" style="margin-right:4px;"></i>
                    <?php endif; ?>
                    <?php if ($row['icon']): ?>
                      <i class="fas <?= htmlspecialchars($row['icon']) ?> text-blue-500" style="margin-right:6px;font-size:.8rem;"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($row['title']) ?>
                  </span>
                </td>
                <td><code><?= htmlspecialchars($row['url']) ?></code></td>
                <td>
                  <?php if ($row['parent_title']): ?>
                    <span class="badge" style="background:#eff6ff;color:#1e40af;font-size:.7rem;">
                      <?= htmlspecialchars($row['parent_title']) ?>
                    </span>
                  <?php else: ?>
                    <span style="color:#cbd5e1;font-size:.75rem;">Main level</span>
                  <?php endif; ?>
                </td>
                <td style="text-align:center;">
                  <span class="badge <?= $row['is_active'] ? 'badge-green' : 'badge-red' ?>">
                    <?= $row['is_active'] ? 'Active' : 'Inactive' ?>
                  </span>
                </td>
                <td style="text-align:right;">
                  <div style="display:flex;gap:4px;justify-content:flex-end;">
                    <!-- Edit -->
                    <a href="?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                      <i class="fas fa-edit"></i>
                    </a>

                    <!-- Toggle Status -->
                    <form method="POST" style="display:inline;">
                      <input type="hidden" name="action" value="toggle">
                      <input type="hidden" name="id" value="<?= $row['id'] ?>">
                      <button type="submit" class="btn btn-outline btn-sm" title="Toggle Active">
                        <i class="fas fa-eye<?= $row['is_active'] ? '-slash' : '' ?>"></i>
                      </button>
                    </form>

                    <!-- Delete -->
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this menu item? If it has submenus, they will be deleted as well.');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $row['id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($menuRows)): ?>
              <tr>
                <td colspan="6" style="text-align:center;padding:32px;color:#94a3b8;">No menu items found. Add your first item.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<?php include 'includes/footer.php'; ?>
