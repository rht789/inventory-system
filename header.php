<?php
session_start();

// Handle logout logic directly in header
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'] ?? null;
?>

<!-- Tailwind and Font Awesome -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<header class="sticky top-0 z-50 bg-white rounded-tl-2xl shadow-sm px-6 py-3 flex justify-between items-center">
  <!-- Sidebar toggle (mobile) -->
  <button id="sidebarToggle" class="lg:hidden text-gray-600 hover:text-gray-800">
    <i class="fa fa-bars text-xl"></i>
  </button>

  <h1 class="text-xl font-bold text-gray-800">SmartInventory</h1>

  <div class="flex items-center gap-4 relative">
    <button class="text-gray-600 hover:text-gray-800">
      <i class="fa fa-bell text-lg"></i>
    </button>

    <?php if ($user): ?>
    <div class="relative">
      <!-- Profile trigger -->
      <button id="profileToggle" class="flex items-center gap-2 focus:outline-none">
        <img src="/uploads<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile"
             class="w-9 h-9 rounded-full border border-gray-300 shadow-sm object-cover" />
        <i class="fa fa-user text-xl text-gray-600"></i>
      </button>

      <!-- Dropdown -->
      <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white border shadow-lg rounded-lg p-4 z-50 text-sm">
        <div class="text-gray-800 font-medium truncate"><?= htmlspecialchars($user['email']) ?></div>
        <div class="text-gray-500 text-xs mb-1 capitalize"><?= htmlspecialchars($user['role']) ?></div>
        <div class="text-gray-700 mb-3"><?= htmlspecialchars($user['username']) ?></div>

        <!-- Logout form -->
        <form method="POST">
          <button type="submit" name="logout" class="flex items-center gap-2 text-red-600 hover:text-red-700">
            <i class="fa fa-sign-out-alt"></i> Logout
          </button>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>
</header>

<!-- JS: Toggle profile dropdown -->
<script>
  const toggleBtn = document.getElementById('profileToggle');
  const dropdown = document.getElementById('profileDropdown');

  toggleBtn?.addEventListener('click', () => {
    dropdown.classList.toggle('hidden');
  });

  document.addEventListener('click', function(e) {
    if (!toggleBtn.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.add('hidden');
    }
  });
</script>
