<?php
require_once 'function.php';
$db  = new Database();
$pdo = $db->con;

// ── Fetch all data ─────────────────────────────────────────────
$settings  = $pdo->query("SELECT skey,sval FROM samarth_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$instrs    = $pdo->query("SELECT * FROM samarth_instructions WHERE is_active=1 ORDER BY sort_order,id")->fetchAll(PDO::FETCH_ASSOC);
$roadmap   = $pdo->query("SELECT * FROM samarth_roadmap    WHERE is_active=1 ORDER BY sort_order,id")->fetchAll(PDO::FETCH_ASSOC);
$videos    = $pdo->query("SELECT * FROM samarth_videos     WHERE is_active=1 ORDER BY sort_order,id")->fetchAll(PDO::FETCH_ASSOC);
$documents = $pdo->query("SELECT * FROM samarth_documents  WHERE is_active=1 ORDER BY sort_order,id")->fetchAll(PDO::FETCH_ASSOC);
$infoSecs  = $pdo->query("SELECT * FROM samarth_info_sections WHERE is_active=1 ORDER BY sort_order,id")->fetchAll(PDO::FETCH_ASSOC);
$checkDocs = $pdo->query("SELECT * FROM samarth_checklist WHERE is_active=1 ORDER BY sort_order,id")->fetchAll(PDO::FETCH_ASSOC);

function sc($k,$s,$f='') { return htmlspecialchars($s[$k]??$f); }

$regMode       = $settings['reg_mode']            ?? 'both';
$offlinePath   = $settings['offline_form_path']   ?? '';
$offlineTitle  = $settings['offline_form_title']  ?? 'Application Form (Offline)';
$offlineInstr  = $settings['offline_instructions']?? '';
$submitEmail   = $settings['submit_email']         ?? '';
$submitAddress = $settings['submit_address']        ?? '';

// ── Handle registration form POST ──────────────────────────────
$formMsg = '';
$formErr = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['samarth_reg'])) {
    $name  = trim($_POST['full_name']    ?? '');
    $email = trim($_POST['email']        ?? '');
    $phone = trim($_POST['phone']        ?? '');
    $org   = trim($_POST['organization'] ?? '');
    $sec   = trim($_POST['sector']       ?? '');
    $msg   = trim($_POST['message']      ?? '');
    if (!$name || !$email || !$phone) {
        $formErr = "Name, email and phone are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formErr = "Please enter a valid email address.";
    } else {
        $pdo->prepare("INSERT INTO samarth_registrations (full_name,email,phone,organization,sector,message) VALUES (?,?,?,?,?,?)")
            ->execute([$name,$email,$phone,$org,$sec,$msg]);
        $formMsg = "Thank you, <strong>$name</strong>! We have received your registration. Our team will contact you shortly.";
    }
}

// ── Helpers ────────────────────────────────────────────────────
function youtubeId($url) {
    preg_match('/(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/shorts\/))([A-Za-z0-9_-]{11})/', $url, $m);
    return $m[1] ?? $url;
}
$docIcons = ['pdf'=>['fa-file-pdf','#ef4444'],'ppt'=>['fa-file-powerpoint','#f97316'],'pptx'=>['fa-file-powerpoint','#f97316'],'doc'=>['fa-file-word','#3b82f6'],'docx'=>['fa-file-word','#3b82f6'],'xls'=>['fa-file-excel','#22c55e'],'xlsx'=>['fa-file-excel','#22c55e']];
$infoTypeStyle = [
    'documentation'    => ['fa-folder-open',       '#1d4ed8','#eff6ff','#bfdbfe'],
    'application_form' => ['fa-file-signature',    '#7c3aed','#f5f3ff','#ddd6fe'],
    'important_info'   => ['fa-exclamation-triangle','#b45309','#fffbeb','#fde68a'],
    'general'          => ['fa-info-circle',        '#0891b2','#ecfeff','#a5f3fc'],
];

include 'includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════════ -->
<!-- HERO                                                      -->
<!-- ══════════════════════════════════════════════════════════ -->
<section class="relative overflow-hidden" style="background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 50%,#1d4ed8 100%);min-height:480px;">
  <div style="position:absolute;top:-80px;right:-80px;width:400px;height:400px;border-radius:50%;border:60px solid rgba(255,255,255,.04);"></div>
  <div style="position:absolute;bottom:-100px;left:-60px;width:300px;height:300px;border-radius:50%;border:40px solid rgba(255,255,255,.05);"></div>
  <div class="max-w-6xl mx-auto px-6 py-20 relative z-10">
    <div class="grid lg:grid-cols-2 gap-12 items-center">
      <div data-aos="fade-right">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider mb-6"
              style="background:rgba(99,179,237,.2);color:#93c5fd;border:1px solid rgba(99,179,237,.3);">
          <i class="fas fa-landmark"></i> <?= sc('hero_badge',$settings,'Government of India Initiative') ?>
        </span>
        <h1 class="text-4xl lg:text-5xl font-bold text-white leading-tight mb-6"><?= sc('hero_heading',$settings,'Assessment Samarth Scheme') ?></h1>
        <p class="text-blue-100 text-lg leading-relaxed mb-8"><?= sc('hero_subtext',$settings) ?></p>
        <div class="flex flex-wrap gap-4">
          <a href="#register" class="inline-flex items-center gap-2 px-7 py-3.5 bg-white text-blue-900 font-bold rounded-xl hover:bg-blue-50 transition shadow-lg"><i class="fas fa-user-plus"></i> Register Now</a>
          <a href="#documents" class="inline-flex items-center gap-2 px-7 py-3.5 font-semibold rounded-xl transition" style="background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.25);backdrop-filter:blur(8px);"><i class="fas fa-download"></i> Download Resources</a>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4" data-aos="fade-left">
        <?php foreach ([['fa-clipboard-check','30,000+','Assessments Done'],['fa-users-cog','250+','Empanelled Assessors'],['fa-certificate','Multi-Sector','Industry Coverage'],['fa-trophy','Govt. Recognised','Certifications']] as [$ic,$val,$lbl]): ?>
        <div class="rounded-2xl p-5 text-center" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);backdrop-filter:blur(12px);">
          <i class="fas <?=$ic?> text-blue-300 text-2xl mb-3 block"></i>
          <div class="text-white font-bold text-xl"><?=$val?></div>
          <div class="text-blue-200 text-xs mt-1"><?=$lbl?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Sticky jump nav -->
<div class="bg-white border-b border-gray-100 sticky top-20 z-40" style="box-shadow:0 2px 12px rgba(0,0,0,.06);">
  <div class="max-w-6xl mx-auto px-6">
    <div class="flex overflow-x-auto gap-0">
      <?php
      $jumps = ['#instructions'=>['fa-list-ol','Instructions'],'#roadmap'=>['fa-road','Roadmap'],'#info'=>['fa-info-circle','Information'],'#checklist'=>['fa-clipboard-list','Checklist'],'#videos'=>['fa-video','Videos'],'#documents'=>['fa-file-pdf','Resources'],'#register'=>['fa-user-plus','Register']];
      foreach ($jumps as $href=>[$icon,$label]): ?>
      <a href="<?=$href?>" class="flex items-center gap-2 px-4 py-4 text-sm font-medium text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition whitespace-nowrap border-b-2 border-transparent hover:border-blue-600">
        <i class="fas <?=$icon?> text-xs"></i> <?=$label?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>


<!-- ══════════════════════════════════════════════════════════ -->
<!-- INSTRUCTIONS                                              -->
<!-- ══════════════════════════════════════════════════════════ -->
<?php if (!empty($instrs)): ?>
<section id="instructions" class="py-20 bg-gray-50">
  <div class="max-w-6xl mx-auto px-6">
    <div class="text-center mb-14" data-aos="fade-up">
      <h2 class="text-4xl font-bold text-gray-900 mb-3"><?= sc('instructions_heading',$settings,'How to Participate') ?></h2>
      <p class="text-xl text-gray-500 max-w-2xl mx-auto"><?= sc('instructions_sub',$settings) ?></p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($instrs as $idx => $instr): ?>
      <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg border border-gray-100 hover:border-blue-200 transition-all duration-300 group" data-aos="fade-up" data-aos-delay="<?=$idx*80?>">
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 rounded-xl flex-shrink-0 flex items-center justify-center group-hover:scale-110 transition-transform" style="background:linear-gradient(135deg,#1d4ed8,#3b82f6);">
            <i class="fas <?=htmlspecialchars($instr['icon'])?> text-white text-lg"></i>
          </div>
          <div class="flex-1">
            <div class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-1">Step <?=str_pad($idx+1,2,'0',STR_PAD_LEFT)?></div>
            <h3 class="font-bold text-gray-900 text-base mb-2"><?=htmlspecialchars($instr['title'])?></h3>
            <p class="text-gray-500 text-sm leading-relaxed"><?=htmlspecialchars($instr['description'])?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════════ -->
<!-- ROADMAP                                                   -->
<!-- ══════════════════════════════════════════════════════════ -->
<?php if (!empty($roadmap)): ?>
<section id="roadmap" class="py-20 bg-white">
  <div class="max-w-5xl mx-auto px-6">
    <div class="text-center mb-14" data-aos="fade-up">
      <h2 class="text-4xl font-bold text-gray-900 mb-3"><?= sc('roadmap_heading',$settings,'Programme Roadmap') ?></h2>
      <p class="text-xl text-gray-500 max-w-2xl mx-auto"><?= sc('roadmap_sub',$settings) ?></p>
    </div>
    <div class="relative">
      <div class="absolute left-1/2 top-0 bottom-0 w-0.5 -translate-x-1/2 hidden md:block" style="background:linear-gradient(to bottom,#dbeafe,#1d4ed8,#dbeafe);"></div>
      <div class="space-y-10">
        <?php foreach ($roadmap as $idx => $step): $even=($idx%2===0); ?>
        <div class="relative flex items-start gap-6 md:gap-0" data-aos="<?=$even?'fade-right':'fade-left'?>" data-aos-delay="<?=$idx*100?>">
          <div class="md:w-1/2 <?=$even?'md:pr-12 md:text-right':'md:order-3 md:pl-12'?>">
            <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-100 hover:shadow-xl hover:border-blue-200 transition-all duration-300 group inline-block w-full">
              <div class="flex items-center gap-3 <?=$even?'md:flex-row-reverse':''?> mb-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#1e3a8a,#1d4ed8);"><i class="fas <?=htmlspecialchars($step['icon'])?> text-white text-sm"></i></div>
                <span class="text-xs font-black text-blue-600 tracking-wider uppercase"><?=htmlspecialchars($step['step_number'])?></span>
              </div>
              <h3 class="font-bold text-gray-900 text-lg mb-2"><?=htmlspecialchars($step['title'])?></h3>
              <p class="text-gray-500 text-sm leading-relaxed"><?=htmlspecialchars($step['description'])?></p>
            </div>
          </div>
          <div class="hidden md:flex md:order-2 md:w-0 items-center justify-center absolute left-1/2 -translate-x-1/2" style="top:24px;">
            <div class="w-5 h-5 rounded-full border-4 border-blue-600 bg-white shadow-md z-10"></div>
          </div>
          <div class="hidden md:block md:w-1/2 <?=$even?'md:order-3':'md:order-1 md:pr-12'?>"></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════════ -->
<!-- IMPORTANT INFORMATION SECTIONS                            -->
<!-- ══════════════════════════════════════════════════════════ -->
<?php if (!empty($infoSecs)): ?>
<section id="info" class="py-20 bg-gray-50">
  <div class="max-w-6xl mx-auto px-6">
    <div class="text-center mb-14" data-aos="fade-up">
      <h2 class="text-4xl font-bold text-gray-900 mb-3"><?= sc('info_heading',$settings,'Important Information') ?></h2>
      <p class="text-xl text-gray-500 max-w-2xl mx-auto"><?= sc('info_sub',$settings) ?></p>
    </div>
    <div class="grid md:grid-cols-2 gap-6">
      <?php foreach ($infoSecs as $idx => $sec):
        [$sIcon,$sColor,$sBg,$sBorder] = $infoTypeStyle[$sec['section_type']] ?? ['fa-info-circle','#0891b2','#ecfeff','#a5f3fc'];
        $lines = array_filter(array_map('trim', explode("\n", $sec['content'])));
      ?>
      <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg border transition-all duration-300 group"
           style="border-color:<?=$sBorder?>;" data-aos="fade-up" data-aos-delay="<?=$idx*80?>">
        <div class="px-6 py-4 flex items-center gap-3" style="background:<?=$sBg?>;border-bottom:1px solid <?=$sBorder?>;">
          <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:<?=$sColor?>;">
            <i class="fas <?=htmlspecialchars($sec['icon'])?> text-white text-sm"></i>
          </div>
          <h3 class="font-bold text-gray-900" style="font-size:.95rem;"><?=htmlspecialchars($sec['title'])?></h3>
        </div>
        <div class="px-6 py-5">
          <?php if (!empty($lines)): ?>
          <ul class="space-y-2">
            <?php foreach ($lines as $line): ?>
            <li class="flex items-start gap-2 text-sm text-gray-600 leading-relaxed">
              <span class="w-1.5 h-1.5 rounded-full mt-2 flex-shrink-0" style="background:<?=$sColor?>;"></span>
              <span><?=htmlspecialchars(ltrim($line,'0123456789. •-*'))?></span>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════════ -->
<!-- REQUIRED DOCUMENTS CHECKLIST                              -->
<!-- ══════════════════════════════════════════════════════════ -->
<?php if (!empty($checkDocs)): ?>
<section id="checklist" class="py-20 bg-white">
  <div class="max-w-4xl mx-auto px-6">
    <div class="text-center mb-12" data-aos="fade-up">
      <h2 class="text-4xl font-bold text-gray-900 mb-3"><?= sc('checklist_heading',$settings,'Required Documents') ?></h2>
      <p class="text-xl text-gray-500 max-w-2xl mx-auto"><?= sc('checklist_sub',$settings) ?></p>
    </div>
    <div class="flex items-center justify-center gap-6 mb-8 text-sm text-gray-500">
      <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> Mandatory</span>
      <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-gray-300 inline-block"></span> Optional</span>
    </div>
    <div class="space-y-3" data-aos="fade-up" data-aos-delay="100">
      <?php foreach ($checkDocs as $item): ?>
      <div class="flex items-start gap-4 p-4 rounded-xl border transition-all hover:shadow-sm"
           style="border-color:<?=$item['is_mandatory']?'#fecaca':'#e5e7eb'?>;background:<?=$item['is_mandatory']?'#fff7f7':'#fafafa'?>;">
        <div class="w-6 h-6 rounded border-2 flex items-center justify-center flex-shrink-0 mt-0.5"
             style="border-color:<?=$item['is_mandatory']?'#ef4444':'#d1d5db'?>;background:<?=$item['is_mandatory']?'#fee2e2':'#f3f4f6'?>;">
          <i class="fas fa-<?=$item['is_mandatory']?'asterisk':'circle'?>" style="font-size:.5rem;color:<?=$item['is_mandatory']?'#ef4444':'#9ca3af'?>;"></i>
        </div>
        <div class="flex-1">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-semibold text-gray-900 text-sm"><?=htmlspecialchars($item['title'])?></span>
            <?php if ($item['is_mandatory']): ?>
              <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700">Required</span>
            <?php else: ?>
              <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Optional</span>
            <?php endif; ?>
          </div>
          <?php if ($item['description']): ?>
          <p class="text-xs text-gray-500 mt-1 leading-relaxed"><?=htmlspecialchars($item['description'])?></p>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if ($offlinePath && $regMode !== 'online'): ?>
    <div class="mt-8 text-center" data-aos="fade-up" data-aos-delay="150">
      <a href="<?=htmlspecialchars($offlinePath)?>" target="_blank"
         class="inline-flex items-center gap-3 px-7 py-3.5 rounded-xl font-bold text-white transition-all hover:shadow-lg hover:-translate-y-0.5"
         style="background:linear-gradient(135deg,#1d4ed8,#3b82f6);">
        <i class="fas fa-file-pdf text-red-300"></i> Download Application Form (PDF) <i class="fas fa-arrow-down text-sm"></i>
      </a>
      <p class="text-gray-400 text-xs mt-2">Attach all required documents listed above with the form.</p>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════════ -->
<!-- VIDEOS                                                    -->
<!-- ══════════════════════════════════════════════════════════ -->
<?php if (!empty($videos)): ?>
<section id="videos" class="py-20 bg-gray-50">
  <div class="max-w-6xl mx-auto px-6">
    <div class="text-center mb-14" data-aos="fade-up">
      <h2 class="text-4xl font-bold text-gray-900 mb-3"><?= sc('videos_heading',$settings,'Training Videos & Tutorials') ?></h2>
      <p class="text-xl text-gray-500 max-w-2xl mx-auto"><?= sc('videos_sub',$settings) ?></p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($videos as $idx => $vid): ?>
      <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl border border-gray-100 transition-all duration-300 group cursor-pointer"
           data-aos="fade-up" data-aos-delay="<?=$idx*80?>" onclick="openVideoModal(<?=$idx?>)">
        <div class="relative" style="padding-bottom:56.25%;overflow:hidden;background:#0f172a;">
          <?php if ($vid['thumbnail']): ?>
            <img src="<?=htmlspecialchars($vid['thumbnail'])?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="">
          <?php elseif ($vid['video_type']==='youtube'): ?>
            <img src="https://img.youtube.com/vi/<?=htmlspecialchars(youtubeId($vid['video_url']))?>/mqdefault.jpg" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="">
          <?php else: ?>
            <div class="absolute inset-0 flex items-center justify-center" style="background:linear-gradient(135deg,#1e3a8a,#1d4ed8);"><i class="fas fa-video text-white text-4xl opacity-50"></i></div>
          <?php endif; ?>
          <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"><div class="w-14 h-14 rounded-full bg-white/90 flex items-center justify-center shadow-xl"><i class="fas fa-play text-blue-700 text-xl ml-1"></i></div></div>
          <span class="absolute top-3 right-3 text-xs font-bold px-2 py-1 rounded-full" style="background:<?=$vid['video_type']==='youtube'?'#ef4444':'#1d4ed8'?>;color:#fff;"><?=$vid['video_type']==='youtube'?'YouTube':'Video'?></span>
        </div>
        <div class="p-5">
          <h3 class="font-semibold text-gray-900 mb-2 group-hover:text-blue-700 transition-colors"><?=htmlspecialchars($vid['title'])?></h3>
          <?php if ($vid['description']): ?><p class="text-sm text-gray-500"><?=htmlspecialchars($vid['description'])?></p><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
$videosJson = json_encode(array_values(array_map(function($v){
    return ['title'=>htmlspecialchars($v['title'],ENT_QUOTES),'type'=>$v['video_type'],'url'=>htmlspecialchars($v['video_url'],ENT_QUOTES),'yt_id'=>$v['video_type']==='youtube'?youtubeId($v['video_url']):''];
},$videos)));
?>
<div id="videoModal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.88);backdrop-filter:blur(6px);align-items:center;justify-content:center;padding:24px;">
  <div style="max-width:820px;width:100%;position:relative;">
    <button onclick="closeVideoModal()" style="position:absolute;top:-44px;right:0;background:rgba(255,255,255,.15);border:none;color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;"><i class="fas fa-times"></i></button>
    <p id="vmTitle" style="color:#fff;font-weight:700;font-size:1.1rem;margin-bottom:12px;"></p>
    <div style="position:relative;padding-bottom:56.25%;background:#000;border-radius:14px;overflow:hidden;">
      <iframe id="vmFrame" style="position:absolute;inset:0;width:100%;height:100%;border:none;" allowfullscreen></iframe>
      <video id="vmVideo" style="position:absolute;inset:0;width:100%;height:100%;display:none;" controls></video>
    </div>
  </div>
</div>
<script>
const SV=<?=$videosJson?>;const vm=document.getElementById('videoModal');const vf=document.getElementById('vmFrame');const vv=document.getElementById('vmVideo');const vt=document.getElementById('vmTitle');
function openVideoModal(i){const v=SV[i];vt.textContent=v.title;vf.style.display='none';vv.style.display='none';if(v.type==='youtube'){vf.src='https://www.youtube.com/embed/'+v.yt_id+'?autoplay=1&rel=0';vf.style.display='';}else{vv.src=v.url;vv.style.display='';vv.play();}vm.style.display='flex';document.body.style.overflow='hidden';}
function closeVideoModal(){vm.style.display='none';vf.src='';vv.pause();vv.src='';document.body.style.overflow='';}
vm.addEventListener('click',e=>{if(e.target===vm)closeVideoModal();});document.addEventListener('keydown',e=>{if(e.key==='Escape')closeVideoModal();});
</script>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════════ -->
<!-- RESOURCES / DOCUMENTS                                     -->
<!-- ══════════════════════════════════════════════════════════ -->
<?php if (!empty($documents)): ?>
<section id="documents" class="py-20 bg-white">
  <div class="max-w-6xl mx-auto px-6">
    <div class="text-center mb-14" data-aos="fade-up">
      <h2 class="text-4xl font-bold text-gray-900 mb-3"><?= sc('documents_heading',$settings,'Resources & Downloads') ?></h2>
      <p class="text-xl text-gray-500 max-w-2xl mx-auto"><?= sc('documents_sub',$settings) ?></p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <?php foreach ($documents as $idx => $doc): $ft=strtolower($doc['file_type']); [$dicon,$dcolor]=$docIcons[$ft]??['fa-file','#64748b']; ?>
      <a href="<?=htmlspecialchars($doc['file_path'])?>" target="_blank" rel="noopener"
         class="group flex items-start gap-4 bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-lg hover:border-blue-200 transition-all duration-300"
         data-aos="fade-up" data-aos-delay="<?=$idx*70?>">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform" style="background:<?=$dcolor?>18;">
          <i class="fas <?=$dicon?> text-2xl" style="color:<?=$dcolor?>;"></i>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-gray-900 group-hover:text-blue-700 transition-colors text-sm mb-1"><?=htmlspecialchars($doc['title'])?></p>
          <?php if ($doc['description']): ?><p class="text-xs text-gray-400 mb-2"><?=htmlspecialchars($doc['description'])?></p><?php endif; ?>
          <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase px-2 py-0.5 rounded" style="background:<?=$dcolor?>18;color:<?=$dcolor?>;"><?=strtoupper($ft)?></span>
            <?php if ($doc['file_size']): ?><span class="text-xs text-gray-400"><?=htmlspecialchars($doc['file_size'])?></span><?php endif; ?>
          </div>
        </div>
        <i class="fas fa-download text-gray-300 group-hover:text-blue-500 transition-colors flex-shrink-0 mt-1"></i>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════════ -->
<!-- REGISTRATION — ONLINE / OFFLINE TOGGLE                   -->
<!-- ══════════════════════════════════════════════════════════ -->
<section id="register" class="py-20" style="background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 60%,#1d4ed8 100%);">
  <div class="max-w-3xl mx-auto px-6">

    <div class="text-center mb-10" data-aos="fade-up">
      <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider mb-5"
            style="background:rgba(99,179,237,.15);color:#93c5fd;border:1px solid rgba(99,179,237,.25);">
        <i class="fas fa-user-plus"></i> Free Registration
      </span>
      <h2 class="text-4xl font-bold text-white mb-3"><?= sc('form_heading',$settings,'Register Your Interest') ?></h2>
      <p class="text-blue-200 text-lg"><?= sc('form_sub',$settings) ?></p>
    </div>

    <!-- Mode toggle -->
    <?php if ($regMode === 'both'): ?>
    <div class="flex justify-center mb-8" data-aos="fade-up" data-aos-delay="50">
      <div class="inline-flex rounded-xl p-1" style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);">
        <button id="tabOnlineBtn" onclick="switchTab('online')" class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all duration-200" style="background:rgba(255,255,255,.15);color:#fff;">
          <i class="fas fa-globe mr-2"></i>Online Form
        </button>
        <button id="tabOfflineBtn" onclick="switchTab('offline')" class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all duration-200" style="background:transparent;color:#93c5fd;">
          <i class="fas fa-file-pdf mr-2"></i>Offline / Paper Form
        </button>
      </div>
    </div>
    <?php endif; ?>

    <!-- Alerts -->
    <?php if ($formMsg): ?>
    <div style="background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.4);border-radius:14px;padding:20px 24px;color:#86efac;font-size:.95rem;text-align:center;margin-bottom:24px;">
      <i class="fas fa-check-circle text-green-400 text-xl mb-2 block"></i><?= $formMsg ?>
    </div>
    <?php elseif ($formErr): ?>
    <div style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.4);border-radius:14px;padding:16px 24px;color:#fca5a5;text-align:center;margin-bottom:24px;">
      <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($formErr) ?>
    </div>
    <?php endif; ?>

    <!-- ONLINE FORM -->
    <?php if ($regMode !== 'offline'): ?>
    <div id="panelOnline">
      <form method="POST" class="rounded-2xl p-8 md:p-10" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);backdrop-filter:blur(16px);">
        <input type="hidden" name="samarth_reg" value="1">
        <?php
        $is = "width:100%;padding:12px 16px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.2);border-radius:10px;color:#fff;font-size:.9rem;outline:none;box-sizing:border-box;";
        $ls = "display:block;font-size:.8rem;font-weight:600;color:#93c5fd;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;";
        $fo = "onfocus=\"this.style.borderColor='#60a5fa'\" onblur=\"this.style.borderColor='rgba(255,255,255,.2)'\"";
        ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div style="grid-column:1/-1;"><label style="<?=$ls?>">Full Name *</label><input type="text" name="full_name" required placeholder="Your full name" style="<?=$is?>" <?=$fo?>></div>
          <div><label style="<?=$ls?>">Email Address *</label><input type="email" name="email" required placeholder="you@example.com" style="<?=$is?>" <?=$fo?>></div>
          <div><label style="<?=$ls?>">Phone Number *</label><input type="tel" name="phone" required placeholder="+91 XXXXX XXXXX" style="<?=$is?>" <?=$fo?>></div>
          <div><label style="<?=$ls?>">Organisation</label><input type="text" name="organization" placeholder="Company / School / College" style="<?=$is?>" <?=$fo?>></div>
          <div>
            <label style="<?=$ls?>">Sector / Industry</label>
            <select name="sector" style="<?=$is?>">
              <option value="" style="color:#1e3a8a;">-- Select Sector --</option>
              <?php foreach (['IT / Software','Healthcare','Manufacturing','Construction','Agriculture','Retail / FMCG','Logistics / Transport','Education','Government','Other'] as $s): ?>
              <option value="<?=htmlspecialchars($s)?>" style="color:#1e3a8a;"><?=htmlspecialchars($s)?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div style="grid-column:1/-1;"><label style="<?=$ls?>">Message / Query (optional)</label><textarea name="message" rows="4" placeholder="Tell us about your requirements..." style="<?=$is?>resize:vertical;" <?=$fo?>></textarea></div>
        </div>
        <div class="text-center mt-8">
          <button type="submit" class="inline-flex items-center gap-3 px-10 py-4 font-bold rounded-xl transition-all duration-200 shadow-xl hover:shadow-2xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#fff,#e0f2fe);color:#1e3a8a;font-size:1rem;">
            <i class="fas fa-paper-plane"></i> Submit Registration
          </button>
          <p class="text-blue-300 text-xs mt-4"><i class="fas fa-lock mr-1"></i> Your information is secure and will not be shared.</p>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- OFFLINE PANEL -->
    <?php if ($regMode !== 'online'): ?>
    <div id="panelOffline" style="<?= $regMode==='both' ? 'display:none;' : '' ?>">
      <div class="rounded-2xl p-8 md:p-10" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);backdrop-filter:blur(16px);">

        <!-- Download PDF -->
        <?php if ($offlinePath): ?>
        <div class="text-center mb-8">
          <a href="<?=htmlspecialchars($offlinePath)?>" target="_blank"
             class="inline-flex items-center gap-3 px-8 py-4 rounded-xl font-bold transition-all hover:shadow-xl hover:-translate-y-0.5"
             style="background:linear-gradient(135deg,#fff,#fee2e2);color:#991b1b;font-size:1rem;">
            <i class="fas fa-file-pdf text-2xl text-red-500"></i>
            <div class="text-left">
              <div><?=htmlspecialchars($offlineTitle)?></div>
              <div style="font-size:.72rem;font-weight:400;color:#b91c1c;">Click to Download PDF</div>
            </div>
            <i class="fas fa-download ml-2"></i>
          </a>
        </div>
        <?php else: ?>
        <div class="text-center mb-8 text-blue-200 opacity-60">
          <i class="fas fa-clock text-3xl mb-3 block"></i>
          <p class="text-sm">Offline form will be available shortly.</p>
        </div>
        <?php endif; ?>

        <!-- Step instructions -->
        <?php if ($offlineInstr): ?>
        <h3 class="text-white font-bold text-base mb-4 flex items-center gap-2">
          <i class="fas fa-list-ol text-blue-300"></i> How to Submit Offline
        </h3>
        <ol class="space-y-3 mb-6">
          <?php foreach (array_values(array_filter(array_map('trim',explode("\n",$offlineInstr)))) as $si=>$step): ?>
          <li class="flex items-start gap-3">
            <span class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-black" style="background:rgba(255,255,255,.15);color:#93c5fd;border:1px solid rgba(255,255,255,.25);"><?=$si+1?></span>
            <span class="text-blue-100 text-sm leading-relaxed pt-1"><?=htmlspecialchars(ltrim($step,'0123456789. '))?></span>
          </li>
          <?php endforeach; ?>
        </ol>
        <?php endif; ?>

        <!-- Submission contacts -->
        <?php if ($submitEmail || $submitAddress): ?>
        <div class="grid sm:grid-cols-2 gap-4">
          <?php if ($submitEmail): ?>
          <div class="rounded-xl p-4" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);">
            <div class="flex items-center gap-2 mb-1"><i class="fas fa-envelope text-blue-300 text-sm"></i><span class="text-blue-300 text-xs font-bold uppercase tracking-wider">Email Submission</span></div>
            <a href="mailto:<?=htmlspecialchars($submitEmail)?>" class="text-white text-sm hover:text-blue-300 transition"><?=htmlspecialchars($submitEmail)?></a>
          </div>
          <?php endif; ?>
          <?php if ($submitAddress): ?>
          <div class="rounded-xl p-4" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);">
            <div class="flex items-center gap-2 mb-1"><i class="fas fa-map-marker-alt text-blue-300 text-sm"></i><span class="text-blue-300 text-xs font-bold uppercase tracking-wider">Office Address</span></div>
            <p class="text-white text-xs leading-relaxed"><?=htmlspecialchars($submitAddress)?></p>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<?php if ($regMode === 'both'): ?>
<script>
function switchTab(tab) {
  const op = document.getElementById('panelOnline');
  const fp = document.getElementById('panelOffline');
  const ob = document.getElementById('tabOnlineBtn');
  const fb = document.getElementById('tabOfflineBtn');
  if (tab === 'online') {
    op.style.display=''; fp.style.display='none';
    ob.style.background='rgba(255,255,255,.15)'; ob.style.color='#fff';
    fb.style.background='transparent'; fb.style.color='#93c5fd';
  } else {
    op.style.display='none'; fp.style.display='';
    fb.style.background='rgba(255,255,255,.15)'; fb.style.color='#fff';
    ob.style.background='transparent'; ob.style.color='#93c5fd';
  }
}
if (location.hash==='#offline') switchTab('offline');
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
