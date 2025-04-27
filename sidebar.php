<?php
include 'authcheck.php'; // Adjust path if needed
requireLogin();

$currentPage = basename($_SERVER['PHP_SELF']);
$settingsPages = ['profile.php', 'password.php', 'preference.php', 'company.php'];
$isSettingsOpen = in_array($currentPage, $settingsPages) ? 'block' : 'hidden';
$role = getUserRole();
?>

<style>
  /* Header Styling */
  header {
    position: fixed !important; /* Ensure header is fixed */
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000; /* Ensure header is above sidebar */
  }

  /* Sidebar Styling */
  #sidebar {
    position: fixed;
    top: 64px; /* Adjust based on header height (64px is approximate for your header) */
    left: 0;
    width: 4rem; /* Collapsed width */
    height: calc(100vh - 64px); /* Adjust height to fit below header */
    transition: width 0.3s ease-in-out, transform 0.3s ease-in-out;
    z-index: 900; /* Below header but above main content */
  }

  /* Ensure sidebar is properly sized on mobile */
  @media (max-width: 1023px) {
    #sidebar {
      width: 16rem; /* Full width on mobile when visible */
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }
    
    .nav-text {
      opacity: 1 !important; /* Always show text on mobile */
    }
    
    #sidebar-title {
      opacity: 1 !important;
      max-width: 200px !important;
    }
    
    .submenu-arrow {
      opacity: 1 !important; 
    }
    
    /* For overlay when mobile sidebar is open */
    #sidebar-overlay {
      display: none;
      position: fixed;
      top: 64px;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 899;
    }
  }

  /* Desktop hover behavior */
  @media (min-width: 1024px) {
    #sidebar:hover {
      width: 16rem; /* Expanded width */
    }

    .nav-text {
      opacity: 0;
      white-space: nowrap;
      transition: opacity 0.2s ease-in-out;
    }

    #sidebar:hover .nav-text {
      opacity: 1;
    }

    #sidebar-title {
      opacity: 0;
      max-width: 0;
      overflow: hidden;
      transition: all 0.2s ease-in-out;
    }

    #sidebar:hover #sidebar-title {
      opacity: 1;
      max-width: 200px;
    }

    #sidebar .submenu-arrow {
      opacity: 0;
      transition: opacity 0.2s ease-in-out;
    }

    #sidebar:hover .submenu-arrow {
      opacity: 1;
    }
  }

  #settingsSubmenu {
    margin-left: 0.5rem;
  }
  
  @media (min-width: 1024px) {
    #sidebar:hover #settingsSubmenu {
      margin-left: 2rem;
    }
  }

  /* Main Content Styling */
  .main-content {
    margin-top: 64px; /* Ensure main content starts below the fixed header */
    transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out;
  }
  
  /* Desktop sizing for main content */
  @media (min-width: 1024px) {
    .main-content {
      margin-left: 4rem;
      width: calc(100% - 4rem);
    }
    
    #sidebar:hover ~ .main-content {
      margin-left: 16rem;
      width: calc(100% - 16rem);
    }
  }
  
  /* Mobile sizing for main content */
  @media (max-width: 1023px) {
    .main-content {
      margin-left: 0;
      width: 100%;
    }
  }
</style>

<!-- Mobile sidebar overlay -->
<div id="sidebar-overlay" onclick="toggleSidebar()"></div>

<aside id="sidebar"
  class="fixed bg-white border-r transform -translate-x-full lg:translate-x-0 transition-all duration-200 ease-in-out shadow-md p-4 overflow-x-hidden">

  <div class="flex items-center justify-end mb-6">
    <button class="text-gray-400 hover:text-gray-600 lg:hidden" onclick="toggleSidebar()">
      <i class="fa fa-times text-lg"></i>
    </button>
  </div>

  <nav class="flex flex-col gap-2 text-sm text-gray-600">

    <?php if ($role === 'admin'): ?>
      <a href="dashboard.php" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-100 <?= $currentPage == 'dashboard.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
        <i class="fa fa-home w-4"></i> <span class="nav-text">Dashboard</span>
      </a>
    <?php endif; ?>

    <?php if ($role === 'staff'): ?>
      <a href="home.php" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-100 <?= $currentPage == 'home.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
        <i class="fa fa-home w-4"></i> <span class="nav-text">Home</span>
      </a>
    <?php endif; ?>

    <?php if (in_array($role, ['admin', 'staff'])): ?>
      <a href="sales.php" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-100 <?= $currentPage == 'sales.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
        <i class="fa fa-shopping-cart w-4"></i> <span class="nav-text">Sales</span>
      </a>
      <a href="products.php" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-100 <?= $currentPage == 'products.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
        <i class="fa fa-cube w-4"></i> <span class="nav-text">Products</span>
      </a>
      <a href="stock.php" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-100 <?= $currentPage == 'stock.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
        <i class="fa fa-boxes w-4"></i> <span class="nav-text">Stock</span>
      </a>
      <a href="batches.php" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-100 <?= $currentPage == 'batches.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
        <i class="fa fa-layer-group w-4"></i> <span class="nav-text">Batch</span>
      </a>
    <?php endif; ?>

    <?php if ($role === 'admin'): ?>
      <a href="reports.php" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-100 <?= $currentPage == 'reports.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
        <i class="fa fa-chart-bar w-4"></i> <span class="nav-text">Reports</span>
      </a>
      <a href="users.php" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-100 <?= $currentPage == 'users.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
        <i class="fa fa-user-friends w-4"></i> <span class="nav-text">Users</span>
      </a>
    <?php endif; ?>
    
    <?php if (in_array($role, ['admin', 'staff'])): ?>
      <div>
        <button onclick="toggleSettingsMenu()" 
                class="flex items-center justify-between w-full px-3 py-3 rounded-lg hover:bg-gray-100 <?= in_array($currentPage, $settingsPages) ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
          <div class="flex items-center gap-3">
            <i class="fa fa-cog w-4"></i> <span class="nav-text">Setting</span>
          </div>
          <i class="fa fa-chevron-down text-xs submenu-arrow"></i>
        </button>
        <div id="settingsSubmenu" class="mt-1 text-sm flex flex-col gap-1 <?= $isSettingsOpen ?>">
          <a href="profile.php" class="px-3 py-2 rounded hover:bg-gray-100 <?= $currentPage == 'profile.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
            <span class="nav-text">Profile</span>
          </a>
          <a href="password.php" class="px-3 py-2 rounded hover:bg-gray-100 <?= $currentPage == 'password.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
            <span class="nav-text">Password</span>
          </a>
          <a href="preference.php" class="px-3 py-2 rounded hover:bg-gray-100 <?= $currentPage == 'preference.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
            <span class="nav-text">Preferences</span>
          </a>
          <a href="company.php" class="px-3 py-2 rounded hover:bg-gray-100 <?= $currentPage == 'company.php' ? 'bg-gray-100 text-gray-900 font-medium' : '' ?>">
            <span class="nav-text">Company</span>
          </a>
        </div>
      </div>
    <?php endif; ?>

  </nav>
</aside>

<script>
  // Add main-content class to body content
  document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const mainContentElements = document.querySelectorAll('body > *:not(#sidebar):not(#sidebar-overlay):not(script)');
    
    // Add a div wrapper for all content that isn't the sidebar
    const mainContentWrapper = document.createElement('div');
    mainContentWrapper.className = 'main-content';
    
    // Get the parent node (body)
    const body = document.body;
    
    // Insert the wrapper right after the sidebar
    if (sidebar.nextSibling) {
      body.insertBefore(mainContentWrapper, sidebar.nextSibling);
    } else {
      body.appendChild(mainContentWrapper);
    }
    
    // Move all other content into this wrapper
    mainContentElements.forEach(element => {
      if (element !== sidebar && element !== mainContentWrapper && element !== sidebarOverlay) {
        mainContentWrapper.appendChild(element);
      }
    });
    
    // Connect sidebar toggle in header to our function
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
      sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    // Handle touch events for mobile
    if ('ontouchstart' in window) {
      // Add touch-friendly triggers
      addTouchInteractions();
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
      if (window.innerWidth < 1024) {
        const isClickInsideSidebar = sidebar.contains(e.target);
        const isClickSidebarToggle = sidebarToggle && sidebarToggle.contains(e.target);
        const sidebarIsOpen = !sidebar.classList.contains('-translate-x-full');
        
        if (!isClickInsideSidebar && !isClickSidebarToggle && sidebarIsOpen) {
          toggleSidebar();
        }
      }
    });
  });
  
  // Toggle sidebar visibility
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    
    sidebar.classList.toggle('-translate-x-full');
    
    // Toggle overlay visibility
    if (window.innerWidth < 1024) {
      if (sidebar.classList.contains('-translate-x-full')) {
        sidebarOverlay.style.display = 'none';
      } else {
        sidebarOverlay.style.display = 'block';
      }
    }
  }
  
  // Settings submenu toggle
  function toggleSettingsMenu() {
    document.getElementById('settingsSubmenu').classList.toggle('hidden');
  }
  
  // Add touch interactions for mobile
  function addTouchInteractions() {
    // For swipe gestures if needed in the future
    let touchStartX = 0;
    let touchEndX = 0;
    
    document.addEventListener('touchstart', e => {
      touchStartX = e.changedTouches[0].screenX;
    }, false);
    
    document.addEventListener('touchend', e => {
      touchEndX = e.changedTouches[0].screenX;
      handleSwipe();
    }, false);
    
    function handleSwipe() {
      const sidebar = document.getElementById('sidebar');
      const sidebarIsOpen = !sidebar.classList.contains('-translate-x-full');
      
      if (touchEndX - touchStartX > 100 && !sidebarIsOpen) {
        // Swipe right, open sidebar
        toggleSidebar();
      } else if (touchStartX - touchEndX > 100 && sidebarIsOpen) {
        // Swipe left, close sidebar
        toggleSidebar();
      }
    }
  }
</script>