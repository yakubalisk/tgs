<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TGS Admin Panel</title>
  <link href="../assets/css/style.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    :root{--sidebar-w:240px;--primary:#1d4ed8;--primary-dark:#1e3a8a;--accent:#3b82f6;--bg:#f1f5f9;--white:#fff;--text:#1e293b;--text-muted:#64748b;--border:#e2e8f0;--shadow:0 1px 3px rgba(0,0,0,.08),0 4px 12px rgba(0,0,0,.04);}
    *{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;}
    /* ── Sidebar ── */
    #admin-sidebar{width:var(--sidebar-w);min-height:100vh;background:var(--primary-dark);display:flex;flex-direction:column;position:fixed;top:0;left:0;z-index:50;transition:.3s;}
    #admin-sidebar .logo{padding:24px 20px 20px;border-bottom:1px solid rgba(255,255,255,.1);}
    #admin-sidebar .logo span{font-size:1.25rem;font-weight:700;color:#fff;letter-spacing:.5px;}
    #admin-sidebar .logo small{display:block;font-size:.7rem;color:rgba(255,255,255,.5);margin-top:2px;}
    #admin-sidebar nav{flex:1;padding:16px 0;overflow-y:auto;}
    #admin-sidebar nav a{display:flex;align-items:center;gap:10px;padding:11px 20px;color:rgba(255,255,255,.75);font-size:.875rem;text-decoration:none;transition:.2s;border-left:3px solid transparent;}
    #admin-sidebar nav a:hover,#admin-sidebar nav a.active{background:rgba(255,255,255,.08);color:#fff;border-left-color:var(--accent);}
    #admin-sidebar nav a i{width:18px;text-align:center;font-size:.95rem;}
    #admin-sidebar .sidebar-label{padding:12px 20px 4px;font-size:.65rem;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:1px;}
    #admin-sidebar .sidebar-footer{padding:16px 20px;border-top:1px solid rgba(255,255,255,.1);}
    #admin-sidebar .sidebar-footer a{display:flex;align-items:center;gap:8px;color:rgba(255,255,255,.6);font-size:.8rem;text-decoration:none;}
    #admin-sidebar .sidebar-footer a:hover{color:#f87171;}
    /* ── Main wrapper ── */
    #admin-main{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;min-height:100vh;}
    /* ── Top bar ── */
    #admin-topbar{background:var(--white);border-bottom:1px solid var(--border);padding:0 28px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:40;box-shadow:var(--shadow);}
    #admin-topbar .page-title{font-size:1rem;font-weight:600;color:var(--text);}
    #admin-topbar .user-info{display:flex;align-items:center;gap:10px;font-size:.85rem;color:var(--text-muted);}
    #admin-topbar .user-info span{background:var(--primary);color:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;}
    /* ── Content area ── */
    #admin-content{padding:28px;flex:1;}
    /* ── Cards & Utilities ── */
    .admin-card{background:var(--white);border-radius:12px;box-shadow:var(--shadow);border:1px solid var(--border);}
    .admin-card-header{padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
    .admin-card-header h2{font-size:1rem;font-weight:600;}
    .admin-card-body{padding:24px;}
    .btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;font-size:.85rem;font-weight:500;cursor:pointer;border:none;text-decoration:none;transition:.2s;}
    .btn-primary{background:var(--primary);color:#fff;}.btn-primary:hover{background:var(--primary-dark);}
    .btn-sm{padding:5px 12px;font-size:.78rem;}
    .btn-danger{background:#ef4444;color:#fff;}.btn-danger:hover{background:#dc2626;}
    .btn-warning{background:#f59e0b;color:#fff;}.btn-warning:hover{background:#d97706;}
    .btn-success{background:#22c55e;color:#fff;}.btn-success:hover{background:#16a34a;}
    .btn-outline{background:transparent;border:1px solid var(--border);color:var(--text);}.btn-outline:hover{background:var(--bg);}
    .form-group{margin-bottom:18px;}
    .form-label{display:block;font-size:.82rem;font-weight:600;margin-bottom:6px;color:var(--text);}
    .form-control{width:100%;padding:9px 13px;border:1px solid var(--border);border-radius:8px;font-size:.875rem;color:var(--text);background:var(--white);outline:none;transition:.2s;}
    .form-control:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(59,130,246,.15);}
    textarea.form-control{resize:vertical;min-height:90px;}
    .alert{padding:12px 16px;border-radius:8px;font-size:.85rem;margin-bottom:16px;}
    .alert-success{background:#dcfce7;color:#166534;border:1px solid #bbf7d0;}
    .alert-danger{background:#fee2e2;color:#991b1b;border:1px solid #fecaca;}
    .table-wrap{overflow-x:auto;}
    table{width:100%;border-collapse:collapse;font-size:.85rem;}
    th{background:#f8fafc;padding:11px 14px;text-align:left;font-weight:600;color:var(--text-muted);border-bottom:1px solid var(--border);}
    td{padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle;}
    tr:last-child td{border-bottom:none;}
    tr:hover td{background:#f8fafc;}
    .badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:600;}
    .badge-green{background:#dcfce7;color:#166534;}
    .badge-red{background:#fee2e2;color:#991b1b;}
    .thumb{width:52px;height:40px;object-fit:cover;border-radius:6px;border:1px solid var(--border);}
    .stat-card{background:var(--white);border-radius:12px;box-shadow:var(--shadow);border:1px solid var(--border);padding:20px;display:flex;align-items:center;gap:16px;}
    .stat-icon{width:48px;height:48px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;}
    .stat-icon.blue{background:#dbeafe;color:var(--primary);}
    .stat-icon.green{background:#dcfce7;color:#16a34a;}
    .stat-icon.orange{background:#ffedd5;color:#ea580c;}
    .stat-icon.purple{background:#f3e8ff;color:#9333ea;}
    .stat-info p{font-size:.8rem;color:var(--text-muted);}
    .stat-info h3{font-size:1.5rem;font-weight:700;color:var(--text);}
  </style>
</head>
<body>

<?php
// Determine current page for active nav highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
function navLink($href, $icon, $label, $current) {
    $active = ($current === $href) ? 'active' : '';
    echo "<a href=\"$href\" class=\"$active\"><i class=\"fas fa-$icon\"></i> $label</a>";
}
?>

<!-- Sidebar -->
<aside id="admin-sidebar">
  <div class="logo">
    <span>⚡ TGS Admin</span>
    <small>Content Management System</small>
  </div>
  <nav>
    <div class="sidebar-label">Main</div>
    <?php navLink('index.php',      'gauge',        'Dashboard',     $currentPage); ?>

    <div class="sidebar-label">Homepage</div>
    <?php navLink('hero_slides.php',     'images',       'Hero Slides',   $currentPage); ?>
    <?php navLink('home_settings.php',   'sliders',      'Site Settings', $currentPage); ?>
    <?php navLink('services_manage.php', 'briefcase',    'Services',      $currentPage); ?>
    <?php navLink('leaders_manage.php',  'user-tie',     'Leadership',    $currentPage); ?>
    <?php navLink('advantages_manage.php','trophy',      'Why Choose Us', $currentPage); ?>
    <?php navLink('client_logos.php',    'building',     'Client Logos',  $currentPage); ?>
    <?php navLink('gallery_manage.php',  'images',       'Gallery',       $currentPage); ?>
    <?php navLink('samarth_manage.php',  'certificate',  'Samarth',       $currentPage); ?>
    <?php navLink('menu_manage.php',     'bars',         'Header Menu',   $currentPage); ?>
    <?php navLink('pages_manage.php',    'file-lines',   'Custom Pages',  $currentPage); ?>

    <div class="sidebar-label">Inbox</div>
    <?php navLink('messages.php',  'envelope',  'Messages',  $currentPage); ?>
  </nav>
  <div class="sidebar-footer">
    <a href="logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a>
  </div>
</aside>

<!-- Main Wrapper -->
<div id="admin-main">
  <!-- Top Bar -->
  <div id="admin-topbar">
    <span class="page-title">
      <?php
      $titles = [
        'index.php'              => 'Dashboard',
        'hero_slides.php'        => 'Hero Slides',
        'home_settings.php'      => 'Site Settings',
        'services_manage.php'    => 'Services',
        'leaders_manage.php'     => 'Leadership Team',
        'advantages_manage.php'  => 'Why Choose Us',
        'client_logos.php'       => 'Client Logos',
        'gallery_manage.php'     => 'Gallery',
        'samarth_manage.php'     => 'Assessment Samarth',
        'menu_manage.php'        => 'Header Menu Links',
        'pages_manage.php'       => 'Custom Pages Builder',
        'messages.php'           => 'Messages',
      ];
      echo $titles[$currentPage] ?? 'Admin Panel';
      ?>
    </span>
    <div class="user-info">
      <span>A</span> Admin
    </div>
  </div>

  <!-- Page content goes here -->
  <div id="admin-content">
