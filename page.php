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

    <!-- BLOCK TYPE: INTERACTIVE LIST / GRID -->
    <?php elseif ($block['block_type'] === 'list'): 
      $listData = json_decode($block['content'] ?? '[]', true) ?: [];
      $headers = !empty($listData) ? array_keys($listData[0]) : [];
      
      // Extract unique states & genders for filter dropdowns if columns exist
      $states = [];
      $genders = [];
      foreach ($listData as $row) {
          foreach ($row as $k => $v) {
              if (strtolower(trim($k)) === 'state' && !empty($v)) {
                  $states[] = trim($v);
              }
              if (strtolower(trim($k)) === 'gender' && !empty($v)) {
                  $genders[] = trim($v);
              }
          }
      }
      $states = array_unique($states); sort($states);
      $genders = array_unique($genders); sort($genders);
      
      $blockIdAttr = 'list_' . $block['id'];
    ?>
      <section id="<?= $blockIdAttr ?>" class="py-20 bg-gray-50 border-b border-gray-100">
        <div class="container mx-auto px-6 max-w-6xl">
          
          <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-3xl font-bold text-gray-900 mb-4"><?= htmlspecialchars($block['heading']) ?></h2>
            <?php if ($block['subtext']): ?>
              <p class="text-lg text-gray-500 max-w-2xl mx-auto"><?= htmlspecialchars($block['subtext']) ?></p>
            <?php endif; ?>
          </div>

          <!-- Controls: Search & Filters -->
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-8 flex flex-col md:flex-row gap-4 items-center justify-between" data-aos="fade-up">
            <!-- Search -->
            <div class="relative w-full md:max-w-xs">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <i class="fas fa-search text-sm"></i>
              </span>
              <input type="text" id="search_<?= $blockIdAttr ?>" onkeyup="filterList_<?= $blockIdAttr ?>()"
                     placeholder="Search name, code, state..." 
                     class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3 w-full md:w-auto justify-end">
              <?php if (!empty($states)): ?>
                <select id="state_<?= $blockIdAttr ?>" onchange="filterList_<?= $blockIdAttr ?>()"
                        class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                  <option value="">All States</option>
                  <?php foreach ($states as $s): ?>
                    <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                  <?php endforeach; ?>
                </select>
              <?php endif; ?>

              <?php if (!empty($genders)): ?>
                <select id="gender_<?= $blockIdAttr ?>" onchange="filterList_<?= $blockIdAttr ?>()"
                        class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                  <option value="">All Genders</option>
                  <?php foreach ($genders as $g): ?>
                    <option value="<?= htmlspecialchars($g) ?>"><?= htmlspecialchars($g) ?></option>
                  <?php endforeach; ?>
                </select>
              <?php endif; ?>

              <?php if ($block['image_path']): ?>
                <a href="<?= htmlspecialchars($block['image_path']) ?>" target="_blank"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold rounded-xl text-sm transition">
                  <i class="fas fa-file-csv"></i> Download CSV
                </a>
              <?php endif; ?>
            </div>
          </div>

          <!-- TABLE VIEW -->
          <?php if ($block['layout_option'] === 'table_view'): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" data-aos="fade-up">
              <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left" id="table_<?= $blockIdAttr ?>">
                  <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-gray-700 font-bold text-xs uppercase tracking-wider">
                      <?php foreach ($headers as $h): ?>
                        <th class="px-6 py-4"><?= htmlspecialchars($h) ?></th>
                      <?php endforeach; ?>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100 text-gray-600 text-sm">
                    <?php foreach ($listData as $rowIdx => $row): ?>
                      <tr class="hover:bg-gray-50/50 transition row-item" 
                          data-search="<?= htmlspecialchars(strtolower(implode(' ', array_values($row)))) ?>"
                          <?php foreach ($row as $k => $v): ?>
                            data-<?= strtolower(trim($k)) ?>="<?= htmlspecialchars(trim($v)) ?>"
                          <?php endforeach; ?>>
                        <?php foreach ($headers as $h): ?>
                          <td class="px-6 py-4 whitespace-nowrap">
                            <?php if (strtolower(trim($h)) === 'name'): ?>
                              <span class="font-semibold text-gray-900"><?= htmlspecialchars($row[$h] ?? '') ?></span>
                            <?php elseif (strtolower(trim($h)) === 'assessor code'): ?>
                              <code class="px-2 py-0.5 bg-gray-100 rounded text-xs font-mono text-gray-700"><?= htmlspecialchars($row[$h] ?? '') ?></code>
                            <?php else: ?>
                              <?= htmlspecialchars($row[$h] ?? '') ?>
                            <?php endif; ?>
                          </td>
                        <?php endforeach; ?>
                      </tr>
                    <?php endforeach; ?>
                    <tr id="no_results_<?= $blockIdAttr ?>" style="display:none;">
                      <td colspan="<?= count($headers) ?>" class="text-center py-12 text-gray-400">
                        <i class="fas fa-filter text-3xl mb-3 block"></i> No matching assessors or records found.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          
          <!-- CARDS VIEW -->
          <?php else: ?>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6" id="cards_<?= $blockIdAttr ?>" data-aos="fade-up">
              <?php foreach ($listData as $rowIdx => $row): 
                $sno = ''; $name = ''; $code = ''; $gender = ''; $state = ''; $qual = '';
                foreach ($row as $k => $v) {
                    $lk = strtolower(trim($k));
                    if (strpos($lk, 'sno') !== false || strpos($lk, 's.no') !== false) $sno = $v;
                    elseif ($lk === 'name') $name = $v;
                    elseif (strpos($lk, 'code') !== false) $code = $v;
                    elseif ($lk === 'gender') $gender = $v;
                    elseif ($lk === 'state') $state = $v;
                    elseif (strpos($lk, 'qual') !== false) $qual = $v;
                }
              ?>
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 row-item"
                     data-search="<?= htmlspecialchars(strtolower(implode(' ', array_values($row)))) ?>"
                     <?php foreach ($row as $k => $v): ?>
                       data-<?= strtolower(trim($k)) ?>="<?= htmlspecialchars(trim($v)) ?>"
                     <?php endforeach; ?>>
                  <div class="flex items-center justify-between mb-4 border-b border-gray-50 pb-3">
                    <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">Assessor #<?= htmlspecialchars($sno) ?></span>
                    <span class="text-xs text-gray-400 font-mono"><?= htmlspecialchars($code) ?></span>
                  </div>
                  <h4 class="font-bold text-gray-900 text-lg mb-2"><?= htmlspecialchars($name) ?></h4>
                  
                  <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex justify-between">
                      <span class="text-gray-400">Gender:</span>
                      <span class="font-medium"><?= htmlspecialchars($gender) ?></span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-400">State:</span>
                      <span class="font-medium text-gray-800"><?= htmlspecialchars($state) ?></span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-400">Qualification:</span>
                      <span class="font-medium text-gray-700"><?= htmlspecialchars($qual) ?></span>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            
            <div id="no_results_card_<?= $blockIdAttr ?>" style="display:none;" class="text-center py-16 bg-white rounded-2xl border border-gray-100 text-gray-400" data-aos="fade-up">
              <i class="fas fa-filter text-4xl mb-3 block"></i> No records matching filters found.
            </div>
          <?php endif; ?>

        </div>
      </section>

      <!-- Client-side filtering script -->
      <script>
      function filterList_<?= $blockIdAttr ?>() {
        const query = document.getElementById('search_<?= $blockIdAttr ?>').value.toLowerCase().trim();
        
        const stateEl = document.getElementById('state_<?= $blockIdAttr ?>');
        const stateFilter = stateEl ? stateEl.value.toLowerCase().trim() : '';
        
        const genderEl = document.getElementById('gender_<?= $blockIdAttr ?>');
        const genderFilter = genderEl ? genderEl.value.toLowerCase().trim() : '';

        const containerId = '<?= $block['layout_option'] === 'table_view' ? 'table_' . $blockIdAttr : 'cards_' . $blockIdAttr ?>';
        const container = document.getElementById(containerId);
        const rows = container.getElementsByClassName('row-item');
        
        let visibleCount = 0;
        
        for (let i = 0; i < rows.length; i++) {
          const row = rows[i];
          const searchVal = row.dataset.search || '';
          const rowState  = (row.dataset.state || '').toLowerCase().trim();
          const rowGender = (row.dataset.gender || '').toLowerCase().trim();

          const matchesQuery = query === '' || searchVal.includes(query);
          const matchesState = stateFilter === '' || rowState === stateFilter;
          const matchesGender = genderFilter === '' || rowGender === genderFilter;

          if (matchesQuery && matchesState && matchesGender) {
            row.style.display = '';
            visibleCount++;
          } else {
            row.style.display = 'none';
          }
        }

        const noResultsId = '<?= $block['layout_option'] === 'table_view' ? 'no_results_' . $blockIdAttr : 'no_results_card_' . $blockIdAttr ?>';
        const noResults = document.getElementById(noResultsId);
        if (noResults) {
          noResults.style.display = visibleCount === 0 ? '' : 'none';
        }
      }
      
      // Handle pre-filtering from URL hash on load
      window.addEventListener('DOMContentLoaded', () => {
        const hash = window.location.hash;
        if (hash && hash.startsWith('#state=')) {
          const stateVal = decodeURIComponent(hash.split('=')[1]);
          const stateEl = document.getElementById('state_<?= $blockIdAttr ?>');
          if (stateEl) {
            // Find option matches (case-insensitive)
            for (let opt of stateEl.options) {
              if (opt.value.toLowerCase() === stateVal.toLowerCase()) {
                stateEl.value = opt.value;
                break;
              }
            }
            filterList_<?= $blockIdAttr ?>();
            setTimeout(() => {
              const el = document.getElementById('<?= $blockIdAttr ?>');
              if (el) el.scrollIntoView({ behavior: 'smooth' });
            }, 300);
          }
        }
      });
      </script>

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
