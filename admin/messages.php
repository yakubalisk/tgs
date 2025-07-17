<?php
include '../function.php';
$db = new Database();

$contacts = $db->getAssoc("SELECT * FROM contact ORDER BY created_at DESC");
// print_r($contacts);die();
?>

<?php include 'includes/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 py-12">
  <h1 class="text-2xl font-bold mb-6">All Contact Messages</h1>

  <div class="overflow-x-auto bg-white shadow rounded-lg">
    <table class="min-w-full table-auto">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 text-left">Name</th>
          <th class="px-4 py-2 text-left">Email</th>
          <th class="px-4 py-2 text-left">Phone</th>
          <th class="px-4 py-2 text-left">Message</th>
          <th class="px-4 py-2 text-left">Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($contacts as $row): ?>
        <tr class="border-t">
          <td class="px-4 py-2"><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($row['email']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($row['phone_no']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($row['message']) ?></td>
          <td class="px-4 py-2 text-sm text-gray-500"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
