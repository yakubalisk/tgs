<?php
require_once 'function.php';
$db  = new Database();
$pdo = $db->con;

// ── Fetch all homepage data ─────────────────────────────────────
$heroSlides  = $pdo->query("SELECT * FROM hero_slides  WHERE is_active=1 ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
$services    = $pdo->query("SELECT * FROM services     WHERE is_active=1 ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
$leaders     = $pdo->query("SELECT * FROM leaders      WHERE is_active=1 ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
$advantages  = $pdo->query("SELECT * FROM advantages   WHERE is_active=1 ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
$clientLogos = $pdo->query("SELECT * FROM client_logos WHERE is_active=1 ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
$galleryImgs = $pdo->query("SELECT * FROM gallery      WHERE is_active=1 ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);

// ── Fetch settings ──────────────────────────────────────────────
$settingsRaw = $pdo->query("SELECT skey, sval FROM home_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
function cfg($key, $settings, $fallback = '') { return $settings[$key] ?? $fallback; }

$expertiseItems = json_decode(cfg('expertise_items', $settingsRaw, '[]'), true) ?: [];

include 'includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- HERO SLIDER SECTION                                           -->
<!-- ══════════════════════════════════════════════════════════════ -->
<section id="home" class="relative w-full overflow-hidden" style="min-height:100vh;">

<?php if (!empty($heroSlides)): ?>

  <!-- Slides Container -->
  <div id="heroSlider" class="relative w-full h-full" style="min-height:100vh;">
    <?php foreach ($heroSlides as $idx => $slide): ?>
    <div class="hero-slide absolute inset-0 transition-opacity duration-700 <?= $idx === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' ?>"
         style="min-height:100vh;">
      <!-- Background Image -->
      <div class="absolute inset-0 bg-cover bg-center bg-no-repeat"
           style="background-image:url('<?= htmlspecialchars($slide['image']) ?>');"></div>
      <!-- Dark overlay -->
      <div class="absolute inset-0 bg-gradient-to-br from-blue-950/80 via-blue-900/70 to-blue-700/60"></div>

      <!-- Slide Content -->
      <div class="relative z-20 flex items-center min-h-screen">
        <div class="container mx-auto px-6 py-24">
          <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Left -->
            <div class="space-y-8 text-white" data-aos="fade-up">
              <?php if ($slide['title']): ?>
              <h1 class="text-5xl lg:text-6xl font-bold leading-tight">
                <?= htmlspecialchars($slide['title']) ?>
              </h1>
              <?php else: ?>
              <h1 class="text-5xl lg:text-6xl font-bold leading-tight">
                Global Talent,<span class="text-blue-300"> Local Expertise</span>
              </h1>
              <?php endif; ?>

              <?php if ($slide['subtitle']): ?>
              <p class="text-xl text-white/90 leading-relaxed max-w-2xl">
                <?= htmlspecialchars($slide['subtitle']) ?>
              </p>
              <?php endif; ?>

              <?php if ($slide['btn1_label'] || $slide['btn2_label']): ?>
              <div class="flex flex-col sm:flex-row gap-4">
                <?php if ($slide['btn1_label']): ?>
                <a href="<?= htmlspecialchars($slide['btn1_link'] ?: '#') ?>"
                   class="inline-flex items-center justify-center px-6 py-3 bg-white text-blue-700 text-lg font-medium rounded-md hover:bg-gray-100 transition group">
                  <?= htmlspecialchars($slide['btn1_label']) ?>
                  <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </a>
                <?php endif; ?>
                <?php if ($slide['btn2_label']): ?>
                <a href="<?= htmlspecialchars($slide['btn2_link'] ?: '#') ?>"
                   class="inline-flex items-center justify-center px-6 py-3 border border-white text-white text-lg font-medium rounded-md hover:bg-white hover:text-blue-700 transition">
                  <?= htmlspecialchars($slide['btn2_label']) ?>
                </a>
                <?php endif; ?>
              </div>
              <?php else: ?>
              <!-- Default CTAs when no buttons set -->
              <div class="flex flex-col sm:flex-row gap-4">
                <a href="#services" class="inline-flex items-center justify-center px-6 py-3 bg-white text-blue-700 text-lg font-medium rounded-md hover:bg-gray-100 transition group">
                  Explore Services <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                </a>
                <a href="contact.php" class="inline-flex items-center justify-center px-6 py-3 border border-white text-white text-lg font-medium rounded-md hover:bg-white hover:text-blue-700 transition">
                  Get Consultation
                </a>
              </div>
              <?php endif; ?>

              <!-- Stats -->
              <div class="grid grid-cols-3 gap-6 pt-8 border-t border-white/20">
                <div class="text-center">
                  <div class="flex items-center justify-center mb-2"><i class="fas fa-globe text-blue-300 text-xl"></i></div>
                  <div class="text-2xl font-bold"><?= htmlspecialchars(cfg('hero_stat1_val', $settingsRaw, '9+')) ?></div>
                  <div class="text-sm text-white/80"><?= htmlspecialchars(cfg('hero_stat1_label', $settingsRaw, 'Years Experience')) ?></div>
                </div>
                <div class="text-center">
                  <div class="flex items-center justify-center mb-2"><i class="fas fa-users text-blue-300 text-xl"></i></div>
                  <div class="text-2xl font-bold"><?= htmlspecialchars(cfg('hero_stat2_val', $settingsRaw, '1000+')) ?></div>
                  <div class="text-sm text-white/80"><?= htmlspecialchars(cfg('hero_stat2_label', $settingsRaw, 'Successful Placements')) ?></div>
                </div>
                <div class="text-center">
                  <div class="flex items-center justify-center mb-2"><i class="fas fa-award text-blue-300 text-xl"></i></div>
                  <div class="text-2xl font-bold"><?= htmlspecialchars(cfg('hero_stat3_val', $settingsRaw, '50+')) ?></div>
                  <div class="text-sm text-white/80"><?= htmlspecialchars(cfg('hero_stat3_label', $settingsRaw, 'Industry Sectors')) ?></div>
                </div>
              </div>
            </div>

            <!-- Right — Expertise Box -->
            <div class="relative" data-aos="fade-left">
              <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 shadow-xl text-white border border-white/20">
                <h3 class="text-2xl font-semibold mb-6"><?= htmlspecialchars(cfg('expertise_heading', $settingsRaw, 'Our Expertise Areas')) ?></h3>
                <ul class="space-y-4">
                  <?php foreach ($expertiseItems as $item): ?>
                  <li class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-blue-300 rounded-full flex-shrink-0"></div>
                    <span><?= htmlspecialchars($item) ?></span>
                  </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- Slide Number Indicator -->
    <?php if (count($heroSlides) > 1): ?>

    <!-- Prev / Next Arrows -->
    <button onclick="heroGo(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 z-30
      w-12 h-12 bg-white/20 hover:bg-white/40 backdrop-blur-sm rounded-full
      flex items-center justify-center text-white transition-all duration-200 border border-white/30">
      <i class="fas fa-chevron-left text-lg"></i>
    </button>
    <button onclick="heroGo(1)" class="absolute right-4 top-1/2 -translate-y-1/2 z-30
      w-12 h-12 bg-white/20 hover:bg-white/40 backdrop-blur-sm rounded-full
      flex items-center justify-center text-white transition-all duration-200 border border-white/30">
      <i class="fas fa-chevron-right text-lg"></i>
    </button>

    <!-- Dots -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-30 flex gap-3">
      <?php foreach ($heroSlides as $idx => $s): ?>
      <button onclick="heroGoTo(<?= $idx ?>)" id="heroDot<?= $idx ?>"
        class="hero-dot w-3 h-3 rounded-full border-2 border-white transition-all duration-300
               <?= $idx === 0 ? 'bg-white scale-125' : 'bg-white/40 hover:bg-white/70' ?>">
      </button>
      <?php endforeach; ?>
    </div>

    <?php endif; ?>
  </div>

<?php else: ?>
  <!-- Fallback hero when no slides configured -->
  <div class="relative min-h-screen flex items-center bg-gradient-to-br from-blue-900 to-blue-600 text-white overflow-hidden">
    <div class="container mx-auto px-6 py-20 relative z-10">
      <div class="grid lg:grid-cols-2 gap-12 items-center">
        <div class="space-y-8" data-aos="fade-up">
          <h1 class="text-5xl lg:text-6xl font-bold leading-tight">
            Global Talent,<span class="text-blue-300"> Local Expertise</span>
          </h1>
          <p class="text-xl text-white/90 leading-relaxed max-w-2xl">
            Since 2015, Tirth Global Solutions has been connecting exceptional talent with leading organizations across industries and geographies.
          </p>
          <div class="flex flex-col sm:flex-row gap-4">
            <a href="#services" class="inline-flex items-center justify-center px-6 py-3 bg-white text-blue-700 text-lg font-medium rounded-md hover:bg-gray-100 transition group">
              Explore Services <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </a>
            <a href="contact.php" class="inline-flex items-center justify-center px-6 py-3 border border-white text-white text-lg font-medium rounded-md hover:bg-white hover:text-blue-700 transition">
              Get Consultation
            </a>
          </div>
        </div>
        <div class="relative" data-aos="fade-left">
          <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 shadow-xl text-white border border-white/20">
            <h3 class="text-2xl font-semibold mb-6"><?= htmlspecialchars(cfg('expertise_heading', $settingsRaw, 'Our Expertise Areas')) ?></h3>
            <ul class="space-y-4">
              <?php foreach ($expertiseItems as $item): ?>
              <li class="flex items-center gap-3"><div class="w-2 h-2 bg-blue-300 rounded-full"></div><span><?= htmlspecialchars($item) ?></span></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>
</section>

<!-- Slider JS -->
<?php if (count($heroSlides) > 1): ?>
<script>
(function() {
  const slides   = document.querySelectorAll('.hero-slide');
  const dots     = document.querySelectorAll('.hero-dot');
  let   current  = 0;
  let   timer    = null;
  const total    = slides.length;

  function show(idx) {
    slides[current].classList.replace('opacity-100','opacity-0');
    slides[current].classList.replace('z-10','z-0');
    dots[current].classList.remove('bg-white','scale-125');
    dots[current].classList.add('bg-white/40');

    current = (idx + total) % total;

    slides[current].classList.replace('opacity-0','opacity-100');
    slides[current].classList.replace('z-0','z-10');
    dots[current].classList.remove('bg-white/40');
    dots[current].classList.add('bg-white','scale-125');
  }

  function startTimer() {
    clearInterval(timer);
    timer = setInterval(() => show(current + 1), 5000);
  }

  window.heroGo   = (dir) => { show(current + dir); startTimer(); };
  window.heroGoTo = (idx) => { show(idx);            startTimer(); };

  startTimer();
})();
</script>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════════════ -->
<!-- WHO WE ARE                                                    -->
<!-- ══════════════════════════════════════════════════════════════ -->
<section class="bg-blue-50 py-16">
  <div class="max-w-5xl mx-auto px-4">
    <h2 class="text-3xl font-bold text-center mb-8">
      <?= htmlspecialchars(cfg('who_heading', $settingsRaw, 'Who We Are')) ?>
    </h2>
    <p class="text-gray-700 text-center leading-loose">
      <?= htmlspecialchars(cfg('who_description', $settingsRaw)) ?>
    </p>

    <!-- Stat Cards -->
    <div class="mt-10 grid md:grid-cols-3 gap-6 text-center">
      <?php
      $statColors = ['blue','green','purple'];
      for ($i = 1; $i <= 3; $i++):
        $color  = $statColors[$i-1];
        $bgMap  = ['blue'=>'bg-blue-100','green'=>'bg-green-100','purple'=>'bg-purple-100'];
        $txtMap = ['blue'=>'text-blue-600','green'=>'text-green-600','purple'=>'text-purple-600'];
        $bg  = $bgMap[$color]  ?? 'bg-blue-100';
        $txt = $txtMap[$color] ?? 'text-blue-600';
        $icon = htmlspecialchars(cfg("stat{$i}_icon", $settingsRaw, 'fa-star'));
      ?>
      <div class="bg-white shadow p-6 rounded-xl">
        <div class="<?= $bg ?> p-3 rounded-full mb-4 inline-block">
          <i class="fas <?= $icon ?> <?= $txt ?> text-xl"></i>
        </div>
        <h4 class="text-lg font-semibold">
          <?= htmlspecialchars(cfg("stat{$i}_number",$settingsRaw)) ?>
          <?= htmlspecialchars(cfg("stat{$i}_label",$settingsRaw)) ?>
        </h4>
        <p class="text-gray-600 text-sm"><?= htmlspecialchars(cfg("stat{$i}_sub",$settingsRaw)) ?></p>
      </div>
      <?php endfor; ?>
    </div>

    <!-- Track Record -->
    <div class="px-0 mt-6">
      <div class="bg-blue-100 rounded-2xl p-8">
        <h3 class="text-2xl font-semibold text-center text-gray-900 mb-8">Our Track Record</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
          <?php for ($i = 1; $i <= 4; $i++): ?>
          <div class="text-center">
            <div class="text-3xl font-bold text-blue-600 mb-2"><?= htmlspecialchars(cfg("track{$i}_val",$settingsRaw)) ?></div>
            <div class="text-gray-500"><?= htmlspecialchars(cfg("track{$i}_label",$settingsRaw)) ?></div>
          </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════════════════════════ -->
<!-- TRUSTED CLIENTS / LOGOS                                       -->
<!-- ══════════════════════════════════════════════════════════════ -->
<section id="client-logos" class="px-4 py-16 bg-gray-50">
  <div class="max-w-6xl mx-auto text-center">
    <h2 class="text-4xl font-bold text-gray-900 mb-3">
      <?= htmlspecialchars(cfg('clients_heading', $settingsRaw, 'Trusted By Industry Leaders')) ?>
    </h2>
    <p class="text-xl text-gray-500 max-w-3xl mx-auto mb-10">
      <?= htmlspecialchars(cfg('clients_sub', $settingsRaw, "We've proudly partnered with government bodies, corporate leaders, and global enterprises.")) ?>
    </p>

    <?php if (!empty($clientLogos)): ?>
    <!-- Logo grid with company names -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 items-stretch">
      <?php foreach ($clientLogos as $logo): ?>
      <div class="group flex flex-col items-center justify-center bg-white rounded-xl border border-gray-200 p-5 shadow-sm hover:shadow-md hover:border-blue-300 transition-all duration-300">
        <img src="<?= htmlspecialchars($logo['image']) ?>"
             alt="<?= htmlspecialchars($logo['alt_text']) ?>"
             class="h-14 max-w-full object-contain grayscale group-hover:grayscale-0 transition duration-300 mb-3" />
        <?php if ($logo['alt_text']): ?>
        <span class="text-xs font-semibold text-gray-500 group-hover:text-blue-600 transition-colors text-center leading-tight">
          <?= htmlspecialchars($logo['alt_text']) ?>
        </span>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <!-- Placeholder when no logos uploaded yet -->
    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
      <i class="fas fa-building text-5xl mb-4 text-blue-200"></i>
      <p class="text-lg">Partner logos coming soon.</p>
      <?php if (isset($_SESSION['admin_logged_in'])): ?>
      <a href="admin/client_logos.php" class="mt-4 text-blue-600 text-sm hover:underline">Upload logos in Admin Panel →</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</section>


<!-- ══════════════════════════════════════════════════════════════ -->
<!-- LEADERSHIP TEAM                                               -->
<!-- ══════════════════════════════════════════════════════════════ -->
<?php if (!empty($leaders)): ?>
<section id="leadership" class="py-20 bg-white dark:bg-gray-900 text-gray-900 dark:text-white" style="overflow:visible;">
  <div class="container mx-auto px-4">
    <div class="text-center mb-16">
      <h2 class="text-4xl font-bold mb-4"><?= htmlspecialchars(cfg('leadership_heading',$settingsRaw,'Leadership Team')) ?></h2>
      <p class="text-xl text-gray-500 max-w-3xl mx-auto"><?= htmlspecialchars(cfg('leadership_sub',$settingsRaw)) ?></p>
    </div>

    <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
      <?php foreach ($leaders as $leader):
        $expertise = json_decode($leader['expertise'], true) ?: [];
      ?>
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 group border border-gray-200 flex flex-col">
        <!-- Coloured top band + avatar -->
        <div class="relative bg-gradient-to-r from-blue-600 to-indigo-600 rounded-t-2xl h-24 flex-shrink-0">
          <div class="absolute -bottom-10 left-1/2 -translate-x-1/2">
            <div class="w-20 h-20 rounded-full border-4 border-white shadow-lg overflow-hidden bg-white">
              <?php if ($leader['image']): ?>
              <img src="<?= htmlspecialchars($leader['image']) ?>"
                   alt="<?= htmlspecialchars($leader['name']) ?>"
                   class="w-full h-full object-cover">
              <?php else: ?>
              <div class="w-full h-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center">
                <span class="text-white text-2xl font-bold"><?= strtoupper(substr($leader['name'],0,1)) ?></span>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <!-- Card body -->
        <div class="pt-14 pb-8 px-8 flex flex-col flex-1">
          <div class="text-center mb-4">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($leader['name']) ?></h3>
            <p class="text-sm text-blue-600 font-medium mt-1"><?= htmlspecialchars($leader['position']) ?></p>
          </div>
          <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed mb-5 text-center flex-1"><?= htmlspecialchars($leader['description']) ?></p>
          <div class="flex flex-wrap gap-2 justify-center">
            <?php foreach ($expertise as $skill): ?>
            <span class="bg-blue-50 text-blue-700 text-xs font-medium px-3 py-1 rounded-full border border-blue-100">
              <?= htmlspecialchars($skill) ?>
            </span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php $philosophy = cfg('leadership_philosophy',$settingsRaw); if ($philosophy): ?>
    <div class="mt-16 text-center">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-8 max-w-4xl mx-auto">
        <h3 class="text-2xl font-semibold mb-4">Leadership Philosophy</h3>
        <p class="text-gray-500 leading-relaxed text-lg"><?= htmlspecialchars($philosophy) ?></p>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════════════ -->
<!-- SERVICES                                                      -->
<!-- ══════════════════════════════════════════════════════════════ -->
<?php if (!empty($services)): ?>
<section id="services" class="py-20 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
  <div class="container mx-auto px-4">
    <div class="text-center mb-16">
      <h2 class="text-4xl font-bold mb-4"><?= htmlspecialchars(cfg('services_heading',$settingsRaw,'Our Services')) ?></h2>
      <p class="text-xl text-gray-500 max-w-3xl mx-auto"><?= htmlspecialchars(cfg('services_sub',$settingsRaw)) ?></p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
      <?php foreach ($services as $service):
        $features = json_decode($service['features'], true) ?: [];
      ?>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow hover:shadow-lg transition-all duration-300 group p-6 border border-gray-200">
        <div class="text-center pb-4">
          <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
            <i class="fa <?= htmlspecialchars($service['icon']) ?> text-white text-2xl"></i>
          </div>
          <h3 class="text-xl font-semibold group-hover:text-blue-600 transition-colors"><?= htmlspecialchars($service['title']) ?></h3>
        </div>
        <p class="text-gray-500 mb-6 leading-relaxed text-sm"><?= htmlspecialchars($service['description']) ?></p>
        <ul class="space-y-2 mb-6">
          <?php foreach ($features as $feature): ?>
          <li class="flex items-center gap-2 text-sm">
            <i class="fas fa-check-circle text-blue-600 text-sm"></i>
            <span class="text-gray-600"><?= htmlspecialchars($feature) ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
        <a href="#" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-100 transition group">
          Learn More <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
        </a>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Recruitment Process -->
    <div class="bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900 dark:to-blue-800 rounded-2xl p-8">
      <h3 class="text-3xl font-semibold text-center text-gray-900 dark:text-white mb-8">Our Recruitment Process</h3>
      <div class="grid md:grid-cols-4 gap-6 text-center text-sm text-gray-700 dark:text-gray-300">
        <div>
          <div class="w-12 h-12 bg-blue-600 text-white rounded-full mx-auto flex items-center justify-center mb-4 font-bold">1</div>
          <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Requirement Analysis</h4>
          <p>Understanding client needs and defining role specifications</p>
        </div>
        <div>
          <div class="w-12 h-12 bg-blue-600 text-white rounded-full mx-auto flex items-center justify-center mb-4 font-bold">2</div>
          <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Talent Sourcing</h4>
          <p>Global talent search and candidate identification</p>
        </div>
        <div>
          <div class="w-12 h-12 bg-blue-600 text-white rounded-full mx-auto flex items-center justify-center mb-4 font-bold">3</div>
          <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Assessment & Screening</h4>
          <p>Comprehensive evaluation and competency validation</p>
        </div>
        <div>
          <div class="w-12 h-12 bg-blue-600 text-white rounded-full mx-auto flex items-center justify-center mb-4 font-bold">4</div>
          <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Placement & Support</h4>
          <p>Seamless onboarding and ongoing support</p>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════════════ -->
<!-- CTA BANNER                                                    -->
<!-- ══════════════════════════════════════════════════════════════ -->
<section class="bg-blue-900 text-white px-4 py-12 text-center">
  <h2 class="text-2xl font-semibold"><?= htmlspecialchars(cfg('cta_heading',$settingsRaw,'Looking to hire or get hired?')) ?></h2>
  <p class="mt-2"><?= htmlspecialchars(cfg('cta_sub',$settingsRaw,'Let TGS help you transform your dreams into reality.')) ?></p>
  <a href="<?= htmlspecialchars(cfg('cta_link',$settingsRaw,'contact.php')) ?>"
     class="mt-4 inline-block bg-white text-blue-800 px-6 py-2 rounded-full font-semibold hover:bg-gray-200">
    <?= htmlspecialchars(cfg('cta_btn',$settingsRaw,'Get Started')) ?>
  </a>
</section>


<!-- ══════════════════════════════════════════════════════════════ -->
<!-- WHY CHOOSE TGS                                                -->
<!-- ══════════════════════════════════════════════════════════════ -->
<?php if (!empty($advantages)): ?>
<section id="why-us" class="py-20 bg-gradient-to-br from-blue-100 to-blue-50 dark:from-blue-900 dark:to-blue-800 text-gray-900 dark:text-white">
  <div class="container mx-auto px-4">
    <div class="text-center mb-16">
      <h2 class="text-4xl font-bold mb-4"><?= htmlspecialchars(cfg('whyus_heading',$settingsRaw,'Why Choose TGS?')) ?></h2>
      <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto"><?= htmlspecialchars(cfg('whyus_sub',$settingsRaw)) ?></p>
    </div>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
      <?php foreach ($advantages as $advantage): ?>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow hover:shadow-lg transition-all duration-300 group text-center p-6">
        <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
          <i class="fa <?= htmlspecialchars($advantage['icon']) ?> text-white text-2xl"></i>
        </div>
        <h3 class="text-xl font-semibold mb-2 group-hover:text-blue-600 transition-colors"><?= htmlspecialchars($advantage['title']) ?></h3>
        <p class="text-gray-500 dark:text-gray-400 mb-4 leading-relaxed"><?= htmlspecialchars($advantage['description']) ?></p>
        <div class="inline-flex items-center px-3 py-1 bg-blue-100 dark:bg-blue-700 rounded-full">
          <span class="text-sm font-medium text-blue-700 dark:text-blue-100"><?= htmlspecialchars($advantage['stats']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
// Build unique gallery categories
$galCategories = ['All'];
foreach ($galleryImgs as $gi) {
    $c = trim($gi['category'] ?: 'General');
    if (!in_array($c, $galCategories)) $galCategories[] = $c;
}
// JSON encode images for JS lightbox
$galleryJson = json_encode(array_values(array_map(fn($g) => [
    'image'    => htmlspecialchars($g['image'], ENT_QUOTES),
    'title'    => htmlspecialchars($g['title'] ?: '', ENT_QUOTES),
    'category' => htmlspecialchars($g['category'] ?: 'General', ENT_QUOTES),
], $galleryImgs)));
?>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- GALLERY                                                       -->
<!-- ══════════════════════════════════════════════════════════════ -->
<section id="gallery" class="py-20 bg-white">
  <div class="container mx-auto px-4">

    <!-- Section Header -->
    <div class="text-center mb-12">
      <h2 class="text-4xl font-bold text-gray-900 mb-3">
        <?= htmlspecialchars(cfg('gallery_heading', $settingsRaw, 'Our Gallery')) ?>
      </h2>
      <p class="text-xl text-gray-500 max-w-2xl mx-auto">
        <?= htmlspecialchars(cfg('gallery_sub', $settingsRaw, 'A glimpse into our work, events, and the people behind TGS.')) ?>
      </p>
    </div>

    <?php if (!empty($galleryImgs)): ?>

    <!-- Category Filter Tabs -->
    <?php if (count($galCategories) > 2): ?>
    <div class="flex flex-wrap justify-center gap-3 mb-10" id="galFilterBtns">
      <?php foreach ($galCategories as $ci => $cat): ?>
      <button
        onclick="filterGallery('<?= htmlspecialchars($cat, ENT_QUOTES) ?>', this)"
        class="gal-filter-btn px-5 py-2 rounded-full text-sm font-semibold border transition-all duration-200
               <?= $ci === 0 ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400 hover:text-blue-600' ?>">
        <?= htmlspecialchars($cat) ?>
      </button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Masonry Grid -->
    <div id="galleryGrid" class="columns-2 sm:columns-3 lg:columns-4 gap-4 space-y-4">
      <?php foreach ($galleryImgs as $idx => $gimg): ?>
      <div class="gal-item break-inside-avoid cursor-pointer group relative rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300"
           data-category="<?= htmlspecialchars($gimg['category'] ?: 'General', ENT_QUOTES) ?>"
           onclick="openLightbox(<?= $idx ?>)"
           style="margin-bottom:16px;">
        <img src="<?= htmlspecialchars($gimg['image']) ?>"
             alt="<?= htmlspecialchars($gimg['title'] ?: 'Gallery image') ?>"
             class="w-full h-auto object-cover block group-hover:scale-105 transition-transform duration-500"
             loading="lazy">
        <!-- Hover overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-blue-900/80 via-transparent to-transparent
                    opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4">
          <?php if ($gimg['title']): ?>
          <p class="text-white text-sm font-semibold leading-tight"><?= htmlspecialchars($gimg['title']) ?></p>
          <?php endif; ?>
          <span class="text-blue-200 text-xs mt-1"><?= htmlspecialchars($gimg['category'] ?: 'General') ?></span>
          <div class="absolute top-3 right-3 w-8 h-8 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
            <i class="fas fa-expand text-white text-xs"></i>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ── LIGHTBOX ────────────────────────────────────────────── -->
    <div id="galLightbox"
         class="fixed inset-0 z-[999] bg-black/90 backdrop-blur-sm flex items-center justify-center p-4"
         style="display:none!important;"
         onclick="closeLightboxOutside(event)">
      <!-- Prev -->
      <button onclick="lbGo(-1);event.stopPropagation();"
              class="absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25
                     border border-white/20 text-white flex items-center justify-center transition z-10">
        <i class="fas fa-chevron-left"></i>
      </button>
      <!-- Image + Caption -->
      <div class="max-w-4xl w-full text-center" onclick="event.stopPropagation()">
        <img id="lbImg" src="" alt="" class="max-h-[75vh] w-auto mx-auto rounded-xl shadow-2xl object-contain">
        <div class="mt-4">
          <p id="lbTitle" class="text-white font-semibold text-lg"></p>
          <p id="lbCat"   class="text-blue-300 text-sm mt-1"></p>
          <p id="lbCount" class="text-white/40 text-xs mt-2"></p>
        </div>
      </div>
      <!-- Next -->
      <button onclick="lbGo(1);event.stopPropagation();"
              class="absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25
                     border border-white/20 text-white flex items-center justify-center transition z-10">
        <i class="fas fa-chevron-right"></i>
      </button>
      <!-- Close -->
      <button onclick="closeLightbox()"
              class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/25
                     border border-white/20 text-white flex items-center justify-center transition">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <?php else: ?>
    <!-- Placeholder when no images -->
    <div class="flex flex-col items-center justify-center py-20 text-gray-300">
      <i class="fas fa-images text-6xl mb-5 text-blue-100"></i>
      <p class="text-xl text-gray-400 font-medium">Gallery coming soon</p>
      <p class="text-gray-300 text-sm mt-2">Upload images from the admin panel to populate this section.</p>
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- Gallery JS -->
<?php if (!empty($galleryImgs)): ?>
<script>
(function() {
  const IMGS = <?= $galleryJson ?>;
  let current = 0;
  let filtered = [...Array(IMGS.length).keys()]; // indices of visible items

  // ── Filter ─────────────────────────────────────────────────────
  window.filterGallery = function(cat, btn) {
    // update buttons
    document.querySelectorAll('.gal-filter-btn').forEach(b => {
      b.classList.remove('bg-blue-600','text-white','border-blue-600');
      b.classList.add('bg-white','text-gray-600','border-gray-300');
    });
    btn.classList.add('bg-blue-600','text-white','border-blue-600');
    btn.classList.remove('bg-white','text-gray-600','border-gray-300');

    // show/hide items
    filtered = [];
    document.querySelectorAll('.gal-item').forEach((el, idx) => {
      const match = cat === 'All' || el.dataset.category === cat;
      el.style.display = match ? '' : 'none';
      if (match) filtered.push(idx);
    });
  };

  // ── Lightbox ───────────────────────────────────────────────────
  const lb      = document.getElementById('galLightbox');
  const lbImg   = document.getElementById('lbImg');
  const lbTitle = document.getElementById('lbTitle');
  const lbCat   = document.getElementById('lbCat');
  const lbCount = document.getElementById('lbCount');

  window.openLightbox = function(idx) {
    current = filtered.indexOf(idx);
    if (current === -1) { current = 0; filtered = [...Array(IMGS.length).keys()]; }
    renderLB();
    lb.style.removeProperty('display');
    document.body.style.overflow = 'hidden';
  };

  function renderLB() {
    const img = IMGS[filtered[current]];
    lbImg.src   = img.image;
    lbImg.alt   = img.title;
    lbTitle.textContent = img.title   || '';
    lbCat.textContent   = img.category || 'General';
    lbCount.textContent = (current + 1) + ' / ' + filtered.length;
  }

  window.lbGo = function(dir) {
    current = (current + dir + filtered.length) % filtered.length;
    renderLB();
  };

  window.closeLightbox = function() {
    lb.style.display = 'none';
    document.body.style.overflow = '';
  };

  window.closeLightboxOutside = function(e) {
    if (e.target === lb) closeLightbox();
  };

  // Keyboard nav
  document.addEventListener('keydown', e => {
    if (lb.style.display === 'none') return;
    if (e.key === 'ArrowRight') lbGo(1);
    if (e.key === 'ArrowLeft')  lbGo(-1);
    if (e.key === 'Escape')     closeLightbox();
  });
})();
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
