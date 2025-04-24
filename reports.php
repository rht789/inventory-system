<?php
include 'authcheck.php'; // Adjust path as needed
requireLogin();           // Ensures the user is logged in
requireRole('admin');
?>

<?php
include 'header.php';
include 'sidebar.php';
?>

<main class="lg:ml-64 min-h-screen p-6 bg-gray-50">
  <!-- Toast container -->
  <div id="toast"
       class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg hidden z-50">
  </div>

  <!-- Reports Header -->
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Reports</h2>
    <button id="allDownloadBtn" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-md text-sm flex items-center gap-2">
      <i class="fas fa-download"></i> All Download
    </button>
  </div>

  <!-- Filters Card -->
  <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold mb-4">Reports Filters</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Report Type -->
      <div>
        <label for="reportType" class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
        <select id="reportType" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
          <option value="sales">Sales Report</option>
          <option value="product_sales">Product Sales Report</option>
          <option value="stock_movement">Stock Movement Report</option>
          <option value="user_sales">User Sales Report</option>
        </select>
      </div>
      
      <!-- Time Range -->
      <div>
        <label for="timeRange" class="block text-sm font-medium text-gray-700 mb-1">Time Range</label>
        <select id="timeRange" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
          <option value="all_time">All Time</option>
          <option value="today">Today</option>
          <option value="this_week">This Week</option>
          <option value="this_month">This Month</option>
          <option value="last_month">Last Month</option>
          <option value="custom">Custom Range</option>
        </select>
      </div>
      
      <!-- Custom Date Range (hidden by default) -->
      <div id="customDateRange" class="hidden md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="customStartDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
          <input type="date" id="customStartDate" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
        </div>
        <div>
          <label for="customEndDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input type="date" id="customEndDate" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
        </div>
      </div>
      
      <!-- Download Type -->
      <div class="self-end">
        <div class="relative inline-block text-left">
          <button id="downloadTypeBtn" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
            Download Type
            <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
          <div id="downloadTypeDropdown" class="hidden origin-top-right absolute right-0 mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10">
            <div class="py-1" role="menu" aria-orientation="vertical">
              <button id="downloadCsvBtn" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" role="menuitem">
                <i class="fas fa-file-csv mr-2"></i> CSV
              </button>
              <button id="downloadPdfBtn" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" role="menuitem">
                <i class="fas fa-file-pdf mr-2"></i> PDF
              </button>
              <button id="downloadExcelBtn" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" role="menuitem">
                <i class="fas fa-file-excel mr-2"></i> Excel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Dynamic Sales Report Filters -->
    <div class="mt-6 pt-4 border-t border-gray-200">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Customer Filter (Always visible for Sales Report) -->
        <div>
          <label for="customerFilter" class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
          <select id="customerFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Customers</option>
            <!-- Will be populated dynamically -->
          </select>
        </div>
        
        <!-- Status Filter (Always visible for Sales Report) -->
        <div>
          <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select id="statusFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="delivered">Delivered</option>
            <option value="canceled">Canceled</option>
          </select>
        </div>
      </div>

      <!-- Hidden dynamic filters (these should still be in the document for dynamic toggling) -->
      <div id="salesFilters" class="dynamic-filter hidden"></div>
      <div id="productSalesFilters" class="dynamic-filter hidden grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="categoryFilter" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select id="categoryFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Categories</option>
            <!-- Will be populated dynamically -->
          </select>
        </div>
        <div>
          <label for="productFilter" class="block text-sm font-medium text-gray-700 mb-1">Product</label>
          <select id="productFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Products</option>
            <!-- Will be populated dynamically -->
          </select>
        </div>
      </div>

      <div id="stockMovementFilters" class="dynamic-filter hidden grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="stockProductFilter" class="block text-sm font-medium text-gray-700 mb-1">Product</label>
          <select id="stockProductFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Products</option>
            <!-- Will be populated dynamically -->
          </select>
        </div>
        <div>
          <label for="userFilter" class="block text-sm font-medium text-gray-700 mb-1">User</label>
          <select id="userFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Users</option>
            <!-- Will be populated dynamically -->
          </select>
        </div>
        <div>
          <label for="movementTypeFilter" class="block text-sm font-medium text-gray-700 mb-1">Movement Type</label>
          <select id="movementTypeFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Types</option>
            <option value="in">Stock In</option>
            <option value="out">Stock Out</option>
            <option value="adjustment">Adjustment</option>
          </select>
        </div>
      </div>

      <div id="batchFilters" class="dynamic-filter hidden grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="userSalesFilter" class="block text-sm font-medium text-gray-700 mb-1">User</label>
          <select id="userSalesFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Users</option>
            <!-- Will be populated dynamically -->
          </select>
        </div>
        <div>
          <label for="userSalesStatusFilter" class="block text-sm font-medium text-gray-700 mb-1">Order Status</label>
          <select id="userSalesStatusFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="delivered">Delivered</option>
            <option value="canceled">Canceled</option>
          </select>
        </div>
      </div>
      
      <!-- Generate Report Button - Placed at the very bottom of all filters -->
      <div class="mt-6 flex justify-center">
        <button id="generateReportBtn" class="bg-gray-800 hover:bg-gray-900 text-white px-6 py-3 rounded-md text-sm font-medium w-full max-w-md">
          Generate Report
        </button>
      </div>
    </div>
  </div>

  <!-- Report Summary Cards -->
  <div id="reportSummary" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 hidden">
    <!-- Cards will be added dynamically based on report type -->
  </div>
  
  <!-- Report Content -->
  <div id="reportContent" class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <!-- Report will be rendered here -->
    <div class="text-center py-12 text-gray-500">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="text-lg font-medium">No Report Generated</h3>
      <p class="mt-1">Select a report type and click "Generate Report" to view data.</p>
    </div>
  </div>

  <!-- Loading Indicator -->
  <div id="loadingIndicator" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-5 rounded-lg shadow-lg flex flex-col items-center">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-gray-900 mb-3"></div>
      <p class="text-gray-700 font-medium">Generating Report...</p>
    </div>
  </div>
</main>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Include jsPDF for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
<!-- Include SheetJS for Excel export -->
<script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>

<script>
// Global variables
let reportChart;
let reportData = null;
let currentReportType = '';

// DOM references
const
  toast = document.getElementById('toast'),
  reportTypeSelect = document.getElementById('reportType'),
  timeRangeSelect = document.getElementById('timeRange'),
  customDateRange = document.getElementById('customDateRange'),
  customStartDate = document.getElementById('customStartDate'),
  customEndDate = document.getElementById('customEndDate'),
  generateReportBtn = document.getElementById('generateReportBtn'),
  downloadTypeBtn = document.getElementById('downloadTypeBtn'),
  downloadTypeDropdown = document.getElementById('downloadTypeDropdown'),
  downloadCsvBtn = document.getElementById('downloadCsvBtn'),
  downloadPdfBtn = document.getElementById('downloadPdfBtn'),
  downloadExcelBtn = document.getElementById('downloadExcelBtn'),
  allDownloadBtn = document.getElementById('allDownloadBtn'),
  loadingIndicator = document.getElementById('loadingIndicator'),
  reportSummary = document.getElementById('reportSummary'),
  reportContent = document.getElementById('reportContent');

// Initialize page elements after DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
  // Initialize event listeners for the report page
  initReportPage();
  
  // Populate dynamic dropdowns with data
  loadCustomers();
  loadCategories();
  loadProducts('');
  loadUsers();
});

// Initialize report page functionality
function initReportPage() {
  // Report type change handler
  document.getElementById('reportType').addEventListener('change', updateDynamicFilters);
  
  // Time range change handler
  document.getElementById('timeRange').addEventListener('change', function() {
    const customDateRange = document.getElementById('customDateRange');
    if (this.value === 'custom') {
      customDateRange.classList.remove('hidden');
    } else {
      customDateRange.classList.add('hidden');
    }
  });
  
  // Generate report button click handler
  document.getElementById('generateReportBtn').addEventListener('click', function() {
    generateReport();
  });
  
  // Download dropdown toggle
  document.getElementById('downloadTypeBtn').addEventListener('click', function() {
    const dropdown = document.getElementById('downloadTypeDropdown');
    dropdown.classList.toggle('hidden');
  });
  
  // Close dropdown when clicking outside
  document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('downloadTypeDropdown');
    const button = document.getElementById('downloadTypeBtn');
    if (!dropdown.contains(event.target) && !button.contains(event.target) && !dropdown.classList.contains('hidden')) {
      dropdown.classList.add('hidden');
    }
  });
  
  // Download buttons click handlers
  document.getElementById('downloadCsvBtn').addEventListener('click', function() {
    downloadReport('csv');
    document.getElementById('downloadTypeDropdown').classList.add('hidden');
  });
  
  document.getElementById('downloadPdfBtn').addEventListener('click', function() {
    downloadReport('pdf');
    document.getElementById('downloadTypeDropdown').classList.add('hidden');
  });
  
  document.getElementById('downloadExcelBtn').addEventListener('click', function() {
    downloadReport('excel');
    document.getElementById('downloadTypeDropdown').classList.add('hidden');
  });
  
  // Category change should update products dropdown
  document.getElementById('categoryFilter').addEventListener('change', function() {
    loadProducts(this.value);
  });
  
  // Initialize by showing the correct filters
  updateDynamicFilters();
}

// Show/hide filter sections based on report type
function updateDynamicFilters() {
  const reportType = document.getElementById('reportType').value;
  
  // Hide all dynamic filter sections first
  const dynamicFilters = document.querySelectorAll('.dynamic-filter');
  dynamicFilters.forEach(filter => {
    filter.classList.add('hidden');
  });
  
  // Show relevant filters based on report type
  const customerStatusFilters = document.querySelectorAll('#customerFilter, #statusFilter');
  
  if (reportType === 'sales') {
    // For sales report - customer and status filters are already visible
    customerStatusFilters.forEach(filter => {
      filter.closest('.grid').classList.remove('hidden');
    });
  } else if (reportType === 'product_sales') {
    // For product sales report
    document.getElementById('productSalesFilters').classList.remove('hidden');
    // Hide customer/status filters
    customerStatusFilters.forEach(filter => {
      filter.closest('.grid').classList.add('hidden');
    });
  } else if (reportType === 'stock_movement') {
    // For stock movement report
    document.getElementById('stockMovementFilters').classList.remove('hidden');
    // Hide customer/status filters
    customerStatusFilters.forEach(filter => {
      filter.closest('.grid').classList.add('hidden');
    });
  } else if (reportType === 'user_sales') {
    // For user sales report
    document.getElementById('batchFilters').classList.remove('hidden');
    // Hide customer/status filters
    customerStatusFilters.forEach(filter => {
      filter.closest('.grid').classList.add('hidden');
    });
    
    // Make sure userSalesFilter is populated with users
    const userSalesFilter = document.getElementById('userSalesFilter');
    if (userSalesFilter && userSalesFilter.options.length <= 1) {
      loadUsers();
    }
  }
}

// Load customers for the dropdown
function loadCustomers() {
  fetch('api/customers.php')
    .then(response => {
      if (!response.ok) {
        throw new Error('Failed to load customers');
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        const customerSelect = document.getElementById('customerFilter');
        // Clear existing options except the first one
        customerSelect.innerHTML = '<option value="">All Customers</option>';
        
        // Add customer options
        data.customers.forEach(customer => {
          const option = document.createElement('option');
          option.value = customer.id;
          option.textContent = customer.name;
          customerSelect.appendChild(option);
        });
      }
    })
    .catch(error => {
      console.error('Error loading customers:', error);
      showToast('Failed to load customers data', 'error');
    });
}

// Load categories for the dropdown
function loadCategories() {
  fetch('api/categories.php')
    .then(response => {
      if (!response.ok) {
        throw new Error('Failed to load categories');
      }
      return response.json();
    })
    .then(data => {
      const categorySelect = document.getElementById('categoryFilter');
      // Clear existing options except the first one
      categorySelect.innerHTML = '<option value="">All Categories</option>';
      
      // Check if data is an array (direct array response) or has a success property with categories array
      const categories = Array.isArray(data) ? data : (data.success && data.categories ? data.categories : []);
      
      // Add category options
      categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category.id;
        option.textContent = category.name;
        categorySelect.appendChild(option);
      });
    })
    .catch(error => {
      console.error('Error loading categories:', error);
      showToast('Failed to load categories data', 'error');
    });
}

// Load products for the dropdown, filtered by category if provided
function loadProducts(categoryId) {
  let url = 'api/products.php';
  if (categoryId) {
    url += `?category_id=${categoryId}`;
  }
  
  fetch(url)
    .then(response => {
      if (!response.ok) {
        throw new Error('Failed to load products');
      }
      return response.json();
    })
    .then(data => {
      // Update all product dropdowns
      const productSelects = [
        document.getElementById('productFilter'),
        document.getElementById('stockProductFilter')
      ];
      
      // Check if data is an array (direct array response) or has a success property with products array
      const products = Array.isArray(data) ? data : (data.success && data.products ? data.products : []);
      
      productSelects.forEach(select => {
        if (select) {
          // Clear existing options except the first one
          select.innerHTML = '<option value="">All Products</option>';
          
          // Add product options
          products.forEach(product => {
            const option = document.createElement('option');
            option.value = product.id;
            option.textContent = product.name;
            select.appendChild(option);
          });
        }
      });
    })
    .catch(error => {
      console.error('Error loading products:', error);
      showToast('Failed to load products data', 'error');
    });
}

// Load users for the dropdown
function loadUsers() {
  fetch('api/users.php')
    .then(response => {
      if (!response.ok) {
        throw new Error('Failed to load users');
      }
      return response.json();
    })
    .then(data => {
      // Update all user dropdowns
      const userSelects = [
        document.getElementById('userFilter'),
        document.getElementById('userSalesFilter')
      ];
      
      // Check the structure of the data
      // The users API returns { admin: [...], staff: [...] }
      let users = [];
      
      if (data.success && data.users) {
        // If API returns success with users array
        users = data.users;
      } else if (data.admin || data.staff) {
        // API returns grouped users by role
        if (Array.isArray(data.admin)) {
          users = users.concat(data.admin);
        }
        if (Array.isArray(data.staff)) {
          users = users.concat(data.staff);
        }
      } else if (Array.isArray(data)) {
        // Direct array of users
        users = data;
      }
      
      // Add user options to each select dropdown
      userSelects.forEach(select => {
        if (select) {
          // Clear existing options except the first one
          select.innerHTML = '<option value="">All Users</option>';
          
          // Add user options
          users.forEach(user => {
            const option = document.createElement('option');
            option.value = user.id;
            option.textContent = `${user.username} (${user.role || 'User'})`;
            select.appendChild(option);
          });
        }
      });
    })
    .catch(error => {
      console.error('Error loading users:', error);
      showToast('Failed to load users data', 'error');
    });
}

// Show toast notification
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.classList.remove('hidden');
  
  // Set toast color based on type
  toast.className = 'fixed top-4 right-4 z-50 p-4 rounded shadow-lg text-white';
  if (type === 'success') {
    toast.classList.add('bg-green-500');
  } else if (type === 'error') {
    toast.classList.add('bg-red-500');
  } else if (type === 'info') {
    toast.classList.add('bg-blue-500');
  }
  
  setTimeout(() => {
    toast.classList.add('hidden');
  }, 3000);
}

// Generate report based on selected filters
function generateReport(autoDownload = false) {
    const reportType = document.getElementById('reportType').value;
    const timeRange = document.getElementById('timeRange').value;
  
  // Get common filters
  const params = new URLSearchParams();
  params.append('reportType', reportType);
  params.append('timeRange', timeRange);
  
  // Handle custom date range if selected
  if (timeRange === 'custom') {
    const startDate = document.getElementById('customStartDate').value;
    const endDate = document.getElementById('customEndDate').value;
    
    if (!startDate || !endDate) {
      showToast('Please select both start and end dates for custom range', 'error');
      return;
    }
    
    params.append('startDate', startDate);
    params.append('endDate', endDate);
  }
  
  // Add report-specific filters
    if (reportType === 'sales') {
    const customerId = document.getElementById('customerFilter').value;
    const status = document.getElementById('statusFilter').value;
    
    if (customerId) params.append('customerId', customerId);
    if (status) params.append('status', status);
  } 
  else if (reportType === 'product_sales') {
    const productId = document.getElementById('productFilter').value;
    const categoryId = document.getElementById('categoryFilter').value;
    
    if (productId) params.append('productId', productId);
    if (categoryId) params.append('categoryId', categoryId);
  } 
  else if (reportType === 'stock_movement') {
    const productId = document.getElementById('stockProductFilter').value;
    const movementType = document.getElementById('movementTypeFilter').value;
    const userId = document.getElementById('userFilter').value;
    
    if (productId) params.append('productId', productId);
    if (movementType) params.append('movementType', movementType);
    if (userId) params.append('userId', userId);
  } 
  else if (reportType === 'user_sales') {
    const userId = document.getElementById('userSalesFilter').value;
    const status = document.getElementById('userSalesStatusFilter').value;
    
    if (userId) params.append('userId', userId);
    if (status) params.append('status', status);
  }
  
  // Show loading indicator
  const reportContent = document.getElementById('reportContent');
  reportContent.innerHTML = '<div class="flex justify-center items-center p-12"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div></div>';
  
  // Store current parameters for debugging
  console.log('Report parameters:', Object.fromEntries(params));
  
  // Fetch report data
  fetch(`api/reports.php?${params.toString()}`)
    .then(response => {
      if (!response.ok) {
        throw new Error('Failed to generate report: ' + response.status);
      }
      return response.json();
    })
    .then(data => {
      console.log('API Response:', data);

    if (data.success) {
        // If report generation was successful
        // Show report summary
        const reportSummary = document.getElementById('reportSummary');
        if (reportSummary) {
          reportSummary.classList.remove('hidden');
        }
        
        // Display the report
        displayReportData(data, reportType);
        
        // If auto download was requested
        if (autoDownload) {
          downloadReport(data.defaultFormat || 'pdf');
        }
      } else {
        // If there was an error message in the response
        showToast(data.message || 'Failed to generate report', 'error');
        reportContent.innerHTML = '<div class="text-center p-8 text-red-500">Failed to generate report. Please try different filters.</div>';
      }
    })
    .catch(error => {
      console.error('Error generating report:', error);
      showToast('An error occurred while generating the report', 'error');
      reportContent.innerHTML = '<div class="text-center p-8 text-red-500">An error occurred. Please try again later.</div>';
    });
}

// Display report data in the content area
function displayReportData(data, reportType) {
  const reportContent = document.getElementById('reportContent');
  
  // Clear previous content
  reportContent.innerHTML = '';
  
  // Log the report type and data for debugging
  console.log(`Displaying ${reportType} report with data:`, data);
  
  // Create report content based on report type
  if (reportType === 'sales') {
    displaySalesReport(data, reportContent);
  } else if (reportType === 'product_sales') {
    displayProductSalesReport(data, reportContent);
  } else if (reportType === 'stock_movement') {
    displayStockMovementReport(data, reportContent);
  } else if (reportType === 'user_sales') {
    displayUserSalesReport(data, reportContent);
  } else {
    reportContent.innerHTML = '<div class="text-center p-8 text-gray-500">Unknown report type selected.</div>';
  }
}

// Display sales report
function displaySalesReport(data, container) {
  // Create summary section
  const summaryDiv = document.createElement('div');
  summaryDiv.className = 'bg-white rounded-lg shadow-md p-6 mb-6';
  summaryDiv.innerHTML = `
    <h3 class="text-lg font-semibold mb-4">Summary</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-gray-50 p-4 rounded">
        <p class="text-gray-500 text-sm">Total Sales</p>
        <p class="text-xl font-bold">${data.summary.totalSales || 0}</p>
      </div>
      <div class="bg-gray-50 p-4 rounded">
        <p class="text-gray-500 text-sm">Total Revenue <span class="text-xs text-gray-500">(delivered only)</span></p>
        <p class="text-xl font-bold">৳${parseFloat(data.summary.totalRevenue || 0).toFixed(2)}</p>
      </div>
      <div class="bg-gray-50 p-4 rounded">
        <p class="text-gray-500 text-sm">Average Sale <span class="text-xs text-gray-500">(delivered only)</span></p>
        <p class="text-xl font-bold">৳${parseFloat(data.summary.averageSale || 0).toFixed(2)}</p>
      </div>
    </div>
    <div class="text-xs text-gray-500 mt-2 italic">Note: Revenue is calculated only from delivered orders.</div>
  `;
  container.appendChild(summaryDiv);
  
  // Create table section
  const tableDiv = document.createElement('div');
  tableDiv.className = 'bg-white rounded-lg shadow-md p-6 overflow-x-auto';
  
  // Get sales data from the appropriate location in the response
  const salesData = data.sales || data.data.sales || [];
  
  // Create table
  let tableHTML = `
    <table class="min-w-full">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
      <tbody class="bg-white divide-y divide-gray-200">
  `;
  
  // Add rows
  if (salesData && salesData.length > 0) {
    salesData.forEach(sale => {
      tableHTML += `
        <tr>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${sale.invoice_number || ''}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${sale.date || ''}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${sale.customer_name || ''}</td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(sale.status)}">
              ${sale.status || ''}
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${sale.item_count || 0}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">৳${parseFloat(sale.total || 0).toFixed(2)}</td>
        </tr>
      `;
    });
    
    // Log to console for debugging
    console.log('Sale data received:', salesData);
  } else {
    tableHTML += `
      <tr>
        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No sales data found for the selected filters.</td>
                        </tr>
    `;
    
    // Log to console for debugging
    console.log('No sales data or empty array received:', data);
  }
  
  tableHTML += `
                </tbody>
            </table>
        `;
  
  tableDiv.innerHTML = tableHTML;
  container.appendChild(tableDiv);
}

// Display product sales report
function displayProductSalesReport(data, container) {
  // Create summary section
  const summaryDiv = document.createElement('div');
  summaryDiv.className = 'bg-white rounded-lg shadow-md p-6 mb-6';
  summaryDiv.innerHTML = `
    <h3 class="text-lg font-semibold mb-4">Summary</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-gray-50 p-4 rounded">
        <p class="text-gray-500 text-sm">Total Products Sold <span class="text-xs text-gray-500">(delivered only)</span></p>
        <p class="text-xl font-bold">${data.summary.totalQuantity || 0}</p>
      </div>
      <div class="bg-gray-50 p-4 rounded">
        <p class="text-gray-500 text-sm">Total Revenue <span class="text-xs text-gray-500">(delivered only)</span></p>
        <p class="text-xl font-bold">৳${parseFloat(data.summary.totalRevenue || 0).toFixed(2)}</p>
      </div>
      <div class="bg-gray-50 p-4 rounded">
        <p class="text-gray-500 text-sm">Products Tracked</p>
        <p class="text-xl font-bold">${data.summary.productCount || 0}</p>
      </div>
    </div>
    <div class="text-xs text-gray-500 mt-2 italic">Note: Only sales with "delivered" status are included in calculations.</div>
  `;
  container.appendChild(summaryDiv);
  
  // Create table section
  const tableDiv = document.createElement('div');
  tableDiv.className = 'bg-white rounded-lg shadow-md p-6 overflow-x-auto';
  
  // Get product data from the appropriate location
  const productData = data.products || data.data.products || [];
  
  // Create table
  let tableHTML = `
    <table class="min-w-full">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Sold</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Price</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
  `;
  
  // Add rows
  if (productData && productData.length > 0) {
    productData.forEach(product => {
      tableHTML += `
        <tr>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.name || ''}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.category || ''}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.quantity_sold || 0}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">৳${parseFloat(product.revenue || 0).toFixed(2)}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">৳${parseFloat(product.average_price || 0).toFixed(2)}</td>
        </tr>
      `;
    });
    
    // Log to console for debugging
    console.log('Product data received:', productData);
  } else {
    tableHTML += `
      <tr>
        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No product sales data found for the selected filters.</td>
                        </tr>
    `;
    
    // Log to console for debugging
    console.log('No product data or empty array received:', data);
  }
  
  tableHTML += `
                </tbody>
            </table>
        `;
  
  tableDiv.innerHTML = tableHTML;
  container.appendChild(tableDiv);
}

// Display stock movement report
function displayStockMovementReport(data, container) {
  // Create summary section
  const summaryDiv = document.createElement('div');
  summaryDiv.className = 'bg-white rounded-lg shadow-md p-6 mb-6';
  summaryDiv.innerHTML = `
    <h3 class="text-lg font-semibold mb-4">Summary</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
      <div class="bg-gray-50 p-4 rounded">
        <p class="text-gray-500 text-sm">Total Movements</p>
        <p class="text-xl font-bold">${data.summary.totalMovements || 0}</p>
      </div>
      <div class="bg-gray-50 p-4 rounded">
        <p class="text-gray-500 text-sm">Stock In</p>
        <p class="text-xl font-bold">${data.summary.totalStockIn || 0}</p>
      </div>
      <div class="bg-gray-50 p-4 rounded">
        <p class="text-gray-500 text-sm">Stock Out</p>
        <p class="text-xl font-bold">${data.summary.totalStockOut || 0}</p>
      </div>
      <div class="bg-gray-50 p-4 rounded">
        <p class="text-gray-500 text-sm">Total Items Moved</p>
        <p class="text-xl font-bold">${(data.summary.totalStockInQuantity || 0) + (data.summary.totalStockOutQuantity || 0) + (data.summary.totalAdjustmentQuantity || 0)}</p>
      </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-green-50 p-4 rounded">
        <p class="text-gray-600 text-sm">Total Items In</p>
        <p class="text-xl font-bold">${data.summary.totalStockInQuantity || 0}</p>
      </div>
      <div class="bg-red-50 p-4 rounded">
        <p class="text-gray-600 text-sm">Total Items Out</p>
        <p class="text-xl font-bold">${data.summary.totalStockOutQuantity || 0}</p>
      </div>
      <div class="bg-blue-50 p-4 rounded">
        <p class="text-gray-600 text-sm">Total Adjustments</p>
        <p class="text-xl font-bold">${data.summary.totalAdjustmentQuantity || 0}</p>
      </div>
    </div>
  `;
  container.appendChild(summaryDiv);
  
  // Create table section
  const tableDiv = document.createElement('div');
  tableDiv.className = 'bg-white rounded-lg shadow-md p-6 overflow-x-auto';
  
  // Get movement data from the appropriate location
  const movementData = data.movements || data.data.movements || [];
  
  // Create table
  let tableHTML = `
    <table class="min-w-full">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
  `;
  
  // Add rows
  if (movementData && movementData.length > 0) {
    movementData.forEach(movement => {
      tableHTML += `
        <tr>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${movement.date || ''}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${movement.product_name || ''}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${movement.size_name || 'Default'}</td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getMovementTypeColor(movement.type)}">
              ${movement.type || ''}
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${movement.quantity || 0}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${movement.user_name || ''}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${movement.reason || '-'}</td>
        </tr>
      `;
    });
    
    // Log to console for debugging
    console.log('Movement data received:', movementData);
  } else {
    tableHTML += `
      <tr>
        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No stock movements found for the selected filters.</td>
      </tr>
    `;
    
    // Log to console for debugging
    console.log('No movement data or empty array received:', data);
  }
  
  tableHTML += `
                </tbody>
            </table>
        `;
  
  tableDiv.innerHTML = tableHTML;
  container.appendChild(tableDiv);
}

// Display user sales report
function displayUserSalesReport(data, container) {
  // Create summary section
  const summaryDiv = document.createElement('div');
  summaryDiv.className = 'bg-white rounded-lg shadow-md p-6 mb-6';
  summaryDiv.innerHTML = `
    <h3 class="text-lg font-semibold mb-4">Summary</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-gray-50 p-4 rounded">
        <p class="text-gray-500 text-sm">Total Users</p>
        <p class="text-xl font-bold">${data.summary.totalUsers || 0}</p>
      </div>
      <div class="bg-gray-50 p-4 rounded">
        <p class="text-gray-500 text-sm">Total Sales</p>
        <p class="text-xl font-bold">${data.summary.totalSales || 0}</p>
      </div>
      <div class="bg-gray-50 p-4 rounded">
        <p class="text-gray-500 text-sm">Total Revenue <span class="text-xs text-gray-500">(delivered only)</span></p>
        <p class="text-xl font-bold">৳${parseFloat(data.summary.totalRevenue || 0).toFixed(2)}</p>
      </div>
    </div>
    <div class="text-xs text-gray-500 mt-2 italic">Note: Revenue is calculated only from delivered orders.</div>
  `;
  container.appendChild(summaryDiv);
  
  // Create table section
  const tableDiv = document.createElement('div');
  tableDiv.className = 'bg-white rounded-lg shadow-md p-6 overflow-x-auto';
  
  // Get user sales data
  const userSalesData = data.users || data.data.users || [];
  
  // Create table
  let tableHTML = `
    <table class="min-w-full">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sales</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Items</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Sale Value</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
  `;
  
  // Add rows
  if (userSalesData && userSalesData.length > 0) {
    userSalesData.forEach(user => {
      tableHTML += `
        <tr>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.username || ''}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.role || ''}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.sales_count || 0}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${user.items_sold || 0}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">৳${parseFloat(user.revenue || 0).toFixed(2)}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">৳${parseFloat(user.average_sale || 0).toFixed(2)}</td>
        </tr>
      `;
    });
    
    // Log to console for debugging
    console.log('User sales data received:', userSalesData);
  } else {
    tableHTML += `
      <tr>
        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No user sales data found for the selected filters.</td>
      </tr>
    `;
    
    // Log to console for debugging
    console.log('No user sales data or empty array received:', data);
  }
  
  tableHTML += `
    </tbody>
  </table>
  `;
  
  tableDiv.innerHTML = tableHTML;
  container.appendChild(tableDiv);
}

// Get color class for sale status badges
function getStatusColor(status) {
  switch (status.toLowerCase()) {
    case 'completed':
      return 'bg-green-100 text-green-800';
    case 'pending':
      return 'bg-yellow-100 text-yellow-800';
    case 'cancelled':
      return 'bg-red-100 text-red-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
}

// Get color class for movement type badges
function getMovementTypeColor(type) {
  switch (type.toLowerCase()) {
    case 'in':
    case 'stock in':
      return 'bg-green-100 text-green-800';
    case 'out':
    case 'stock out':
      return 'bg-red-100 text-red-800';
    case 'adjustment':
      return 'bg-blue-100 text-blue-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
}

// Get color class for batch status badges
function getBatchStatusColor(status) {
  switch (status.toLowerCase()) {
    case 'active':
      return 'bg-green-100 text-green-800';
    case 'expiring soon':
      return 'bg-yellow-100 text-yellow-800';
    case 'expired':
      return 'bg-red-100 text-red-800';
    case 'consumed':
      return 'bg-blue-100 text-blue-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
}

// Download report in the specified format
function downloadReport(format) {
    const reportType = document.getElementById('reportType').value;
  const timeRange = document.getElementById('timeRange').value;
  
  // Get common filters
  const params = new URLSearchParams();
  params.append('reportType', reportType);
  params.append('timeRange', timeRange);
  params.append('format', format);
  params.append('download', 'true');
  
  // Handle custom date range if selected
  if (timeRange === 'custom') {
    const startDate = document.getElementById('customStartDate').value;
    const endDate = document.getElementById('customEndDate').value;
    
    if (!startDate || !endDate) {
      showToast('Please select both start and end dates for custom range', 'error');
      return;
    }
    
    params.append('startDate', startDate);
    params.append('endDate', endDate);
  }
  
  // Add report-specific filters
  if (reportType === 'sales') {
    const customerId = document.getElementById('customerFilter').value;
    const status = document.getElementById('statusFilter').value;
    
    if (customerId) params.append('customerId', customerId);
    if (status) params.append('status', status);
  } 
  else if (reportType === 'product_sales') {
    const productId = document.getElementById('productFilter').value;
    const categoryId = document.getElementById('categoryFilter').value;
    
    if (productId) params.append('productId', productId);
    if (categoryId) params.append('categoryId', categoryId);
  } 
  else if (reportType === 'stock_movement') {
    const productId = document.getElementById('stockProductFilter').value;
    const movementType = document.getElementById('movementTypeFilter').value;
    const userId = document.getElementById('userFilter').value;
    
    if (productId) params.append('productId', productId);
    if (movementType) params.append('movementType', movementType);
    if (userId) params.append('userId', userId);
  } 
  else if (reportType === 'user_sales') {
    const userId = document.getElementById('userSalesFilter').value;
    const status = document.getElementById('userSalesStatusFilter').value;
    
    if (userId) params.append('userId', userId);
    if (status) params.append('status', status);
  }
  
  // Open download URL in a new tab/window
  window.open(`api/reports.php?${params.toString()}`, '_blank');
}
</script>