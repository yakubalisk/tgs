<?php
require_once '../middleware.php';
require_once '../function.php';
include 'includes/header.php';
$db = new Database();
$pdo = $db->con;

// Counts
$counts = [
    'messages'   => $pdo->query("SELECT COUNT(*) FROM contact")->fetchColumn(),
    'slides'     => $pdo->query("SELECT COUNT(*) FROM hero_slides WHERE is_active=1")->fetchColumn(),
    'services'   => $pdo->query("SELECT COUNT(*) FROM services WHERE is_active=1")->fetchColumn(),
    'leaders'    => $pdo->query("SELECT COUNT(*) FROM leaders WHERE is_active=1")->fetchColumn(),
    'advantages' => $pdo->query("SELECT COUNT(*) FROM advantages WHERE is_active=1")->fetchColumn(),
    'logos'      => $pdo->query("SELECT COUNT(*) FROM client_logos WHERE is_active=1")->fetchColumn(),
    'gallery'    => $pdo->query("SELECT COUNT(*) FROM gallery WHERE is_active=1")->fetchColumn(),
];

// Recent messages
$recentMessages = $pdo->query("SELECT name, email, created_at FROM contact ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 style="font-size:1.4rem;font-weight:700;margin-bottom:24px;">Dashboard</h1>

<!-- Stat cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-bottom:28px;">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fas fa-images"></i></div>
    <div class="stat-info"><p>Hero Slides</p><h3><?= $counts['slides'] ?></h3></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-briefcase"></i></div>
    <div class="stat-info"><p>Services</p><h3><?= $counts['services'] ?></h3></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple"><i class="fas fa-user-tie"></i></div>
    <div class="stat-info"><p>Leaders</p><h3><?= $counts['leaders'] ?></h3></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fas fa-trophy"></i></div>
    <div class="stat-info"><p>Advantages</p><h3><?= $counts['advantages'] ?></h3></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fas fa-building"></i></div>
    <div class="stat-info"><p>Client Logos</p><h3><?= $counts['logos'] ?></h3></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-envelope"></i></div>
    <div class="stat-info"><p>Messages</p><h3><?= $counts['messages'] ?></h3></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple"><i class="fas fa-images"></i></div>
    <div class="stat-info"><p>Gallery Images</p><h3><?= $counts['gallery'] ?></h3></div>
  </div>
</div>

<!-- Quick Links -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
  <div class="admin-card">
    <div class="admin-card-header"><h2>Quick Actions</h2></div>
    <div class="admin-card-body" style="display:flex;flex-direction:column;gap:10px;">
      <a href="hero_slides.php" class="btn btn-primary"><i class="fas fa-images"></i> Manage Hero Slides</a>
      <a href="home_settings.php" class="btn btn-outline"><i class="fas fa-sliders"></i> Edit Site Settings</a>
      <a href="services_manage.php" class="btn btn-outline"><i class="fas fa-briefcase"></i> Manage Services</a>
      <a href="leaders_manage.php" class="btn btn-outline"><i class="fas fa-user-tie"></i> Manage Leadership</a>
      <a href="client_logos.php" class="btn btn-outline"><i class="fas fa-building"></i> Upload Client Logos</a>
      <a href="gallery_manage.php" class="btn btn-outline"><i class="fas fa-images"></i> Manage Gallery</a>
      <a href="messages.php" class="btn btn-outline"><i class="fas fa-envelope"></i> View Messages</a>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card-header"><h2>Recent Messages</h2></div>
    <div class="admin-card-body" style="padding:0;">
      <?php if (empty($recentMessages)): ?>
        <p style="padding:20px;color:#64748b;">No messages yet.</p>
      <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Name</th><th>Email</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach ($recentMessages as $m): ?>
            <tr>
              <td><?= htmlspecialchars($m['name']) ?></td>
              <td style="font-size:.78rem;color:#64748b;"><?= htmlspecialchars($m['email']) ?></td>
              <td style="font-size:.75rem;color:#94a3b8;"><?= date('d M', strtotime($m['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
