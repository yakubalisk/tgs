<?php
require_once '../middleware.php';
require_once '../function.php';
$db = new Database();
$contacts = $db->getAssoc("SELECT * FROM contact ORDER BY created_at DESC");
include 'includes/header.php';
?>

<h1 style="font-size:1.4rem;font-weight:700;margin-bottom:24px;">All Contact Messages</h1>

<div class="admin-card">
  <div class="admin-card-header"><h2>Messages (<?= count($contacts) ?>)</h2></div>
  <div class="admin-card-body" style="padding:0;">
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Name</th><th>Email</th><th>Phone</th><th>Message</th><th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($contacts as $row): ?>
          <tr>
            <td><strong><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></strong></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['phone_no']) ?></td>
            <td style="max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($row['message']) ?></td>
            <td style="white-space:nowrap;color:#64748b;"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($contacts)): ?>
            <tr><td colspan="5" style="text-align:center;padding:32px;color:#64748b;">No messages yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
