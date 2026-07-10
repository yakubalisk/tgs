<?php
require_once '../middleware.php';
require_once '../function.php';
include 'includes/header.php';

$db  = new Database();
$pdo = $db->con;
$msg = '';
$err = '';

// ── Save settings ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO home_settings (skey, sval) VALUES (:k,:v)
                               ON DUPLICATE KEY UPDATE sval=VALUES(sval)");
        foreach ($_POST as $k => $v) {
            if ($k === 'action') continue;
            // expertise items come as a textarea with one item per line → convert to JSON
            if ($k === 'expertise_items') {
                $lines = array_filter(array_map('trim', explode("\n", $v)));
                $v = json_encode(array_values($lines));
            }
            $stmt->execute([':k' => $k, ':v' => trim($v)]);
        }
        $msg = "Settings saved successfully.";
    } catch (Exception $e) {
        $err = "Error: " . $e->getMessage();
    }
}

// ── Load all settings ──────────────────────────────────────────
$rows = $pdo->query("SELECT skey, sval FROM home_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
function s($key, $rows, $fallback = '') { return htmlspecialchars($rows[$key] ?? $fallback); }

// decode expertise for textarea display
$expertiseArr = json_decode($rows['expertise_items'] ?? '[]', true) ?: [];
$expertiseText = implode("\n", $expertiseArr);
?>

<h1 style="font-size:1.4rem;font-weight:700;margin-bottom:24px;">Site Settings</h1>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<form method="POST">
  <input type="hidden" name="action" value="save">

  <!-- HERO MINI STATS -->
  <div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-header"><h2>Hero Section — Stats Bar</h2></div>
    <div class="admin-card-body">
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
        <?php for ($i=1;$i<=3;$i++): ?>
          <div>
            <div class="form-group">
              <label class="form-label">Stat <?=$i?> Value</label>
              <input type="text" name="hero_stat<?=$i?>_val" class="form-control" value="<?= s("hero_stat{$i}_val",$rows) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Stat <?=$i?> Label</label>
              <input type="text" name="hero_stat<?=$i?>_label" class="form-control" value="<?= s("hero_stat{$i}_label",$rows) ?>">
            </div>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>

  <!-- EXPERTISE BOX -->
  <div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-header"><h2>Hero Section — Expertise Box</h2></div>
    <div class="admin-card-body">
      <div class="form-group">
        <label class="form-label">Box Heading</label>
        <input type="text" name="expertise_heading" class="form-control" value="<?= s('expertise_heading',$rows) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Expertise Items <small style="color:#64748b;">(one per line)</small></label>
        <textarea name="expertise_items" class="form-control" style="min-height:120px;"><?= htmlspecialchars($expertiseText) ?></textarea>
      </div>
    </div>
  </div>

  <!-- WHO WE ARE -->
  <div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-header"><h2>Who We Are Section</h2></div>
    <div class="admin-card-body">
      <div class="form-group">
        <label class="form-label">Section Heading</label>
        <input type="text" name="who_heading" class="form-control" value="<?= s('who_heading',$rows) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="who_description" class="form-control"><?= s('who_description',$rows) ?></textarea>
      </div>
      <p style="font-size:.8rem;font-weight:600;color:#64748b;margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px;">Stat Cards</p>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
        <?php for ($i=1;$i<=3;$i++): ?>
          <div style="border:1px solid #e2e8f0;border-radius:8px;padding:14px;">
            <p style="font-size:.8rem;font-weight:700;margin-bottom:10px;">Card <?=$i?></p>
            <div class="form-group">
              <label class="form-label">Number/Value</label>
              <input type="text" name="stat<?=$i?>_number" class="form-control" value="<?= s("stat{$i}_number",$rows) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Label</label>
              <input type="text" name="stat<?=$i?>_label" class="form-control" value="<?= s("stat{$i}_label",$rows) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Sub-text</label>
              <input type="text" name="stat<?=$i?>_sub" class="form-control" value="<?= s("stat{$i}_sub",$rows) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Icon (FA class)</label>
              <input type="text" name="stat<?=$i?>_icon" class="form-control" placeholder="fa-clipboard-check" value="<?= s("stat{$i}_icon",$rows) ?>">
            </div>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>

  <!-- TRACK RECORD -->
  <div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-header"><h2>Track Record Numbers</h2></div>
    <div class="admin-card-body">
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">
        <?php for ($i=1;$i<=4;$i++): ?>
          <div>
            <div class="form-group">
              <label class="form-label">Value <?=$i?></label>
              <input type="text" name="track<?=$i?>_val" class="form-control" value="<?= s("track{$i}_val",$rows) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Label <?=$i?></label>
              <input type="text" name="track<?=$i?>_label" class="form-control" value="<?= s("track{$i}_label",$rows) ?>">
            </div>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>

  <!-- CLIENTS SECTION -->
  <div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-header"><h2>Trusted Clients Section</h2></div>
    <div class="admin-card-body">
      <div class="form-group">
        <label class="form-label">Heading</label>
        <input type="text" name="clients_heading" class="form-control" value="<?= s('clients_heading',$rows) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Sub-text</label>
        <textarea name="clients_sub" class="form-control"><?= s('clients_sub',$rows) ?></textarea>
      </div>
    </div>
  </div>

  <!-- LEADERSHIP SECTION -->
  <div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-header"><h2>Leadership Section Text</h2></div>
    <div class="admin-card-body">
      <div class="form-group">
        <label class="form-label">Section Heading</label>
        <input type="text" name="leadership_heading" class="form-control" value="<?= s('leadership_heading',$rows) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Sub-text</label>
        <textarea name="leadership_sub" class="form-control"><?= s('leadership_sub',$rows) ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Leadership Philosophy Text</label>
        <textarea name="leadership_philosophy" class="form-control" style="min-height:120px;"><?= s('leadership_philosophy',$rows) ?></textarea>
      </div>
    </div>
  </div>

  <!-- SERVICES SECTION -->
  <div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-header"><h2>Services Section Text</h2></div>
    <div class="admin-card-body">
      <div class="form-group">
        <label class="form-label">Section Heading</label>
        <input type="text" name="services_heading" class="form-control" value="<?= s('services_heading',$rows) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Sub-text</label>
        <textarea name="services_sub" class="form-control"><?= s('services_sub',$rows) ?></textarea>
      </div>
    </div>
  </div>

  <!-- WHY CHOOSE US -->
  <div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-header"><h2>Why Choose Us Section</h2></div>
    <div class="admin-card-body">
      <div class="form-group">
        <label class="form-label">Section Heading</label>
        <input type="text" name="whyus_heading" class="form-control" value="<?= s('whyus_heading',$rows) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Sub-text</label>
        <textarea name="whyus_sub" class="form-control"><?= s('whyus_sub',$rows) ?></textarea>
      </div>
    </div>
  </div>

  <!-- CTA BANNER -->
  <div class="admin-card" style="margin-bottom:24px;">
    <div class="admin-card-header"><h2>CTA Banner</h2></div>
    <div class="admin-card-body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="form-group">
          <label class="form-label">Heading</label>
          <input type="text" name="cta_heading" class="form-control" value="<?= s('cta_heading',$rows) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Sub-text</label>
          <input type="text" name="cta_sub" class="form-control" value="<?= s('cta_sub',$rows) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Button Label</label>
          <input type="text" name="cta_btn" class="form-control" value="<?= s('cta_btn',$rows) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Button Link</label>
          <input type="text" name="cta_link" class="form-control" value="<?= s('cta_link',$rows) ?>">
        </div>
      </div>
    </div>
  </div>

  <button type="submit" class="btn btn-primary" style="font-size:1rem;padding:12px 28px;">
    <i class="fas fa-save"></i> Save All Settings
  </button>
</form>

<?php include 'includes/footer.php'; ?>
