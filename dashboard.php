<?php
require_once 'authcheck.php';
requireLogin();           // Ensures the user is logged in
requireRole('admin');
?>

<?php
include 'header.php';
include 'sidebar.php';
?>

<main class="min-h-screen p-6 bg-gray-50">
  <!-- Toast container -->
  <div id="toast"
       class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg hidden z-50">
  </div>

  <div class="mb-8">
    <div class="flex flex-wrap justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Admin Dashboard</h2>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-indigo-500 hover:shadow-md transition duration-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 font-medium">Total Product</p>
            <p class="text-2xl font-bold text-gray-800" id="totalProductCount">0</p>
          </div>
          <div class="bg-indigo-100 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-orange-500 hover:shadow-md transition duration-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 font-medium">Recently Manufactured</p>
            <p class="text-2xl font-bold text-gray-800" id="recentlyManufacturedCount">0</p>
          </div>
          <div class="bg-orange-100 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-green-500 hover:shadow-md transition duration-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 font-medium">Total Stock</p>
            <p class="text-2xl font-bold text-gray-800" id="totalStockCount">0</p>
          </div>
          <div class="bg-green-100 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-blue-500 hover:shadow-md transition duration-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 font-medium">Sales Today</p>
            <p class="text-2xl font-bold text-gray-800" id="salesTodayAmount">৳0.00</p>
          </div>
          <div class="bg-blue-100 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Charts and Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Monthly Sales Chart -->
      <div class="bg-white rounded-lg shadow-sm p-5 lg:col-span-2">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-800">Monthly Sales</h3>
          <div class="flex gap-2">
            <select id="timeRangeFilter" class="border border-gray-300 rounded-md px-3 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500 text-sm">
              <option value="monthly">Monthly</option>
              <option value="weekly">Weekly</option>
              <option value="daily">Daily</option>
            </select>
            <select id="periodFilter" class="border border-gray-300 rounded-md px-3 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500 text-sm">
              <option value="6">Last 6 Months</option>
              <option value="3">Last 3 Months</option>
              <option value="12">Last 12 Months</option>
            </select>
          </div>
        </div>
        <p class="text-sm text-gray-500 mb-4">Sales performance over the last 6 months</p>
        <div class="h-64">
          <canvas id="salesChart"></canvas>
        </div>
      </div>

      <!-- Recent Sales -->
      <div class="bg-white rounded-lg shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Sales</h3>
        <div id="recent-sales-loading" class="flex justify-center items-center py-8 hidden">
          <div class="animate-spin h-8 w-8 border-4 border-indigo-500 rounded-full border-t-transparent"></div>
        </div>
        <div id="recent-sales-list"></div>
      </div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="bg-white rounded-lg shadow-sm p-5 mb-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Low Stock Alerts</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200" id="low-stock-list">
          </tbody>
        </table>
        <div id="low-stock-loading" class="flex justify-center items-center py-8 hidden">
          <div class="animate-spin h-8 w-8 border-4 border-indigo-500 rounded-full border-t-transparent"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Drill Down Modal -->
  <div id="drillDownModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 overflow-hidden">
      <div class="p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-bold text-gray-800">Sales Details</h2>
          <button onclick="document.getElementById('drillDownModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div id="drillDownContent" class="max-h-96 overflow-y-auto">
          <!-- Content will be dynamically populated -->
        </div>
      </div>
      <div class="bg-gray-50 px-6 py-3 flex justify-end">
        <button onclick="document.getElementById('drillDownModal').classList.add('hidden')" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded mr-2">
          Close
        </button>
      </div>
    </div>
  </div>
</main>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
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

    const gridColor = 'rgba(0, 0, 0, 0.1)';
    const textColor = '#666';

    salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sales',
                data: salesData,
                backgroundColor: 'rgba(99, 102, 241, 0.5)',
                borderColor: 'rgb(99, 102, 241)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor,
                        callback: function(value) {
                            return '৳' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '৳' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            onClick: (event, elements) => {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    const label = labels[index];
                    showSalesDetails(label, data[index].details);
                }
            }
        }
    });
}

// Function to show sales details in modal
function showSalesDetails(label, details) {
    const modal = document.getElementById('drillDownModal');
    const content = document.getElementById('drillDownContent');
    
    content.innerHTML = `
        <h2 class="text-lg font-bold mb-4">Sales Details for ${label}</h2>
        <p><strong>Total Sales:</strong> ৳${details.total.toLocaleString()}</p>
        <p><strong>Number of Transactions:</strong> ${details.transactions.length}</p>
        <h3 class="text-md font-semibold mt-4">Transactions</h3>
        <ul class="list-disc pl-5">
            ${details.transactions.map(t => `
                <li>Order #${t.order_id}: ৳${t.amount.toLocaleString()}</li>
            `).join('')}
        </ul>
    `;
    
    modal.classList.remove('hidden');
}

// Load initial data
document.addEventListener('DOMContentLoaded', () => {
    loadSalesAnalytics();
});

// Handle filter changes
document.getElementById('timeRangeFilter').addEventListener('change', (e) => {
    loadSalesAnalytics({ timeRange: e.target.value });
});

document.getElementById('periodFilter').addEventListener('change', (e) => {
    loadSalesAnalytics({ period: e.target.value });
});
</script>