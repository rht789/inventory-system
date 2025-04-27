<?php
require_once 'authcheck.php';
requireLogin(); // Ensures the user is logged in

// Get user information
$userId = $_SESSION['user_id'] ?? null;
$username = $_SESSION['user_username'] ?? '';
$role = $_SESSION['user_role'] ?? '';

include 'header.php';
include 'sidebar.php';

// Get greeting based on time of day
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good morning";
} elseif ($hour < 18) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}
?>

<main class="lg:ml-64 min-h-screen p-6 bg-gray-50">
  <!-- Toast container -->
  <div id="toast"
       class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg hidden z-50">
  </div>

  <!-- Hero section with greeting and date -->
  <div class="bg-white rounded-lg shadow-sm p-8 mb-6 relative overflow-hidden">
    <div class="relative z-10">
      <h2 class="text-3xl font-bold text-gray-800"><?= $greeting ?>, <?= htmlspecialchars($username) ?></h2>
      <p class="mt-2 text-gray-600"><?php echo date('l, F j, Y'); ?></p>
      
      <div class="mt-6 flex gap-3">
        <?php if ($role === 'admin'): ?>
          <a href="dashboard.php" 
             class="inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-md text-white transition-all">
            <span>Admin Dashboard</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </a>
          <a href="reports.php" 
             class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white hover:bg-gray-50 rounded-md text-gray-700 transition-all">
            <span>View Reports</span>
          </a>
        <?php else: ?>
          <a href="sales.php?action=new" 
             class="inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-md text-white transition-all">
            <span>Create New Sale</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </a>
          <a href="stock.php?action=add" 
             class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white hover:bg-gray-50 rounded-md text-gray-700 transition-all">
            <span>Restock Items</span>
          </a>
        <?php endif; ?>
      </div>
    </div>
    
    <!-- Role-specific decorative element -->
    <?php if ($role === 'admin'): ?>
      <div class="absolute top-0 right-0 w-48 h-48 bg-indigo-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
      <div class="absolute bottom-0 right-24 w-32 h-32 bg-indigo-50 rounded-full -mb-12 opacity-40"></div>
    <?php else: ?>
      <div class="absolute top-0 right-0 w-48 h-48 bg-gray-100 rounded-full -mr-16 -mt-16 opacity-50"></div>
      <div class="absolute bottom-0 right-24 w-32 h-32 bg-gray-100 rounded-full -mb-12 opacity-40"></div>
    <?php endif; ?>
  </div>

  <!-- Quick Tasks Section -->
  <div class="mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Tasks</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php if ($role === 'admin'): ?>
        <!-- Admin-specific quick tasks -->
        <a href="products.php?action=add" class="bg-white p-5 rounded-lg shadow-sm hover:shadow-md transition duration-200 text-center">
          <div class="w-12 h-12 mx-auto mb-3 bg-gray-50 rounded-full flex items-center justify-center">
            <i class="fas fa-plus text-gray-700"></i>
          </div>
          <h4 class="font-medium text-gray-800">Add Product</h4>
        </a>
        
        <a href="users.php?action=add" class="bg-white p-5 rounded-lg shadow-sm hover:shadow-md transition duration-200 text-center">
          <div class="w-12 h-12 mx-auto mb-3 bg-gray-50 rounded-full flex items-center justify-center">
            <i class="fas fa-user-plus text-gray-700"></i>
          </div>
          <h4 class="font-medium text-gray-800">Add User</h4>
        </a>
        
        <a href="reports.php" class="bg-white p-5 rounded-lg shadow-sm hover:shadow-md transition duration-200 text-center">
          <div class="w-12 h-12 mx-auto mb-3 bg-gray-50 rounded-full flex items-center justify-center">
            <i class="fas fa-chart-bar text-gray-700"></i>
          </div>
          <h4 class="font-medium text-gray-800">View Reports</h4>
        </a>
        
        <a href="stock.php?status=low" class="bg-white p-5 rounded-lg shadow-sm hover:shadow-md transition duration-200 text-center">
          <div class="w-12 h-12 mx-auto mb-3 bg-gray-50 rounded-full flex items-center justify-center">
            <i class="fas fa-exclamation-triangle text-gray-700"></i>
          </div>
          <h4 class="font-medium text-gray-800">Low Stock</h4>
        </a>
      <?php else: ?>
        <!-- Staff-specific quick tasks -->
        <a href="sales.php?action=new" class="bg-white p-5 rounded-lg shadow-sm hover:shadow-md transition duration-200 text-center">
          <div class="w-12 h-12 mx-auto mb-3 bg-gray-50 rounded-full flex items-center justify-center">
            <i class="fas fa-shopping-cart text-gray-700"></i>
          </div>
          <h4 class="font-medium text-gray-800">New Sale</h4>
        </a>
        
        <a href="stock.php?action=add" class="bg-white p-5 rounded-lg shadow-sm hover:shadow-md transition duration-200 text-center">
          <div class="w-12 h-12 mx-auto mb-3 bg-gray-50 rounded-full flex items-center justify-center">
            <i class="fas fa-box text-gray-700"></i>
          </div>
          <h4 class="font-medium text-gray-800">Restock Items</h4>
        </a>
        
        <a href="products.php" class="bg-white p-5 rounded-lg shadow-sm hover:shadow-md transition duration-200 text-center">
          <div class="w-12 h-12 mx-auto mb-3 bg-gray-50 rounded-full flex items-center justify-center">
            <i class="fas fa-search text-gray-700"></i>
          </div>
          <h4 class="font-medium text-gray-800">Check Products</h4>
        </a>
        
        <a href="batches.php?action=add" class="bg-white p-5 rounded-lg shadow-sm hover:shadow-md transition duration-200 text-center">
          <div class="w-12 h-12 mx-auto mb-3 bg-gray-50 rounded-full flex items-center justify-center">
            <i class="fas fa-layer-group text-gray-700"></i>
          </div>
          <h4 class="font-medium text-gray-800">New Batch</h4>
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Main Content Area -->
  <?php if ($role === 'admin'): ?>
  <!-- ADMIN LAYOUT -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - System Overview -->
    <div class="lg:col-span-2 grid grid-cols-1 gap-6">
      <!-- System Status Summary -->
      <div class="bg-white rounded-lg shadow-sm p-5 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">System Status</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="p-4 bg-indigo-50 rounded-lg">
            <p class="text-xs text-indigo-800 font-medium">TOTAL PRODUCTS</p>
            <p class="text-2xl font-bold text-gray-800" id="admin-total-products">--</p>
          </div>
          <div class="p-4 bg-green-50 rounded-lg">
            <p class="text-xs text-green-800 font-medium">TOTAL USERS</p>
            <p class="text-2xl font-bold text-gray-800" id="admin-total-users">--</p>
          </div>
          <div class="p-4 bg-yellow-50 rounded-lg">
            <p class="text-xs text-yellow-800 font-medium">LOW STOCK</p>
            <p class="text-2xl font-bold text-gray-800" id="admin-low-stock">--</p>
          </div>
          <div class="p-4 bg-blue-50 rounded-lg">
            <p class="text-xs text-blue-800 font-medium">PENDING SALES</p>
            <p class="text-2xl font-bold text-gray-800" id="admin-pending-sales">--</p>
          </div>
        </div>
      </div>
      
      <!-- Attention Required Section -->
      <div class="bg-white rounded-lg shadow-sm p-5 mb-6" id="attention-section">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-800">System Alerts</h3>
          <a href="reports.php" class="text-sm text-gray-600 hover:text-gray-900">View All</a>
        </div>
        <div class="space-y-4" id="attention-content">
          <div class="animate-pulse flex space-x-4">
            <div class="flex-1 space-y-4 py-1">
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
              <div class="space-y-2">
                <div class="h-4 bg-gray-200 rounded"></div>
                <div class="h-4 bg-gray-200 rounded w-5/6"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Recent System Activities -->
      <div class="bg-white rounded-lg shadow-sm p-5">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-800">Recent System Activities</h3>
          <a href="#" class="text-sm text-gray-600 hover:text-gray-900">View All</a>
        </div>
        <div class="space-y-4" id="system-activities">
          <div class="flex justify-center items-center h-24">
            <div class="animate-spin h-8 w-8 border-4 border-gray-400 rounded-full border-t-transparent"></div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Right Column - Admin Tools -->
    <div class="space-y-6">
      <!-- Sales Summary -->
      <div class="bg-white rounded-lg shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Sales Summary</h3>
        <div class="space-y-3">
          <div class="flex justify-between">
            <span class="text-gray-600">Today</span>
            <span class="font-medium text-gray-800" id="admin-sales-today">--</span>
          </div>
          <div class="w-full h-px bg-gray-100"></div>
          
          <div class="flex justify-between">
            <span class="text-gray-600">This Week</span>
            <span class="font-medium text-gray-800" id="admin-sales-week">--</span>
          </div>
          <div class="w-full h-px bg-gray-100"></div>
          
          <div class="flex justify-between">
            <span class="text-gray-600">This Month</span>
            <span class="font-medium text-gray-800" id="admin-sales-month">--</span>
          </div>
          
          <div class="w-full h-px bg-gray-100 mt-3"></div>
          <div class="pt-2">
            <a href="reports.php" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center justify-center">
              <span>View Detailed Reports</span>
              <i class="fas fa-arrow-right ml-1"></i>
            </a>
          </div>
        </div>
      </div>
      
      <!-- Admin Shortcuts -->
      <div class="bg-white rounded-lg shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Admin Tools</h3>
        <div class="space-y-2">
          <a href="dashboard.php" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded-md text-gray-800 transition-colors">
            <div class="flex items-center">
              <i class="fas fa-tachometer-alt w-5 text-gray-500"></i>
              <span class="ml-3">Analytics Dashboard</span>
            </div>
          </a>
          <a href="users.php" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded-md text-gray-800 transition-colors">
            <div class="flex items-center">
              <i class="fas fa-users w-5 text-gray-500"></i>
              <span class="ml-3">Manage Users</span>
            </div>
          </a>
          <a href="reports.php?type=inventory" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded-md text-gray-800 transition-colors">
            <div class="flex items-center">
              <i class="fas fa-boxes w-5 text-gray-500"></i>
              <span class="ml-3">Inventory Report</span>
            </div>
          </a>
          <a href="settings.php" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded-md text-gray-800 transition-colors">
            <div class="flex items-center">
              <i class="fas fa-cog w-5 text-gray-500"></i>
              <span class="ml-3">System Settings</span>
            </div>
          </a>
        </div>
      </div>
      
      <!-- User Management Quick Access -->
      <div class="bg-white rounded-lg shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">User Management</h3>
        <div class="space-y-2">
          <a href="users.php?action=add" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded-md text-gray-800 transition-colors">
            <div class="flex items-center">
              <i class="fas fa-user-plus w-5 text-gray-500"></i>
              <span class="ml-3">Add New User</span>
            </div>
          </a>
          <a href="users.php" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded-md text-gray-800 transition-colors">
            <div class="flex items-center">
              <i class="fas fa-user-edit w-5 text-gray-500"></i>
              <span class="ml-3">Edit Users</span>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>
  
  <?php else: ?>
  <!-- STAFF LAYOUT -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Section - My Tasks -->
    <div class="lg:col-span-2">
      <!-- Attention Required Section -->
      <div class="bg-white rounded-lg shadow-sm p-5 mb-6" id="attention-section">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Attention Required</h3>
        <div class="space-y-4" id="attention-content">
          <div class="animate-pulse flex space-x-4">
            <div class="flex-1 space-y-4 py-1">
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
              <div class="space-y-2">
                <div class="h-4 bg-gray-200 rounded"></div>
                <div class="h-4 bg-gray-200 rounded w-5/6"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- My Recent Activities -->
      <div class="bg-white rounded-lg shadow-sm p-5 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">My Recent Activities</h3>
        <div class="space-y-4" id="user-activities">
          <div class="flex justify-center items-center h-24">
            <div class="animate-spin h-8 w-8 border-4 border-gray-400 rounded-full border-t-transparent"></div>
          </div>
        </div>
      </div>
      
      <!-- Recent Sales -->
      <div class="bg-white rounded-lg shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Sales</h3>
        <div class="space-y-4" id="recent-sales">
          <div class="flex justify-center items-center h-24">
            <div class="animate-spin h-8 w-8 border-4 border-gray-400 rounded-full border-t-transparent"></div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Right Section - Status & Tools -->
    <div class="space-y-6">
      <!-- Today's Summary -->
      <div class="bg-white rounded-lg shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Today's Summary</h3>
        <div class="space-y-3">
          <div class="flex justify-between">
            <span class="text-gray-600">My Sales</span>
            <span class="font-medium text-gray-800" id="today-sales-count">--</span>
          </div>
          <div class="w-full h-px bg-gray-100"></div>
          
          <div class="flex justify-between">
            <span class="text-gray-600">Revenue</span>
            <span class="font-medium text-gray-800" id="today-revenue">--</span>
          </div>
          <div class="w-full h-px bg-gray-100"></div>
          
          <div class="flex justify-between">
            <span class="text-gray-600">Stock Updates</span>
            <span class="font-medium text-gray-800" id="today-stock-updates">--</span>
          </div>
        </div>
      </div>
      
      <!-- Shortcuts -->
      <div class="bg-white rounded-lg shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Shortcuts</h3>
        <div class="space-y-2">
          <a href="sales.php?action=new" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded-md text-gray-800 transition-colors">
            <div class="flex items-center">
              <i class="fas fa-shopping-cart w-5 text-gray-500"></i>
              <span class="ml-3">New Sale</span>
            </div>
          </a>
          <a href="products.php" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded-md text-gray-800 transition-colors">
            <div class="flex items-center">
              <i class="fas fa-search w-5 text-gray-500"></i>
              <span class="ml-3">Search Products</span>
            </div>
          </a>
          <a href="stock.php?action=add" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded-md text-gray-800 transition-colors">
            <div class="flex items-center">
              <i class="fas fa-box w-5 text-gray-500"></i>
              <span class="ml-3">Add Stock</span>
            </div>
          </a>
          <a href="profile.php" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded-md text-gray-800 transition-colors">
            <div class="flex items-center">
              <i class="fas fa-user-circle w-5 text-gray-500"></i>
              <span class="ml-3">My Profile</span>
            </div>
          </a>
        </div>
      </div>
      
      <!-- Help & Resources -->
      <div class="bg-white rounded-lg shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Help & Resources</h3>
        <div class="space-y-2">
          <a href="#" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded-md text-gray-800 transition-colors">
            <div class="flex items-center">
              <i class="fas fa-book w-5 text-gray-500"></i>
              <span class="ml-3">User Guide</span>
            </div>
          </a>
          <a href="#" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded-md text-gray-800 transition-colors">
            <div class="flex items-center">
              <i class="fas fa-question-circle w-5 text-gray-500"></i>
              <span class="ml-3">Support</span>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</main>

<!-- Load dynamic content with JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  <?php if ($role === 'admin'): ?>
  // Admin-specific data loading
  fetchAdminMetrics();
  fetchSystemAlerts();
  fetchSystemActivities();
  <?php else: ?>
  // Staff-specific data loading
  fetchUserAlerts();
  fetchUserActivities();
  fetchRecentSales();
  fetchTodaysSummary();
  <?php endif; ?>
});

<?php if ($role === 'admin'): ?>
// Admin functions
function fetchAdminMetrics() {
  // Simulate loading admin metrics with a short delay
  setTimeout(() => {
    document.getElementById('admin-total-products').textContent = '<?php echo rand(100, 500); ?>';
    document.getElementById('admin-total-users').textContent = '<?php echo rand(5, 30); ?>';
    document.getElementById('admin-low-stock').textContent = '<?php echo rand(0, 15); ?>';
    document.getElementById('admin-pending-sales').textContent = '<?php echo rand(0, 10); ?>';
    
    document.getElementById('admin-sales-today').textContent = '$<?php echo number_format(rand(500, 2000), 2); ?>';
    document.getElementById('admin-sales-week').textContent = '$<?php echo number_format(rand(3000, 10000), 2); ?>';
    document.getElementById('admin-sales-month').textContent = '$<?php echo number_format(rand(12000, 50000), 2); ?>';
  }, 800);
}

function fetchSystemAlerts() {
  // Simulate loading system alerts
  setTimeout(() => {
    const alertsContainer = document.getElementById('attention-content');
    
    // Sample alerts data - in production, this would come from an API
    const alerts = [
      { type: 'warning', message: 'Low stock for 5 products', link: 'stock.php?status=low' },
      { type: 'info', message: '3 new user registrations pending approval', link: 'users.php?status=pending' },
      { type: 'danger', message: 'Database backup not completed in the last 7 days', link: 'settings.php?section=backup' }
    ];
    
    if (alerts.length === 0) {
      alertsContainer.innerHTML = '<p class="text-gray-500 text-center py-4">No alerts at this time!</p>';
      return;
    }
    
    let alertsHTML = '';
    alerts.forEach(alert => {
      let bgColor = 'bg-blue-50';
      let textColor = 'text-blue-800';
      let iconClass = 'fas fa-info-circle text-blue-500';
      
      if (alert.type === 'warning') {
        bgColor = 'bg-yellow-50';
        textColor = 'text-yellow-800';
        iconClass = 'fas fa-exclamation-circle text-yellow-500';
      } else if (alert.type === 'danger') {
        bgColor = 'bg-red-50';
        textColor = 'text-red-800';
        iconClass = 'fas fa-exclamation-triangle text-red-500';
      }
      
      alertsHTML += `
        <a href="${alert.link}" class="block ${bgColor} rounded-lg p-4 hover:shadow-md transition-shadow">
          <div class="flex items-start">
            <div class="flex-shrink-0 pt-0.5">
              <i class="${iconClass}"></i>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium ${textColor}">${alert.message}</p>
            </div>
          </div>
        </a>
      `;
    });
    
    alertsContainer.innerHTML = alertsHTML;
  }, 1000);
}

function fetchSystemActivities() {
  // Simulate loading system activities
  setTimeout(() => {
    const activitiesContainer = document.getElementById('system-activities');
    
    // Sample activities - in production, this would come from an API
    const activities = [
      { user: 'John Doe', action: 'added new product', item: 'HP Laptop 15-inch', time: '10 minutes ago' },
      { user: 'Jane Smith', action: 'updated inventory', item: 'Samsung Galaxy S21', time: '1 hour ago' },
      { user: 'Mike Johnson', action: 'completed sale', item: 'Invoice #1234', time: '2 hours ago' },
      { user: 'Sarah Williams', action: 'registered new account', item: '', time: 'Today at 9:30 AM' }
    ];
    
    if (activities.length === 0) {
      activitiesContainer.innerHTML = '<p class="text-gray-500 text-center py-4">No recent activities.</p>';
      return;
    }
    
    let activitiesHTML = '';
    activities.forEach(activity => {
      activitiesHTML += `
        <div class="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded-lg">
          <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
            <i class="fas fa-user-circle text-gray-500"></i>
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-sm text-gray-800">
              <span class="font-medium">${activity.user}</span> ${activity.action}
              ${activity.item ? `<span class="font-medium">${activity.item}</span>` : ''}
            </p>
            <p class="text-xs text-gray-500">${activity.time}</p>
          </div>
        </div>
      `;
    });
    
    activitiesContainer.innerHTML = activitiesHTML;
  }, 1200);
}

<?php else: ?>
// Staff functions
function fetchUserAlerts() {
  // Simulate loading staff alerts
  setTimeout(() => {
    const alertsContainer = document.getElementById('attention-content');
    
    // Sample alerts data for staff
    const alerts = [
      { type: 'warning', message: '3 products running low on stock', link: 'stock.php?status=low' },
      { type: 'info', message: '2 price updates pending review', link: 'products.php?status=pending' }
    ];
    
    if (alerts.length === 0) {
      alertsContainer.innerHTML = '<p class="text-gray-500 text-center py-4">No alerts at this time!</p>';
      return;
    }
    
    let alertsHTML = '';
    alerts.forEach(alert => {
      let bgColor = 'bg-blue-50';
      let textColor = 'text-blue-800';
      let iconClass = 'fas fa-info-circle text-blue-500';
      
      if (alert.type === 'warning') {
        bgColor = 'bg-yellow-50';
        textColor = 'text-yellow-800';
        iconClass = 'fas fa-exclamation-circle text-yellow-500';
      } else if (alert.type === 'danger') {
        bgColor = 'bg-red-50';
        textColor = 'text-red-800';
        iconClass = 'fas fa-exclamation-triangle text-red-500';
      }
      
      alertsHTML += `
        <a href="${alert.link}" class="block ${bgColor} rounded-lg p-4 hover:shadow-md transition-shadow">
          <div class="flex items-start">
            <div class="flex-shrink-0 pt-0.5">
              <i class="${iconClass}"></i>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium ${textColor}">${alert.message}</p>
            </div>
          </div>
        </a>
      `;
    });
    
    alertsContainer.innerHTML = alertsHTML;
  }, 1000);
}

function fetchUserActivities() {
  // Simulate loading staff activities
  setTimeout(() => {
    const activitiesContainer = document.getElementById('user-activities');
    
    // Sample activities for staff
    const activities = [
      { action: 'Completed sale', details: 'Invoice #1089', time: '30 minutes ago' },
      { action: 'Updated product quantity', details: 'Apple iPhone 13', time: '2 hours ago' },
      { action: 'Added new batch', details: 'Batch #4578', time: 'Today at 10:15 AM' }
    ];
    
    if (activities.length === 0) {
      activitiesContainer.innerHTML = '<p class="text-gray-500 text-center py-4">No recent activities.</p>';
      return;
    }
    
    let activitiesHTML = '';
    activities.forEach(activity => {
      activitiesHTML += `
        <div class="flex items-start p-3 hover:bg-gray-50 rounded-lg">
          <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
            <i class="fas fa-check text-blue-600"></i>
          </div>
          <div class="ml-3 min-w-0 flex-1">
            <p class="text-sm text-gray-800 font-medium">${activity.action}</p>
            <p class="text-sm text-gray-600">${activity.details}</p>
            <p class="text-xs text-gray-500 mt-1">${activity.time}</p>
          </div>
        </div>
      `;
    });
    
    activitiesContainer.innerHTML = activitiesHTML;
  }, 1200);
}

function fetchRecentSales() {
  // Simulate loading recent sales
  setTimeout(() => {
    const salesContainer = document.getElementById('recent-sales');
    
    // Sample sales data
    const sales = [
      { invoice: '#1092', customer: 'John Smith', amount: '$125.00', time: '45 minutes ago' },
      { invoice: '#1091', customer: 'Mary Johnson', amount: '$78.50', time: '1 hour ago' },
      { invoice: '#1090', customer: 'Robert Brown', amount: '$210.75', time: '3 hours ago' }
    ];
    
    if (sales.length === 0) {
      salesContainer.innerHTML = '<p class="text-gray-500 text-center py-4">No recent sales.</p>';
      return;
    }
    
    let salesHTML = '';
    sales.forEach(sale => {
      salesHTML += `
        <div class="flex justify-between items-center p-3 hover:bg-gray-50 rounded-lg">
          <div>
            <p class="text-sm font-medium text-gray-800">${sale.invoice}</p>
            <p class="text-xs text-gray-500">${sale.customer}</p>
            <p class="text-xs text-gray-500">${sale.time}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-800">${sale.amount}</p>
          </div>
        </div>
      `;
    });
    
    salesContainer.innerHTML = salesHTML;
  }, 1400);
}

function fetchTodaysSummary() {
  // Simulate loading today's summary for staff
  setTimeout(() => {
    document.getElementById('today-sales-count').textContent = '<?php echo rand(3, 15); ?>';
    document.getElementById('today-revenue').textContent = '$<?php echo number_format(rand(250, 1200), 2); ?>';
    document.getElementById('today-stock-updates').textContent = '<?php echo rand(1, 8); ?>';
  }, 800);
}
<?php endif; ?>
</script>

<?php
include 'footer.php';
?> 