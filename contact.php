<?php include 'includes/header.php'; ?>

<!-- <section class="py-16 bg-gray-100">
  <div class="max-w-3xl mx-auto px-4">
    <h2 class="text-3xl font-bold mb-6 text-center">Contact Us</h2>
    <form action="submit_contact.php" method="POST" class="space-y-4 bg-white p-6 shadow rounded-lg">
      <input type="text" name="name" placeholder="Your Name" class="w-full p-2 border rounded" required>
      <input type="email" name="email" placeholder="Your Email" class="w-full p-2 border rounded" required>
      <textarea name="message" placeholder="Your Message" class="w-full p-2 border rounded" rows="5" required></textarea>
      <button type="submit" class="bg-blue-700 text-white px-6 py-2 rounded hover:bg-blue-800">Send</button>
    </form>
  </div>
</section> -->

<div>
  <section id="contact" class="py-20 bg-white">
  <div class="container mx-auto px-4">
    <!-- Heading -->
    <div class="text-center mb-16">
      <h2 class="text-4xl font-bold text-gray-900 mb-4">Get In Touch</h2>
      <p class="text-xl text-gray-600 max-w-3xl mx-auto">
        Ready to find the perfect talent for your organization? Contact our expert team 
        for customized recruitment solutions and HR services.
      </p>
    </div>

    <div class="grid lg:grid-cols-2 gap-12 max-w-6xl mx-auto">
      <!-- Contact Information -->
      <div class="space-y-8">
        <div>
          <h3 class="text-2xl font-semibold text-gray-900 mb-6">Connect With Our Experts</h3>
          <p class="text-gray-600 leading-relaxed mb-8">
            With over 9 years of experience in global recruitment, our team is ready 
            to understand your unique requirements and provide tailored solutions that 
            drive your business success.
          </p>
        </div>

        <div class="space-y-6">
          <!-- Phone Card -->
          <div class="bg-white shadow-md hover:shadow-xl transition-all duration-300 rounded-xl p-6">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-phone text-blue-700 text-lg"></i>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900 mb-1">Phone</h4>
                <p class="text-gray-600">+91-865-507-5656</p>
                <p class="text-gray-600">+91-865-517-5656</p>
              </div>
            </div>
          </div>

          <!-- Email Card -->
          <div class="bg-white shadow-md hover:shadow-xl transition-all duration-300 rounded-xl p-6">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-envelope text-blue-700 text-lg"></i>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900 mb-1">Email</h4>
                <p class="text-gray-600">hr.tirthglobal@gmail.com</p>
                <p class="text-gray-600">hr@tirthglobal.com</p>
              </div>
            </div>
          </div>

          <!-- Address Card -->
          <div class="bg-white shadow-md hover:shadow-xl transition-all duration-300 rounded-xl p-6">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-map-marker-alt text-blue-700 text-lg"></i>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900 mb-1">Address</h4>
                <p class="text-gray-600">
                  Tirth Global Solutions LLP<br />
                  292, Affinwala Building, 2nd Floor, Room No. 6, SBS Road Opp. EMCA House<br />
                  India
                </p>
              </div>
            </div>
          </div>

          <!-- Business Hours Card -->
          <div class="bg-white shadow-md hover:shadow-xl transition-all duration-300 rounded-xl p-6">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-blue-700 text-lg"></i>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900 mb-1">Business Hours</h4>
                <p class="text-gray-600">
                  Monday - Friday: 9:00 AM - 6:00 PM<br />
                  Saturday: 9:00 AM - 1:00 PM<br />
                  Sunday: Closed
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Form -->
      <div>
        <div class="bg-white shadow-md hover:shadow-xl transition-all duration-300 rounded-xl p-8">
          <div class="flex items-center gap-3 mb-6">
            <i class="fas fa-building text-blue-700 text-lg"></i>
            <h3 class="text-2xl font-semibold text-gray-900">Request Consultation</h3>
          </div>

          <form class="space-y-6">
            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-800 mb-2">First Name *</label>
                <input type="text" placeholder="Enter your first name" class="w-full p-3 border border-gray-300 rounded-md" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-800 mb-2">Last Name *</label>
                <input type="text" placeholder="Enter your last name" class="w-full p-3 border border-gray-300 rounded-md" />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-800 mb-2">Email Address *</label>
              <input type="email" placeholder="Enter your email address" class="w-full p-3 border border-gray-300 rounded-md" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-800 mb-2">Phone Number *</label>
              <input type="tel" placeholder="Enter your phone number" class="w-full p-3 border border-gray-300 rounded-md" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-800 mb-2">Company Name</label>
              <input type="text" placeholder="Enter your company name" class="w-full p-3 border border-gray-300 rounded-md" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-800 mb-2">Service Interest</label>
              <select class="w-full p-3 border border-gray-300 rounded-md bg-white">
                <option>Select a service</option>
                <option>Manpower Recruitment</option>
                <option>Skill Assessment</option>
                <option>HR Management</option>
                <option>Visa Processing</option>
                <option>Training & Development</option>
                <option>Other Services</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-800 mb-2">Message *</label>
              <textarea rows="4" placeholder="Tell us about your requirements..." class="w-full p-3 border border-gray-300 rounded-md"></textarea>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-md flex items-center justify-center gap-2">
              <i class="fas fa-paper-plane"></i>
              Send Message
            </button>

            <p class="text-xs text-gray-500 text-center">
              By submitting this form, you agree to our privacy policy and terms of service.
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

</div>

<?php include 'includes/footer.php'; ?>
