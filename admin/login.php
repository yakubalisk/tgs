<?php
session_start();
include '../function.php';
$db = new Database();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = $_POST['password'];

  if ($username && $password) {
    $user = $db->getAssoc("SELECT * FROM admin_users WHERE username = :username", ['username' => $username]);

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['admin_logged_in'] = true;
      $_SESSION['admin_user'] = $user['username'];
      header("Location: index.php");
      exit;
    } else {
      $error = "Invalid username or password";
    }
  } else {
    $error = "Please fill in both fields";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>TGS Admin Login</title>
  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const toggleIcon = document.getElementById('togglePasswordIcon');

      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.innerHTML = eyeOffIcon;
      } else {
        passwordInput.type = 'password';
        toggleIcon.innerHTML = eyeIcon;
      }
    }

    const eyeIcon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
    const eyeOffIcon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.057 10.057 0 014.276-5.148M10.6 5.215A9.968 9.968 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.973 9.973 0 01-3.018 4.103M3 3l18 18"/></svg>';
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-100 via-white to-indigo-100 flex items-center justify-center p-4">

  <div class="w-full max-w-md bg-white shadow-lg rounded-xl overflow-hidden">
    <div class="p-6 text-center">
      <div class="flex justify-center mb-4">
        <svg class="h-12 w-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path d="M12 12c2.28 0 4-1.72 4-4s-1.72-4-4-4-4 1.72-4 4 1.72 4 4 4zM4 20c0-2.67 5.33-4 8-4s8 1.33 8 4v1H4v-1z"/>
        </svg>
      </div>
      <h2 class="text-2xl font-bold text-blue-600">TGS Admin Portal</h2>
      <p class="text-sm text-gray-500 mt-1">Sign in to access the admin dashboard</p>
    </div>
    <div class="px-6 pb-6">
      <?php if (!empty($error)) : ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <form method="post" class="space-y-4">
        <div>
          <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
          <input type="text" name="username" id="username" required placeholder="Enter your username"
                 class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <div class="relative">
            <input type="password" name="password" id="password" required placeholder="Enter your password"
                   class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
            <button type="button" onclick="togglePassword()" class="absolute right-2 top-2 p-1">
              <span id="togglePasswordIcon" class="h-5 w-5 text-gray-500" aria-hidden="true"><?php echo "<script>document.write(eyeIcon);</script>"; ?></span>
            </button>
          </div>
        </div>

        <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-200">
          Sign In
        </button>
      </form>

      <div class="mt-4 text-center text-sm text-gray-500">
        Demo credentials: <strong>admin / admin123</strong>
      </div>
    </div>
  </div>

</body>
</html>
