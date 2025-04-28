/**
 * sales-ui.js
 * Contains functions for UI manipulation in the sales module
 */

// Import utilities if we were using ES6 modules
// import { handleError, showToast, capitalizeFirstLetter } from './sales-utils.js';
// import * as api from './sales-api.js';

/**
 * Shows or hides the loading state
 * 
 * @param {boolean} isLoading - Whether the loading state should be shown
 */
function showLoadingState(isLoading) {
  const loadingElement = document.getElementById('sales-loading');
  const emptyElement = document.getElementById('empty-sales-placeholder');
  const tableElement = document.querySelector('.overflow-x-auto');
  
  if (isLoading) {
    loadingElement.classList.remove('hidden');
    emptyElement.classList.add('hidden');
    tableElement.classList.add('hidden');
  } else {
    loadingElement.classList.add('hidden');
    // Other visibility will be handled by renderSalesList
  }
}

/**
 * Renders the sales list with the provided data
 * 
 * @param {Array} sales - Array of sales objects
 */
function renderSalesList(sales) {
  const tableBody = document.getElementById('sales-list');
  const emptyPlaceholder = document.getElementById('empty-sales-placeholder');
  const tableContainer = document.querySelector('.overflow-x-auto');
  
  // Clear existing content
  tableBody.innerHTML = '';
  
  // If no sales, show empty state
  if (!sales || sales.length === 0) {
    tableContainer.classList.add('hidden');
    emptyPlaceholder.classList.remove('hidden');
    
    // Update bulk actions
    updateBulkActionsVisibility();
    return;
  }
  
  // Show table and hide empty state
  tableContainer.classList.remove('hidden');
  emptyPlaceholder.classList.add('hidden');
  
  // Render each sale
  sales.forEach(sale => {
    const row = document.createElement('tr');
    
    // Format date and time
    const date = new Date(sale.created_at);
    const formattedDate = date.toLocaleDateString();
    const formattedTime = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    
    // Get status badge
    let statusBadge = getStatusBadge(sale.status);
    
    // Set sale ID in the status badge
    statusBadge = statusBadge.replace('data-sale-id=""', `data-sale-id="${sale.id}"`);
    
    // Format products text (up to 2 products)
    let productsText = "";
    if (sale.items && sale.items.length > 0) {
      const productsToShow = sale.items.slice(0, 2);
      productsText = productsToShow.map(item => {
        return item.product_name + (item.size_name ? ` (${item.size_name})` : '');
      }).join(', ');
      
      if (sale.items.length > 2) {
        productsText += ` +${sale.items.length - 2} more`;
      }
    } else {
      productsText = "No products";
    }
    
    row.innerHTML = `
      <td class="px-3 py-4">
        <div class="flex items-center">
          <input type="checkbox" class="sale-checkbox w-4 h-4 text-gray-800 border-gray-300 rounded focus:ring-gray-500 focus:ring-offset-1" 
                 data-sale-id="${sale.id}" data-sale-status="${sale.status}">
        </div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap">${sale.order_id}</td>
      <td class="px-6 py-4">${sale.customer_name}</td>
      <td class="px-6 py-4">${productsText}</td>
      <td class="px-6 py-4 text-right font-medium">৳ ${parseFloat(sale.total).toFixed(2)}</td>
      <td class="px-6 py-4 text-center">${statusBadge}</td>
      <td class="px-6 py-4 text-center text-gray-500">
        ${formattedDate}<br>
        ${formattedTime}
      </td>
      <td class="px-6 py-4 text-center">
        <div class="flex justify-center gap-3">
          <button type="button" class="text-blue-600 hover:text-blue-800 transition-colors" onclick="viewSaleDetails(${sale.id})" title="View Details">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </button>
          <button type="button" class="text-indigo-600 hover:text-indigo-800 transition-colors" onclick="downloadInvoice(${sale.id})" title="Download Invoice">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </button>
          <button type="button" class="text-gray-600 hover:text-gray-800 transition-colors" onclick="editSale(${sale.id})" title="Edit Sale">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </button>
          <button type="button" class="text-red-600 hover:text-red-800 transition-colors" onclick="deleteSale(${sale.id})" title="Delete Sale">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </td>
    `;
    
    tableBody.appendChild(row);
  });
  
  // Initialize status dropdowns
  const statusBadges = tableBody.querySelectorAll('.status-badge');
  statusBadges.forEach(badge => {
    badge.addEventListener('click', function() {
      const saleId = this.getAttribute('data-sale-id');
      const currentStatus = this.getAttribute('data-status');
      const statusCell = this.closest('td');
      openStatusDropdown(saleId, currentStatus, statusCell);
    });
  });
  
  // Initialize checkboxes
  initCheckboxes();
  
  // Update bulk actions
  updateBulkActionsVisibility();
}

/**
 * Returns HTML for a status badge with appropriate styling
 * 
 * @param {string} status - The status value (pending, confirmed, delivered, canceled)
 * @returns {string} - HTML for the status badge
 */
function getStatusBadge(status) {
  let badgeClass = '';
  let textClass = '';
  
  switch(status.toLowerCase()) {
    case 'pending':
      badgeClass = 'bg-yellow-100';
      textClass = 'text-yellow-800';
      break;
    case 'confirmed':
      badgeClass = 'bg-blue-100';
      textClass = 'text-blue-800';
      break;
    case 'delivered':
      badgeClass = 'bg-green-100';
      textClass = 'text-green-800';
      break;
    case 'canceled':
      badgeClass = 'bg-red-100';
      textClass = 'text-red-800';
      break;
    default:
      badgeClass = 'bg-gray-100';
      textClass = 'text-gray-800';
  }
  
  return `<span class="status-badge px-3 py-1 rounded-full text-xs ${badgeClass} ${textClass} cursor-pointer inline-flex items-center" data-status="${status.toLowerCase()}" data-sale-id="">
    ${capitalizeFirstLetter(status)}
    <svg class="w-3 h-3 ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
    </svg>
  </span>`;
}

/**
 * Updates statistics based on sales data
 * 
 * @param {Array} sales - Array of sales objects
 */
function updateStatistics(sales) {
  // Default all counts to 0
  let totalCount = 0;
  let pendingCount = 0;
  let deliveredCount = 0;
  let canceledCount = 0;
  
  if (sales && sales.length) {
    totalCount = sales.length;
    
    // Count by status
    sales.forEach(sale => {
      if (sale.status === 'pending') pendingCount++;
      if (sale.status === 'delivered') deliveredCount++;
      if (sale.status === 'canceled') canceledCount++;
    });
  }
  
  // Update the counters in the UI
  document.getElementById('totalSalesCount').textContent = totalCount;
  document.getElementById('pendingOrdersCount').textContent = pendingCount;
  document.getElementById('deliveredOrdersCount').textContent = deliveredCount;
  document.getElementById('canceledOrdersCount').textContent = canceledCount;
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
  toast.className = "fixed bottom-4 right-4 px-4 py-2 rounded-md shadow-lg z-50";
  
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

// If we were using ES6 modules, we'd export these functions
// export { showLoadingState, renderSalesList, getStatusBadge, updateStatistics, showToast }; 