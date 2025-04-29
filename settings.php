<?php
include 'authcheck.php'; // Adjust path as needed
requireLogin();           // Ensures the user is logged in
allowRoles(['admin', 'staff']); // Both roles can access
?>

<?php include 'header.php'; include 'sidebar.php'; ?>
<main class="min-h-screen bg-gray-50 flex flex-col items-center justify-start py-10 px-2">
  <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-2xl border border-gray-100">
    <h2 class="text-3xl font-bold text-gray-800 mb-2 flex items-center gap-2">
      <i class="fas fa-building text-blue-600"></i> Company Profile
    </h2>
    <p class="text-gray-500 mb-6">Update your company information. This will be used across your system and on reports.</p>
    <form id="companySettingsForm" enctype="multipart/form-data" class="space-y-8">
      <!-- Logo Upload -->
      <div class="flex items-center gap-6">
        <div class="flex-shrink-0">
          <img id="companyLogoPreview" src="https://ui-avatars.com/api/?name=Company&background=random&color=fff&size=128" alt="Company Logo" class="h-24 w-24 rounded-full border-2 border-gray-200 object-cover shadow">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Company Logo</label>
          <input type="file" name="company_logo" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" onchange="previewLogo(event)">
          <p class="text-xs text-gray-400 mt-1">PNG, JPG, or GIF. Max 2MB.</p>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
          <input type="text" name="company_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tagline / Subtitle</label>
          <input type="text" name="company_tagline" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="e.g. Excellence in Inventory">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
          <input type="text" name="company_phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
          <input type="email" name="company_email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
        <textarea name="company_address" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Company address..."></textarea>
      </div>
      <div class="flex justify-end">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-2 rounded-lg shadow transition-all focus:outline-none focus:ring-2 focus:ring-blue-400">Save Changes</button>
      </div>
    </form>
  </div>
</main>
<script>
function previewLogo(event) {
  const [file] = event.target.files;
  if (file) {
    document.getElementById('companyLogoPreview').src = URL.createObjectURL(file);
  }
}
</script>
