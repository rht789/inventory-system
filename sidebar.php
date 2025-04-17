<!-- sidebar.php -->
<aside id="sidebar"
  class="fixed top-0 left-0 z-40 h-full w-64 bg-white border-r transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out shadow-md rounded-r-2xl p-4">
  
  <div class="text-xl font-bold text-gray-800 flex items-center justify-between mb-8">
    SmartInventory
    <button class="text-gray-400 hover:text-gray-600 lg:hidden" onclick="document.getElementById('sidebar').classList.add('-translate-x-full')">
      <i class="fa fa-times text-lg"></i>
    </button>
  </div>

  <nav class="flex flex-col gap-2 text-sm text-gray-600">
    <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
      <i class="fa fa-home w-4"></i> Dashboard
    </a>
    <a href="orders.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
      <i class="fa fa-shopping-cart w-4"></i> Orders
    </a>
    <a href="products.php" class="flex items-center gap-3 px-3 py-2 bg-gray-100 text-gray-900 rounded-lg font-medium">
      <i class="fa fa-cube w-4"></i> Products
    </a>
    <a href="stock.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
      <i class="fa fa-boxes w-4"></i> Stock
    </a>
    <a href="reports.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
      <i class="fa fa-chart-bar w-4"></i> Reports
    </a>
    <a href="owners.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
      <i class="fa fa-user-friends w-4"></i> Users
    </a>
    <a href="settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100">
      <i class="fa fa-cog w-4"></i> Setting
    </a>
  </nav>
</aside>
