// Advanced Analytics Module

// Initialize analytics dashboard
function initAdvancedAnalytics() {
  // Set up event listeners
  document.querySelectorAll('[data-analytics-tab]').forEach(tab => {
    tab.addEventListener('click', function() {
      const targetTab = this.getAttribute('data-analytics-tab');
      showAnalyticsTab(targetTab);
      loadAnalyticsData(targetTab);
    });
  });
  
  // Load sales forecast by default
  loadAnalyticsData('sales_forecast');
}

// Show the selected analytics tab content
function showAnalyticsTab(tabId) {
  // Hide all tabs
  document.querySelectorAll('.analytics-tab-content').forEach(tab => {
    tab.classList.add('hidden');
  });
  
  // Show the selected tab
  const selectedTab = document.getElementById(`${tabId}-tab`);
  if (selectedTab) {
    selectedTab.classList.remove('hidden');
  }
  
  // Update active tab state
  document.querySelectorAll('[data-analytics-tab]').forEach(tab => {
    tab.classList.remove('bg-gray-700', 'text-white');
    tab.classList.add('hover:bg-gray-600');
  });
  
  const activeTab = document.querySelector(`[data-analytics-tab="${tabId}"]`);
  if (activeTab) {
    activeTab.classList.add('bg-gray-700', 'text-white');
    activeTab.classList.remove('hover:bg-gray-600');
  }
}

// Load analytics data based on tab
function loadAnalyticsData(type) {
  const loadingIndicator = document.getElementById('analyticsLoading');
  if (loadingIndicator) loadingIndicator.classList.remove('hidden');
  
  fetch(`api/predictive-analytics.php?type=${type}`)
    .then(response => {
      if (!response.ok) {
        throw new Error(`Failed to load analytics data: ${response.status} ${response.statusText}`);
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        renderAnalyticsData(type, data.data);
      } else {
        console.error('API returned error:', data.message || 'Unknown error');
        
        // Create a more detailed error message if we have suggestions
        let errorMessage = data.message || 'Failed to load analytics data';
        if (data.suggestion) {
          errorMessage += '<br><br><strong>Suggestion:</strong> ' + data.suggestion;
        }
        
        showToast(errorMessage, 'error');
        
        // Show error message in the tab container
        const container = document.getElementById(`${type}-tab`);
        if (container) {
          container.innerHTML = `
            <div class="p-6 text-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <h3 class="text-lg font-medium text-gray-900">Error Loading Data</h3>
              <div class="mt-2 text-gray-500">${errorMessage}</div>
              <button class="mt-4 px-4 py-2 bg-gray-800 text-white rounded-md retry-button" data-type="${type}">
                Try Again
              </button>
            </div>
          `;
          
          // Add event listener to retry button
          const retryButton = container.querySelector('.retry-button');
          if (retryButton) {
            retryButton.addEventListener('click', function() {
              loadAnalyticsData(this.getAttribute('data-type'));
            });
          }
        }
      }
    })
    .catch(error => {
      console.error('Error loading analytics data:', error);
      showToast('Failed to load analytics data: ' + error.message, 'error');
      
      // Show error message in the tab container
      const container = document.getElementById(`${type}-tab`);
      if (container) {
        container.innerHTML = `
          <div class="p-6 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900">Connection Error</h3>
            <p class="mt-2 text-gray-500">${error.message}</p>
            <button class="mt-4 px-4 py-2 bg-gray-800 text-white rounded-md retry-button" data-type="${type}">
              Try Again
            </button>
          </div>
        `;
        
        // Add event listener to retry button
        const retryButton = container.querySelector('.retry-button');
        if (retryButton) {
          retryButton.addEventListener('click', function() {
            loadAnalyticsData(this.getAttribute('data-type'));
          });
        }
      }
    })
    .finally(() => {
      if (loadingIndicator) loadingIndicator.classList.add('hidden');
    });
}

// Render analytics data based on type
function renderAnalyticsData(type, data) {
  switch (type) {
    case 'sales_forecast':
      renderSalesForecast(data);
      break;
    case 'inventory_prediction':
      renderInventoryPrediction(data);
      break;
    case 'profit_margin_analysis':
      renderProfitMarginAnalysis(data);
      break;
    case 'product_performance':
      renderProductPerformance(data);
      break;
    case 'customer_segmentation':
      renderCustomerSegmentation(data);
      break;
    default:
      renderOverallAnalytics(data);
      break;
  }
}

// Render sales forecast
function renderSalesForecast(data) {
  const container = document.getElementById('sales_forecast-tab');
  if (!container) return;
  
  // Clear previous content
  container.innerHTML = '';
  
  // Create sales forecast visualization
  const chartContainer = document.createElement('div');
  chartContainer.classList.add('h-80', 'mb-6');
  chartContainer.innerHTML = '<canvas id="forecastChart"></canvas>';
  container.appendChild(chartContainer);
  
  // Add forecast data table
  const tableSection = document.createElement('div');
  tableSection.classList.add('bg-white', 'rounded-lg', 'shadow-sm', 'p-4', 'mb-6');
  tableSection.innerHTML = `
    <h3 class="text-lg font-semibold mb-4">Sales Forecast (Next 3 Months)</h3>
    <div class="overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Month</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Projected Sales</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Confidence Level</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="forecast-table-body">
          ${data.forecast.map(item => `
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">${item.month_name}</td>
              <td class="px-6 py-4 whitespace-nowrap">৳${parseFloat(item.projected_sales).toLocaleString()}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-32 h-3 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500" style="width: ${Math.round(item.confidence_level * 100)}%"></div>
                  </div>
                  <span class="ml-2">${Math.round(item.confidence_level * 100)}%</span>
                </div>
              </td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  `;
  container.appendChild(tableSection);
  
  // Create statistics cards
  const statsContainer = document.createElement('div');
  statsContainer.classList.add('grid', 'grid-cols-1', 'md:grid-cols-3', 'gap-4', 'mb-6');
  
  // Format growth rate for display
  const growthRate = (data.monthly_growth_rate * 100).toFixed(1);
  const growthClass = parseFloat(growthRate) >= 0 ? 'text-green-600' : 'text-red-600';
  
  statsContainer.innerHTML = `
    <div class="bg-white rounded-lg shadow-sm p-4">
      <h4 class="text-sm text-gray-500 font-medium">Monthly Growth Rate</h4>
      <p class="text-2xl font-bold ${growthClass}">${growthRate}%</p>
      <p class="text-xs text-gray-500 mt-1">Average month-over-month growth</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
      <h4 class="text-sm text-gray-500 font-medium">Growth Volatility</h4>
      <p class="text-2xl font-bold text-gray-800">${(data.growth_volatility * 100).toFixed(1)}%</p>
      <p class="text-xs text-gray-500 mt-1">Standard deviation of growth rates</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
      <h4 class="text-sm text-gray-500 font-medium">Forecast Period</h4>
      <p class="text-2xl font-bold text-gray-800">3 Months</p>
      <p class="text-xs text-gray-500 mt-1">Based on 6 months of historical data</p>
    </div>
  `;
  container.appendChild(statsContainer);
  
  // Create and render the chart
  renderForecastChart(data);
}

// Render forecast chart
function renderForecastChart(data) {
  const ctx = document.getElementById('forecastChart').getContext('2d');
  if (!ctx) return;
  
  // Combine historical and forecast data
  const months = [
    ...data.historical_data.map(item => item.month),
    ...data.forecast.map(item => item.month)
  ];
  
  const sales = [
    ...data.historical_data.map(item => parseFloat(item.total_sales)),
    ...data.forecast.map(item => null) // Empty values for forecast period (for now)
  ];
  
  const forecastData = [
    ...data.historical_data.map(() => null), // Empty values for historical period
    ...data.forecast.map(item => parseFloat(item.projected_sales))
  ];
  
  const monthNames = months.map(month => {
    const [year, monthNum] = month.split('-');
    return new Date(year, monthNum - 1).toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
  });
  
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: monthNames,
      datasets: [
        {
          label: 'Historical Sales',
          data: sales,
          borderColor: 'rgba(75, 192, 192, 1)',
          backgroundColor: 'rgba(75, 192, 192, 0.1)',
          borderWidth: 2,
          fill: true,
          tension: 0.1
        },
        {
          label: 'Sales Forecast',
          data: forecastData,
          borderColor: 'rgba(153, 102, 255, 1)',
          backgroundColor: 'rgba(153, 102, 255, 0.1)',
          borderWidth: 2,
          borderDash: [5, 5],
          fill: true,
          tension: 0.1
        }
      ]
    },
    options: {
      responsive: true,
      interaction: {
        intersect: false,
        mode: 'index'
      },
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Sales (৳)'
          }
        },
        x: {
          title: {
            display: true,
            text: 'Month'
          }
        }
      },
      plugins: {
        legend: {
          position: 'top'
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return `${context.dataset.label}: ৳${context.parsed.y.toLocaleString()}`;
            }
          }
        }
      }
    }
  });
}

// Function to show success/error toast messages
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  if (!toast) return;
  
  // Set toast color based on type
  if (type === 'success') {
    toast.classList.remove('bg-red-500');
    toast.classList.add('bg-green-500');
  } else {
    toast.classList.remove('bg-green-500');
    toast.classList.add('bg-red-500');
  }
  
  // Set message - handle HTML content
  if (message.includes('<')) {
    // If message contains HTML
    toast.innerHTML = message;
  } else {
    // Plain text
    toast.textContent = message;
  }
  
  // Show the toast
  toast.classList.remove('hidden');
  
  // Hide after 3 seconds
  setTimeout(() => {
    toast.classList.add('hidden');
  }, 3000);
}

// Render inventory prediction
function renderInventoryPrediction(data) {
  const container = document.getElementById('inventory_prediction-tab');
  if (!container) return;
  
  // Clear previous content
  container.innerHTML = '';
  
  // Check if data is valid
  if (!data || !Array.isArray(data) || data.length === 0) {
    container.innerHTML = `
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <h3 class="text-lg font-semibold mb-4">Inventory Prediction</h3>
        <p class="text-center text-gray-500 py-4">No inventory prediction data available.</p>
      </div>
    `;
    return;
  }
  
  try {
    // Create inventory prediction content
    const tableSection = document.createElement('div');
    tableSection.classList.add('bg-white', 'rounded-lg', 'shadow-sm', 'p-4', 'mb-6');
    tableSection.innerHTML = `
      <h3 class="text-lg font-semibold mb-4">Inventory Prediction</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Product</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Current Stock</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Daily Sales</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Days of Supply</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Reorder</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            ${data.map(item => {
              try {
                // Safely extract data with defaults
                const name = item.name || 'Unknown Product';
                const currentStock = item.current_stock || 0;
                const dailySales = item.daily_sales || 0;
                const daysOfSupply = item.days_of_supply || 0;
                const status = item.status || 'ok';
                const reorderRecommendation = item.reorder_recommendation || 0;
                const estimatedReorderCost = item.estimated_reorder_cost || 0;
                
                // Determine status color
                let statusClass = 'bg-green-100 text-green-800';
                if (status === 'critical') {
                  statusClass = 'bg-red-100 text-red-800';
                } else if (status === 'low') {
                  statusClass = 'bg-orange-100 text-orange-800';
                } else if (status === 'warning') {
                  statusClass = 'bg-yellow-100 text-yellow-800';
                }
                
                return `
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">${name}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">${currentStock}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${dailySales}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${daysOfSupply}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                        ${status.charAt(0).toUpperCase() + status.slice(1)}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      ${reorderRecommendation > 0 ? 
                        `<span class="text-sm font-semibold">${reorderRecommendation}</span>
                         <span class="text-xs text-gray-500 block">৳${parseFloat(estimatedReorderCost).toLocaleString()}</span>` : 
                        '-'}
                    </td>
                  </tr>
                `;
              } catch (error) {
                console.error('Error rendering inventory item:', error, item);
                return `
                  <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-red-500">
                      Error displaying this item
                    </td>
                  </tr>
                `;
              }
            }).join('')}
          </tbody>
        </table>
      </div>
    `;
    container.appendChild(tableSection);
  } catch (error) {
    console.error('Error rendering inventory prediction:', error);
    container.innerHTML = `
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <h3 class="text-lg font-semibold mb-4">Inventory Prediction</h3>
        <p class="text-center text-red-500 py-4">Error rendering inventory prediction data: ${error.message}</p>
      </div>
    `;
  }
}

// Render profit margin analysis
function renderProfitMarginAnalysis(data) {
  const container = document.getElementById('profit_margin_analysis-tab');
  if (!container) return;
  
  // Clear previous content
  container.innerHTML = '';
  
  // Check if data is valid and has the expected structure
  if (!data || !data.by_category || !data.by_product) {
    container.innerHTML = `
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <h3 class="text-lg font-semibold mb-4">Profit Margin Analysis</h3>
        <p class="text-center text-gray-500 py-4">No profit margin data available.</p>
      </div>
    `;
    return;
  }
  
  try {
    // Create category profit margin section
    const categorySection = document.createElement('div');
    categorySection.classList.add('bg-white', 'rounded-lg', 'shadow-sm', 'p-4', 'mb-6');
    
    const categoryData = Array.isArray(data.by_category) ? data.by_category : [];
    
    categorySection.innerHTML = `
      <h3 class="text-lg font-semibold mb-4">Profit Margin by Category</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Avg. Profit Margin</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Monthly Profit</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Products</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            ${categoryData.length === 0 ? 
              `<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No category data available</td></tr>` :
              categoryData.slice(0, 10).map(category => {
                const name = category.name || 'Unnamed Category';
                const avgMargin = parseFloat(category.avg_profit_margin || 0).toFixed(1);
                const monthlyProfit = parseFloat(category.monthly_profit || 0);
                const productCount = category.product_count || 0;
                
                return `
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap">${name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${avgMargin}%</td>
                    <td class="px-6 py-4 whitespace-nowrap">৳${monthlyProfit.toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${productCount}</td>
                  </tr>
                `;
              }).join('')
            }
          </tbody>
        </table>
      </div>
    `;
    container.appendChild(categorySection);
    
    // Create product profit margin section
    const productSection = document.createElement('div');
    productSection.classList.add('bg-white', 'rounded-lg', 'shadow-sm', 'p-4');
    
    const productData = Array.isArray(data.by_product) ? data.by_product : [];
    
    productSection.innerHTML = `
      <h3 class="text-lg font-semibold mb-4">Top 10 Products by Profit</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Product</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Buying Price</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Selling Price</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Profit Margin</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Monthly Profit</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            ${productData.length === 0 ? 
              `<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No product data available</td></tr>` :
              productData.slice(0, 10).map(product => {
                const name = product.name || 'Unnamed Product';
                const categoryName = product.category_name || 'Uncategorized';
                const buyingPrice = parseFloat(product.buying_price || 0);
                const sellingPrice = parseFloat(product.selling_price || 0);
                const profitMargin = parseFloat(product.profit_margin || 0).toFixed(1);
                const monthlyProfit = parseFloat(product.monthly_profit || 0);
                
                return `
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">${name}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">${categoryName}</td>
                    <td class="px-6 py-4 whitespace-nowrap">৳${buyingPrice.toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap">৳${sellingPrice.toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${profitMargin}%</td>
                    <td class="px-6 py-4 whitespace-nowrap">৳${monthlyProfit.toLocaleString()}</td>
                  </tr>
                `;
              }).join('')
            }
          </tbody>
        </table>
      </div>
    `;
    container.appendChild(productSection);
  } catch (error) {
    console.error('Error rendering profit margin analysis:', error);
    container.innerHTML = `
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <h3 class="text-lg font-semibold mb-4">Profit Margin Analysis</h3>
        <p class="text-center text-red-500 py-4">Error rendering profit margin data: ${error.message}</p>
      </div>
    `;
  }
}

// Render product performance
function renderProductPerformance(data) {
  const container = document.getElementById('product_performance-tab');
  if (!container) return;
  
  // Clear previous content
  container.innerHTML = '';
  
  // Check if data is valid
  if (!data || !Array.isArray(data) || data.length === 0) {
    container.innerHTML = `
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <h3 class="text-lg font-semibold mb-4">Product Performance</h3>
        <p class="text-center text-gray-500 py-4">No product performance data available.</p>
      </div>
    `;
    return;
  }
  
  try {
    // Create product performance section
    const performanceSection = document.createElement('div');
    performanceSection.classList.add('bg-white', 'rounded-lg', 'shadow-sm', 'p-4', 'mb-6');
    performanceSection.innerHTML = `
      <h3 class="text-lg font-semibold mb-4">Top Products Performance</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Product</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Total Sold</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Orders</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Avg Per Order</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Profit Margin</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            ${data.map(product => {
              try {
                const name = product.name || 'Unnamed Product';
                const categoryName = product.category_name || 'Uncategorized';
                const totalQuantity = parseInt(product.total_quantity_sold || 0);
                const orderCount = parseInt(product.order_count || 0);
                const avgQuantity = parseFloat(product.avg_quantity_per_order || 0).toFixed(1);
                const profitMargin = parseFloat(product.profit_margin || 0).toFixed(1);
                
                return `
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">${name}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">${categoryName}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${totalQuantity}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${orderCount}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${avgQuantity}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${profitMargin}%</td>
                  </tr>
                `;
              } catch (error) {
                console.error('Error rendering product performance item:', error, product);
                return `
                  <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-red-500">
                      Error displaying this item
                    </td>
                  </tr>
                `;
              }
            }).join('')}
          </tbody>
        </table>
      </div>
    `;
    container.appendChild(performanceSection);
  } catch (error) {
    console.error('Error rendering product performance:', error);
    container.innerHTML = `
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <h3 class="text-lg font-semibold mb-4">Product Performance</h3>
        <p class="text-center text-red-500 py-4">Error rendering product performance data: ${error.message}</p>
      </div>
    `;
  }
}

// Render customer segmentation
function renderCustomerSegmentation(data) {
  const container = document.getElementById('customer_segmentation-tab');
  if (!container) return;
  
  // Clear previous content
  container.innerHTML = '';
  
  // Check if data is valid
  if (!data || !data.segments) {
    container.innerHTML = `
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <h3 class="text-lg font-semibold mb-4">Customer Segmentation</h3>
        <p class="text-center text-gray-500 py-4">No customer segmentation data available.</p>
      </div>
    `;
    return;
  }
  
  try {
    // Create segment summary section
    const summarySection = document.createElement('div');
    summarySection.classList.add('grid', 'grid-cols-1', 'md:grid-cols-5', 'gap-4', 'mb-6');
    
    // Map segments to UI elements
    const segments = [
      { key: 'high_value', label: 'High Value', color: 'bg-green-100 border-green-500 text-green-800' },
      { key: 'loyal', label: 'Loyal', color: 'bg-blue-100 border-blue-500 text-blue-800' },
      { key: 'potential', label: 'Potential', color: 'bg-purple-100 border-purple-500 text-purple-800' },
      { key: 'at_risk', label: 'At Risk', color: 'bg-yellow-100 border-yellow-500 text-yellow-800' },
      { key: 'inactive', label: 'Inactive', color: 'bg-red-100 border-red-500 text-red-800' }
    ];
    
    segments.forEach(segment => {
      const count = Array.isArray(data.segments[segment.key]) ? data.segments[segment.key].length : 0;
      summarySection.innerHTML += `
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 ${segment.color}">
          <h4 class="text-sm font-semibold mb-1">${segment.label} Customers</h4>
          <p class="text-2xl font-bold">${count}</p>
          <p class="text-xs mt-1">${count} customers in segment</p>
        </div>
      `;
    });
    
    container.appendChild(summarySection);
    
    // Create detailed segments section
    segments.forEach(segment => {
      const segmentData = Array.isArray(data.segments[segment.key]) ? data.segments[segment.key] : [];
      
      if (segmentData.length > 0) {
        const segmentSection = document.createElement('div');
        segmentSection.classList.add('bg-white', 'rounded-lg', 'shadow-sm', 'p-4', 'mb-6');
        
        segmentSection.innerHTML = `
          <h3 class="text-lg font-semibold mb-4 ${segment.color.replace('bg-', 'text-').replace('-100', '-700')}">${segment.label} Customers</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-gray-50 text-gray-600">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Customer</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Orders</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Total Spent</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Avg Order</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Last Purchase</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                ${segmentData.slice(0, 5).map(customer => {
                  try {
                    const name = customer.name || 'Unknown Customer';
                    const orderCount = parseInt(customer.order_count || 0);
                    const totalSpent = parseFloat(customer.total_spent || 0);
                    const avgOrderValue = parseFloat(customer.avg_order_value || 0);
                    const daysSince = customer.days_since_last_purchase || 'N/A';
                    const lastDate = customer.last_purchase_date ? new Date(customer.last_purchase_date).toLocaleDateString() : 'Never';
                    
                    return `
                      <tr>
                        <td class="px-6 py-4 whitespace-nowrap">${name}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${orderCount}</td>
                        <td class="px-6 py-4 whitespace-nowrap">৳${totalSpent.toLocaleString()}</td>
                        <td class="px-6 py-4 whitespace-nowrap">৳${avgOrderValue.toLocaleString()}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span>${lastDate}</span>
                          ${daysSince !== 'N/A' ? `<span class="text-xs text-gray-500 block">${daysSince} days ago</span>` : ''}
                        </td>
                      </tr>
                    `;
                  } catch (error) {
                    console.error('Error rendering customer item:', error, customer);
                    return `
                      <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-red-500">
                          Error displaying this customer
                        </td>
                      </tr>
                    `;
                  }
                }).join('')}
              </tbody>
            </table>
          </div>
        `;
        
        container.appendChild(segmentSection);
      }
    });
  } catch (error) {
    console.error('Error rendering customer segmentation:', error);
    container.innerHTML = `
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <h3 class="text-lg font-semibold mb-4">Customer Segmentation</h3>
        <p class="text-center text-red-500 py-4">Error rendering customer segmentation data: ${error.message}</p>
      </div>
    `;
  }
}

// Render overall analytics
function renderOverallAnalytics(data) {
  // This function is a fallback for when no specific tab is selected
  // or when we want to show a overview dashboard
  const container = document.getElementById('sales_forecast-tab');
  if (!container) return;
  
  // Clear previous content
  container.innerHTML = '';
  
  // Create a message asking user to select a specific analytics tab
  container.innerHTML = `
    <div class="text-center py-12">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
      </svg>
      <h3 class="text-lg font-medium text-gray-700">Advanced Analytics Dashboard</h3>
      <p class="mt-2 text-gray-500">Please select a specific analytics tab above to view detailed insights.</p>
    </div>
  `;
}

// Expose public methods
window.advancedAnalytics = {
  init: initAdvancedAnalytics,
  loadData: loadAnalyticsData
};