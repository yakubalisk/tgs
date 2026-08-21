<?php
require_once 'function.php';
$db = new Database();
$pdo = $db->con;

// Fetch active categories
$categories = $pdo->query("SELECT * FROM qb_categories WHERE is_active = 1 ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);

// Fetch active Question Papers
$qbsRaw = $pdo->query("SELECT * FROM question_banks WHERE is_active = 1 ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);

// Group QPs by category
$qbsByCat = [];
foreach ($qbsRaw as $q) {
    $qbsByCat[$q['category_id']][] = $q;
}

$pageTitle = "Question Bank";
include 'includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- BREADCRUMB HEADER SECTION                                     -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="relative bg-gradient-to-r from-blue-950 via-blue-900 to-indigo-950 py-16 text-white text-center overflow-hidden">
  <div class="absolute inset-0 opacity-10 pointer-events-none">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('assets/images/courses_bg.jpg');"></div>
  </div>
  <div class="relative z-10 max-w-4xl mx-auto px-4">
    <h1 class="text-4xl font-extrabold tracking-tight md:text-5xl">Question Bank</h1>
    <p class="mt-3 text-lg text-blue-200">Empanelled question papers, assessment sample sheets, and evaluation banks.</p>
    <div class="mt-4 flex items-center justify-center gap-2 text-xs font-semibold text-blue-300 uppercase tracking-widest">
      <a href="index.php" class="hover:text-white transition">Home</a>
      <span class="text-blue-500">/</span>
      <span class="text-white">Question Bank</span>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- DISPLAY CATEGORIES AND LINKS                                   -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="bg-gray-50 py-16 dark:bg-gray-900">
  <div class="container mx-auto px-4 max-w-6xl">
    
    <div class="text-center mb-12">
      <h2 class="text-2xl font-bold text-gray-800 dark:text-white uppercase tracking-wider mb-2">Question Bank & Assessment Papers</h2>
      <p class="text-gray-500 dark:text-gray-400 max-w-2xl mx-auto text-sm">Empanelled QP documents, model test resources, and assessment materials categorized for easy reference and download.</p>
    </div>

    <div class="space-y-10">
      <?php 
      foreach ($categories as $cat): 
          $catId = $cat['id'];
          $catName = $cat['name'];
          $catItems = $qbsByCat[$catId] ?? [];
          if (empty($catItems)) continue; // skip categories with no items

          // Check if category has card display mode
          $isCardMode = false;
          foreach ($catItems as $item) {
              if ($item['item_type'] === 'card') {
                  $isCardMode = true;
                  break;
              }
          }
      ?>
        <!-- Category Block -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden border border-gray-150 dark:border-gray-700">
          
          <!-- Category Header (TGS corporate Navy Blue) -->
          <div class="bg-blue-900 text-white px-6 py-4 flex items-center justify-between">
            <h3 class="text-base font-bold uppercase tracking-wider flex items-center gap-2">
              <i class="fas <?= $isCardMode ? 'fa-graduation-cap' : 'fa-clipboard-list' ?>"></i>
              <?= htmlspecialchars($catName) ?>
            </h3>
            <span class="text-xs bg-white/10 px-2.5 py-1 rounded-full font-semibold">
              <?= count($catItems) ?> <?= count($catItems) === 1 ? 'Item' : 'Items' ?>
            </span>
          </div>

          <!-- Category Contents -->
          <div class="p-6 bg-slate-50/50 dark:bg-slate-800/30">
            
            <?php if ($isCardMode): ?>
              <!-- DISPLAY MODE: CARD GRID -->
              <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
                <?php foreach ($catItems as $item): 
                    $href = '#';
                    if ($item['file_path']) $href = $item['file_path'];
                    elseif ($item['link_url']) $href = $item['link_url'];
                ?>
                  <a href="<?= htmlspecialchars($href) ?>" 
                     target="<?= ($href !== '#') ? '_blank' : '_self' ?>" 
                     class="group flex flex-col items-center text-center">
                    
                    <!-- Cover Container -->
                    <div class="w-full aspect-[3/4] rounded-lg shadow-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex flex-col items-center justify-center p-2.5 relative group-hover:shadow-lg group-hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                      
                      <!-- Color-Coded Book Cover styled in blue/indigo theme -->
                      <div class="w-full h-full border border-gray-150 dark:border-gray-800 rounded bg-slate-50/50 dark:bg-slate-900/30 flex flex-col justify-between p-2 shadow-inner">
                        <div class="text-[7px] text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-gray-800 pb-1">TGS QP</div>
                        
                        <div class="my-auto py-2 px-1 rounded border text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800 break-words leading-tight">
                          <?= htmlspecialchars($catName) ?>
                        </div>
                        
                        <div class="text-[7px] text-gray-400 uppercase tracking-widest">Question Bank</div>
                      </div>

                    </div>

                    <!-- Title Caption -->
                    <span class="mt-3 text-xs font-semibold text-gray-700 dark:text-gray-300 line-clamp-3 group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">
                      <?= htmlspecialchars($item['title']) ?>
                    </span>
                  </a>
                <?php endforeach; ?>
              </div>
            
            <?php else: ?>
              <!-- DISPLAY MODE: TEXT LINKS LIST -->
              <ul class="divide-y divide-gray-100 dark:divide-gray-700 space-y-2">
                <?php foreach ($catItems as $item): 
                    $href = '#';
                    if ($item['file_path']) $href = $item['file_path'];
                    elseif ($item['link_url']) $href = $item['link_url'];
                ?>
                  <li class="py-2.5 flex items-center justify-between group">
                    <a href="<?= htmlspecialchars($href) ?>" 
                       target="<?= ($href !== '#') ? '_blank' : '_self' ?>" 
                       class="flex items-center gap-3 text-sm font-semibold text-blue-700 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 hover:underline transition">
                      <i class="far fa-file-pdf text-red-500 text-lg group-hover:scale-110 transition-transform"></i>
                      <span><?= htmlspecialchars($item['title']) ?></span>
                    </a>
                    
                    <?php if ($href !== '#'): ?>
                      <a href="<?= htmlspecialchars($href) ?>" target="_blank" class="text-xs text-gray-400 group-hover:text-blue-600 transition flex items-center gap-1">
                        Download <i class="fas fa-chevron-right text-[10px]"></i>
                      </a>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

          </div>

        </div>
      <?php endforeach; ?>
    </div>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
