<?php // includes/header.php ?>
<?php 
$currentPage = basename($_SERVER['PHP_SELF']); 
require_once __DIR__ . '/../function.php';
$db = new Database();
$pdo = $db->con;

// Fetch settings
$headerSettings = $pdo->query("SELECT skey, sval FROM home_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
function hs($k, $settings, $fallback = '') {
    return htmlspecialchars($settings[$k] ?? $fallback);
}

// Fetch menu items
$menuRows = $pdo->query("SELECT * FROM menu_items WHERE is_active=1 ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
$menuItems = [];
foreach ($menuRows as $row) {
    if ($row['parent_id'] === null) {
        $menuItems[$row['id']] = $row;
        $menuItems[$row['id']]['children'] = [];
    }
}
foreach ($menuRows as $row) {
    if ($row['parent_id'] !== null && isset($menuItems[$row['parent_id']])) {
        $menuItems[$row['parent_id']]['children'][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= hs('site_name', $headerSettings, 'Tirth Global Solutions') ?> <?= hs('site_suffix', $headerSettings, 'LLP') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* ── Google Font ─────────────────────────────────────────── */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body { font-family: 'Inter', system-ui, sans-serif; }

    /* ── Nav link base + active ──────────────────────────────── */
    .nav-link {
      position: relative;
      font-size: .875rem;
      font-weight: 500;
      color: #374151;
      padding: 6px 2px;
      text-decoration: none;
      transition: color .2s;
      white-space: nowrap;
    }
    .nav-link::after {
      content: '';
      position: absolute;
      left: 0; bottom: -2px;
      width: 0; height: 2px;
      background: #1d4ed8;
      border-radius: 2px;
      transition: width .25s ease;
    }
    .nav-link:hover, .nav-link.active { color: #1d4ed8; }
    .nav-link:hover::after, .nav-link.active::after { width: 100%; }

    /* ── Dropdown ────────────────────────────────────────────── */
    .dropdown-menu {
      position: absolute;
      top: calc(100% + 12px);
      left: 50%;
      transform: translateX(-50%);
      min-width: 210px;
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      box-shadow: 0 8px 32px rgba(0,0,0,.12);
      opacity: 0;
      visibility: hidden;
      transform: translateX(-50%) translateY(6px);
      transition: opacity .2s, transform .2s, visibility .2s;
      z-index: 100;
      padding: 6px 0;
    }
    /* little arrow pointer */
    .dropdown-menu::before {
      content: '';
      position: absolute;
      top: -6px; left: 50%;
      transform: translateX(-50%);
      border: 6px solid transparent;
      border-top: 0;
      border-bottom-color: #fff;
      filter: drop-shadow(0 -2px 1px rgba(0,0,0,.06));
    }
    .dropdown-wrap:hover .dropdown-menu,
    .dropdown-wrap:focus-within .dropdown-menu {
      opacity: 1;
      visibility: visible;
      transform: translateX(-50%) translateY(0);
    }
    .dropdown-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 18px;
      font-size: .82rem;
      font-weight: 500;
      color: #374151;
      text-decoration: none;
      transition: background .15s, color .15s;
    }
    .dropdown-item:hover { background: #eff6ff; color: #1d4ed8; }
    .dropdown-item i { width: 16px; text-align: center; color: #3b82f6; font-size: .8rem; }

    /* ── Dropdown trigger button ─────────────────────────────── */
    .dropdown-trigger {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: .875rem;
      font-weight: 500;
      color: #374151;
      background: none;
      border: none;
      cursor: pointer;
      padding: 6px 2px;
      transition: color .2s;
    }
    .dropdown-trigger .chevron {
      font-size: .65rem;
      transition: transform .2s;
    }
    .dropdown-wrap:hover .dropdown-trigger,
    .dropdown-wrap:focus-within .dropdown-trigger { color: #1d4ed8; }
    .dropdown-wrap:hover .chevron,
    .dropdown-wrap:focus-within .chevron { transform: rotate(180deg); }

    /* ── Mobile slide-in menu ───────────────────────────────── */
    #mobileDrawer {
      position: fixed;
      top: 0; right: -100%;
      width: min(320px, 85vw);
      height: 100vh;
      background: #1e3a8a;
      z-index: 200;
      transition: right .3s ease;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
    }
    #mobileDrawer.open { right: 0; }
    #mobileOverlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.45);
      z-index: 199;
    }
    #mobileOverlay.open { display: block; }
    .mob-link {
      display: block;
      padding: 12px 24px;
      font-size: .9rem;
      font-weight: 500;
      color: rgba(255,255,255,.85);
      text-decoration: none;
      border-left: 3px solid transparent;
      transition: all .15s;
    }
    .mob-link:hover { color: #fff; background: rgba(255,255,255,.08); border-left-color: #60a5fa; }
    .mob-section-title {
      padding: 12px 24px 4px;
      font-size: .65rem;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: rgba(255,255,255,.35);
    }
  </style>
</head>
<body class="bg-white text-gray-800 transition-colors duration-300">

<!-- ══════════════════════════════════════════════════════════ -->
<!-- TOP INFO BAR                                              -->
<!-- ══════════════════════════════════════════════════════════ -->
<div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white text-xs py-2 px-4 hidden sm:block">
  <div class="max-w-7xl mx-auto flex items-center justify-between">
    <div class="flex items-center gap-6">
      <a href="tel:<?= preg_replace('/[^0-9+]/', '', hs('topbar_phone', $headerSettings, '+91-865-507-5656')) ?>" class="flex items-center gap-2 text-blue-200 hover:text-white transition">
        <i class="fas fa-phone-alt text-blue-300 text-[10px]"></i>
        <?= hs('topbar_phone', $headerSettings, '+91-865-507-5656') ?>
      </a>
      <a href="mailto:<?= hs('topbar_email', $headerSettings, 'info.tirthglobal@gmail.com') ?>" class="flex items-center gap-2 text-blue-200 hover:text-white transition">
        <i class="fas fa-envelope text-blue-300 text-[10px]"></i>
        <?= hs('topbar_email', $headerSettings, 'info.tirthglobal@gmail.com') ?>
      </a>
    </div>
    <div class="flex items-center gap-4 text-blue-200">
      <span class="flex items-center gap-1">
        <i class="fas fa-circle text-green-400" style="font-size:6px;"></i>
        <?= hs('topbar_tagline', $headerSettings, 'Since 2015 | Global Recruitment Excellence') ?>
      </span>
      <div class="flex items-center gap-3 ml-4">
        <?php if (hs('topbar_linkedin', $headerSettings, '#') !== ''): ?>
          <a href="<?= hs('topbar_linkedin', $headerSettings, '#') ?>" class="hover:text-white transition" target="_blank"><i class="fab fa-linkedin-in"></i></a>
        <?php endif; ?>
        <?php if (hs('topbar_facebook', $headerSettings, '#') !== ''): ?>
          <a href="<?= hs('topbar_facebook', $headerSettings, '#') ?>" class="hover:text-white transition" target="_blank"><i class="fab fa-facebook-f"></i></a>
        <?php endif; ?>
        <?php if (hs('topbar_twitter', $headerSettings, '#') !== ''): ?>
          <a href="<?= hs('topbar_twitter', $headerSettings, '#') ?>" class="hover:text-white transition" target="_blank"><i class="fab fa-twitter"></i></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- MAIN NAV                                                  -->
<!-- ══════════════════════════════════════════════════════════ -->
<header class="bg-white sticky top-0 z-50 border-b border-gray-100" style="box-shadow:0 2px 20px rgba(0,0,0,.07);">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between h-16 sm:h-20">

      <!-- Logo -->
      <a href="index.php" class="flex items-center gap-3 flex-shrink-0">
        <img src="<?= hs('site_logo', $headerSettings, 'assets/images/tgs_logo.png') ?>" alt="TGS Logo" class="h-10 w-auto object-contain">
        <div class="hidden sm:block">
          <span class="text-blue-900 font-bold text-2xl leading-tight block"><?= hs('site_name', $headerSettings, 'Tirth Global Solutions LLP.') ?></span>
          <!-- <span class="text-blue-500 font-medium text-xs tracking-wide"><?= hs('site_suffix', $headerSettings, 'LLP') ?></span> -->
        </div>
      </a>

      <!-- Desktop Nav -->
      <nav class="hidden lg:flex items-center gap-1 xl:gap-2" id="desktopMenu">
        <?php foreach ($menuItems as $m): ?>
          <?php if (empty($m['children'])): ?>
            <?php 
              $active = ($currentPage === $m['url']) ? 'active' : '';
            ?>
            <a href="<?= htmlspecialchars($m['url']) ?>" class="nav-link <?= $active ?>"><?= htmlspecialchars($m['title']) ?></a>
          <?php else: ?>
            <div class="dropdown-wrap relative">
              <button class="dropdown-trigger">
                <?= htmlspecialchars($m['title']) ?>
                <i class="fas fa-chevron-down chevron"></i>
              </button>
              <div class="dropdown-menu">
                <?php foreach ($m['children'] as $child): ?>
                  <a href="<?= htmlspecialchars($child['url']) ?>" class="dropdown-item">
                    <?php if ($child['icon']): ?>
                      <i class="fas <?= htmlspecialchars($child['icon']) ?>"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($child['title']) ?>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>

        <!-- Contact Us CTA button (rendered by default at the end) -->
        <a href="contact.php"
           class="ml-3 inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg
                  hover:bg-blue-700 active:bg-blue-800 shadow-sm hover:shadow-md transition-all duration-200">
          <i class="fas fa-paper-plane text-xs"></i>
          Contact Us
        </a>
      </nav>

      <!-- Mobile hamburger -->
      <button id="mobileMenuBtn"
              class="lg:hidden w-10 h-10 flex flex-col items-center justify-center gap-[5px] rounded-lg hover:bg-blue-50 transition"
              aria-label="Open menu">
        <span class="ham-line block w-6 h-0.5 bg-blue-900 rounded transition-all duration-300"></span>
        <span class="ham-line block w-6 h-0.5 bg-blue-900 rounded transition-all duration-300"></span>
        <span class="ham-line block w-4 h-0.5 bg-blue-900 rounded transition-all duration-300"></span>
      </button>

    </div>
  </div>
</header>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- MOBILE DRAWER                                             -->
<!-- ══════════════════════════════════════════════════════════ -->
<div id="mobileOverlay"></div>
<div id="mobileDrawer">

  <!-- Drawer header -->
  <div class="flex items-center justify-between px-6 py-5 border-b border-blue-700 flex-shrink-0">
    <div class="flex items-center gap-3">
      <img src="<?= hs('site_logo', $headerSettings, 'assets/images/tgs_logo.png') ?>" alt="TGS" class="h-8 w-auto brightness-0 invert">
      <div>
        <span class="text-white font-bold text-md block leading-tight"><?= hs('site_name', $headerSettings, 'Tirth Global Solutions LLP.') ?></span>
        <!-- <span class="text-blue-300 text-xs"><?= hs('site_suffix', $headerSettings, 'LLP') ?></span> -->
      </div>
    </div>
    <button id="closeMobileMenu" class="w-8 h-8 rounded-full bg-blue-800 text-white flex items-center justify-center hover:bg-blue-700 transition">
      <i class="fas fa-times text-xs"></i>
    </button>
  </div>

  <!-- Drawer links -->
  <nav class="flex-1 py-4">
    <?php foreach ($menuItems as $m): ?>
      <?php if (empty($m['children'])): ?>
        <a href="<?= htmlspecialchars($m['url']) ?>" class="mob-link"><?= htmlspecialchars($m['title']) ?></a>
      <?php else: ?>
        <div class="mob-section-title"><?= htmlspecialchars($m['title']) ?></div>
        <?php foreach ($m['children'] as $child): ?>
          <a href="<?= htmlspecialchars($child['url']) ?>" class="mob-link pl-8">
            <?= htmlspecialchars($child['title']) ?>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>

  <!-- Drawer CTA -->
  <div class="px-6 pb-8 flex-shrink-0">
    <a href="contact.php"
       class="flex items-center justify-center gap-2 w-full py-3 bg-white text-blue-900 text-sm font-bold rounded-xl hover:bg-blue-50 transition">
      <i class="fas fa-paper-plane"></i>
      Contact Us
    </a>
    <div class="mt-4 flex items-center gap-4 justify-center">
      <a href="tel:<?= preg_replace('/[^0-9+]/', '', hs('topbar_phone', $headerSettings, '+91-865-507-5656')) ?>" class="text-blue-300 hover:text-white transition text-sm"><i class="fas fa-phone-alt mr-1"></i>Call Us</a>
      <span class="text-blue-700">|</span>
      <a href="mailto:<?= hs('topbar_email', $headerSettings, 'info.tirthglobal@gmail.com') ?>" class="text-blue-300 hover:text-white transition text-sm"><i class="fas fa-envelope mr-1"></i>Email</a>
    </div>
  </div>
</div>

<script>
(function () {
  const btn     = document.getElementById('mobileMenuBtn');
  const drawer  = document.getElementById('mobileDrawer');
  const overlay = document.getElementById('mobileOverlay');
  const close   = document.getElementById('closeMobileMenu');
  const lines   = document.querySelectorAll('.ham-line');

  function openDrawer() {
    drawer.classList.add('open');
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    lines[0].style.transform = 'rotate(45deg) translate(4px, 4px)';
    lines[1].style.transform = 'rotate(-45deg) translate(4px, -4px)';
    lines[2].style.opacity = '0';
  }
  function closeDrawer() {
    drawer.classList.remove('open');
    overlay.classList.remove('open');
    document.body.style.overflow = '';
    lines.forEach(l => { l.style.transform = ''; l.style.opacity = ''; });
  }

  btn.addEventListener('click', openDrawer);
  close.addEventListener('click', closeDrawer);
  overlay.addEventListener('click', closeDrawer);

  // AOS init (safe — runs after body is ready)
  window.addEventListener('DOMContentLoaded', () => {
    if (typeof AOS !== 'undefined') AOS.init({ once: true, duration: 700 });
  });
})();
</script>
