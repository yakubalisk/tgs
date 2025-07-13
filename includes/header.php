<?php // includes/header.php ?>
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tirth Global Solutions LLP</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- <link href="assets/css/style.css" rel="stylesheet"> -->
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
<!-- Add this in your <head> if not already included -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-white text-gray-800 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-300">

<header class="bg-white dark:bg-gray-800 shadow sticky top-0 z-50">


<div class="flex items-center justify-between px-6 py-2 text-sm text-gray-600 border-b border-gray-300">
  <div class="flex items-center gap-6">
    <div class="flex items-center gap-2">
      <i class="fa fa-light fa-phone text-sm text-gray-400"></i>
      <span class="text-sm text-gray-400">+91-865-507-5656</span>
    </div>
    <div class="flex items-center gap-2">
      <i class="fa fa-light fa-envelope text-sm text-gray-400"></i>
      <span class="text-sm text-gray-400">hr.tirthglobal@gmail.com</span>
    </div>
  </div>
  <div class="text-xs text-gray-400">
    Since 2015 | Global Recruitment Excellence
  </div>
</div>

  <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
    <a href="index.php" class="flex items-center text-2xl font-bold text-blue-700 dark:text-white">
    <img 
      width="80" 
      height="50" 
      src="assets/images/tgs_logo.png" 
      alt="Tirth Global Solutions Logo"
      class="w-100 h-12"
    >
    <h3 class="text-gray-500 text-2xl ml-2">Tirth Global Solutions</h3>
  </a>
    <!-- <a href="index.php" class="text-2xl font-bold text-blue-700 dark:text-white"><img width="50" height="50" src="assets/images/tgs_logo.png"><span class="text-gray-500 text-base ml-2">Tirth Global Solutions</span></a> -->
    <nav class="space-x-4">
  <a href="index.php" class="<?= $currentPage == 'index.php' ? 'text-blue-600 font-semibold underline' : 'hover:underline' ?>">Home</a>
  <a href="about.php" class="<?= $currentPage == 'about.php' ? 'text-blue-600 font-semibold underline' : 'hover:underline' ?>">About</a>
  <a href="services.php" class="<?= $currentPage == 'services.php' ? 'text-blue-600 font-semibold underline' : 'hover:underline' ?>">Services</a>
  <a href="recruitment-process.php" class="<?= $currentPage == 'recruitment-process.php' ? 'text-blue-600 font-semibold underline' : 'hover:underline' ?>">Recruitment Process</a>
  <a href="clients.php" class="<?= $currentPage == 'clients.php' ? 'text-blue-600 font-semibold underline' : 'hover:underline' ?>">Clients</a>
  <a href="why-us.php" class="<?= $currentPage == 'why-us.php' ? 'text-blue-600 font-semibold underline' : 'hover:underline' ?>">Why Us</a>
  <a href="careers.php" class="<?= $currentPage == 'careers.php' ? 'text-blue-600 font-semibold underline' : 'hover:underline' ?>">Careers</a>
  <!-- <a href="contact.php" class="<?= $currentPage == 'contact.php' ? 'text-blue-600 font-semibold underline' : 'hover:underline' ?>">Contact</a> -->
  <!-- Get Quote - Outline Button, hidden on small screens -->
<!-- <button class="hidden md:inline-flex items-center px-4 py-2 border border-blue-600 text-blue-600 text-sm rounded-md hover:bg-blue-50 transition">
  Get Quote
</button> -->

<!-- Contact Us - Filled Button -->
<a href="contact.php" class="items-center px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition">
  Contact Us
</a>

  <!-- <button id="toggle-theme" class="ml-4 px-2 py-1 border border-gray-400 rounded text-sm">Toggle Theme</button> -->
    </nav>
  </div>
</header>

