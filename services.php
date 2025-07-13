<?php include 'includes/header.php'; 
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
    'title' => 'Training & Developmente',
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

?>

<!-- <section class="py-16">
  <div class="max-w-6xl mx-auto px-4">
    <h2 class="text-3xl font-bold mb-8 text-center">Our Services</h2>
    <div class="grid md:grid-cols-2 gap-6">
      <div class="p-6 bg-white shadow rounded-xl">
        <h3 class="text-lg font-semibold mb-2">Recruitment</h3>
        <p class="text-sm text-gray-600">Entry to Executive level hiring with precision.</p>
      </div>
      <div class="p-6 bg-white shadow rounded-xl">
        <h3 class="text-lg font-semibold mb-2">Training & Development</h3>
        <p class="text-sm text-gray-600">Customized learning modules & assessments.</p>
      </div>
    </div>
  </div>
</section> -->
<div data-aos="fade-up" class="text-center pt-0">
<section id="services" class="py-20 bg-gradient-to-b from-blue-100 to-blue-50 dark:bg-gray-900 text-gray-900 dark:text-white">
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
</div>

<?php include 'includes/footer.php'; ?>
