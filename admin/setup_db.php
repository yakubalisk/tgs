<?php
require_once '../function.php';
$db = new Database();
$pdo = $db->con;

$errors = [];
$success = [];

$tables = [

// ── hero_slides ──────────────────────────────────────────────
"hero_slides" => "CREATE TABLE IF NOT EXISTS `hero_slides` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `image`       VARCHAR(500) NOT NULL,
  `title`       VARCHAR(255) DEFAULT '',
  `subtitle`    TEXT DEFAULT '',
  `btn1_label`  VARCHAR(100) DEFAULT '',
  `btn1_link`   VARCHAR(255) DEFAULT '',
  `btn2_label`  VARCHAR(100) DEFAULT '',
  `btn2_link`   VARCHAR(255) DEFAULT '',
  `sort_order`  INT DEFAULT 0,
  `is_active`   TINYINT(1) DEFAULT 1,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// ── home_settings ─────────────────────────────────────────────
"home_settings" => "CREATE TABLE IF NOT EXISTS `home_settings` (
  `id`    INT AUTO_INCREMENT PRIMARY KEY,
  `skey`  VARCHAR(100) NOT NULL UNIQUE,
  `sval`  TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// ── services ─────────────────────────────────────────────────
"services" => "CREATE TABLE IF NOT EXISTS `services` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `icon`        VARCHAR(100) DEFAULT 'fa-briefcase',
  `title`       VARCHAR(255) NOT NULL,
  `description` TEXT,
  `features`    TEXT COMMENT 'JSON array of feature strings',
  `sort_order`  INT DEFAULT 0,
  `is_active`   TINYINT(1) DEFAULT 1,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// ── leaders ──────────────────────────────────────────────────
"leaders" => "CREATE TABLE IF NOT EXISTS `leaders` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(255) NOT NULL,
  `position`    VARCHAR(255) DEFAULT '',
  `image`       VARCHAR(500) DEFAULT '',
  `description` TEXT,
  `expertise`   TEXT COMMENT 'JSON array of expertise strings',
  `sort_order`  INT DEFAULT 0,
  `is_active`   TINYINT(1) DEFAULT 1,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// ── advantages ───────────────────────────────────────────────
"advantages" => "CREATE TABLE IF NOT EXISTS `advantages` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `icon`        VARCHAR(100) DEFAULT 'fa-star',
  `title`       VARCHAR(255) NOT NULL,
  `description` TEXT,
  `stats`       VARCHAR(100) DEFAULT '',
  `sort_order`  INT DEFAULT 0,
  `is_active`   TINYINT(1) DEFAULT 1,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// ── client_logos ─────────────────────────────────────────────
"client_logos" => "CREATE TABLE IF NOT EXISTS `client_logos` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `image`      VARCHAR(500) NOT NULL,
  `alt_text`   VARCHAR(255) DEFAULT '',
  `sort_order` INT DEFAULT 0,
  `is_active`  TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

];

foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        $success[] = "✅ Table <strong>$name</strong> created / already exists.";
    } catch (Exception $e) {
        $errors[] = "❌ Table <strong>$name</strong>: " . $e->getMessage();
    }
}

// ── Seed default home_settings ───────────────────────────────
$defaults = [
    // Who We Are
    'who_heading'        => 'Who We Are',
    'who_description'    => 'Founded in 2015, Tirth Global Solutions LLP is a global recruitment and assessment firm based in India. With a strong footprint in HR, skill development, and surveillance exports, we serve a worldwide clientele.',
    'stat1_number'       => '30,000+',
    'stat1_label'        => 'Assessments',
    'stat1_sub'          => 'Across India under various State & Central Schemes',
    'stat1_icon'         => 'fa-clipboard-check',
    'stat1_color'        => 'blue',
    'stat2_number'       => 'Empanelled',
    'stat2_label'        => 'Experts',
    'stat2_sub'          => 'Over 250 assessors across sectors',
    'stat2_icon'         => 'fa-user-tie',
    'stat2_color'        => 'green',
    'stat3_number'       => 'Surveillance',
    'stat3_label'        => 'Export Leader',
    'stat3_sub'          => 'IP Cameras, VMS Software, and more',
    'stat3_icon'         => 'fa-shield-alt',
    'stat3_color'        => 'purple',
    // Track Record
    'track1_val'  => '2015', 'track1_label'  => 'Year Established',
    'track2_val'  => 'Global','track2_label' => 'Network Reach',
    'track3_val'  => 'Multi', 'track3_label'  => 'Industry Expert',
    'track4_val'  => '24/7',  'track4_label'  => 'Support Available',
    // Hero mini stats
    'hero_stat1_val'   => '9+',   'hero_stat1_label' => 'Years Experience',
    'hero_stat2_val'   => '1000+','hero_stat2_label' => 'Successful Placements',
    'hero_stat3_val'   => '50+',  'hero_stat3_label' => 'Industry Sectors',
    // Hero expertise box
    'expertise_heading' => 'Our Expertise Areas',
    'expertise_items'   => json_encode([
        'White Collar & Blue Collar Recruitment',
        'Skilled & Semi-skilled Manpower',
        'HR Management & Consulting',
        'Visa Stamping & Documentation',
        'Legal & Export-Import Logistics',
    ]),
    // CTA Banner
    'cta_heading' => 'Looking to hire or get hired?',
    'cta_sub'     => 'Let TGS help you transform your dreams into reality.',
    'cta_btn'     => 'Get Started',
    'cta_link'    => 'contact.php',
    // Trusted clients section
    'clients_heading' => 'Trusted By Industry Leaders',
    'clients_sub'     => "We've proudly partnered with government bodies, corporate leaders, and global enterprises across multiple sectors.",
    // Leadership section
    'leadership_heading' => 'Leadership Team',
    'leadership_sub'     => 'Meet the experienced professionals who guide TGS towards excellence in global recruitment and HR solutions.',
    'leadership_philosophy' => 'Our leadership team believes in fostering a culture of excellence, innovation, and client-centricity. With a combined experience spanning multiple industries and global markets, we ensure that TGS remains at the forefront of recruitment solutions while maintaining the highest standards of professional integrity.',
    // Services section
    'services_heading' => 'Our Services',
    'services_sub'     => "Comprehensive recruitment and HR solutions tailored to meet your organization's unique requirements across global markets.",
    // Why us section
    'whyus_heading' => 'Why Choose TGS?',
    'whyus_sub'     => 'Your trusted recruitment partner with proven expertise, global reach, and commitment to delivering exceptional talent solutions.',
];

$seedStmt = $pdo->prepare("INSERT IGNORE INTO home_settings (skey, sval) VALUES (:k, :v)");
foreach ($defaults as $k => $v) {
    try {
        $seedStmt->execute([':k' => $k, ':v' => $v]);
    } catch (Exception $e) {
        $errors[] = "⚠️ Setting <strong>$k</strong>: " . $e->getMessage();
    }
}
$success[] = "✅ Default <strong>home_settings</strong> seeded.";

// ── Seed default services ─────────────────────────────────────
$defaultServices = [
    ['fa-user',           'Manpower Recruitment',     'Comprehensive recruitment services for White Collar, Blue Collar, Skilled, Semi-skilled & Unskilled manpower across all industries.',            '["Global Talent Network","Industry Expertise","Fast Turnaround","Quality Assurance"]', 1],
    ['fa-file',           'Skill Assessment',         'Advanced skill evaluation and competency mapping to ensure perfect candidate-role alignment for optimal performance.',                              '["Technical Evaluation","Behavioral Assessment","Competency Mapping","Performance Prediction"]', 2],
    ['fa-briefcase',      'HR Management',            'End-to-end HR solutions including policy development, employee relations, and organizational development.',                                         '["Policy Development","Employee Relations","Organizational Design","HR Consulting"]', 3],
    ['fa-shield',         'Visa Stamping Process',    'Complete visa documentation and stamping support for international placements with legal compliance.',                                              '["Documentation Support","Legal Compliance","Fast Processing","Global Coverage"]', 4],
    ['fa-graduation-cap', 'Training & Development',   'Corporate etiquette training and cross-cultural preparation for seamless international integration.',                                               '["Corporate Etiquette","Cross-Cultural Training","Onboarding Support","Soft Skills Development"]', 5],
    ['fa-globe',          'Export-Import Logistics',  'Professional support for export-import documentation and logistics coordination for global operations.',                                            '["Documentation","Logistics Coordination","Compliance Support","Global Network"]', 6],
];
$srvStmt = $pdo->prepare("INSERT IGNORE INTO services (icon,title,description,features,sort_order) VALUES (?,?,?,?,?)");
foreach ($defaultServices as $s) {
    try { $srvStmt->execute($s); } catch (Exception $e) { /* already exists */ }
}
$success[] = "✅ Default <strong>services</strong> seeded.";

// ── Seed default leaders ──────────────────────────────────────
$defaultLeaders = [
    ['Dr. Nimit Sheth', 'MD & CEO', 'assets/images/ceo.jpg', 
     'Visionary leader driving TGS\'s global expansion and strategic direction. With extensive experience in recruitment and business development, Dr. Sheth ensures operational excellence and client satisfaction.',
     '["Strategic Leadership","Global Business","HR Management"]', 1],
    ['Dr. Vipul Saxena', 'HR Advisor', 'assets/images/hr.jpg',
     'Expert HR strategist providing guidance on talent acquisition, organizational development, and employee relations. Dr. Saxena\'s insights help shape our recruitment methodologies and client solutions.',
     '["Talent Acquisition","Organizational Development","HR Strategy"]', 2],
];
$ldrStmt = $pdo->prepare("INSERT IGNORE INTO leaders (name,position,image,description,expertise,sort_order) VALUES (?,?,?,?,?,?)");
foreach ($defaultLeaders as $l) {
    try { $ldrStmt->execute($l); } catch (Exception $e) { /* skip */ }
}
$success[] = "✅ Default <strong>leaders</strong> seeded.";

// ── Seed default advantages ───────────────────────────────────
$defaultAdv = [
    ['fa-award',  '9+ Years of Excellence', 'Established in 2015 with proven track record of successful placements across industries', 'Since 2015',    1],
    ['fa-globe',  'Global Network',          'Extensive network identifying talents across geographies for worldwide opportunities',    'Worldwide Reach',2],
    ['fa-users',  'Expert Team',             'Rising quality metrics and key performances by our experienced professional team',       'Quality Focused',3],
];
$advStmt = $pdo->prepare("INSERT IGNORE INTO advantages (icon,title,description,stats,sort_order) VALUES (?,?,?,?,?)");
foreach ($defaultAdv as $a) {
    try { $advStmt->execute($a); } catch (Exception $e) { /* skip */ }
}
$success[] = "✅ Default <strong>advantages</strong> seeded.";

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>TGS – Database Setup</title>
  <style>
    body { font-family: sans-serif; max-width: 700px; margin: 60px auto; background: #f3f4f6; color: #111; }
    h1   { color: #1d4ed8; }
    .msg { background: #fff; border-radius: 8px; padding: 16px 20px; margin: 8px 0; box-shadow: 0 1px 4px rgba(0,0,0,.1); font-size: 15px; }
    .err { border-left: 4px solid #ef4444; }
    .ok  { border-left: 4px solid #22c55e; }
    a { display: inline-block; margin-top: 24px; background: #1d4ed8; color: #fff; padding: 10px 24px; border-radius: 6px; text-decoration: none; }
  </style>
</head>
<body>
  <h1>🛠️ TGS Database Setup</h1>
  <?php foreach ($success as $m): ?>
    <div class="msg ok"><?= $m ?></div>
  <?php endforeach; ?>
  <?php foreach ($errors as $m): ?>
    <div class="msg err"><?= $m ?></div>
  <?php endforeach; ?>
  <?php if (empty($errors)): ?>
    <div class="msg ok">🎉 <strong>All done!</strong> Database is ready. You can now delete this file.</div>
  <?php endif; ?>
  <a href="index.php">← Go to Admin Dashboard</a>
</body>
</html>
