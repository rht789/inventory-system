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
    case 'delivered':
      return 'bg-green-100 text-green-800';
    case 'confirmed':
      return 'bg-blue-100 text-blue-800';
    case 'pending':
      return 'bg-yellow-100 text-yellow-800';
    case 'canceled':
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