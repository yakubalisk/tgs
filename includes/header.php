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
  <!-- Top Bar -->
  <div class="flex items-center justify-between px-6 py-2 text-sm text-gray-600 border-b border-gray-300">
    <div class="flex items-center gap-6">
      <div class="flex items-center gap-2">
        <i class="fa fa-phone text-sm text-gray-400"></i>
        <span class="text-sm text-gray-400">+91-865-507-5656</span>
      </div>
      <div class="flex items-center gap-2">
        <i class="fa fa-envelope text-sm text-gray-400"></i>
        <span class="text-sm text-gray-400">info.tirthglobal@gmail.com</span>
      </div>
    </div>
    <div class="text-xs text-gray-400 hidden sm:block">
      Since 2015 | Global Recruitment Excellence
    </div>
  </div>

  <!-- Main Nav -->
  <div class="max-w-7xl mx-auto px-4 py-4">
    <div class="flex items-center justify-between">
      <a href="index.php" class="flex items-center text-2xl font-bold text-blue-700 dark:text-white">
        <img width="80" height="50" src="assets/images/tgs_logo.png" alt="TGS Logo" class="w-100 h-12">
        <h3 class="text-gray-500 text-2xl ml-2">Tirth Global Solutions LLP</h3>
      </a>

      <!-- Mobile Toggle Button -->
      <button id="menuToggle" class="sm:hidden text-gray-700 dark:text-gray-200 focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

      <!-- Desktop Nav -->
      <nav class="hidden sm:flex items-center space-x-4" id="desktopMenu">
        <a href="index.php">Home</a>
        <a href="about.php">About</a>

        <!-- Services Dropdown -->
        <div class="relative group">
          <button class="hover:text-blue-600">Services</button>
          <div class="absolute left-0 mt-2 w-48 bg-white shadow-lg rounded-md border opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
            <a href="services.php#manpower" class="block px-4 py-2 hover:bg-blue-50">Manpower Recruitment</a>
            <a href="services.php#skill" class="block px-4 py-2 hover:bg-blue-50">Skill Assessment</a>
            <a href="services.php#hr" class="block px-4 py-2 hover:bg-blue-50">HR Management</a>
            <a href="services.php#training" class="block px-4 py-2 hover:bg-blue-50">Training & Development</a>
          </div>
        </div>

        <!-- Assessment Dropdown -->
        <div class="relative group">
          <button class="hover:text-blue-600">Assessment</button>
          <div class="absolute left-0 mt-2 w-48 bg-white shadow-lg rounded-md border opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
            <a href="careers.php#schemes" class="block px-4 py-2 hover:bg-blue-50">Assessment Samarth</a>
            <a href="careers.php#assessors" class="block px-4 py-2 hover:bg-blue-50">Sports Assessor</a>
          </div>
        </div>

        <a href="recruitment-process.php">Recruitment Process</a>
        <a href="why-us.php">Why Us</a>
        <a href="careers.php">Careers</a>
        <a href="contact.php" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">Contact Us</a>
      </nav>
    </div>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="sm:hidden hidden flex flex-col space-y-2 mt-4 text-sm">
      <a href="index.php">Home</a>
      <a href="about.php">About</a>
      <a href="services.php">Services</a>
      <a href="services.php#skill">Skill Assessment</a>
      <a href="services.php#hr">HR Management</a>
      <a href="services.php#training">Training & Development</a>
      <a href="careers.php">Assessment</a>
      <a href="careers.php#schemes">Assessment Samarth</a>
      <a href="careers.php#assessors">Sports Assessor</a>
      <a href="recruitment-process.php">Recruitment Process</a>
      <a href="why-us.php">Why Us</a>
      <a href="careers.php">Careers</a>
      <a href="contact.php" class="bg-blue-600 text-white px-4 py-2 rounded text-center">Contact Us</a>
    </div>
  </div>

  <script>
    const menuToggle = document.getElementById('menuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    menuToggle.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });
  </script>
</header>


