// Global variables
let reportChart;
let reportData = null;
let currentReportType = '';

// Make reportData accessible globally
window.reportData = null;

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
        // Store report data globally
        reportData = data;
        window.reportData = data;
        currentReportType = reportType;
        
        // Show report summary
        const reportSummary = document.getElementById('reportSummary');
        if (reportSummary) {
          reportSummary.classList.remove('hidden');
        }
        
        // Show download options
        const downloadTypeContainer = document.getElementById('downloadTypeContainer');
        if (downloadTypeContainer) {
          downloadTypeContainer.classList.remove('hidden');
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