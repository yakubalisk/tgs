<?php include 'includes/header.php'; ?>

<section class="py-16 bg-gray-100">
  <div class="max-w-3xl mx-auto px-4">
    <h2 class="text-3xl font-bold mb-6 text-center">Contact Us</h2>
    <form action="submit_contact.php" method="POST" class="space-y-4 bg-white p-6 shadow rounded-lg">
      <input type="text" name="name" placeholder="Your Name" class="w-full p-2 border rounded" required>
      <input type="email" name="email" placeholder="Your Email" class="w-full p-2 border rounded" required>
      <textarea name="message" placeholder="Your Message" class="w-full p-2 border rounded" rows="5" required></textarea>
      <button type="submit" class="bg-blue-700 text-white px-6 py-2 rounded hover:bg-blue-800">Send</button>
    </form>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
