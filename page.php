<?php
// page.php - Generic Router for Custom Dynamic Pages
require_once 'function.php';
$db  = new Database();
$pdo = $db->con;

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: index.php");
    exit;
}

// Fetch Page Details
$stmt = $pdo->prepare("SELECT * FROM custom_pages WHERE slug = ? AND is_active = 1 LIMIT 1");
$stmt->execute([$slug]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$page) {
    // 404 fallback or redirect
    header("Location: index.php");
    exit;
}

// Fetch Page Blocks ordered by sort_order
$blockStmt = $pdo->prepare("SELECT * FROM page_blocks WHERE page_id = ? ORDER BY sort_order ASC, id ASC");
$blockStmt->execute([$page['id']]);
$blocks = $blockStmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- DYNAMIC PAGE BODY                                         -->
<!-- ══════════════════════════════════════════════════════════ -->
<main class="min-h-screen">
  <?php foreach ($blocks as $idx => $block): ?>
    
    <!-- BLOCK TYPE: HERO -->
    <?php if ($block['block_type'] === 'hero'): ?>
      <section class="relative overflow-hidden py-24 sm:py-32" 
               style="background: <?= $block['layout_option'] === 'bg_dark' ? '#0f172a' : 'linear-gradient(135deg,#0f172a 0%,#1e3a8a 50%,#1d4ed8 100%)' ?>;">
        <!-- decorative background elements -->
        <div class="absolute -top-10 -right-10 w-96 h-96 rounded-full border-[40px] border-white/5 pointer-events-none"></div>
        <div class="absolute -bottom-10 -left-10 w-80 h-80 rounded-full border-[20px] border-white/5 pointer-events-none"></div>
        <div class="container mx-auto px-6 relative z-10 text-center max-w-4xl" data-aos="fade-up">
          <h1 class="text-4xl sm:text-6xl font-bold text-white tracking-tight mb-6 leading-tight">
            <?= htmlspecialchars($block['heading']) ?>
          </h1>
          <?php if ($block['subtext']): ?>
            <p class="text-lg sm:text-xl text-blue-100/90 leading-relaxed max-w-2xl mx-auto mb-8">
              <?= htmlspecialchars($block['subtext']) ?>
            </p>
          <?php endif; ?>
          <?php if ($block['button_label'] && $block['button_link']): ?>
            <a href="<?= htmlspecialchars($block['button_link']) ?>" 
               class="inline-flex items-center gap-2 px-6 py-3.5 bg-white text-blue-900 font-bold rounded-xl hover:bg-blue-50 transition shadow-lg">
              <?= htmlspecialchars($block['button_label']) ?>
            </a>
          <?php endif; ?>
        </div>
      </section>

    <!-- BLOCK TYPE: TEXT / RICH CONTENT -->
    <?php elseif ($block['block_type'] === 'text'): ?>
      <section class="py-16 bg-white" data-aos="fade-up">
        <div class="container mx-auto px-6 max-w-3xl">
          <?php if ($block['heading']): ?>
            <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center"><?= htmlspecialchars($block['heading']) ?></h2>
          <?php endif; ?>
          <?php if ($block['subtext']): ?>
            <p class="text-lg text-gray-500 mb-8 text-center italic"><?= htmlspecialchars($block['subtext']) ?></p>
          <?php endif; ?>
          <div class="prose max-w-none text-gray-600 leading-relaxed space-y-4">
            <?= nl2br(htmlspecialchars($block['content'])) ?>
          </div>
        </div>
      </section>

    <!-- BLOCK TYPE: IMAGE & TEXT -->
    <?php elseif ($block['block_type'] === 'image_text'): 
      $isLeft = $block['layout_option'] === 'left_image';
    ?>
      <section class="py-20 <?= $idx % 2 === 0 ? 'bg-gray-50' : 'bg-white' ?>">
        <div class="container mx-auto px-6 max-w-6xl">
          <div class="grid md:grid-cols-2 gap-12 items-center">
            
            <!-- Image Panel -->
            <div class="<?= $isLeft ? 'md:order-1' : 'md:order-2' ?>" data-aos="<?= $isLeft ? 'fade-right' : 'fade-left' ?>">
              <?php if ($block['image_path']): ?>
                <img src="<?= htmlspecialchars($block['image_path']) ?>" 
                     alt="<?= htmlspecialchars($block['heading']) ?>" 
                     class="w-full h-auto rounded-2xl shadow-lg border border-gray-100 object-cover" 
                     style="max-height: 400px;">
              <?php else: ?>
                <div class="w-full h-64 bg-gray-200 rounded-2xl flex items-center justify-center text-gray-400">
                  <i class="fas fa-image text-4xl"></i>
                </div>
              <?php endif; ?>
            </div>

            <!-- Content Panel -->
            <div class="<?= $isLeft ? 'md:order-2' : 'md:order-1' ?>" data-aos="<?= $isLeft ? 'fade-left' : 'fade-right' ?>">
              <h2 class="text-3xl font-bold text-gray-900 mb-4"><?= htmlspecialchars($block['heading']) ?></h2>
              <?php if ($block['subtext']): ?>
                <p class="text-lg font-semibold text-blue-600 mb-4"><?= htmlspecialchars($block['subtext']) ?></p>
              <?php endif; ?>
              <div class="text-gray-600 leading-relaxed mb-6 space-y-4">
                <?= nl2br(htmlspecialchars($block['content'])) ?>
              </div>
              <?php if ($block['button_label'] && $block['button_link']): ?>
                <a href="<?= htmlspecialchars($block['button_link']) ?>" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
                  <?= htmlspecialchars($block['button_label']) ?>
                </a>
              <?php endif; ?>
            </div>

          </div>
        </div>
      </section>

    <!-- BLOCK TYPE: FEATURES GRID -->
    <?php elseif ($block['block_type'] === 'features'): 
      $items = json_decode($block['content'] ?? '[]', true) ?: [];
    ?>
      <section class="py-20 <?= $idx % 2 === 0 ? 'bg-gray-50' : 'bg-white' ?>" data-aos="fade-up">
        <div class="container mx-auto px-6 max-w-6xl">
          <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4"><?= htmlspecialchars($block['heading']) ?></h2>
            <?php if ($block['subtext']): ?>
              <p class="text-xl text-gray-500 max-w-2xl mx-auto"><?= htmlspecialchars($block['subtext']) ?></p>
            <?php endif; ?>
          </div>
          
          <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($items as $fidx => $item): ?>
              <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                  <i class="fas fa-check text-sm"></i>
                </div>
                <div>
                  <h4 class="font-bold text-gray-800 text-base mb-1">
                    <?= is_array($item) ? htmlspecialchars($item['title'] ?? '') : htmlspecialchars($item) ?>
                  </h4>
                  <?php if (is_array($item) && isset($item['description'])): ?>
                    <p class="text-gray-500 text-sm mt-1"><?= htmlspecialchars($item['description']) ?></p>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

    <!-- BLOCK TYPE: CALL TO ACTION -->
    <?php elseif ($block['block_type'] === 'cta'): ?>
      <section class="py-16" 
               style="background: <?= $block['layout_option'] === 'bg_white' ? '#fff' : ($block['layout_option'] === 'bg_gray' ? '#f9fafb' : 'linear-gradient(135deg,#1e3a8a,#1d4ed8)') ?>;">
        <div class="container mx-auto px-6 max-w-4xl text-center" data-aos="fade-up">
          <h2 class="text-3xl font-bold mb-4 <?= $block['layout_option'] === 'bg_white' || $block['layout_option'] === 'bg_gray' ? 'text-gray-900' : 'text-white' ?>">
            <?= htmlspecialchars($block['heading']) ?>
          </h2>
          <?php if ($block['subtext']): ?>
            <p class="text-lg mb-8 <?= $block['layout_option'] === 'bg_white' || $block['layout_option'] === 'bg_gray' ? 'text-gray-500' : 'text-blue-100' ?>">
              <?= htmlspecialchars($block['subtext']) ?>
            </p>
          <?php endif; ?>
          <?php if ($block['button_label'] && $block['button_link']): ?>
            <a href="<?= htmlspecialchars($block['button_link']) ?>" 
               class="inline-flex items-center gap-2 px-8 py-3.5 font-bold rounded-xl transition shadow-lg
                      <?= $block['layout_option'] === 'bg_white' || $block['layout_option'] === 'bg_gray' ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-white text-blue-900 hover:bg-blue-50' ?>">
              <?= htmlspecialchars($block['button_label']) ?>
            </a>
          <?php endif; ?>
        </div>
      </section>

    <?php endif; ?>

  <?php endforeach; ?>
</main>

<?php include 'includes/footer.php'; ?>
