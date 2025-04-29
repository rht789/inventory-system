<?php
require_once 'authcheck.php';
requireLogin();           // Ensures the user is logged in
requireRole('admin');
?>

<?php
include 'header.php';
include 'sidebar.php';
?>

<main class="min-h-screen p-6 bg-gray-50 dark:bg-gray-900 dark-mode-transition">
  <!-- Toast container -->
  <div id="toast"
       class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg hidden z-50 dark:bg-green-600">
  </div>

  <div class="mb-8">
    <div class="flex flex-wrap justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Admin Dashboard</h2>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 border-l-4 border-indigo-500 hover:shadow-md transition duration-200 dark:border-indigo-400">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Product</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-white" id="totalProductCount">0</p>
          </div>
          <div class="bg-indigo-100 dark:bg-indigo-900 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-700 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
      </div>
      
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 border-l-4 border-orange-500 hover:shadow-md transition duration-200 dark:border-orange-400">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Recently Manufactured</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-white" id="recentlyManufacturedCount">0</p>
          </div>
          <div class="bg-orange-100 dark:bg-orange-900 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-700 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
        </div>
      </div>
      
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 border-l-4 border-green-500 hover:shadow-md transition duration-200 dark:border-green-400">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Stock</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-white" id="totalStockCount">0</p>
          </div>
          <div class="bg-green-100 dark:bg-green-900 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-700 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
          </div>
        </div>
      </div>
      
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 border-l-4 border-blue-500 hover:shadow-md transition duration-200 dark:border-blue-400">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Sales Today</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-white" id="salesTodayAmount">৳0.00</p>
          </div>
          <div class="bg-blue-100 dark:bg-blue-900 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-700 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Interactive Analytics Section -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Monthly Sales Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 lg:col-span-2">
      <div class="flex flex-wrap justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Monthly Sales</h3>
        <div class="flex gap-2">
          <select id="timeRangeFilter" class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-1.5 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500 text-sm dark:text-white">
            <option value="monthly">Monthly</option>
            <option value="weekly">Weekly</option>
            <option value="daily">Daily</option>
          </select>
          <select id="periodFilter" class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-1.5 bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500 text-sm dark:text-white">
            <option value="6m">Last 6 Months</option>
            <option value="3m">Last 3 Months</option>
            <option value="30d">Last 30 Days</option>
            <option value="1y">Last Year</option>
          </select>
        </div>
      </div>
      <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Sales performance over the last 6 months</p>
      <div class="h-80">
        <canvas id="salesChart"></canvas>
      </div>
    </div>

    <!-- Recent Sales -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Recent Sales</h3>
      <div id="recent-sales" class="space-y-4">
        <!-- Will be populated dynamically -->
        <div class="flex justify-center items-center h-40">
          <div class="animate-spin h-8 w-8 border-4 border-indigo-500 dark:border-indigo-400 rounded-full border-t-transparent"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Low Stock Alerts -->
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Low Stock Alerts</h3>
    <div class="overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Product</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Category</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Stock</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Min</th>
            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Action</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" id="low-stock-list">
          <!-- Will be populated dynamically -->
          <tr>
            <td colspan="5" class="px-6 py-4 text-center">
              <div class="flex justify-center">
                <div class="animate-spin h-8 w-8 border-4 border-indigo-500 dark:border-indigo-400 rounded-full border-t-transparent"></div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  
  <!-- Drill-down Modal -->
  <div id="drillDownModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 overflow-hidden">
      <div class="p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-bold text-gray-800 dark:text-white">Sales Details</h2>
          <button onclick="document.getElementById('drillDownModal').classList.add('hidden')" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div id="drillDownContent" class="max-h-96 overflow-y-auto">
          <!-- Content will be dynamically populated -->
        </div>
      </div>
      <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex justify-end">
        <button onclick="document.getElementById('drillDownModal').classList.add('hidden')" class="bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-800 dark:text-white font-medium py-2 px-4 rounded mr-2">
          Close
        </button>
      </div>
    </div>
  </div>
</main>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Update chart colors based on theme
document.addEventListener('themeChanged', (e) => {
    if (salesChart) {
        const isDark = e.detail.isDark;
        salesChart.options.scales.x.grid.color = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
        salesChart.options.scales.y.grid.color = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
        salesChart.options.scales.x.ticks.color = isDark ? '#fff' : '#666';
        salesChart.options.scales.y.ticks.color = isDark ? '#fff' : '#666';
        salesChart.update();
    }
});

const ctx = document.getElementById('salesChart').getContext('2d');
let salesChart;

// Fetch sales data for the chart
async function loadSalesAnalytics(filters = {}) {
    const params = new URLSearchParams(filters).toString();
    const response = await fetch(`api/sales.php?analytics=true&${params}`);
    const data = await response.json();
    
    if (data.success) {
        updateSalesChart(data.data);
    } else {
        showToast('Failed to load sales analytics', 'error');
    }
}

// Update the chart with new data
function updateSalesChart(data) {
    const labels = data.map(item => item.label);
    const salesData = data.map(item => item.total);

    if (salesChart) salesChart.destroy();

    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
    const textColor = isDark ? '#fff' : '#666';

    salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sales',
                data: salesData,
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: { 
                    beginAtZero: true, 
                    title: { 
                        display: true, 
                        text: 'Sales (৳)',
                        color: textColor
                    },
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor
                    }
                },
                x: { 
                    title: { 
                        display: true, 
                        text: 'Time Period',
                        color: textColor
                    },
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor
                    }
                }
            },
            onClick: (event, elements) => {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    const label = labels[index];
                    openDrillDownModal(label, data[index].details);
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Sales: ৳${context.parsed.y.toLocaleString()}`;
                        }
                    }
                },
                legend: {
                    labels: {
                        color: textColor
                    }
                }
            }
        }
    });
}

// Open a modal with detailed data
function openDrillDownModal(label, details) {
    const modal = document.getElementById('drillDownModal');
    const content = document.getElementById('drillDownContent');

    content.innerHTML = `
        <h2 class="text-lg font-bold mb-4 dark:text-white">Sales Details for ${label}</h2>
        <p class="dark:text-gray-300"><strong>Total Sales:</strong> ৳${details.total.toLocaleString()}</p>
        <p class="dark:text-gray-300"><strong>Number of Transactions:</strong> ${details.transactions.length}</p>
        <h3 class="text-md font-semibold mt-4 dark:text-white">Transactions</h3>
        <ul class="list-disc pl-5 dark:text-gray-300">
            ${details.transactions.map(tx => `<li>Order #${tx.order_id}: ৳${parseFloat(tx.total).toLocaleString()} (${tx.customer_name})</li>`).join('')}
        </ul>
    `;

    modal.classList.remove('hidden');
}

// Fetch recently manufactured batches count
async function loadRecentlyManufactured() {
    const response = await fetch('api/batches.php?recentlyManufactured=true');
    const data = await response.json();
    if (data.success) {
        document.getElementById('recentlyManufacturedCount').textContent = data.count;
    }
}

// Fetch total product count
async function loadTotalProductCount() {
    try {
        const response = await fetch('api/products.php');
        const data = await response.json();
        if (data && Array.isArray(data)) {
            document.getElementById('totalProductCount').textContent = data.length;
        }
    } catch (error) {
        console.error('Error loading total product count:', error);
    }
}

// Fetch total stock count
async function loadTotalStockCount() {
    try {
        const response = await fetch('api/products.php');
        const data = await response.json();
        if (data && Array.isArray(data)) {
            const totalStock = data.reduce((sum, product) => sum + parseInt(product.stock || 0), 0);
            document.getElementById('totalStockCount').textContent = totalStock;
        }
    } catch (error) {
        console.error('Error loading total stock count:', error);
    }
}

// Fetch today's sales amount
async function loadTodaySales() {
    try {
        const response = await fetch('api/sales.php?time=today');
        const data = await response.json();
        if (data.success && data.sales) {
            const todayTotal = data.sales.reduce((sum, sale) => sum + parseFloat(sale.total || 0), 0);
            document.getElementById('salesTodayAmount').textContent = '৳' + todayTotal.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    } catch (error) {
        console.error('Error loading today sales amount:', error);
    }
}

/**
 * Show a toast notification
 */
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  
  if (!toast) {
    console.error("Toast element not found");
    return;
  }

  // Reset toast state
  toast.className = "fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50";
  
  // Set icon and color based on message type
  let icon = '';
  
  switch(type) {
    case 'success':
      toast.classList.add('bg-green-500', 'text-white');
      icon = '<i class="fas fa-check-circle mr-2"></i>';
      break;
    case 'error':
      toast.classList.add('bg-red-500', 'text-white');
      icon = '<i class="fas fa-exclamation-circle mr-2"></i>';
      break;
    case 'warning':
      toast.classList.add('bg-yellow-500', 'text-white');
      icon = '<i class="fas fa-exclamation-triangle mr-2"></i>';
      break;
    default:
      toast.classList.add('bg-gray-700', 'text-white');
      icon = '<i class="fas fa-info-circle mr-2"></i>';
  }
  
  // Set toast content with icon
  toast.innerHTML = `${icon}<span>${message}</span>`;
  
  // Show toast
  toast.classList.remove('hidden');
  
  // Hide after 3 seconds
  setTimeout(() => {
    toast.classList.add('hidden');
  }, 3000);
}

// Fetch low stock items
async function loadLowStockItems() {
    try {
        const response = await fetch('api/products.php?lowStock=true');
        const data = await response.json();
        
        if (data.success && data.products) {
            const lowStockList = document.getElementById('low-stock-list');
            lowStockList.innerHTML = '';
            
            if (data.products.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        No low stock items found
                    </td>
                `;
                lowStockList.appendChild(row);
                return;
            }
            
            data.products.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">${product.name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${product.category}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">${product.stock}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">${product.min_stock}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <button onclick="window.location.href='stock.php?product_id=${product.id}'" class="bg-gray-800 hover:bg-gray-900 text-white px-3 py-1 rounded-md text-sm transition duration-200">Add Stock</button>
                    </td>
                `;
                lowStockList.appendChild(row);
            });
        }
    } catch (error) {
        console.error('Error loading low stock items:', error);
        const lowStockList = document.getElementById('low-stock-list');
        lowStockList.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-red-500">
                    Error loading low stock items
                </td>
            </tr>
        `;
    }
}

// Fetch recent sales for the dashboard (only delivered orders)
async function loadRecentSales() {
    try {
        const response = await fetch('api/sales.php?recent=true&limit=5&status=delivered');
        const data = await response.json();
        
        const recentSalesList = document.getElementById('recent-sales');
        recentSalesList.innerHTML = '';
        
        if (data.success && data.sales && data.sales.length > 0) {
            data.sales.forEach((sale, index) => {
                const div = document.createElement('div');
                div.className = `flex justify-between items-center ${index < data.sales.length - 1 ? 'pb-3 border-b mb-3' : ''}`;
                div.innerHTML = `
                    <div>
                        <p class="font-medium">#${sale.order_id}</p>
                        <p class="text-sm text-gray-500">${sale.customer_name}</p>
                    </div>
                    <p class="font-medium">৳${parseFloat(sale.total).toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })}</p>
                `;
                recentSalesList.appendChild(div);
            });
        } else {
            recentSalesList.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    No delivered orders found
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading recent sales:', error);
        const recentSalesList = document.getElementById('recent-sales');
        recentSalesList.innerHTML = `
            <div class="text-center py-8 text-red-500">
                Error loading recent sales
            </div>
        `;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    loadSalesAnalytics({ timeRange: 'monthly', period: '6m' });
    loadRecentlyManufactured();
    loadLowStockItems();
    loadRecentSales();
    loadTotalProductCount();
    loadTotalStockCount();
    loadTodaySales();

    document.getElementById('timeRangeFilter').addEventListener('change', (e) => {
        const filters = {
            timeRange: e.target.value,
            period: document.getElementById('periodFilter').value
        };
        loadSalesAnalytics(filters);
    });
    
    document.getElementById('periodFilter').addEventListener('change', (e) => {
        const filters = {
            timeRange: document.getElementById('timeRangeFilter').value,
            period: e.target.value
        };
        loadSalesAnalytics(filters);
    });
});
</script>