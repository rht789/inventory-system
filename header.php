<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle logout logic directly in header
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Grab user session data
$userId = $_SESSION['user_id'] ?? null;
$username = $_SESSION['user_username'] ?? '';
$email = $_SESSION['user_email'] ?? '';
$role = $_SESSION['user_role'] ?? '';
$profilePicture = $_SESSION['user_profile_picture'] ?? '';

// Validate profile picture and set default if needed
$uploadDir = 'uploads/profile/';
$hasProfilePicture = false;

if ($profilePicture && file_exists($uploadDir . $profilePicture)) {
    $profileImageSrc = $uploadDir . $profilePicture;
    $hasProfilePicture = true;
} else {
    // Use a placeholder image from the web
    $profileImageSrc = "https://ui-avatars.com/api/?name=" . urlencode($username) . "&background=random&color=fff&size=128";
}
?>

<!-- Tailwind and Font Awesome -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<!-- Custom styles for the header -->
<style>
  .notification-indicator {
    position: absolute;
    top: -2px;
    right: -2px;
    height: 8px;
    width: 8px;
    border-radius: 50%;
    background-color: #ef4444;
  }

  .dropdown-animation {
    transition: all 0.2s ease-in-out;
    transform-origin: top right;
  }

  .header-light {
    background: white;
    border-bottom: 1px solid #e5e7eb;
  }

  /* Ensure header is fixed (reinforce the rule) */
  header {
    position: fixed !important;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
  }
</style>

<header class="sticky top-0 z-50 header-light shadow-sm px-6 py-3 flex justify-between items-center">
  <!-- Sidebar toggle (mobile) -->
  <div class="flex items-center gap-4">
    <button id="sidebarToggle" class="lg:hidden text-gray-600 hover:text-gray-900 transition">
    <i class="fa fa-bars text-xl"></i>
  </button>

    <!-- Logo and name -->
    <div class="flex items-center gap-3">
      <i class="fas fa-box-open text-2xl text-blue-600"></i>
      <div>
  <h1 class="text-xl font-bold text-gray-800">SmartInventory</h1>
        <span class="text-xs text-gray-500 hidden sm:inline-block">Inventory Management System</span>
      </div>
    </div>
  </div>

  <!-- Right side header elements -->
  <div class="flex items-center gap-5 relative">
    <!-- Quick actions dropdown -->
    <div class="relative hidden md:block">
      <button id="quickActionsToggle" class="flex items-center gap-2 text-gray-700 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-md transition text-sm focus:outline-none">
        <i class="fas fa-bolt"></i>
        <span>Quick Actions</span>
        <i class="fas fa-chevron-down text-xs"></i>
      </button>
      <div id="quickActionsDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white shadow-xl rounded-lg overflow-hidden z-50 dropdown-animation">
        <div class="p-2 bg-gray-50 border-b border-gray-200">
          <h3 class="text-sm font-semibold text-gray-700">Quick Actions</h3>
        </div>
        <div class="p-1">
          <a href="inventory.php?action=add" class="flex items-center gap-2 p-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">
            <i class="fas fa-plus-circle text-green-500"></i> Add New Product
          </a>
          <a href="sales.php?action=new" class="flex items-center gap-2 p-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">
            <i class="fas fa-shopping-cart text-blue-500"></i> Create New Sale
          </a>
          <a href="reports.php" class="flex items-center gap-2 p-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">
            <i class="fas fa-chart-line text-purple-500"></i> View Reports
          </a>
          <a href="stock.php?action=add" class="flex items-center gap-2 p-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">
            <i class="fas fa-cubes text-amber-500"></i> Stock Adjustment
          </a>
        </div>
      </div>
    </div>

    <!-- Notifications -->
    <div class="relative">
      <button id="notificationToggle" class="text-gray-600 hover:text-gray-900 transition relative">
      <i class="fa fa-bell text-lg"></i>
        <span class="notification-indicator"></span>
      </button>
      <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white shadow-xl rounded-lg overflow-hidden z-50 dropdown-animation">
        <div class="p-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
          <h3 class="text-sm font-semibold text-gray-700">Notifications</h3>
          <span class="text-xs text-blue-500 cursor-pointer hover:underline">Mark all as read</span>
        </div>
        <div class="max-h-96 overflow-y-auto">
          <div class="p-3 border-b border-gray-100 hover:bg-gray-50">
            <div class="flex gap-3">
              <div class="bg-amber-100 text-amber-500 rounded-md w-10 h-10 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle"></i>
              </div>
              <div>
                <p class="text-sm text-gray-800 font-medium">Low inventory alert</p>
                <p class="text-xs text-gray-500 mt-1">Product "Wireless Mouse" is below minimum stock level</p>
                <p class="text-xs text-gray-400 mt-2">2 hours ago</p>
              </div>
            </div>
          </div>
          <div class="p-3 border-b border-gray-100 hover:bg-gray-50">
            <div class="flex gap-3">
              <div class="bg-green-100 text-green-500 rounded-md w-10 h-10 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-check-circle"></i>
              </div>
              <div>
                <p class="text-sm text-gray-800 font-medium">Sale completed</p>
                <p class="text-xs text-gray-500 mt-1">Sale #1082 has been completed successfully</p>
                <p class="text-xs text-gray-400 mt-2">4 hours ago</p>
              </div>
            </div>
          </div>
          <div class="p-3 border-b border-gray-100 hover:bg-gray-50">
            <div class="flex gap-3">
              <div class="bg-blue-100 text-blue-500 rounded-md w-10 h-10 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-truck"></i>
              </div>
              <div>
                <p class="text-sm text-gray-800 font-medium">New stock arrived</p>
                <p class="text-xs text-gray-500 mt-1">20 units of "Laptop Charger" have been added to inventory</p>
                <p class="text-xs text-gray-400 mt-2">Yesterday</p>
              </div>
            </div>
          </div>
        </div>
        <a href="notifications.php" class="block p-2 text-center text-sm text-blue-600 bg-gray-50 hover:bg-gray-100">
          View all notifications
        </a>
      </div>
    </div>

    <!-- Help Button -->
    <button id="helpButton" class="text-gray-600 hover:text-gray-900 transition hidden md:block">
      <i class="fa fa-question-circle text-lg"></i>
    </button>

    <?php if ($userId): ?>
    <!-- Profile dropdown -->
    <div class="relative">
      <!-- Profile trigger -->
      <button id="profileToggle" class="flex items-center gap-2 focus:outline-none group">
        <div class="flex flex-col items-end">
          <span class="text-sm font-medium text-gray-700 hidden md:block"><?= htmlspecialchars($username) ?></span>
          <span class="text-xs text-gray-500 capitalize hidden md:block"><?= htmlspecialchars($role) ?></span>
        </div>
        <img src="<?= $profileImageSrc ?>" alt="Profile Picture"
             class="w-9 h-9 rounded-full border-2 border-gray-200 group-hover:border-blue-400 transition duration-300 shadow-sm object-cover" />
      </button>

      <!-- Dropdown -->
      <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white border shadow-xl rounded-lg overflow-hidden z-50 dropdown-animation">
        <div class="p-4 bg-gray-50 border-b border-gray-200">
          <div class="flex items-center gap-3">
            <img src="<?= $profileImageSrc ?>" alt="Profile"
                 class="w-12 h-12 rounded-full border border-gray-300 shadow-sm object-cover" />
            <div>
              <div class="text-gray-800 font-medium"><?= htmlspecialchars($username) ?></div>
              <div class="text-gray-500 text-xs capitalize"><?= htmlspecialchars($role) ?></div>
              <div class="text-gray-500 text-xs truncate mt-1"><?= htmlspecialchars($email) ?></div>
            </div>
          </div>
        </div>
        
        <div class="p-2">
          <a href="profile.php" class="flex items-center gap-2 p-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">
            <i class="fas fa-user-circle text-gray-500 w-5"></i> My Profile
          </a>
          <a href="settings.php" class="flex items-center gap-2 p-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">
            <i class="fas fa-cog text-gray-500 w-5"></i> Settings
          </a>
          <a href="activity.php" class="flex items-center gap-2 p-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">
            <i class="fas fa-history text-gray-500 w-5"></i> Activity Log
          </a>
          
          <div class="border-t border-gray-200 my-1"></div>

        <!-- Logout form -->
          <form method="POST" class="p-2">
            <button type="submit" name="logout" class="flex w-full items-center gap-2 text-sm text-red-600 hover:bg-red-50 rounded-md p-2">
              <i class="fas fa-sign-out-alt w-5"></i> Logout
          </button>
        </form>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</header>

<!-- JS for dropdowns -->
<script>
  // Profile dropdown
  const profileToggle = document.getElementById('profileToggle');
  const profileDropdown = document.getElementById('profileDropdown');

  profileToggle?.addEventListener('click', () => {
    profileDropdown.classList.toggle('hidden');
  });

  // Notification dropdown
  const notificationToggle = document.getElementById('notificationToggle');
  const notificationDropdown = document.getElementById('notificationDropdown');

  notificationToggle?.addEventListener('click', () => {
    notificationDropdown.classList.toggle('hidden');
  });

  // Quick actions dropdown
  const quickActionsToggle = document.getElementById('quickActionsToggle');
  const quickActionsDropdown = document.getElementById('quickActionsDropdown');

  quickActionsToggle?.addEventListener('click', () => {
    quickActionsDropdown.classList.toggle('hidden');
  });

  // Close all dropdowns when clicking outside
  document.addEventListener('click', function(e) {
    // Profile dropdown
    if (profileToggle && profileDropdown && 
        !profileToggle.contains(e.target) && 
        !profileDropdown.contains(e.target)) {
      profileDropdown.classList.add('hidden');
    }
    
    // Notification dropdown
    if (notificationToggle && notificationDropdown && 
        !notificationToggle.contains(e.target) && 
        !notificationDropdown.contains(e.target)) {
      notificationDropdown.classList.add('hidden');
    }
    
    // Quick actions dropdown
    if (quickActionsToggle && quickActionsDropdown && 
        !quickActionsToggle.contains(e.target) && 
        !quickActionsDropdown.contains(e.target)) {
      quickActionsDropdown.classList.add('hidden');
    }
  });
</script>

<!-- Help Modal -->
<div id="helpModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
  <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[80vh] flex flex-col">
    <!-- Modal Header -->
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
      <h3 class="text-lg font-semibold text-gray-800">Help & Documentation</h3>
      <button id="closeHelpModal" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <!-- Modal Body with Scrollable Content -->
    <div class="px-6 py-4 overflow-y-auto">
      <div class="space-y-6">
        <!-- Quick Overview Section -->
        <div>
          <h4 class="text-md font-medium text-gray-800 mb-2">Quick Overview</h4>
          <p class="text-sm text-gray-600">
            Welcome to SmartInventory! This inventory management system helps you track products, 
            manage sales, and generate reports. Use the sidebar to navigate between different sections.
          </p>
        </div>
        
        <!-- Common Tasks Section -->
        <div>
          <h4 class="text-md font-medium text-gray-800 mb-2">Common Tasks</h4>
          <ul class="text-sm text-gray-600 space-y-2">
            <li class="flex items-start gap-2">
              <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
              <span><b>Add a Product:</b> Navigate to Products and click "Add New Product"</span>
            </li>
            <li class="flex items-start gap-2">
              <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
              <span><b>Create a Sale:</b> Visit the Sales page and click "New Sale"</span>
            </li>
            <li class="flex items-start gap-2">
              <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
              <span><b>Adjust Stock:</b> Go to Stock page and use "Add Stock" or "Remove Stock"</span>
            </li>
            <li class="flex items-start gap-2">
              <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
              <span><b>Generate Reports:</b> Visit the Reports page, select report type and date range</span>
            </li>
          </ul>
        </div>
        
        <!-- Keyboard Shortcuts -->
        <div>
          <h4 class="text-md font-medium text-gray-800 mb-2">Keyboard Shortcuts</h4>
          <div class="grid grid-cols-2 gap-2 text-sm">
            <div class="flex items-center gap-2">
              <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl+H</kbd>
              <span>Return to Dashboard</span>
            </div>
            <div class="flex items-center gap-2">
              <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl+P</kbd>
              <span>Products Page</span>
            </div>
            <div class="flex items-center gap-2">
              <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl+S</kbd>
              <span>Sales Page</span>
            </div>
            <div class="flex items-center gap-2">
              <kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl+R</kbd>
              <span>Reports Page</span>
            </div>
          </div>
        </div>
        
        <!-- Need More Help Section -->
        <div class="bg-blue-50 p-4 rounded-lg">
          <h4 class="text-md font-medium text-blue-800 mb-2">Need More Help?</h4>
          <p class="text-sm text-blue-600 mb-2">
            If you need further assistance, please contact the system administrator.
          </p>
          <a href="mailto:support@smartinventory.com" class="text-sm text-blue-700 hover:underline flex items-center gap-1">
            <i class="fas fa-envelope"></i> support@smartinventory.com
          </a>
        </div>
      </div>
    </div>
    
    <!-- Modal Footer -->
    <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 rounded-b-lg">
      <div class="flex justify-end">
        <button id="closeHelpButton" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm transition">
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    $('.sidebarToggle').on('click', function() {
      $('#sidebar').toggleClass('translate-x-0 -translate-x-full');
      $('#sidebarOverlay').toggleClass('hidden');
    });
    
    $('#sidebarOverlay').on('click', function() {
      $('#sidebar').removeClass('translate-x-0').addClass('-translate-x-full');
      $(this).addClass('hidden');
    });
  });
  
  // Help Modal Functionality
  $(document).ready(function() {
    // Open modal when help button is clicked
    $('#helpButton').on('click', function() {
      $('#helpModal').removeClass('hidden');
      $('body').addClass('overflow-hidden'); // Prevent scrolling of background
    });
    
    // Close modal when close buttons are clicked
    $('#closeHelpModal, #closeHelpButton').on('click', function() {
      $('#helpModal').addClass('hidden');
      $('body').removeClass('overflow-hidden');
    });
    
    // Close modal when clicking outside of it
    $('#helpModal').on('click', function(e) {
      if ($(e.target).is('#helpModal')) {
        $('#helpModal').addClass('hidden');
        $('body').removeClass('overflow-hidden');
      }
    });
    
    // Close modal when Escape key is pressed
    $(document).on('keydown', function(e) {
      if (e.key === 'Escape' && !$('#helpModal').hasClass('hidden')) {
        $('#helpModal').addClass('hidden');
        $('body').removeClass('overflow-hidden');
      }
    });
  });

  // Vanilla JavaScript fallback for the help button
  document.addEventListener('DOMContentLoaded', function() {
    const helpButton = document.getElementById('helpButton');
    const helpModal = document.getElementById('helpModal');
    const closeHelpModal = document.getElementById('closeHelpModal');
    const closeHelpButton = document.getElementById('closeHelpButton');
    
    if (helpButton) {
      helpButton.addEventListener('click', function() {
        helpModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
      });
    }
    
    if (closeHelpModal) {
      closeHelpModal.addEventListener('click', function() {
        helpModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
      });
    }
    
    if (closeHelpButton) {
      closeHelpButton.addEventListener('click', function() {
        helpModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
      });
    }
    
    // Close when clicking outside
    if (helpModal) {
      helpModal.addEventListener('click', function(e) {
        if (e.target === helpModal) {
          helpModal.classList.add('hidden');
          document.body.classList.remove('overflow-hidden');
        }
      });
    }
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && helpModal && !helpModal.classList.contains('hidden')) {
        helpModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
      }
    });
  });

  // Keyboard shortcuts
  document.addEventListener('keydown', function(e) {
    // Check if Ctrl key is pressed
    if (e.ctrlKey) {
      switch (e.key.toLowerCase()) {
        case 'h': // Ctrl+H - Dashboard
          e.preventDefault();
          window.location.href = 'dashboard.php';
          break;
        case 'p': // Ctrl+P - Products
          e.preventDefault();
          window.location.href = 'products.php';
          break;
        case 's': // Ctrl+S - Sales
          e.preventDefault();
          window.location.href = 'sales.php';
          break;
        case 'r': // Ctrl+R - Reports
          e.preventDefault();
          window.location.href = 'reports.php';
          break;
      }
    }
  });
</script>
