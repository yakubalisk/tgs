<?php include 'includes/header.php'; 
$leaders = array(
  [
    'image' => "assets\images\ceo.jpg",
    'name' => "Dr. Nimit Sheth",
    'position' => "MD & CEO",
    'description' => "Visionary leader driving TGS's global expansion and strategic direction. With extensive experience in recruitment and business development, Dr. Sheth ensures operational excellence and client satisfaction.",
    'expertise' => ["Strategic Leadership", "Global Business", "HR Management"]
  ],
  [
    'image' => "assets\images\hr.jpg",
    'name' => "Dr. Vipul Saxena", 
    'position' => "HR Advisor",
    'description' => "Expert HR strategist providing guidance on talent acquisition, organizational development, and employee relations. Dr. Saxena's insights help shape our recruitment methodologies and client solutions.",
    'expertise' => ["Talent Acquisition", "Organizational Development", "HR Strategy"]
  ]
);

$services = [
  [
    'icon' => 'fa-user',
    'title' => 'Manpower Recruitment',
    'description' => 'Comprehensive recruitment services for White Collar, Blue Collar, Skilled, Semi-skilled & Unskilled manpower across all industries.',
    'features' => ['Global Talent Network', 'Industry Expertise', 'Fast Turnaround', 'Quality Assurance']
  ],
  [
    'icon' => 'fa-file',
    'title' => 'Skill Assessment',
    'description' => 'Advanced skill evaluation and competency mapping to ensure perfect candidate-role alignment for optimal performance.',
    'features' => ['Technical Evaluation', 'Behavioral Assessment', 'Competency Mapping', 'Performance Prediction']
  ],  
  [
    'icon' => 'fa-briefcase',
    'title' => 'HR Management',
    'description' => 'End-to-end HR solutions including policy development, employee relations, and organizational development.',
    'features' => ['Policy Development', 'Employee Relations', 'Organizational Design', 'HR Consulting']
  ],  
  [
    'icon' => 'fa-shield',
    'title' => 'Visa Stamping Process',
    'description' => 'Complete visa documentation and stamping support for international placements with legal compliance.',
    'features' => ['Documentation Support', 'Legal Compliance', 'Fast Processing', 'Global Coverage']
  ],  
  [
    'icon' => 'fa-graduation-cap',
    'title' => 'Global Coverage',
    'description' => 'Corporate etiquette training and cross-cultural preparation for seamless international integration.',
    'features' => ['Corporate Etiquette', 'Cross-Cultural Training', 'Onboarding Support', 'Soft Skills Development']
  ],  
  [
    'icon' => 'fa-globe',
    'title' => 'Export-Import Logistics',
    'description' => 'Professional support for export-import documentation and logistics coordination for global operations.',
    'features' => ['Documentation', 'Logistics Coordination', 'Compliance Support', 'Global Network']
  ],
  
];

 $advantages = [
    [
      'icon' => 'fa-award',
      'title' => "9+ Years of Excellence",
      'description' => "Established in 2015 with proven track record of successful placements across industries",
      'stats' => "Since 2015"
    ],
    [
      'icon' => 'fa-globe',
      'title' => "Global Network",
      'description' => "Extensive network identifying talents across geographies for worldwide opportunities",
      'stats' => "Worldwide Reach"
    ],
    [
      'icon' => 'fa-users',
      'title' => "Expert Team",
      'description' => "Rising quality metrics and key performances by our experienced professional team",
      'stats' => "Quality Focused"
    ],
  ];
?>

<!-- Hero Section -->
<!-- <section class="bg-gradient-to-r from-blue-900 to-blue-600 text-white py-20 text-center">
  <h1 class="text-5xl font-bold">Transforming Dreams to Reality</h1>
  <p class="mt-4 text-xl">Global Recruitment & Skill Assessment Experts</p>
  <div class="mt-6">
    <a href="about.php" class="bg-white text-blue-900 px-6 py-2 rounded-full font-semibold hover:bg-gray-100">Learn More</a>
    <a href="contact.php" class="ml-4 border border-white px-6 py-2 rounded-full hover:bg-white hover:text-blue-900">Contact Us</a>
  </div>
</section>
-->
<section id="home" class="relative min-h-screen flex items-center bg-gradient-to-br from-blue-900 to-blue-600 text-white overflow-hidden">
  <!-- Background pattern -->
  <div class="absolute inset-0 bg-[url('/path-to-your-grid-pattern.svg')] bg-repeat opacity-10"></div>

  <div class="container mx-auto px-4 py-20 relative z-10">
    <div class="grid lg:grid-cols-2 gap-12 items-center">
      <!-- Left Column -->
      <div class="space-y-8">
        <div class="space-y-4">
          <h1 class="text-5xl lg:text-6xl font-bold leading-tight">
            Global Talent,
            <span class="text-blue-300"> Local Expertise</span>
          </h1>
          <p class="text-xl text-white/90 leading-relaxed max-w-2xl">
            Since 2015, Tirth Global Solutions has been connecting exceptional talent 
            with leading organizations across industries and geographies. Your trusted 
            partner for comprehensive HR solutions and recruitment excellence.
          </p>
        </div>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4">
          <a href="#services" class="inline-flex items-center justify-center px-6 py-3 bg-white text-blue-700 text-lg font-medium rounded-md hover:bg-gray-100 transition group">
            Explore Services
            <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
          </a>
          <a href="#contact" class="inline-flex items-center justify-center px-6 py-3 border border-white text-white text-lg font-medium rounded-md hover:bg-white hover:text-blue-700 transition">
            Get Consultation
          </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-6 pt-8 border-t border-white/20">
          <div class="text-center">
            <div class="flex items-center justify-center mb-2">
              <i class="fas fa-globe text-blue-300 text-xl"></i>
            </div>
            <div class="text-2xl font-bold">9+</div>
            <div class="text-sm text-white/80">Years Experience</div>
          </div>
          <div class="text-center">
            <div class="flex items-center justify-center mb-2">
              <i class="fas fa-users text-blue-300 text-xl"></i>
            </div>
            <div class="text-2xl font-bold">1000+</div>
            <div class="text-sm text-white/80">Successful Placements</div>
          </div>
          <div class="text-center">
            <div class="flex items-center justify-center mb-2">
              <i class="fas fa-award text-blue-300 text-xl"></i>
            </div>
            <div class="text-2xl font-bold">50+</div>
            <div class="text-sm text-white/80">Industry Sectors</div>
          </div>
        </div>
      </div>

      <!-- Right Column - Expertise Box -->
      <div class="relative">
        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 shadow-xl text-white">
          <h3 class="text-2xl font-semibold mb-6">Our Expertise Areas</h3>
          <ul class="space-y-4">
            <li class="flex items-center gap-3">
              <div class="w-2 h-2 bg-blue-300 rounded-full"></div>
              <span>White Collar & Blue Collar Recruitment</span>
            </li>
            <li class="flex items-center gap-3">
              <div class="w-2 h-2 bg-blue-300 rounded-full"></div>
              <span>Skilled & Semi-skilled Manpower</span>
            </li>
            <li class="flex items-center gap-3">
              <div class="w-2 h-2 bg-blue-300 rounded-full"></div>
              <span>HR Management & Consulting</span>
            </li>
            <li class="flex items-center gap-3">
              <div class="w-2 h-2 bg-blue-300 rounded-full"></div>
              <span>Visa Stamping & Documentation</span>
            </li>
            <li class="flex items-center gap-3">
              <div class="w-2 h-2 bg-blue-300 rounded-full"></div>
              <span>Legal & Export-Import Logistics</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- Taglines Section -->
<!-- <section class="bg-white py-12">
  <div class="max-w-6xl mx-auto grid md:grid-cols-3 gap-8 text-center">
    <div>
      <h3 class="text-lg font-semibold">Experts at work</h3>
      <p class="text-sm text-gray-600">Sourcing the best talents to build your empire.</p>
    </div>
    <div>
      <h3 class="text-lg font-semibold">Your search ends here</h3>
      <p class="text-sm text-gray-600">Matching passion with purpose globally.</p>
    </div>
    <div>
      <h3 class="text-lg font-semibold">Making society safer</h3>
      <p class="text-sm text-gray-600">With surveillance systems that never sleep.</p>
    </div>
  </div>
</section> -->

<!-- About Snapshot -->
<section class="bg-blue-50 py-16">
  <div class="max-w-5xl mx-auto px-4">
    <h2 class="text-3xl font-bold text-center mb-8">Who We Are</h2>
    <p class="text-gray-700 text-center leading-loose">
      Founded in 2015, Tirth Global Solutions LLP is a global recruitment and assessment firm based in India.
      With a strong footprint in HR, skill development, and surveillance exports, we serve a worldwide clientele.
    </p>
    <div class="mt-10 grid md:grid-cols-3 gap-6 text-center">
      <div class="bg-white shadow p-6 rounded-xl">
        <div class="bg-blue-100 p-3 rounded-full mb-4">
          <i class="fas fa-clipboard-check text-blue-600 text-xl"></i>
        </div>
        <h4 class="text-lg font-semibold">25,000+ Assessments</h4>
        <p class="text-gray-600 text-sm">Across India under various State & Central Schemes</p>
      </div>
      <div class="bg-white shadow p-6 rounded-xl">
        <div class="bg-green-100 p-3 rounded-full mb-4">
          <i class="fas fa-user-tie text-green-600 text-xl"></i>
        </div>
        <h4 class="text-lg font-semibold">Empanelled Experts</h4>
        <p class="text-gray-600 text-sm">Over 250 assessors across sectors</p>
      </div>
      <div class="bg-white shadow p-6 rounded-xl">
        <div class="bg-purple-100 p-3 rounded-full mb-4">
          <i class="fas fa-shield-alt text-purple-600 text-xl"></i>
        </div>
        <h4 class="text-lg font-semibold">Surveillance Export Leader</h4>
        <p class="text-gray-600 text-sm">IP Cameras, VMS Software, and more</p>
      </div>
    </div>
  </div>

<div class="px-4">
  <div class="bg-blue-100 rounded-2xl p-8 mt-6">
    <h3 class="text-2xl font-semibold text-center text-gray-900 mb-8">
      Our Track Record
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
      <div class="text-center">
        <div class="text-3xl font-bold text-blue-600 mb-2">2015</div>
        <div class="text-gray-500">Year Established</div>
      </div>
      <div class="text-center">
        <div class="text-3xl font-bold text-blue-600 mb-2">Global</div>
        <div class="text-gray-500">Network Reach</div>
      </div>
      <div class="text-center">
        <div class="text-3xl font-bold text-blue-600 mb-2">Multi</div>
        <div class="text-gray-500">Industry Expert</div>
      </div>
      <div class="text-center">
        <div class="text-3xl font-bold text-blue-600 mb-2">24/7</div>
        <div class="text-gray-500">Support Available</div>
      </div>
    </div>
  </div>
</div>

</section>

<section id="leadership" class="py-20 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
  <div class="container mx-auto px-4">

    <!-- Section Header -->
    <div class="text-center mb-16">
      <h2 class="text-4xl font-bold mb-4">Leadership Team</h2>
      <p class="text-xl text-gray-500 max-w-3xl mx-auto">
        Meet the experienced professionals who guide TGS towards excellence 
        in global recruitment and HR solutions.
      </p>
    </div>

    <!-- Leaders Grid -->
    <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
      <!-- Repeat this block for each leader -->
      <?php foreach ($leaders as $leader): ?>
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow hover:shadow-lg transition-all duration-300 group border-[1px] border-gray-200">
          <div class="p-8">
            <div class="text-center mb-6">
              <div class="w-24 h-24 border-2 border-blue-500 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-105 transition-transform overflow-hidden">
                <img 
                src="<?= htmlspecialchars($leader['image']) ?>" 
                alt="Leader profile picture"
                class="w-full h-full object-cover rounded-full"
                >
              </div>
              <h3 class="text-2xl font-semibold mb-1"><?= htmlspecialchars($leader['name']) ?></h3>
              <p class="text-lg text-blue-600 font-medium"><?= htmlspecialchars($leader['position']) ?></p>
            </div>

            <p class="text-gray-500 leading-relaxed mb-6 text-center">
              <?= htmlspecialchars($leader['description']) ?>
            </p>

            <!-- Expertise Badges -->
            <div class="flex flex-wrap gap-2 justify-center">
              <?php foreach ($leader['expertise'] as $skill): ?>
                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-3 py-1 rounded-full">
                  <?= htmlspecialchars($skill) ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Leadership Philosophy -->
    <div class="mt-16 text-center">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-8 max-w-4xl mx-auto">
        <h3 class="text-2xl font-semibold mb-4">Leadership Philosophy</h3>
        <p class="text-gray-500 leading-relaxed text-lg">
          Our leadership team believes in fostering a culture of excellence, innovation, 
          and client-centricity. With a combined experience spanning multiple industries 
          and global markets, we ensure that TGS remains at the forefront of recruitment 
          solutions while maintaining the highest standards of professional integrity.
        </p>
      </div>
    </div>

  </div>
</section>

<section id="services" class="py-20 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
  <div class="container mx-auto px-4">

    <!-- Section Header -->
    <div class="text-center mb-16">
      <h2 class="text-4xl font-bold mb-4">Our Services</h2>
      <p class="text-xl text-gray-500 max-w-3xl mx-auto">
        Comprehensive recruitment and HR solutions tailored to meet your 
        organization's unique requirements across global markets.
      </p>
    </div>

    <!-- Services Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
      <?php foreach ($services as $service): ?>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow hover:shadow-lg transition-all duration-300 group p-6 border-[1px] border-gray-200">
        <!-- Icon and Title -->
        <div class="text-center pb-4">
          <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
            <i class="fa <?= htmlspecialchars($service['icon']) ?> text-white text-2xl"></i> <!-- Replace icon if dynamic -->
          </div>
          <h3 class="text-xl font-semibold group-hover:text-blue-600 transition-colors"><?= htmlspecialchars($service['title']) ?></h3>
        </div>

        <!-- Description -->
        <p class="text-gray-500 mb-6 leading-relaxed text-sm">
          <?= htmlspecialchars($service['description']) ?>
        </p>

        <!-- Features -->
        <ul class="space-y-2 mb-6">
          <?php foreach ($service['features'] as $feature): ?>
            <li class="flex items-center gap-2 text-sm">
              <i class="fas fa-check-circle text-blue-600 text-sm"></i>
              <span class="text-gray-600"><?= htmlspecialchars($feature) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>

        <!-- Learn More Button -->
        <a href="#" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-100 transition group">
          Learn More
          <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
        </a>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Recruitment Process Section -->
    <div class="bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900 dark:to-blue-800 rounded-2xl p-8">
      <h3 class="text-3xl font-semibold text-center text-gray-900 dark:text-white mb-8">
        Our Recruitment Process
      </h3>
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



<!-- CTA Banner -->
<section class="bg-blue-900 text-white px-4 py-12 text-center">
  <h2 class="text-2xl font-semibold">Looking to hire or get hired?</h2>
  <p class="mt-2">Let TGS help you transform your dreams into reality.</p>
  <a href="contact.php" class="mt-4 inline-block bg-white text-blue-800 px-6 py-2 rounded-full font-semibold hover:bg-gray-200">Get Started</a>
</section>

<section id="why-us" class="py-20 bg-gradient-to-br from-blue-100 to-blue-50 dark:from-blue-900 dark:to-blue-800 text-gray-900 dark:text-white">
  <div class="container mx-auto px-4">

    <!-- Section Header -->
    <div class="text-center mb-16">
      <h2 class="text-4xl font-bold mb-4">Why Choose TGS?</h2>
      <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
        Your trusted recruitment partner with proven expertise, global reach, 
        and commitment to delivering exceptional talent solutions.
      </p>
    </div>

    <!-- Key Advantages Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
      <?php foreach ($advantages as $advantage): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow hover:shadow-lg transition-all duration-300 group text-center p-6">
          
          <!-- Icon Placeholder -->
          <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
            <i class="fa <?= htmlspecialchars($advantage['icon']) ?> text-white text-2xl"></i> <!-- Replace icon if dynamic -->
          </div>

          <!-- Title -->
          <h3 class="text-xl font-semibold mb-2 group-hover:text-blue-600 transition-colors">
            <?= htmlspecialchars($advantage['title']) ?>
          </h3>

          <!-- Description -->
          <p class="text-gray-500 dark:text-gray-400 mb-4 leading-relaxed">
            <?= htmlspecialchars($advantage['description']) ?>
          </p>

          <!-- Stat Badge -->
          <div class="inline-flex items-center px-3 py-1 bg-blue-100 dark:bg-blue-700 rounded-full">
            <span class="text-sm font-medium text-blue-700 dark:text-blue-100">
              <?= htmlspecialchars($advantage['stats']) ?>
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>



<?php include 'includes/footer.php'; ?>
