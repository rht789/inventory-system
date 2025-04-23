<?php
include 'authcheck.php'; // Adjust path if needed
requireLogin();

$currentPage = basename($_SERVER['PHP_SELF']);
$settingsPages = ['profile.php', 'password.php', 'preference.php', 'company.php'];
$isSettingsOpen = in_array($currentPage, $settingsPages) ? 'block' : 'hidden';
$role = getUserRole();
?>

<aside id="sidebar"
  class="fixed top-0 left-0 z-40 h-full w-64 bg-white border-r transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out shadow-md rounded-r-2xl p-4">

  <div class="text-xl font-bold text-gray-800 flex items-center justify-between mb-8">
    SmartInventory
    <button class="text-gray-400 hover:text-gray-600 lg:hidden" onclick="document.getElementById('sidebar').classList.add('-translate-x-full')">
      <i class="fa fa-times text-lg"></i>
    </button>
  </div>

  <nav class="flex flex-col gap-2 text-sm text-gray-600">

    <?php if ($role === 'admin'): ?>
      <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 <?= $currentPage == 'dashboard.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
        <i class="fa fa-home w-4"></i> Dashboard
      </a>
    <?php endif; ?>

    <?php if (in_array($role, ['admin', 'staff'])): ?>
      <a href="orders.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 <?= $currentPage == 'orders.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
        <i class="fa fa-shopping-cart w-4"></i> Orders
      </a>
      <a href="products.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 <?= $currentPage == 'products.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
        <i class="fa fa-cube w-4"></i> Products
      </a>
      <a href="stock.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 <?= $currentPage == 'stock.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
        <i class="fa fa-boxes w-4"></i> Stock
      </a>
      <a href="batch.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 <?= $currentPage == 'batch.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
        <i class="fa fa-layer-group w-4"></i> Batch
      </a>
      <div>
        <button onclick="document.getElementById('settingsSubmenu').classList.toggle('hidden')" 
                class="flex items-center justify-between w-full px-3 py-2 rounded-lg hover:bg-gray-100 <?= in_array($currentPage, $settingsPages) ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
          <div class="flex items-center gap-3">
            <i class="fa fa-cog w-4"></i> Setting
          </div>
          <i class="fa fa-chevron-down text-xs"></i>
        </button>
        <div id="settingsSubmenu" class="ml-8 mt-1 text-sm flex flex-col gap-1 <?= $isSettingsOpen ?>">
          <a href="profile.php" class="px-2 py-1 rounded hover:bg-gray-100 <?= $currentPage == 'profile.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">Profile</a>
          <a href="password.php" class="px-2 py-1 rounded hover:bg-gray-100 <?= $currentPage == 'password.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">Password</a>
          <a href="preference.php" class="px-2 py-1 rounded hover:bg-gray-100 <?= $currentPage == 'preference.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">Preferences</a>
          <a href="company.php" class="px-2 py-1 rounded hover:bg-gray-100 <?= $currentPage == 'company.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">Company</a>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($role === 'admin'): ?>
      <a href="reports.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 <?= $currentPage == 'reports.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
        <i class="fa fa-chart-bar w-4"></i> Reports
      </a>
      <a href="users.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 <?= $currentPage == 'users.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
        <i class="fa fa-user-friends w-4"></i> Users
      </a>
    <?php endif; ?>

  </nav>
</aside>