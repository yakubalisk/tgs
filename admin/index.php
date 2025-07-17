<?php include '../middleware.php'; 
include '../function.php';
include 'includes/header.php';
$db = new Database();

// Get count of all messages
$total_messages = $db->getAssoc("SELECT COUNT(*) as total FROM contact");
// print_r($total_messages);die();
?>


<div class="max-w-6xl mx-auto px-4 py-12">
  <h1 class="text-3xl font-bold mb-6">Admin Dashboard</h1>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white shadow p-6 rounded-lg text-center">
      <h2 class="text-4xl font-bold text-blue-700"><?= $total_messages['total'] ?></h2>
      <p class="text-gray-600 mt-2">Total Contact Messages</p>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
