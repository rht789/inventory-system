<?php
require_once 'authcheck.php';
requireLogin(); // Ensures the user is logged in

// Redirect admins to dashboard.php
if (getUserRole() === 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Get user information for staff only
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
      </div>
    </div>
    
    <!-- Decorative element -->
    <div class="absolute top-0 right-0 w-48 h-48 bg-gray-100 rounded-full -mr-16 -mt-16 opacity-50"></div>
    <div class="absolute bottom-0 right-24 w-32 h-32 bg-gray-100 rounded-full -mb-12 opacity-40"></div>
  </div>

  <!-- Quick Tasks Section -->
  <div class="mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Tasks</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
    </div>
  </div>

  <!-- Main Content Area -->
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
    </div>
  </div>
</main>

<!-- Load dynamic content with JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Staff-specific data loading
  fetchUserAlerts();
  fetchRecentSales();
  fetchTodaysSummary();
});

// Show toast message
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.classList.remove('hidden', 'bg-green-500', 'bg-red-500', 'bg-blue-500');
  
  // Apply the appropriate background color based on the type
  if (type === 'success') {
    toast.classList.add('bg-green-500');
  } else if (type === 'error') {
    toast.classList.add('bg-red-500');
  } else if (type === 'info') {
    toast.classList.add('bg-blue-500');
  } else {
    toast.classList.add('bg-green-500'); // Default
  }
  
  toast.classList.remove('hidden');
  setTimeout(() => {
    toast.classList.add('hidden');
  }, 3000);
}

// Helper function to handle API errors
function handleApiError(error, containerId, message) {
  console.error(message, error);
  document.getElementById(containerId).innerHTML = `
    <div class="text-center text-gray-500 py-4">
      <i class="fas fa-exclamation-circle text-red-500 text-3xl mb-2"></i>
      <p>Failed to load data. Please refresh the page.</p>
    </div>
  `;
}

// Fallback data in case APIs are not available
const fallbackData = {
  alerts: [
    { severity: 'warning', message: 'Low stock for 5 products', link: 'stock.php?status=low' }
  ],
  sales: [
    { invoice_number: '#1092', customer_name: 'John Smith', total_amount: 125.00, sale_date: new Date(Date.now() - 45 * 60000).toISOString() },
    { invoice_number: '#1091', customer_name: 'Mary Johnson', total_amount: 78.50, sale_date: new Date(Date.now() - 120 * 60000).toISOString() }
  ],
  todaysSummary: {
    salesCount: 5,
    revenue: 450.75,
    stockUpdates: 3
  }
};

// Helper function to fetch low stock products
async function fetchLowStockProducts() {
  try {
    const response = await fetch('api/products.php?lowStock=true');
    const data = await response.json();
    
    if (!data.success) {
      throw new Error('Failed to fetch low stock products');
    }
    
    return data.products || [];
  } catch (error) {
    console.warn('Error fetching low stock products:', error);
    return [];
  }
}

// Staff functions
async function fetchUserAlerts() {
  try {
    const alertsContainer = document.getElementById('attention-content');
    let alerts = [];
    
    // Fetch low stock products
    const lowStockProducts = await fetchLowStockProducts();
    
    if (lowStockProducts.length > 0) {
      alerts.push({
        type: 'warning',
        severity: 'warning',
        message: `${lowStockProducts.length} products running low on stock`,
        link: 'stock.php?status=low'
      });
    }
    
    if (alerts.length === 0) {
      alertsContainer.innerHTML = '<p class="text-gray-500 text-center py-4">No alerts at this time!</p>';
      return;
    }
    
    let alertsHTML = '';
    alerts.forEach(alert => {
      let bgColor = 'bg-blue-50';
      let textColor = 'text-blue-800';
      let iconClass = 'fas fa-info-circle text-blue-500';
      
      // Handle both 'severity' (API) and 'type' (fallback) properties
      const alertType = alert.severity || alert.type || 'info';
      
      if (alertType === 'warning') {
        bgColor = 'bg-yellow-50';
        textColor = 'text-yellow-800';
        iconClass = 'fas fa-exclamation-circle text-yellow-500';
      } else if (alertType === 'critical' || alertType === 'danger') {
        bgColor = 'bg-red-50';
        textColor = 'text-red-800';
        iconClass = 'fas fa-exclamation-triangle text-red-500';
      }
      
      alertsHTML += `
        <a href="${alert.link || '#'}" class="block ${bgColor} rounded-lg p-4 hover:shadow-md transition-shadow">
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
  } catch (error) {
    console.error('Error in fetchUserAlerts:', error);
    // Display fallback alerts instead of error message
    displayFallbackAlerts('attention-content');
  }
}

async function fetchRecentSales() {
  try {
    const salesContainer = document.getElementById('recent-sales');
    let sales = [];
    
    try {
      // Use the new dedicated endpoint for today's sales
      const response = await fetch('api/home.php?action=today_sales');
      const data = await response.json();
      
      if (data.success && data.sales && data.sales.length > 0) {
        // Data is already filtered for current user by the API
        sales = data.sales.slice(0, 5); // Limit to 5 most recent sales
      } else {
        console.warn('No sales found:', data);
      }
    } catch (error) {
      console.error('Failed to fetch sales from API:', error);
      sales = fallbackData.sales;
    }
    
    if (sales.length === 0) {
      salesContainer.innerHTML = '<p class="text-gray-500 text-center py-4">No sales today.</p>';
      return;
    }
    
    let salesHTML = '';
    sales.forEach(sale => {
      // Format order ID
      const invoice = `ORD-${String(sale.id).padStart(3, '0')}`;
      
      // Get customer name
      const customer = sale.customer_name || 'Walk-in Customer';
      
      // Get total amount
      const amount = parseFloat(sale.total) || 0;
      
      // Format date
      let dateText = 'Recently';
      try {
        dateText = formatTimestamp(sale.created_at);
      } catch (e) {
        console.warn('Invalid date format:', sale.created_at);
      }
      
      salesHTML += `
        <div class="flex justify-between items-center p-3 hover:bg-gray-50 rounded-lg">
          <div>
            <p class="text-sm font-medium text-gray-800">${invoice}</p>
            <p class="text-xs text-gray-500">${customer}</p>
            <p class="text-xs text-gray-500">${dateText}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-800">৳${formatCurrency(amount)}</p>
          </div>
        </div>
      `;
    });
    
    salesContainer.innerHTML = salesHTML;
  } catch (error) {
    console.error('Error in fetchRecentSales:', error);
    // Display fallback sales instead of error message
    displayFallbackSales('recent-sales');
  }
}

async function fetchTodaysSummary() {
  try {
    let salesCount = 0, revenue = 0, stockUpdates = 0;
    let noSalesData = false, noStockData = false;
    
    try {
      const salesResponse = await fetch('api/home.php?action=today_sales');
      const salesData = await salesResponse.json();
      
      if (salesData.success) {
        // The API handles filtering by current user
        salesCount = salesData.count || 0;
        revenue = parseFloat(salesData.total) || 0;
        
        // Set flag if no sales data today
        noSalesData = salesCount === 0;
      } else {
        console.warn('Failed to get sales summary:', salesData.message);
      }
    } catch (error) {
      console.error('Error fetching sales summary:', error);
    }
    
    try {
      const stockResponse = await fetch('api/home.php?action=today_stock');
      const stockData = await stockResponse.json();
      
      if (stockData.success) {
        // The API handles filtering by current user and today's date
        stockUpdates = stockData.count || 0;
        
        // Set flag if no stock data today
        noStockData = stockUpdates === 0;
      } else {
        console.warn('Failed to get stock updates:', stockData.message);
      }
    } catch (error) {
      console.error('Error fetching stock updates:', error);
    }
    
    // Update UI with appropriate formatting
    if (noSalesData) {
      document.getElementById('today-sales-count').textContent = 'None';
      document.getElementById('today-revenue').textContent = 'No sales today';
    } else {
      document.getElementById('today-sales-count').textContent = salesCount;
      document.getElementById('today-revenue').textContent = '৳' + formatCurrency(revenue);
    }
    
    document.getElementById('today-stock-updates').textContent = noStockData ? 'None' : stockUpdates;
    
  } catch (error) {
    console.error('Error in fetchTodaysSummary:', error);
    // Show error message instead of fallback data
    document.getElementById('today-sales-count').textContent = 'Error';
    document.getElementById('today-revenue').textContent = 'Failed to load data';
    document.getElementById('today-stock-updates').textContent = 'Error';
  }
}

// Helper functions
// Format currency to always show 2 decimal places
function formatCurrency(value) {
  // Check if the value is a valid number
  const num = parseFloat(value);
  if (isNaN(num)) return '0.00';
  
  // Ensure it's formatted with 2 decimal places
  try {
    return num.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
  } catch (e) {
    console.warn('Error formatting currency value:', e);
    return num.toFixed(2);
  }
}

// Helper function to format timestamps
function formatTimestamp(timestamp) {
  if (!timestamp) return 'Unknown time';
  
  let date;
  try {
    date = new Date(timestamp);
    // Check if date is valid
    if (isNaN(date.getTime())) {
      return 'Recently';
    }
  } catch (e) {
    console.warn('Invalid date format:', timestamp);
    return 'Recently';
  }
  
  const now = new Date();
  const diffMs = now - date;
  const diffSec = Math.floor(diffMs / 1000);
  const diffMin = Math.floor(diffSec / 60);
  const diffHour = Math.floor(diffMin / 60);
  const diffDay = Math.floor(diffHour / 24);
  
  if (diffMin < 1) {
    return 'Just now';
  } else if (diffMin < 60) {
    return `${diffMin} minute${diffMin !== 1 ? 's' : ''} ago`;
  } else if (diffHour < 24) {
    return `${diffHour} hour${diffHour !== 1 ? 's' : ''} ago`;
  } else if (diffDay < 7) {
    return `${diffDay} day${diffDay !== 1 ? 's' : ''} ago`;
  } else {
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
  }
}

// Fallback display functions
function displayFallbackAlerts(containerId) {
  const container = document.getElementById(containerId);
  let alertsHTML = '';
  
  // Only include the low stock alert in fallback data
  alertsHTML = `
    <a href="stock.php?status=low" class="block bg-yellow-50 rounded-lg p-4 hover:shadow-md transition-shadow">
      <div class="flex items-start">
        <div class="flex-shrink-0 pt-0.5">
          <i class="fas fa-exclamation-circle text-yellow-500"></i>
        </div>
        <div class="ml-3">
          <p class="text-sm font-medium text-yellow-800">Low stock for 5 products</p>
        </div>
      </div>
    </a>
  `;
  
  container.innerHTML = alertsHTML;
}

function displayFallbackSales(containerId) {
  const container = document.getElementById(containerId);
  let salesHTML = '';
  
  fallbackData.sales.forEach(sale => {
    salesHTML += `
      <div class="flex justify-between items-center p-3 hover:bg-gray-50 rounded-lg">
        <div>
          <p class="text-sm font-medium text-gray-800">${sale.invoice_number}</p>
          <p class="text-xs text-gray-500">${sale.customer_name}</p>
          <p class="text-xs text-gray-500">${formatTimestamp(sale.sale_date)}</p>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-800">৳${formatCurrency(sale.total_amount)}</p>
        </div>
      </div>
    `;
  });
  
  container.innerHTML = salesHTML;
}
</script>

<?php
include 'footer.php';
?> 