<?php
require_once '../middleware.php';
require_once '../function.php';
require_once 'upload_helper.php';
include 'includes/header.php';

$db  = new Database();
$pdo = $db->con;
$msg = '';
$err = '';

// ── Active tab ─────────────────────────────────────────────────
$tab = $_GET['tab'] ?? 'settings';

// ════════════════════════════════════════════════════════════════
// POST HANDLERS
// ════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Settings save ──────────────────────────────────────────
    if ($action === 'save_settings') {
        $st = $pdo->prepare("INSERT INTO samarth_settings (skey,sval) VALUES (?,?) ON DUPLICATE KEY UPDATE sval=VALUES(sval)");
        foreach ($_POST as $k => $v) {
            if ($k === 'action') continue;
            $st->execute([$k, trim($v)]);
        }
        $msg = "Settings saved."; $tab = 'settings';
    }

    // ── Instructions CRUD ──────────────────────────────────────
    if ($action === 'add_instr' || $action === 'edit_instr') {
        $d = [trim($_POST['icon']??'fa-check-circle'), trim($_POST['title']??''), trim($_POST['description']??''), (int)($_POST['sort_order']??0)];
        if ($action === 'add_instr') {
            $pdo->prepare("INSERT INTO samarth_instructions (icon,title,description,sort_order) VALUES (?,?,?,?)")->execute($d);
            $msg = "Instruction added.";
        } else {
            $d[] = (int)$_POST['id'];
            $pdo->prepare("UPDATE samarth_instructions SET icon=?,title=?,description=?,sort_order=? WHERE id=?")->execute($d);
            $msg = "Instruction updated.";
        }
        $tab = 'instructions';
    }
    if ($action === 'del_instr') { $pdo->prepare("DELETE FROM samarth_instructions WHERE id=?")->execute([(int)$_POST['id']]); $msg="Deleted."; $tab='instructions'; }
    if ($action === 'tog_instr') { $pdo->prepare("UPDATE samarth_instructions SET is_active=1-is_active WHERE id=?")->execute([(int)$_POST['id']]); $msg="Toggled."; $tab='instructions'; }

    // ── Roadmap CRUD ───────────────────────────────────────────
    if ($action === 'add_road' || $action === 'edit_road') {
        $d = [trim($_POST['step_number']??''), trim($_POST['title']??''), trim($_POST['description']??''), trim($_POST['icon']??'fa-circle'), (int)($_POST['sort_order']??0)];
        if ($action === 'add_road') {
            $pdo->prepare("INSERT INTO samarth_roadmap (step_number,title,description,icon,sort_order) VALUES (?,?,?,?,?)")->execute($d);
            $msg = "Step added.";
        } else {
            $d[] = (int)$_POST['id'];
            $pdo->prepare("UPDATE samarth_roadmap SET step_number=?,title=?,description=?,icon=?,sort_order=? WHERE id=?")->execute($d);
            $msg = "Step updated.";
        }
        $tab = 'roadmap';
    }
    if ($action === 'del_road') { $pdo->prepare("DELETE FROM samarth_roadmap WHERE id=?")->execute([(int)$_POST['id']]); $msg="Deleted."; $tab='roadmap'; }
    if ($action === 'tog_road') { $pdo->prepare("UPDATE samarth_roadmap SET is_active=1-is_active WHERE id=?")->execute([(int)$_POST['id']]); $msg="Toggled."; $tab='roadmap'; }

    // ── Videos CRUD ───────────────────────────────────────────
    if ($action === 'add_video' || $action === 'edit_video') {
        $vtype = $_POST['video_type'] === 'upload' ? 'upload' : 'youtube';
        $vurl  = '';
        if ($vtype === 'youtube') {
            $vurl = trim($_POST['youtube_url'] ?? '');
        } else {
            if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['video/mp4','video/webm','video/ogg'];
                $finfo   = finfo_open(FILEINFO_MIME_TYPE);
                $mime    = finfo_file($finfo, $_FILES['video_file']['tmp_name']);
                finfo_close($finfo);
                if (in_array($mime, $allowed)) {
                    $ext  = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
                    $fname= uniqid('vid_',true).'.'.$ext;
                    $dest = __DIR__.'/../assets/uploads/samarth/videos/'.$fname;
                    if (move_uploaded_file($_FILES['video_file']['tmp_name'], $dest))
                        $vurl = 'assets/uploads/samarth/videos/'.$fname;
                } else { $err = "Invalid video type."; $tab='videos'; goto endpost; }
            } elseif ($action === 'edit_video') {
                $old = $pdo->prepare("SELECT video_url FROM samarth_videos WHERE id=?");
                $old->execute([(int)$_POST['id']]);
                $vurl = $old->fetchColumn();
            }
        }
        // Optional thumbnail
        $thumb = '';
        if (!empty($_FILES['thumbnail']['tmp_name'])) {
            try { $thumb = handleImageUpload('thumbnail','samarth/thumbs'); } catch(Exception $e){}
        } elseif ($action === 'edit_video') {
            $old = $pdo->prepare("SELECT thumbnail FROM samarth_videos WHERE id=?");
            $old->execute([(int)$_POST['id']]);
            $thumb = $old->fetchColumn();
        }
        $d = [trim($_POST['title']??''), trim($_POST['description']??''), $vtype, $vurl, $thumb, (int)($_POST['sort_order']??0)];
        if ($action === 'add_video') {
            $pdo->prepare("INSERT INTO samarth_videos (title,description,video_type,video_url,thumbnail,sort_order) VALUES (?,?,?,?,?,?)")->execute($d);
            $msg = "Video added.";
        } else {
            $d[] = (int)$_POST['id'];
            $pdo->prepare("UPDATE samarth_videos SET title=?,description=?,video_type=?,video_url=?,thumbnail=?,sort_order=? WHERE id=?")->execute($d);
            $msg = "Video updated.";
        }
        $tab = 'videos';
    }
    if ($action === 'del_video') {
        $r = $pdo->prepare("SELECT video_url,video_type FROM samarth_videos WHERE id=?");
        $r->execute([(int)$_POST['id']]); $r = $r->fetch(PDO::FETCH_ASSOC);
        if ($r && $r['video_type']==='upload') deleteUploadedFile($r['video_url']);
        $pdo->prepare("DELETE FROM samarth_videos WHERE id=?")->execute([(int)$_POST['id']]);
        $msg="Deleted."; $tab='videos';
    }
    if ($action === 'tog_video') { $pdo->prepare("UPDATE samarth_videos SET is_active=1-is_active WHERE id=?")->execute([(int)$_POST['id']]); $msg="Toggled."; $tab='videos'; }

    // ── Documents CRUD ─────────────────────────────────────────
    if ($action === 'add_doc' || $action === 'edit_doc') {
        $fpath = '';
        if (isset($_FILES['doc_file']) && $_FILES['doc_file']['error'] === UPLOAD_ERR_OK) {
            $allowedMime = ['application/pdf','application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml.presentation','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $_FILES['doc_file']['tmp_name']);
            finfo_close($finfo);
            $ext   = strtolower(pathinfo($_FILES['doc_file']['name'], PATHINFO_EXTENSION));
            $allowedExt = ['pdf','ppt','pptx','doc','docx','xls','xlsx'];
            if (!in_array($ext, $allowedExt)) { $err = "File type not allowed."; $tab='documents'; goto endpost; }
            $size  = $_FILES['doc_file']['size'];
            $fname = uniqid('doc_',true).'.'.$ext;
            $dest  = __DIR__.'/../assets/uploads/samarth/docs/'.$fname;
            if (move_uploaded_file($_FILES['doc_file']['tmp_name'], $dest)) {
                $fpath    = 'assets/uploads/samarth/docs/'.$fname;
                $fsize    = $size > 1048576 ? round($size/1048576,1).' MB' : round($size/1024).' KB';
            }
        } elseif ($action === 'edit_doc') {
            $old = $pdo->prepare("SELECT file_path,file_size FROM samarth_documents WHERE id=?");
            $old->execute([(int)$_POST['id']]);
            $old = $old->fetch(PDO::FETCH_ASSOC);
            $fpath = $old['file_path']; $fsize = $old['file_size'];
            $ext   = strtolower(pathinfo($fpath, PATHINFO_EXTENSION));
        }
        if (!$fpath && $action==='add_doc') { $err="Please upload a file."; $tab='documents'; goto endpost; }
        $d = [trim($_POST['title']??''), trim($_POST['description']??''), $fpath, $ext??'', $fsize??'', (int)($_POST['sort_order']??0)];
        if ($action === 'add_doc') {
            $pdo->prepare("INSERT INTO samarth_documents (title,description,file_path,file_type,file_size,sort_order) VALUES (?,?,?,?,?,?)")->execute($d);
            $msg = "Document uploaded.";
        } else {
            $d[] = (int)$_POST['id'];
            $pdo->prepare("UPDATE samarth_documents SET title=?,description=?,file_path=?,file_type=?,file_size=?,sort_order=? WHERE id=?")->execute($d);
            $msg = "Document updated.";
        }
        $tab = 'documents';
    }
    if ($action === 'del_doc') {
        $r = $pdo->prepare("SELECT file_path FROM samarth_documents WHERE id=?");
        $r->execute([(int)$_POST['id']]); $fp = $r->fetchColumn();
        if ($fp) deleteUploadedFile($fp);
        $pdo->prepare("DELETE FROM samarth_documents WHERE id=?")->execute([(int)$_POST['id']]);
        $msg="Deleted."; $tab='documents';
    }
    if ($action === 'tog_doc') { $pdo->prepare("UPDATE samarth_documents SET is_active=1-is_active WHERE id=?")->execute([(int)$_POST['id']]); $msg="Toggled."; $tab='documents'; }

    // ── Registration status ────────────────────────────────────
    if ($action === 'upd_reg') {
        $pdo->prepare("UPDATE samarth_registrations SET status=? WHERE id=?")->execute([trim($_POST['status']??'new'),(int)$_POST['id']]);
        $msg="Status updated."; $tab='registrations';
    }
    if ($action === 'del_reg') { $pdo->prepare("DELETE FROM samarth_registrations WHERE id=?")->execute([(int)$_POST['id']]); $msg="Deleted."; $tab='registrations'; }

    // ── Offline form upload ────────────────────────────────────
    if ($action === 'upload_offline_form') {
        if (isset($_FILES['offline_pdf']) && $_FILES['offline_pdf']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['offline_pdf']['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') { $err = 'Only PDF files allowed.'; $tab='settings'; goto endpost; }
            $fname = 'samarth_offline_form_'.time().'.pdf';
            $dest  = __DIR__.'/../assets/uploads/samarth/docs/'.$fname;
            if (move_uploaded_file($_FILES['offline_pdf']['tmp_name'], $dest)) {
                $path = 'assets/uploads/samarth/docs/'.$fname;
                $pdo->prepare("INSERT INTO samarth_settings (skey,sval) VALUES (?,?) ON DUPLICATE KEY UPDATE sval=VALUES(sval)")->execute(['offline_form_path',$path]);
                $msg = 'Offline form uploaded.';
            }
        }
        // save title and instructions too
        $st = $pdo->prepare("INSERT INTO samarth_settings (skey,sval) VALUES (?,?) ON DUPLICATE KEY UPDATE sval=VALUES(sval)");
        foreach (['offline_form_title','offline_instructions','submit_address','submit_email','reg_mode','info_heading','info_sub','checklist_heading','checklist_sub'] as $k) {
            if (isset($_POST[$k])) $st->execute([$k, trim($_POST[$k])]);
        }
        if (!$msg) $msg = 'Registration settings saved.';
        $tab = 'settings';
    }

    // ── Info Sections CRUD ─────────────────────────────────────
    if ($action === 'add_info' || $action === 'edit_info') {
        $d = [trim($_POST['section_type']??'general'), trim($_POST['icon']??'fa-info-circle'), trim($_POST['title']??''), trim($_POST['content']??''), (int)($_POST['sort_order']??0)];
        if ($action === 'add_info') {
            $pdo->prepare("INSERT INTO samarth_info_sections (section_type,icon,title,content,sort_order) VALUES (?,?,?,?,?)")->execute($d);
            $msg = 'Section added.';
        } else { $d[] = (int)$_POST['id'];
            $pdo->prepare("UPDATE samarth_info_sections SET section_type=?,icon=?,title=?,content=?,sort_order=? WHERE id=?")->execute($d);
            $msg = 'Section updated.';
        }
        $tab = 'info';
    }
    if ($action === 'del_info') { $pdo->prepare("DELETE FROM samarth_info_sections WHERE id=?")->execute([(int)$_POST['id']]); $msg='Deleted.'; $tab='info'; }
    if ($action === 'tog_info') { $pdo->prepare("UPDATE samarth_info_sections SET is_active=1-is_active WHERE id=?")->execute([(int)$_POST['id']]); $msg='Toggled.'; $tab='info'; }

    // ── Checklist CRUD ─────────────────────────────────────────
    if ($action === 'add_cl' || $action === 'edit_cl') {
        $d = [trim($_POST['title']??''), trim($_POST['description']??''), isset($_POST['is_mandatory'])?1:0, (int)($_POST['sort_order']??0)];
        if ($action === 'add_cl') {
            $pdo->prepare("INSERT INTO samarth_checklist (title,description,is_mandatory,sort_order) VALUES (?,?,?,?)")->execute($d);
            $msg = 'Item added.';
        } else { $d[] = (int)$_POST['id'];
            $pdo->prepare("UPDATE samarth_checklist SET title=?,description=?,is_mandatory=?,sort_order=? WHERE id=?")->execute($d);
            $msg = 'Item updated.';
        }
        $tab = 'checklist';
    }
    if ($action === 'del_cl') { $pdo->prepare("DELETE FROM samarth_checklist WHERE id=?")->execute([(int)$_POST['id']]); $msg='Deleted.'; $tab='checklist'; }
    if ($action === 'tog_cl') { $pdo->prepare("UPDATE samarth_checklist SET is_active=1-is_active WHERE id=?")->execute([(int)$_POST['id']]); $msg='Toggled.'; $tab='checklist'; }
}
endpost:

// ── Fetch all data ────────────────────────────────────────────
$settings    = $pdo->query("SELECT skey,sval FROM samarth_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$instrs      = $pdo->query("SELECT * FROM samarth_instructions ORDER BY sort_order,id")->fetchAll(PDO::FETCH_ASSOC);
$roadmap     = $pdo->query("SELECT * FROM samarth_roadmap ORDER BY sort_order,id")->fetchAll(PDO::FETCH_ASSOC);
$videos      = $pdo->query("SELECT * FROM samarth_videos ORDER BY sort_order,id")->fetchAll(PDO::FETCH_ASSOC);
$documents   = $pdo->query("SELECT * FROM samarth_documents ORDER BY sort_order,id")->fetchAll(PDO::FETCH_ASSOC);
$regs        = $pdo->query("SELECT * FROM samarth_registrations ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$infoSecs    = $pdo->query("SELECT * FROM samarth_info_sections ORDER BY sort_order,id")->fetchAll(PDO::FETCH_ASSOC);
$checklist   = $pdo->query("SELECT * FROM samarth_checklist ORDER BY sort_order,id")->fetchAll(PDO::FETCH_ASSOC);

function sv($k,$s,$f='') { return htmlspecialchars($s[$k]??$f); }

// Edit targets
$editInstr = isset($_GET['edit_instr']) ? $pdo->prepare("SELECT * FROM samarth_instructions WHERE id=?") : null;
if ($editInstr) { $editInstr->execute([(int)$_GET['edit_instr']]); $editInstr = $editInstr->fetch(PDO::FETCH_ASSOC); }
$editRoad = isset($_GET['edit_road']) ? $pdo->prepare("SELECT * FROM samarth_roadmap WHERE id=?") : null;
if ($editRoad) { $editRoad->execute([(int)$_GET['edit_road']]); $editRoad = $editRoad->fetch(PDO::FETCH_ASSOC); }
$editVideo = isset($_GET['edit_video']) ? $pdo->prepare("SELECT * FROM samarth_videos WHERE id=?") : null;
if ($editVideo) { $editVideo->execute([(int)$_GET['edit_video']]); $editVideo = $editVideo->fetch(PDO::FETCH_ASSOC); }
$editDoc = isset($_GET['edit_doc']) ? $pdo->prepare("SELECT * FROM samarth_documents WHERE id=?") : null;
if ($editDoc) { $editDoc->execute([(int)$_GET['edit_doc']]); $editDoc = $editDoc->fetch(PDO::FETCH_ASSOC); }
$editInfo = isset($_GET['edit_info']) ? $pdo->prepare("SELECT * FROM samarth_info_sections WHERE id=?") : null;
if ($editInfo) { $editInfo->execute([(int)$_GET['edit_info']]); $editInfo = $editInfo->fetch(PDO::FETCH_ASSOC); }
$editCl = isset($_GET['edit_cl']) ? $pdo->prepare("SELECT * FROM samarth_checklist WHERE id=?") : null;
if ($editCl) { $editCl->execute([(int)$_GET['edit_cl']]); $editCl = $editCl->fetch(PDO::FETCH_ASSOC); }

$faIcons = ['fa-check-circle','fa-door-open','fa-file-alt','fa-calendar-check','fa-clipboard-list','fa-certificate','fa-search','fa-tasks','fa-award','fa-chalkboard-teacher','fa-users','fa-star','fa-book','fa-rocket','fa-circle','fa-info-circle','fa-exclamation-triangle','fa-folder-open','fa-file-signature','fa-shield-alt'];
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
  <div>
    <h1 style="font-size:1.4rem;font-weight:700;">Assessment Samarth</h1>
    <p style="font-size:.8rem;color:#64748b;margin-top:2px;">Manage all content for the Samarth Assessment page</p>
  </div>
  <a href="../samarth.php" target="_blank" class="btn btn-outline btn-sm">
    <i class="fas fa-external-link-alt"></i> Preview Page
  </a>
</div>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err)  ?></div><?php endif; ?>

<!-- ── TAB NAV ──────────────────────────────────────────────── -->
<div style="display:flex;gap:4px;border-bottom:2px solid #e2e8f0;margin-bottom:24px;overflow-x:auto;">
  <?php
  $tabs = [
    'settings'      => ['fa-sliders',       'Settings'],
    'instructions'  => ['fa-list-ol',       'Instructions ('.count($instrs).')'],
    'roadmap'       => ['fa-road',          'Roadmap ('.count($roadmap).')'],
    'videos'        => ['fa-video',         'Videos ('.count($videos).')'],
    'documents'     => ['fa-file-pdf',      'Documents ('.count($documents).')'],
    'info'          => ['fa-info-circle',   'Info Sections ('.count($infoSecs).')'],
    'checklist'     => ['fa-clipboard-list','Doc Checklist ('.count($checklist).')'],
    'registrations' => ['fa-users',         'Registrations ('.count($regs).')'],
  ];
  foreach ($tabs as $key => [$icon, $label]):
    $active = $tab === $key;
  ?>
  <a href="?tab=<?= $key ?>"
     style="display:inline-flex;align-items:center;gap:6px;padding:10px 16px;font-size:.82rem;font-weight:600;text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-2px;white-space:nowrap;
            color:<?= $active ? '#1d4ed8' : '#64748b' ?>;border-bottom-color:<?= $active ? '#1d4ed8' : 'transparent' ?>;background:<?= $active ? '#eff6ff' : 'transparent' ?>;border-radius:6px 6px 0 0;">
    <i class="fas <?= $icon ?>"></i> <?= $label ?>
  </a>
  <?php endforeach; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- TAB: SETTINGS                                              -->
<!-- ═══════════════════════════════════════════════════════════ -->
<?php if ($tab === 'settings'): ?>
<form method="POST">
  <input type="hidden" name="action" value="save_settings">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

    <?php
    $groups = [
      'Page / Hero' => [
        ['page_title',   'Page Tab Title',    'text'],
        ['hero_heading', 'Hero Heading',       'text'],
        ['hero_badge',   'Badge Text',         'text'],
        ['hero_subtext', 'Hero Sub-text',      'textarea'],
      ],
      'Instructions Section' => [
        ['instructions_heading','Heading','text'],
        ['instructions_sub',    'Sub-text','textarea'],
      ],
      'Roadmap Section' => [
        ['roadmap_heading','Heading','text'],
        ['roadmap_sub',    'Sub-text','textarea'],
      ],
      'Videos Section' => [
        ['videos_heading','Heading','text'],
        ['videos_sub',    'Sub-text','textarea'],
      ],
      'Documents Section' => [
        ['documents_heading','Heading','text'],
        ['documents_sub',    'Sub-text','textarea'],
      ],
      'Info / Important Section' => [
        ['info_heading','Heading','text'],
        ['info_sub',    'Sub-text','textarea'],
        ['checklist_heading','Checklist Heading','text'],
        ['checklist_sub',    'Checklist Sub-text','textarea'],
      ],
      'Registration Form' => [
        ['form_heading','Heading','text'],
        ['form_sub',    'Sub-text','textarea'],
      ],
    ];
    foreach ($groups as $gname => $fields): ?>
    <div class="admin-card" style="grid-column:<?= count($fields)<=2 ? '1' : '1/-1' ?>;">
      <div class="admin-card-header"><h2><?= $gname ?></h2></div>
      <div class="admin-card-body">
        <?php foreach ($fields as [$key,$label,$type]): ?>
        <div class="form-group">
          <label class="form-label"><?= $label ?></label>
          <?php if ($type==='textarea'): ?>
            <textarea name="<?= $key ?>" class="form-control"><?= sv($key,$settings) ?></textarea>
          <?php else: ?>
            <input type="text" name="<?= $key ?>" class="form-control" value="<?= sv($key,$settings) ?>">
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>

  </div>
  <button type="submit" class="btn btn-primary" style="margin-top:16px;font-size:1rem;padding:12px 32px;">
    <i class="fas fa-save"></i> Save All Settings
  </button>
</form>

<!-- Offline Form & Registration Mode -->
<div class="admin-card" style="margin-top:28px;">
  <div class="admin-card-header"><h2><i class="fas fa-file-pdf" style="color:#ef4444;"></i> &nbsp;Offline Application Form & Registration Mode</h2></div>
  <div class="admin-card-body">
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="upload_offline_form">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">

        <div class="form-group">
          <label class="form-label">Registration Mode (shown on page)</label>
          <select name="reg_mode" class="form-control">
            <option value="both"    <?= ($settings['reg_mode']??'both')==='both'    ?'selected':'' ?>>Online + Offline (both)</option>
            <option value="online"  <?= ($settings['reg_mode']??'both')==='online'  ?'selected':'' ?>>Online Only</option>
            <option value="offline" <?= ($settings['reg_mode']??'both')==='offline' ?'selected':'' ?>>Offline Only</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Offline Form Title</label>
          <input type="text" name="offline_form_title" class="form-control" value="<?= sv('offline_form_title',$settings,'Application Form (Offline)') ?>">
        </div>

        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Upload Offline PDF Form <?= !empty($settings['offline_form_path']) ? '(leave blank to keep current)' : '' ?></label>
          <input type="file" name="offline_pdf" class="form-control" accept=".pdf">
          <?php if (!empty($settings['offline_form_path'])): ?>
          <div style="margin-top:8px;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-file-pdf" style="color:#ef4444;font-size:1.4rem;"></i>
            <div>
              <strong style="font-size:.85rem;">Current:</strong> <?= htmlspecialchars(basename($settings['offline_form_path'])) ?><br>
              <a href="../<?= htmlspecialchars($settings['offline_form_path']) ?>" target="_blank" class="btn btn-outline btn-sm" style="margin-top:4px;">
                <i class="fas fa-eye"></i> Preview
              </a>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label">Submission Email</label>
          <input type="text" name="submit_email" class="form-control" value="<?= sv('submit_email',$settings) ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Submission Office Address</label>
          <input type="text" name="submit_address" class="form-control" value="<?= sv('submit_address',$settings) ?>">
        </div>

        <div class="form-group" style="grid-column:1/-1;">
          <label class="form-label">Offline Submission Instructions (shown in offline tab on page)</label>
          <textarea name="offline_instructions" class="form-control" rows="5"><?= sv('offline_instructions',$settings) ?></textarea>
          <small style="color:#64748b;">Use numbered steps, one per line. These will be displayed as a step list.</small>
        </div>

      </div>
      <button type="submit" class="btn btn-primary" style="margin-top:8px;">
        <i class="fas fa-save"></i> Save Registration Settings
      </button>
    </form>
  </div>
</div>

<?php // ═══════════════════════════════════════════════════════ ?>
<?php elseif ($tab === 'instructions'): ?>
<!-- TAB: INSTRUCTIONS -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
  <!-- Form -->
  <div class="admin-card">
    <div class="admin-card-header"><h2><?= $editInstr ? 'Edit' : 'Add' ?> Instruction</h2></div>
    <div class="admin-card-body">
      <form method="POST">
        <input type="hidden" name="action" value="<?= $editInstr ? 'edit_instr' : 'add_instr' ?>">
        <?php if ($editInstr): ?><input type="hidden" name="id" value="<?= $editInstr['id'] ?>"><?php endif; ?>
        <div class="form-group">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($editInstr['title']??'') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control"><?= htmlspecialchars($editInstr['description']??'') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Icon <small style="color:#64748b;">(Font Awesome class)</small></label>
          <div style="display:flex;gap:8px;align-items:center;">
            <input type="text" name="icon" id="instrIcon" class="form-control" value="<?= htmlspecialchars($editInstr['icon']??'fa-check-circle') ?>">
            <i id="instrIconPrev" class="fas <?= htmlspecialchars($editInstr['icon']??'fa-check-circle') ?>" style="font-size:1.4rem;color:#1d4ed8;min-width:24px;"></i>
          </div>
          <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;">
            <?php foreach ($faIcons as $ic): ?>
              <button type="button" onclick="setIcon('instrIcon','instrIconPrev','<?=$ic?>')"
                      style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:5px 7px;cursor:pointer;" title="<?=$ic?>">
                <i class="fas <?=$ic?>"></i>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($editInstr['sort_order']??0) ?>">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $editInstr?'Update':'Add' ?></button>
        <?php if ($editInstr): ?><a href="?tab=instructions" class="btn btn-outline" style="margin-left:8px;">Cancel</a><?php endif; ?>
      </form>
    </div>
  </div>
  <!-- List -->
  <div class="admin-card">
    <div class="admin-card-header"><h2>All Instructions</h2></div>
    <div class="admin-card-body" style="padding:0;">
      <table><thead><tr><th>#</th><th>Icon</th><th>Title</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead><tbody>
        <?php foreach ($instrs as $r): ?>
        <tr>
          <td><?=$r['sort_order']?></td>
          <td><i class="fas <?=htmlspecialchars($r['icon'])?>" style="color:#1d4ed8;"></i></td>
          <td><strong><?=htmlspecialchars($r['title'])?></strong></td>
          <td><span class="badge <?=$r['is_active']?'badge-green':'badge-red'?>"><?=$r['is_active']?'On':'Off'?></span></td>
          <td style="text-align:right;">
            <a href="?tab=instructions&edit_instr=<?=$r['id']?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
            <form method="POST" style="display:inline;"><input type="hidden" name="action" value="tog_instr"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-outline btn-sm"><i class="fas fa-eye<?=$r['is_active']?'-slash':''?>"></i></button></form>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="del_instr"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($instrs)): ?><tr><td colspan="5" style="text-align:center;padding:24px;color:#94a3b8;">No instructions yet.</td></tr><?php endif; ?>
      </tbody></table>
    </div>
  </div>
</div>

<?php elseif ($tab === 'roadmap'): ?>
<!-- TAB: ROADMAP -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
  <div class="admin-card">
    <div class="admin-card-header"><h2><?= $editRoad ? 'Edit' : 'Add' ?> Roadmap Step</h2></div>
    <div class="admin-card-body">
      <form method="POST">
        <input type="hidden" name="action" value="<?= $editRoad ? 'edit_road' : 'add_road' ?>">
        <?php if ($editRoad): ?><input type="hidden" name="id" value="<?= $editRoad['id'] ?>"><?php endif; ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group">
            <label class="form-label">Step Label (e.g. 01)</label>
            <input type="text" name="step_number" class="form-control" value="<?= htmlspecialchars($editRoad['step_number']??'') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($editRoad['sort_order']??0) ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($editRoad['title']??'') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control"><?= htmlspecialchars($editRoad['description']??'') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Icon</label>
          <div style="display:flex;gap:8px;align-items:center;">
            <input type="text" name="icon" id="roadIcon" class="form-control" value="<?= htmlspecialchars($editRoad['icon']??'fa-circle') ?>">
            <i id="roadIconPrev" class="fas <?= htmlspecialchars($editRoad['icon']??'fa-circle') ?>" style="font-size:1.4rem;color:#1d4ed8;min-width:24px;"></i>
          </div>
          <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;">
            <?php foreach ($faIcons as $ic): ?>
              <button type="button" onclick="setIcon('roadIcon','roadIconPrev','<?=$ic?>')" style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:5px 7px;cursor:pointer;"><i class="fas <?=$ic?>"></i></button>
            <?php endforeach; ?>
          </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $editRoad?'Update':'Add Step' ?></button>
        <?php if ($editRoad): ?><a href="?tab=roadmap" class="btn btn-outline" style="margin-left:8px;">Cancel</a><?php endif; ?>
      </form>
    </div>
  </div>
  <div class="admin-card">
    <div class="admin-card-header"><h2>Roadmap Steps</h2></div>
    <div class="admin-card-body" style="padding:0;">
      <table><thead><tr><th>Step</th><th>Icon</th><th>Title</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead><tbody>
        <?php foreach ($roadmap as $r): ?>
        <tr>
          <td><span style="background:#dbeafe;color:#1e40af;font-weight:700;font-size:.75rem;padding:2px 8px;border-radius:20px;"><?=htmlspecialchars($r['step_number'])?></span></td>
          <td><i class="fas <?=htmlspecialchars($r['icon'])?>" style="color:#1d4ed8;"></i></td>
          <td><strong><?=htmlspecialchars($r['title'])?></strong></td>
          <td><span class="badge <?=$r['is_active']?'badge-green':'badge-red'?>"><?=$r['is_active']?'On':'Off'?></span></td>
          <td style="text-align:right;">
            <a href="?tab=roadmap&edit_road=<?=$r['id']?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
            <form method="POST" style="display:inline;"><input type="hidden" name="action" value="tog_road"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-outline btn-sm"><i class="fas fa-eye<?=$r['is_active']?'-slash':''?>"></i></button></form>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="del_road"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($roadmap)): ?><tr><td colspan="5" style="text-align:center;padding:24px;color:#94a3b8;">No steps yet.</td></tr><?php endif; ?>
      </tbody></table>
    </div>
  </div>
</div>

<?php elseif ($tab === 'videos'): ?>
<!-- TAB: VIDEOS -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
  <div class="admin-card">
    <div class="admin-card-header"><h2><?= $editVideo ? 'Edit' : 'Add' ?> Video</h2></div>
    <div class="admin-card-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?= $editVideo ? 'edit_video' : 'add_video' ?>">
        <?php if ($editVideo): ?><input type="hidden" name="id" value="<?= $editVideo['id'] ?>"><?php endif; ?>
        <div class="form-group">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($editVideo['title']??'') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control"><?= htmlspecialchars($editVideo['description']??'') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Video Type</label>
          <select name="video_type" id="vtype" class="form-control" onchange="toggleVtype()">
            <option value="youtube" <?= ($editVideo['video_type']??'youtube')==='youtube'?'selected':'' ?>>YouTube Link</option>
            <option value="upload"  <?= ($editVideo['video_type']??'')==='upload'?'selected':'' ?>>Upload File</option>
          </select>
        </div>
        <div id="vyt" class="form-group" <?= ($editVideo['video_type']??'youtube')==='upload'?'style="display:none"':'' ?>>
          <label class="form-label">YouTube URL or Embed ID</label>
          <input type="text" name="youtube_url" class="form-control" placeholder="https://youtu.be/xxxx  or  just the ID"
                 value="<?= htmlspecialchars(($editVideo['video_type']??'youtube')==='youtube' ? ($editVideo['video_url']??'') : '') ?>">
        </div>
        <div id="vup" class="form-group" <?= ($editVideo['video_type']??'youtube')!=='upload'?'style="display:none"':'' ?>>
          <label class="form-label">Upload Video File (MP4/WebM) <?= $editVideo?'(leave blank to keep current)':'' ?></label>
          <input type="file" name="video_file" class="form-control" accept="video/mp4,video/webm">
          <?php if ($editVideo && $editVideo['video_type']==='upload'): ?>
            <p style="font-size:.75rem;color:#64748b;margin-top:4px;">Current: <?= htmlspecialchars($editVideo['video_url']) ?></p>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label">Thumbnail (optional)</label>
          <input type="file" name="thumbnail" class="form-control" accept="image/*">
          <?php if ($editVideo && $editVideo['thumbnail']): ?>
            <img src="../<?= htmlspecialchars($editVideo['thumbnail']) ?>" style="height:60px;margin-top:6px;border-radius:6px;">
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($editVideo['sort_order']??0) ?>">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $editVideo?'Update':'Add Video' ?></button>
        <?php if ($editVideo): ?><a href="?tab=videos" class="btn btn-outline" style="margin-left:8px;">Cancel</a><?php endif; ?>
      </form>
    </div>
  </div>
  <div class="admin-card">
    <div class="admin-card-header"><h2>All Videos</h2></div>
    <div class="admin-card-body" style="padding:0;">
      <table><thead><tr><th>#</th><th>Type</th><th>Title</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead><tbody>
        <?php foreach ($videos as $r): ?>
        <tr>
          <td><?=$r['sort_order']?></td>
          <td><span style="background:<?=$r['video_type']==='youtube'?'#fee2e2':'#dcfce7'?>;color:<?=$r['video_type']==='youtube'?'#991b1b':'#166534'?>;font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:20px;"><?=strtoupper($r['video_type'])?></span></td>
          <td><strong><?=htmlspecialchars($r['title'])?></strong></td>
          <td><span class="badge <?=$r['is_active']?'badge-green':'badge-red'?>"><?=$r['is_active']?'On':'Off'?></span></td>
          <td style="text-align:right;">
            <a href="?tab=videos&edit_video=<?=$r['id']?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
            <form method="POST" style="display:inline;"><input type="hidden" name="action" value="tog_video"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-outline btn-sm"><i class="fas fa-eye<?=$r['is_active']?'-slash':''?>"></i></button></form>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="del_video"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($videos)): ?><tr><td colspan="5" style="text-align:center;padding:24px;color:#94a3b8;">No videos yet.</td></tr><?php endif; ?>
      </tbody></table>
    </div>
  </div>
</div>

<?php elseif ($tab === 'documents'): ?>
<!-- TAB: DOCUMENTS -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
  <div class="admin-card">
    <div class="admin-card-header"><h2><?= $editDoc ? 'Edit' : 'Upload' ?> Document</h2></div>
    <div class="admin-card-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?= $editDoc ? 'edit_doc' : 'add_doc' ?>">
        <?php if ($editDoc): ?><input type="hidden" name="id" value="<?= $editDoc['id'] ?>"><?php endif; ?>
        <div class="form-group">
          <label class="form-label">Document Title</label>
          <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($editDoc['title']??'') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Description (optional)</label>
          <textarea name="description" class="form-control"><?= htmlspecialchars($editDoc['description']??'') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">File (PDF / PPT / PPTX / DOC / DOCX) <?= $editDoc?'— leave blank to keep current':'' ?></label>
          <input type="file" name="doc_file" class="form-control" accept=".pdf,.ppt,.pptx,.doc,.docx,.xls,.xlsx" <?= $editDoc?'':'required' ?>>
          <?php if ($editDoc): ?>
            <p style="font-size:.75rem;color:#64748b;margin-top:4px;">
              Current: <strong><?= htmlspecialchars(basename($editDoc['file_path'])) ?></strong>
              (<?= htmlspecialchars($editDoc['file_size']) ?>)
            </p>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($editDoc['sort_order']??0) ?>">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $editDoc?'Update':'Upload Document' ?></button>
        <?php if ($editDoc): ?><a href="?tab=documents" class="btn btn-outline" style="margin-left:8px;">Cancel</a><?php endif; ?>
      </form>
    </div>
  </div>
  <div class="admin-card">
    <div class="admin-card-header"><h2>All Documents</h2></div>
    <div class="admin-card-body" style="padding:0;">
      <?php
      $docIcons = ['pdf'=>'fa-file-pdf','ppt'=>'fa-file-powerpoint','pptx'=>'fa-file-powerpoint','doc'=>'fa-file-word','docx'=>'fa-file-word','xls'=>'fa-file-excel','xlsx'=>'fa-file-excel'];
      $docColors= ['pdf'=>'#ef4444','ppt'=>'#f97316','pptx'=>'#f97316','doc'=>'#3b82f6','docx'=>'#3b82f6','xls'=>'#22c55e','xlsx'=>'#22c55e'];
      ?>
      <table><thead><tr><th>#</th><th>Type</th><th>Title</th><th>Size</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead><tbody>
        <?php foreach ($documents as $r): $ft=strtolower($r['file_type']); ?>
        <tr>
          <td><?=$r['sort_order']?></td>
          <td><i class="fas <?=$docIcons[$ft]??'fa-file'?>" style="font-size:1.2rem;color:<?=$docColors[$ft]??'#64748b'?>;"></i></td>
          <td><strong><?=htmlspecialchars($r['title'])?></strong></td>
          <td style="color:#94a3b8;font-size:.75rem;"><?=htmlspecialchars($r['file_size'])?></td>
          <td><span class="badge <?=$r['is_active']?'badge-green':'badge-red'?>"><?=$r['is_active']?'On':'Off'?></span></td>
          <td style="text-align:right;">
            <a href="../<?=htmlspecialchars($r['file_path'])?>" target="_blank" class="btn btn-outline btn-sm" title="Download"><i class="fas fa-download"></i></a>
            <a href="?tab=documents&edit_doc=<?=$r['id']?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
            <form method="POST" style="display:inline;"><input type="hidden" name="action" value="tog_doc"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-outline btn-sm"><i class="fas fa-eye<?=$r['is_active']?'-slash':''?>"></i></button></form>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="del_doc"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($documents)): ?><tr><td colspan="6" style="text-align:center;padding:24px;color:#94a3b8;">No documents yet.</td></tr><?php endif; ?>
      </tbody></table>
    </div>
  </div>
</div>

<?php elseif ($tab === 'registrations'): ?>
<!-- TAB: REGISTRATIONS -->
<div class="admin-card">
  <div class="admin-card-header">
    <h2>Registrations (<?= count($regs) ?>)</h2>
    <?php $new = count(array_filter($regs,fn($r)=>$r['status']==='new')); ?>
    <?php if($new): ?><span class="badge badge-red"><?=$new?> new</span><?php endif; ?>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Organization</th><th>Sector</th><th>Message</th><th>Status</th><th>Date</th><th style="text-align:right">Actions</th></tr></thead>
        <tbody>
          <?php foreach ($regs as $r): ?>
          <tr style="<?=$r['status']==='new'?'background:#fefce8;':''?>">
            <td><strong><?=htmlspecialchars($r['full_name'])?></strong></td>
            <td><a href="mailto:<?=htmlspecialchars($r['email'])?>" style="color:#1d4ed8;"><?=htmlspecialchars($r['email'])?></a></td>
            <td><?=htmlspecialchars($r['phone'])?></td>
            <td><?=htmlspecialchars($r['organization'])?></td>
            <td><?=htmlspecialchars($r['sector'])?></td>
            <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?=htmlspecialchars($r['message'])?></td>
            <td>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="upd_reg">
                <input type="hidden" name="id" value="<?=$r['id']?>">
                <select name="status" class="form-control" style="padding:4px 8px;font-size:.75rem;width:110px;" onchange="this.form.submit()">
                  <?php foreach(['new','reviewed','contacted'] as $s): ?>
                  <option value="<?=$s?>" <?=$r['status']===$s?'selected':''?>><?=ucfirst($s)?></option>
                  <?php endforeach; ?>
                </select>
              </form>
            </td>
            <td style="white-space:nowrap;color:#94a3b8;font-size:.75rem;"><?=date('d M Y',strtotime($r['created_at']))?></td>
            <td style="text-align:right;">
              <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')">
                <input type="hidden" name="action" value="del_reg"><input type="hidden" name="id" value="<?=$r['id']?>">
                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($regs)): ?><tr><td colspan="9" style="text-align:center;padding:32px;color:#94a3b8;">No registrations yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php elseif ($tab === 'info'): ?>
<!-- TAB: INFO SECTIONS -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
  <div class="admin-card">
    <div class="admin-card-header"><h2><?= $editInfo ? 'Edit' : 'Add' ?> Info Section</h2></div>
    <div class="admin-card-body">
      <form method="POST">
        <input type="hidden" name="action" value="<?= $editInfo ? 'edit_info' : 'add_info' ?>">
        <?php if ($editInfo): ?><input type="hidden" name="id" value="<?= $editInfo['id'] ?>"><?php endif; ?>
        <div class="form-group">
          <label class="form-label">Section Type</label>
          <select name="section_type" class="form-control">
            <?php foreach (['documentation'=>'📁 Documentation Submission','application_form'=>'📝 Application Form','important_info'=>'⚠️ Important Notes','general'=>'ℹ️ General Info'] as $k=>$l): ?>
            <option value="<?=$k?>" <?= ($editInfo['section_type']??'general')===$k?'selected':'' ?>><?=$l?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Icon</label>
          <div style="display:flex;gap:8px;align-items:center;">
            <input type="text" name="icon" id="infoIcon" class="form-control" value="<?= htmlspecialchars($editInfo['icon']??'fa-info-circle') ?>">
            <i id="infoIconPrev" class="fas <?= htmlspecialchars($editInfo['icon']??'fa-info-circle') ?>" style="font-size:1.4rem;color:#1d4ed8;min-width:24px;"></i>
          </div>
          <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;">
            <?php foreach ($faIcons as $ic): ?>
              <button type="button" onclick="setIcon('infoIcon','infoIconPrev','<?=$ic?>')" style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:5px 7px;cursor:pointer;"><i class="fas <?=$ic?>"></i></button>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($editInfo['title']??'') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Content <small style="color:#64748b;">(use new lines for bullet points — prefix with • or numbers)</small></label>
          <textarea name="content" class="form-control" rows="8"><?= htmlspecialchars($editInfo['content']??'') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($editInfo['sort_order']??0) ?>">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $editInfo?'Update':'Add Section' ?></button>
        <?php if ($editInfo): ?><a href="?tab=info" class="btn btn-outline" style="margin-left:8px;">Cancel</a><?php endif; ?>
      </form>
    </div>
  </div>
  <div class="admin-card">
    <div class="admin-card-header"><h2>All Info Sections</h2></div>
    <div class="admin-card-body" style="padding:0;">
      <?php $typeLabels=['documentation'=>'Documentation','application_form'=>'App Form','important_info'=>'Important','general'=>'General']; ?>
      <table><thead><tr><th>#</th><th>Type</th><th>Title</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead><tbody>
        <?php foreach ($infoSecs as $r): ?>
        <tr>
          <td><?=$r['sort_order']?></td>
          <td><span style="background:#dbeafe;color:#1e40af;font-size:.7rem;font-weight:700;padding:2px 7px;border-radius:12px;"><?=$typeLabels[$r['section_type']]??$r['section_type']?></span></td>
          <td><strong><?=htmlspecialchars($r['title'])?></strong></td>
          <td><span class="badge <?=$r['is_active']?'badge-green':'badge-red'?>"><?=$r['is_active']?'On':'Off'?></span></td>
          <td style="text-align:right;">
            <a href="?tab=info&edit_info=<?=$r['id']?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
            <form method="POST" style="display:inline;"><input type="hidden" name="action" value="tog_info"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-outline btn-sm"><i class="fas fa-eye<?=$r['is_active']?'-slash':''?>"></i></button></form>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="del_info"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($infoSecs)): ?><tr><td colspan="5" style="text-align:center;padding:24px;color:#94a3b8;">No sections yet.</td></tr><?php endif; ?>
      </tbody></table>
    </div>
  </div>
</div>

<?php elseif ($tab === 'checklist'): ?>
<!-- TAB: DOCUMENT CHECKLIST -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
  <div class="admin-card">
    <div class="admin-card-header"><h2><?= $editCl ? 'Edit' : 'Add' ?> Checklist Item</h2></div>
    <div class="admin-card-body">
      <form method="POST">
        <input type="hidden" name="action" value="<?= $editCl ? 'edit_cl' : 'add_cl' ?>">
        <?php if ($editCl): ?><input type="hidden" name="id" value="<?= $editCl['id'] ?>"><?php endif; ?>
        <div class="form-group">
          <label class="form-label">Document Name / Title</label>
          <input type="text" name="title" class="form-control" required placeholder="e.g. Aadhar Card / Government Photo ID" value="<?= htmlspecialchars($editCl['title']??'') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Description / Note</label>
          <textarea name="description" class="form-control" placeholder="e.g. Self-attested photocopy required"><?= htmlspecialchars($editCl['description']??'') ?></textarea>
        </div>
        <div class="form-group" style="display:flex;align-items:center;gap:10px;">
          <input type="checkbox" name="is_mandatory" id="isMandatory" value="1" <?= ($editCl['is_mandatory']??1)?'checked':'' ?> style="width:18px;height:18px;accent-color:#1d4ed8;">
          <label for="isMandatory" style="font-weight:600;font-size:.9rem;cursor:pointer;">Mark as <span style="color:#ef4444;">Mandatory</span></label>
        </div>
        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" name="sort_order" class="form-control" value="<?= htmlspecialchars($editCl['sort_order']??0) ?>">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $editCl?'Update':'Add Item' ?></button>
        <?php if ($editCl): ?><a href="?tab=checklist" class="btn btn-outline" style="margin-left:8px;">Cancel</a><?php endif; ?>
      </form>
    </div>
  </div>
  <div class="admin-card">
    <div class="admin-card-header"><h2>Required Documents List</h2></div>
    <div class="admin-card-body" style="padding:0;">
      <table><thead><tr><th>#</th><th>Document</th><th>Required?</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead><tbody>
        <?php foreach ($checklist as $r): ?>
        <tr>
          <td><?=$r['sort_order']?></td>
          <td>
            <strong><?=htmlspecialchars($r['title'])?></strong>
            <?php if($r['description']): ?><br><small style="color:#94a3b8;"><?=htmlspecialchars($r['description'])?></small><?php endif; ?>
          </td>
          <td><?php if($r['is_mandatory']): ?><span class="badge badge-red">Mandatory</span><?php else: ?><span class="badge" style="background:#f1f5f9;color:#64748b;">Optional</span><?php endif; ?></td>
          <td><span class="badge <?=$r['is_active']?'badge-green':'badge-red'?>"><?=$r['is_active']?'On':'Off'?></span></td>
          <td style="text-align:right;">
            <a href="?tab=checklist&edit_cl=<?=$r['id']?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
            <form method="POST" style="display:inline;"><input type="hidden" name="action" value="tog_cl"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-outline btn-sm"><i class="fas fa-eye<?=$r['is_active']?'-slash':''?>"></i></button></form>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="del_cl"><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($checklist)): ?><tr><td colspan="5" style="text-align:center;padding:24px;color:#94a3b8;">No items yet.</td></tr><?php endif; ?>
      </tbody></table>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function setIcon(inputId, previewId, cls) {
  document.getElementById(inputId).value = cls;
  document.getElementById(previewId).className = 'fas ' + cls;
}
function toggleVtype() {
  const v = document.getElementById('vtype').value;
  document.getElementById('vyt').style.display = v === 'youtube' ? '' : 'none';
  document.getElementById('vup').style.display = v === 'upload'  ? '' : 'none';
}
</script>

<?php include 'includes/footer.php'; ?>
