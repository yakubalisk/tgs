<?php // includes/footer.php ?>
<footer class="bg-blue-900 text-white">
  <div class="container mx-auto px-4">

    <!-- Main Footer Content -->
    <div class="py-16 grid md:grid-cols-2 lg:grid-cols-4 gap-8">

      <!-- Company Info -->
      <div class="lg:col-span-2">
        <h3 class="text-2xl font-bold mb-4">TGS - Tirth Global Solutions</h3>
        <p class="text-blue-100 leading-relaxed mb-6 max-w-md">
          Since 2015, we've been connecting exceptional talent with leading organizations 
          across industries and geographies. Your trusted partner for comprehensive HR 
          solutions and recruitment excellence.
        </p>
        <div class="space-y-3">
          <div class="flex items-center gap-3">
            <i class="fas fa-phone text-blue-300"></i>
            <span class="text-sm">+91-XXX-XXX-XXXX</span>
          </div>
          <div class="flex items-center gap-3">
            <i class="fas fa-envelope text-blue-300"></i>
            <span class="text-sm">info@tgsindia.com</span>
          </div>
          <div class="flex items-center gap-3">
            <i class="fas fa-map-marker-alt text-blue-300"></i>
            <span class="text-sm">India</span>
          </div>
        </div>
      </div>

      <!-- Quick Links -->
      <div>
        <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
        <ul class="space-y-3">
          <li><a href="#about" class="text-blue-100 hover:text-blue-300 transition">About Us</a></li>
          <li><a href="#services" class="text-blue-100 hover:text-blue-300 transition">Our Services</a></li>
          <li><a href="#leadership" class="text-blue-100 hover:text-blue-300 transition">Leadership</a></li>
          <li><a href="#why-us" class="text-blue-100 hover:text-blue-300 transition">Why Choose Us</a></li>
          <li><a href="#contact" class="text-blue-100 hover:text-blue-300 transition">Contact</a></li>
        </ul>
      </div>

      <!-- Services -->
      <div>
        <h4 class="text-lg font-semibold mb-4">Our Services</h4>
        <ul class="space-y-3">
          <li class="text-blue-100">Manpower Recruitment</li>
          <li class="text-blue-100">Skill Assessment</li>
          <li class="text-blue-100">HR Management</li>
          <li class="text-blue-100">Visa Processing</li>
          <li class="text-blue-100">Training & Development</li>
        </ul>
      </div>
    </div>

    <!-- Newsletter Section -->
    <div class="py-8 border-t border-blue-700">
      <div class="grid md:grid-cols-2 gap-8 items-center">
        <div>
          <h4 class="text-xl font-semibold mb-2">Stay Updated</h4>
          <p class="text-blue-100">
            Subscribe to our newsletter for the latest updates on recruitment trends and opportunities.
          </p>
        </div>
        <form method="POST" class="flex flex-col sm:flex-row gap-3">
          <input 
            type="email" 
            name="newsletter_email"
            placeholder="Enter your email address"
            class="flex-1 px-4 py-2 rounded-md bg-blue-800 border border-blue-600 text-white placeholder:text-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
          <button type="submit" class="px-6 py-2 bg-white text-blue-800 font-semibold rounded hover:bg-blue-100 transition">
            Subscribe
          </button>
        </form>
      </div>
    </div>

    <!-- Bottom Footer -->
    <div class="py-6 border-t border-blue-700">
      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="text-sm text-blue-100">
          © 2024 Tirth Global Solutions LLP. All rights reserved.
        </div>

        <!-- Social Icons -->
        <div class="flex items-center gap-4">
          <a href="#" class="text-white hover:text-blue-300 transition">
            <i class="fab fa-linkedin-in"></i>
          </a>
          <a href="#" class="text-white hover:text-blue-300 transition">
            <i class="fab fa-twitter"></i>
          </a>
          <a href="#" class="text-white hover:text-blue-300 transition">
            <i class="fab fa-facebook-f"></i>
          </a>
        </div>

        <!-- Scroll to Top -->
        <button onclick="window.scrollTo({ top: 0, behavior: 'smooth' });" 
          class="text-white hover:text-blue-300 transition">
          <i class="fas fa-arrow-up"></i>
        </button>
      </div>
    </div>
  </div>
</footer>


</body>
</html>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      AOS.init();

      const toggleTheme = document.getElementById('toggle-theme');
      toggleTheme.addEventListener('click', () => {
        const html = document.documentElement;
        const currentTheme = html.getAttribute('data-theme');
        html.setAttribute('data-theme', currentTheme === 'dark' ? 'light' : 'dark');
      });
    });
  </script>