// js/sales.js

// Global variables
let products = [];
let customers = [];
let selectedProducts = [];
let subtotal = 0;
let discount = 0;
let total = 0;

/**
 * Centralized error handling function
 * @param {Error|Object} error - The error object
 * @param {string} defaultMessage - Default message to show if error has no message property
 */
function handleError(error, defaultMessage = 'An error occurred') {
  console.error('Error:', error);
  const message = error.message || defaultMessage;
  showToast(message, 'error');
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
  // Show loading state
  showLoadingState(true);
  
  // Load initial data - products first to ensure global products array is populated
  loadProducts()
    .then(() => {
      console.log('Products loaded, now loading sales...');
      return loadSales();
    })
    .then(() => {
      console.log('Initial data loading complete');
      showLoadingState(false);
    })
    .catch(error => {
      console.error('Error during initialization:', error);
      showToast('Error loading initial data: ' + error.message, 'error');
      showLoadingState(false);
    });
  
  // Add event listeners
  document.getElementById('searchInput').addEventListener('input', debounce(loadSales, 300));
  document.getElementById('statusSelect').addEventListener('change', loadSales);
  document.getElementById('timeSelect').addEventListener('change', loadSales);
  document.getElementById('addOrderForm').addEventListener('submit', handleAddOrder);
  document.getElementById('discountPercentage').addEventListener('input', calculateTotals);
  document.getElementById('discountProduct').addEventListener('change', calculateTotals);
  
  // Initialize bulk actions
  initBulkActions();
});

// Initialize bulk actions
function initBulkActions() {
  const selectAllCheckbox = document.getElementById('selectAllSales');
  const bulkActionSelect = document.getElementById('bulkActionSelect');
  const applyBulkActionBtn = document.getElementById('applyBulkAction');
  
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      const isChecked = this.checked;
      const checkboxes = document.querySelectorAll('.sale-checkbox');
      
      checkboxes.forEach(checkbox => {
        checkbox.checked = isChecked;
      });
      
      updateBulkActionsVisibility();
    });
  }
  
  if (bulkActionSelect) {
    bulkActionSelect.addEventListener('change', function() {
      applyBulkActionBtn.disabled = !this.value;
    });
  }
  
  if (applyBulkActionBtn) {
    applyBulkActionBtn.addEventListener('click', function() {
      handleBulkAction();
    });
  }
}

// Initialize checkboxes for sales rows
function initCheckboxes() {
  const checkboxes = document.querySelectorAll('.sale-checkbox');
  
  checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
      updateBulkActionsVisibility();
      
      // Update "select all" checkbox state
      const selectAllCheckbox = document.getElementById('selectAllSales');
      const allCheckboxes = document.querySelectorAll('.sale-checkbox');
      const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
      
      if (selectAllCheckbox) {
        selectAllCheckbox.checked = allChecked;
      }
    });
  });
}

// Update visibility of bulk actions based on selection
function updateBulkActionsVisibility() {
  const checkboxes = document.querySelectorAll('.sale-checkbox:checked');
  const bulkActionsContainer = document.getElementById('bulkActionsContainer');
  const selectedCountEl = document.getElementById('selectedCount');
  const applyBulkActionBtn = document.getElementById('applyBulkAction');
  
  if (bulkActionsContainer && selectedCountEl) {
    const count = checkboxes.length;
    
    // Update count display
    selectedCountEl.textContent = `${count} selected`;
    
    // Show/hide and enable/disable bulk actions
    if (count > 0) {
      bulkActionsContainer.classList.remove('opacity-50', 'pointer-events-none');
      if (applyBulkActionBtn) {
        applyBulkActionBtn.disabled = !document.getElementById('bulkActionSelect').value;
      }
    } else {
      bulkActionsContainer.classList.add('opacity-50', 'pointer-events-none');
      if (applyBulkActionBtn) {
        applyBulkActionBtn.disabled = true;
      }
    }
  }
}

// Handle bulk action
function handleBulkAction() {
  const selectedAction = document.getElementById('bulkActionSelect').value;
  const selectedSales = Array.from(document.querySelectorAll('.sale-checkbox:checked'))
    .map(checkbox => checkbox.getAttribute('data-sale-id'));
  
  if (!selectedAction || selectedSales.length === 0) {
    return;
  }
  
  // Ask for confirmation
  let confirmMessage = '';
  
  if (selectedAction === 'delete') {
    confirmMessage = `Are you sure you want to delete ${selectedSales.length} selected sale(s)? This action cannot be undone.`;
  } else if (selectedAction.startsWith('status_')) {
    const status = selectedAction.replace('status_', '');
    confirmMessage = `Are you sure you want to change the status of ${selectedSales.length} selected sale(s) to "${capitalizeFirstLetter(status)}"?`;
  }
  
  if (!confirm(confirmMessage)) {
    return;
  }
  
  // Show loading toast
  showToast(`Processing ${selectedSales.length} sale(s)...`, 'info');
  
  // Handle different actions
  if (selectedAction === 'delete') {
    // Delete selected sales one by one
    Promise.all(selectedSales.map(saleId => {
      return fetch('api/sales.php', {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: saleId })
      })
      .then(response => {
        if (!response.ok) {
          return response.json().then(data => {
            throw new Error(data.message || `Server responded with status ${response.status}`);
          });
        }
        return response.json();
      });
    }))
    .then(() => {
      showToast(`Successfully deleted ${selectedSales.length} sale(s)`, 'success');
      loadSales(); // Refresh the list
    })
    .catch(error => {
      console.error('Error:', error);
      showToast(`Error: ${error.message}`, 'error');
      loadSales(); // Refresh the list to show current state
    });
  } else if (selectedAction.startsWith('status_')) {
    // Change status for selected sales
    const newStatus = selectedAction.replace('status_', '');
    
    Promise.all(selectedSales.map(saleId => {
      return fetch('api/sales.php', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          id: saleId,
          status: newStatus
        })
      })
      .then(response => {
        if (!response.ok) {
          return response.json().then(data => {
            throw new Error(data.message || `Server responded with status ${response.status}`);
          });
        }
        return response.json();
      });
    }))
    .then(() => {
      showToast(`Status updated for ${selectedSales.length} sale(s)`, 'success');
      loadSales(); // Refresh the list
    })
    .catch(error => {
      console.error('Error:', error);
      showToast(`Error: ${error.message}`, 'error');
      loadSales(); // Refresh the list to show current state
    });
  }
}

// Utility function to debounce user input
function debounce(func, delay) {
  let timeout;
  return function() {
    const context = this;
    const args = arguments;
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(context, args), delay);
  };
}

// Show or hide loading state
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
 * Loads sales data with optional filters
 * Fetches sales from the API based on search, status, and time filters
 * Updates the UI with the fetched data
 */
function loadSales() {
  showLoadingState(true);
  
  const search = document.getElementById('searchInput').value;
  const status = document.getElementById('statusSelect').value;
  const timeFilter = document.getElementById('timeSelect').value;
  
  let timeParam = '';
  
  // Convert time filter to API parameter
  if (timeFilter === 'Today') {
    timeParam = 'today';
  } else if (timeFilter === 'This Week') {
    timeParam = 'week';
  } else if (timeFilter === 'This Month') {
    timeParam = 'month';
  }
  
  const url = `api/sales.php?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}${timeParam ? '&time=' + timeParam : ''}`;
  
  fetch(url)
    .then(response => response.json())
    .then(data => {
      showLoadingState(false);
      
      if (data.success) {
        renderSalesList(data.sales);
        updateStatistics(data.sales);
      } else {
        showToast(data.message || 'Error loading sales', 'error');
      }
    })
    .catch(error => {
      showLoadingState(false);
      handleError(error, 'Failed to load sales data');
    });
}

// Update statistics based on sales data
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

// Render sales list in the table
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

// Get a formatted status badge with dropdown functionality
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

// Open status dropdown for changing order status
function openStatusDropdown(saleId, currentStatus, statusCell) {
  // Remove existing dropdown if any
  const existingDropdown = document.querySelector('.status-dropdown');
  if (existingDropdown) {
    existingDropdown.remove();
  }
  
  // Create dropdown
  const dropdown = document.createElement('div');
  dropdown.className = 'status-dropdown absolute bg-white shadow-lg rounded-md py-1 z-20 border border-gray-200';
  dropdown.style.minWidth = '140px';
  
  const statuses = [
    { value: 'pending', label: 'Pending', class: 'text-yellow-700' },
    { value: 'confirmed', label: 'Confirmed', class: 'text-blue-700' },
    { value: 'delivered', label: 'Delivered', class: 'text-green-700' },
    { value: 'canceled', label: 'Canceled', class: 'text-red-700' }
  ];
  
  statuses.forEach(status => {
    const option = document.createElement('div');
    option.className = `px-4 py-2 hover:bg-gray-50 cursor-pointer text-sm ${status.class}`;
    if (status.value === currentStatus) {
      option.className += ' font-medium';
    }
    
    const innerContent = document.createElement('div');
    innerContent.className = 'flex items-center';
    innerContent.innerHTML = `
      ${status.value === currentStatus ? 
        '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>' 
        : '<div class="w-4 mr-2"></div>'}
      ${status.label}
    `;
    
    option.appendChild(innerContent);
    
    option.addEventListener('click', () => {
      updateSaleStatus(saleId, status.value);
      dropdown.remove();
    });
    
    dropdown.appendChild(option);
  });
  
  // Position dropdown
  const rect = statusCell.getBoundingClientRect();
  dropdown.style.top = `${rect.bottom + window.scrollY + 5}px`;
  dropdown.style.left = `${rect.left + window.scrollX}px`;
  
  // Add click outside listener
  document.addEventListener('click', function closeDropdown(e) {
    if (!dropdown.contains(e.target) && !statusCell.contains(e.target)) {
      dropdown.remove();
      document.removeEventListener('click', closeDropdown);
    }
  });
  
  document.body.appendChild(dropdown);
}

/**
 * Calculate subtotal, discount and total based on selected products
 * Handles product-specific discounts by aggregating totals for each product first
 * Updates the UI with calculated values
 */
function calculateTotals() {
  // Calculate subtotal
  subtotal = selectedProducts.reduce((sum, product) => sum + (product ? product.total : 0), 0);
  
  // Calculate discount
  const discountPercentage = parseFloat(document.getElementById('discountPercentage').value) || 0;
  const discountProductId = document.getElementById('discountProduct').value;
  
  if (discountProductId) {
    // Fix: Aggregate totals for each product before applying discount
    const productTotals = {};
    selectedProducts.forEach(product => {
      if (product && product.product_id) {
        productTotals[product.product_id] = (productTotals[product.product_id] || 0) + product.total;
      }
    });
    discount = (productTotals[discountProductId] || 0) * (discountPercentage / 100);
  } else {
    // Apply discount to all products
    discount = subtotal * (discountPercentage / 100);
  }
  
  // Calculate total
  total = subtotal - discount;
  
  // Update display - check which elements exist before updating
  // First try the order form elements
  let subtotalElement = document.getElementById('orderFormSubtotal');
  let discountElement = document.getElementById('discountDisplay');
  let totalElement = document.getElementById('totalDisplay');
  
  if (subtotalElement) {
    subtotalElement.textContent = '৳ ' + subtotal.toFixed(2);
  }
  
  if (discountElement) {
    discountElement.textContent = '৳ ' + discount.toFixed(2);
  }
  
  if (totalElement) {
    totalElement.textContent = '৳ ' + total.toFixed(2);
  }
  
  // For backward compatibility, also try to update using the old IDs
  subtotalElement = document.getElementById('subtotalDisplay');
  if (subtotalElement) {
    subtotalElement.textContent = '৳ ' + subtotal.toFixed(2);
  }
}

/**
 * Update the status of a sale
 * Sends PUT request to the API to change the status of the specified sale
 * 
 * @param {number} saleId - The ID of the sale to update
 * @param {string} newStatus - The new status to set (pending, confirmed, delivered, canceled)
 */
function updateSaleStatus(saleId, newStatus) {
  // Show loading indicator in toast
  showToast('Updating status...', 'info');
  
  fetch('api/sales.php', {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      id: saleId,
      status: newStatus
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showToast('Sale status updated successfully');
      loadSales(); // Refresh the list
    } else {
      showToast(data.message || 'Error updating sale status', 'error');
    }
  })
  .catch(error => {
    handleError(error, 'Failed to update sale status');
  });
}

/**
 * View details of a specific sale
 * Fetches the sale details from the API and displays them in a modal
 * 
 * @param {number} saleId - The ID of the sale to view
 */
function viewSaleDetails(saleId) {
  // Show loading indicator in toast
  showToast('Loading sale details...', 'info');
  
  // Fetch the sale details
  fetch(`api/sales.php?id=${saleId}`)
    .then(response => {
      if (!response.ok) {
        return response.json()
          .then(data => {
            throw new Error(data.message || `Server responded with status ${response.status}`);
          })
          .catch(e => {
            // If JSON parsing fails, throw a more general error with the status
            if (e instanceof SyntaxError) {
              throw new Error(`Server error (${response.status}). Please try again or contact support.`);
            }
            throw e;
          });
      }
      return response.json();
    })
    .then(data => {
      if (data.success && data.data) {
        displaySaleDetails(data.data);
      } else {
        showToast(data.message || 'Error loading sale details', 'error');
      }
    })
    .catch(error => {
      handleError(error, 'Failed to load sale details');
    });
}

// Display sale details in the modal
function displaySaleDetails(sale) {
  // Set order ID and status
  document.getElementById('orderIdDisplay').textContent = `Order #${sale.order_id ? sale.order_id.replace('ORD-', '') : sale.id}`;
  
  // Set status badge
  const statusBadge = document.getElementById('orderStatusBadge');
  let badgeClass = '';
  
  switch(sale.status) {
    case 'pending':
      badgeClass = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
      break;
    case 'confirmed':
      badgeClass = 'bg-blue-100 text-blue-800 border border-blue-200';
      break;
    case 'delivered':
      badgeClass = 'bg-green-100 text-green-800 border border-green-200';
      break;
    case 'canceled':
      badgeClass = 'bg-red-100 text-red-800 border border-red-200';
      break;
    default:
      badgeClass = 'bg-gray-100 text-gray-800 border border-gray-200';
  }
  
  statusBadge.className = `px-3 py-1 rounded-full text-xs ${badgeClass}`;
  statusBadge.textContent = capitalizeFirstLetter(sale.status);
  
  // Set customer information
  document.getElementById('customerNameDisplay').textContent = sale.customer_name || 'N/A';
  document.getElementById('customerPhoneDisplay').textContent = sale.customer_phone || 'N/A';
  document.getElementById('customerEmailDisplay').textContent = sale.customer_email || 'N/A';
  document.getElementById('customerAddressDisplay').textContent = sale.customer_address || 'N/A';
  
  // Format and set date
  const date = new Date(sale.created_at);
  const formattedDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}:${String(date.getSeconds()).padStart(2, '0')}`;
  document.getElementById('orderDateDisplay').textContent = formattedDate;
  
  // Clear and populate items table
  const itemsContainer = document.getElementById('orderItemsDisplay');
  itemsContainer.innerHTML = '';
  
  let subtotal = 0;
  
  sale.items.forEach(item => {
    const row = document.createElement('tr');
    
    // Calculate item price (subtotal / quantity)
    const price = parseFloat(item.subtotal) / parseInt(item.quantity);
    
    // Add to subtotal
    subtotal += parseFloat(item.subtotal);
    
    const itemName = item.product_name || 'Unknown Product';
    const sizeName = item.size_name || '-';
    const quantity = item.quantity || 0;
    
    row.innerHTML = `
      <td class="px-4 py-3">${itemName}</td>
      <td class="px-4 py-3 text-center">${sizeName}</td>
      <td class="px-4 py-3 text-center">${quantity}</td>
      <td class="px-4 py-3 text-right">৳ ${price.toFixed(2)}</td>
      <td class="px-4 py-3 text-right">৳ ${parseFloat(item.subtotal).toFixed(2)}</td>
    `;
    
    itemsContainer.appendChild(row);
  });
  
  // Set totals
  const discountTotal = parseFloat(sale.discount_total) || 0;
  const total = parseFloat(sale.total) || 0;
  
  // Make sure we're getting the elements from the view modal, not the order form
  const viewModalSubtotal = document.getElementById('subtotalDisplay');
  const viewModalDiscount = document.getElementById('discountDisplayView');
  const viewModalTotal = document.getElementById('totalDisplayView');
  
  if (viewModalSubtotal) {
    viewModalSubtotal.textContent = `৳ ${subtotal.toFixed(2)}`;
  }
  
  if (viewModalDiscount) {
    viewModalDiscount.textContent = `৳ ${discountTotal.toFixed(2)}`;
  }
  
  if (viewModalTotal) {
    viewModalTotal.textContent = `৳ ${total.toFixed(2)}`;
  }
  
  // Show/hide note if present
  const noteContainer = document.getElementById('orderNoteContainer');
  const noteDisplay = document.getElementById('orderNoteDisplay');
  
  if (sale.note && sale.note.trim() !== '') {
    noteDisplay.textContent = sale.note;
    noteContainer.classList.remove('hidden');
  } else {
    noteContainer.classList.add('hidden');
  }
  
  // Show the modal
  openViewOrderModal();
}

// Open and close view order modal
function openViewOrderModal() {
  document.getElementById('viewOrderModal').classList.remove('hidden');
}

function closeViewOrderModal() {
  document.getElementById('viewOrderModal').classList.add('hidden');
}

// Print order details
function printReceipt(saleId) {
  // Show loading toast
  showToast('Generating receipt...', 'info');
  
  // Fetch sale details
  fetch(`api/sales.php?id=${saleId}`)
    .then(response => {
      if (!response.ok) {
        throw new Error('Error fetching sale details');
      }
      return response.json();
    })
    .then(data => {
      if (!data.success || !data.data) {
        showToast('Invalid sale ID', 'error');
        return;
      }
      
      const sale = data.data;
      
      // Generate receipt HTML
      const receiptHTML = generateReceiptHTML(sale);
      
      // Open new window and print
      const receiptWindow = window.open('', '_blank');
      if (receiptWindow) {
        receiptWindow.document.write(receiptHTML);
        receiptWindow.document.close();
        // Wait for content to load before printing
        receiptWindow.onload = function() {
          receiptWindow.print();
        };
      } else {
        showToast('Please allow popups to print the receipt', 'error');
      }
    })
    .catch(error => {
      console.error('Error printing receipt:', error);
      showToast('Failed to print receipt: ' + error.message, 'error');
    });
}

function generateReceiptHTML(sale) {
  // Format date
  const date = new Date(sale.created_at);
  const formattedDate = date.toLocaleDateString();
  const formattedTime = date.toLocaleTimeString();
  
  // Calculate subtotal (total + discount)
  const subtotal = parseFloat(sale.total) + parseFloat(sale.discount_total);
  
  // Format customer info
  const customerInfo = `
    <div class="customer-info">
      <p><strong>Customer:</strong> ${sale.customer_name}</p>
      ${sale.customer_phone ? `<p><strong>Phone:</strong> ${sale.customer_phone}</p>` : ''}
      ${sale.customer_email ? `<p><strong>Email:</strong> ${sale.customer_email}</p>` : ''}
      ${sale.customer_address ? `<p><strong>Address:</strong> ${sale.customer_address}</p>` : ''}
    </div>
  `;
  
  // Format items
  let itemsHTML = '';
  sale.items.forEach(item => {
    const itemName = item.size_name ? `${item.product_name} (${item.size_name})` : item.product_name;
    itemsHTML += `
      <tr>
        <td>${itemName}</td>
        <td>${item.quantity}</td>
        <td>৳ ${(item.subtotal / item.quantity).toFixed(2)}</td>
        <td>৳ ${parseFloat(item.subtotal).toFixed(2)}</td>
      </tr>
    `;
  });
  
  // Complete HTML
  return `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Sales Receipt</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          margin: 0;
          padding: 20px;
          font-size: 14px;
        }
        .receipt {
          max-width: 800px;
          margin: 0 auto;
          border: 1px solid #ddd;
          padding: 20px;
        }
        .header {
          text-align: center;
          margin-bottom: 20px;
          border-bottom: 1px solid #ddd;
          padding-bottom: 10px;
        }
        .company-name {
          font-size: 24px;
          font-weight: bold;
          margin-bottom: 5px;
        }
        .receipt-info {
          display: flex;
          justify-content: space-between;
          margin-bottom: 20px;
        }
        .receipt-info > div {
          flex: 1;
        }
        table {
          width: 100%;
          border-collapse: collapse;
          margin-bottom: 20px;
        }
        th, td {
          padding: 8px;
          text-align: left;
          border-bottom: 1px solid #ddd;
        }
        th {
          background-color: #f2f2f2;
        }
        .totals {
          margin-top: 20px;
          text-align: right;
        }
        .footer {
          margin-top: 30px;
          text-align: center;
          font-size: 12px;
          color: #777;
        }
        @media print {
          body {
            padding: 0;
            margin: 0;
          }
          .receipt {
            border: none;
            width: 100%;
            max-width: none;
          }
          .no-print {
            display: none;
          }
        }
      </style>
    </head>
    <body>
      <div class="receipt">
        <div class="header">
          <div class="company-name">Inventory System</div>
          <div>Sales Receipt</div>
        </div>
        
        <div class="receipt-info">
          <div>
            <p><strong>Receipt #:</strong> ${sale.order_id || sale.id}</p>
            <p><strong>Date:</strong> ${formattedDate}</p>
            <p><strong>Time:</strong> ${formattedTime}</p>
            <p><strong>Status:</strong> ${capitalizeFirstLetter(sale.status)}</p>
          </div>
          ${customerInfo}
        </div>
        
        <table>
          <thead>
            <tr>
              <th>Item</th>
              <th>Qty</th>
              <th>Unit Price</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>
            ${itemsHTML}
          </tbody>
        </table>
        
        <div class="totals">
          <p><strong>Subtotal:</strong> ৳ ${subtotal.toFixed(2)}</p>
          <p><strong>Discount:</strong> ৳ ${parseFloat(sale.discount_total).toFixed(2)}</p>
          <p><strong>Total:</strong> ৳ ${parseFloat(sale.total).toFixed(2)}</p>
        </div>
        
        ${sale.note ? `<div class="notes"><strong>Notes:</strong> ${sale.note}</div>` : ''}
        
        <div class="footer">
          <p>Thank you for your business!</p>
        </div>
        
        <div class="no-print" style="margin-top: 20px; text-align: center;">
          <button onclick="window.print()">Print Receipt</button>
        </div>
      </div>
    </body>
    </html>
  `;
}

// Edit sale (populate the form with sale data and open modal)
function editSale(saleId) {
  // Show loading indicator in toast
  showToast('Loading sale data...', 'info');
  
  // First, load products to ensure they're available
  loadProducts()
    .then(() => {
      // Then fetch the sale details
      return fetch(`api/sales.php?id=${saleId}`);
    })
    .then(response => {
      if (!response.ok) {
        return response.json()
          .then(data => {
            throw new Error(data.message || `Server responded with status ${response.status}`);
          })
          .catch(e => {
            // If JSON parsing fails, throw a more general error with the status
            if (e instanceof SyntaxError) {
              throw new Error(`Server error (${response.status}). Please try again or contact support.`);
            }
            throw e;
          });
      }
      return response.json();
    })
    .then(data => {
      if (data.success && data.data) {
        // Open the add order modal
        openAddOrderModal();
        
        const sale = data.data;
        
        // Populate customer information - use safe access pattern
        document.getElementById('customerName').value = sale.customer_name || '';
        document.getElementById('customerPhone').value = sale.customer_phone || '';
        document.getElementById('customerEmail').value = sale.customer_email || '';
        document.getElementById('customerAddress').value = sale.customer_address || '';
        
        // Clear product rows and add new ones for each item
        document.getElementById('productRows').innerHTML = '';
        selectedProducts = [];
        
        if (sale.items && sale.items.length > 0) {
          // Directly populate items since products are already loaded
          populateProductItems(sale.items);
        } else {
          // If no items, add at least one empty row
          addProductRow();
        }
        
        // Set discount - handle potential missing fields safely
        document.getElementById('discountPercentage').value = 
          (sale.discount_percentage !== undefined && sale.discount_percentage !== null) 
            ? parseFloat(sale.discount_percentage).toFixed(2) 
            : '0';
        
        // Set status - default to 'pending' if not available
        const statusField = document.getElementById('orderStatus');
        if (statusField) {
          statusField.value = sale.status || 'pending';
        }
        
        // Set note - handle potentially missing field
        const noteField = document.getElementById('orderNote');
        if (noteField) {
          noteField.value = sale.note || '';
        }
        
        // Add a hidden input for the sale ID to track that this is an edit
        let hiddenIdInput = document.getElementById('editSaleId');
        if (!hiddenIdInput) {
          hiddenIdInput = document.createElement('input');
          hiddenIdInput.type = 'hidden';
          hiddenIdInput.id = 'editSaleId';
          hiddenIdInput.name = 'edit_sale_id';
          document.getElementById('addOrderForm').appendChild(hiddenIdInput);
        }
        hiddenIdInput.value = saleId;
        
        // Change the submit button text
        const submitBtn = document.querySelector('#addOrderForm button[type="submit"]');
        if (submitBtn) {
          submitBtn.textContent = 'Update Order';
        }
        
        showToast('Sale loaded for editing', 'success');
      } else {
        showToast(data.message || 'Error loading sale data', 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      
      // Extract and display useful error message
      let errorMsg = error.message || 'Unknown error';
      
      // Handle database column errors more gracefully
      if (errorMsg.includes('Unknown column')) {
        errorMsg = 'Database schema issue detected. The system may need an update.';
        console.error('Original error:', error.message);
      }
      
      showToast('Failed to load sale data: ' + errorMsg, 'error');
      
      // Retry option for critical errors
      if (confirm('There was a problem loading the sale data. Would you like to try again?')) {
        editSale(saleId);
      }
    });
}

// Helper function to populate product items in the order form
function populateProductItems(items) {
  console.log('Populating items:', items);
  
  if (!Array.isArray(items) || items.length === 0) {
    console.log('No items to populate');
    addProductRow();
    return;
  }
  
  // Clear existing rows first
  document.getElementById('productRows').innerHTML = '';
  
  // Create a new row for each item
  items.forEach((item, index) => {
    console.log(`Processing item ${index}:`, item);
    
    try {
      // Add a new row
      const row = addProductRow();
      
      // Get elements from the row
      const productSelect = row.querySelector('.product-select');
      
      // Check if product_id exists
      if (!item.product_id) {
        console.error(`Item at index ${index} is missing product_id`);
        return;
      }
      
      console.log(`Setting product ${item.product_id} for item ${index}`);
      
      // Set product and trigger change event
      productSelect.value = item.product_id;
      
      // Manually trigger the change event to populate sizes
      const changeEvent = new Event('change', { bubbles: true });
      productSelect.dispatchEvent(changeEvent);
      
      // Wait for size select to be populated
      setTimeout(() => {
        try {
          // Find the size select in the current row
          const sizeSelect = row.querySelector('.size-select');
          
          if (item.product_size_id && sizeSelect && !sizeSelect.disabled) {
            console.log(`Setting size ${item.product_size_id} for item ${index}`);
            sizeSelect.value = item.product_size_id;
            
            // Trigger change event
            const sizeChangeEvent = new Event('change', { bubbles: true });
            sizeSelect.dispatchEvent(sizeChangeEvent);
            
            // Set quantity with a delay to ensure size change is processed
            setTimeout(() => {
              try {
                const quantityInput = row.querySelector('.quantity-input');
                if (quantityInput && !quantityInput.disabled) {
                  console.log(`Setting quantity ${item.quantity} for item ${index}`);
                  quantityInput.value = item.quantity || 1;
                  
                  // Update row total
                  const quantityChangeEvent = new Event('input', { bubbles: true });
                  quantityInput.dispatchEvent(quantityChangeEvent);
                }
              } catch (err) {
                console.error(`Error setting quantity for item ${index}:`, err);
              }
            }, 300);
          } else {
            // If no size or sizes are disabled, set quantity directly
            setTimeout(() => {
              try {
                const quantityInput = row.querySelector('.quantity-input');
                if (quantityInput && !quantityInput.disabled) {
                  console.log(`Setting quantity ${item.quantity} directly for item ${index}`);
                  quantityInput.value = item.quantity || 1;
                  
                  // Update row total
                  const quantityChangeEvent = new Event('input', { bubbles: true });
                  quantityInput.dispatchEvent(quantityChangeEvent);
                }
              } catch (err) {
                console.error(`Error setting quantity for item ${index}:`, err);
              }
            }, 300);
          }
        } catch (err) {
          console.error(`Error setting size for item ${index}:`, err);
        }
      }, 300);
    } catch (err) {
      console.error(`Error populating item ${index}:`, err);
    }
  });
  
  // Force a recalculation of totals after all items are populated
  setTimeout(() => {
    console.log('Calculating final totals');
    calculateTotals();
  }, 1000);
}

// Delete sale
function deleteSale(saleId) {
  if (!confirm('Are you sure you want to delete this sale? This action cannot be undone.')) {
    return;
  }
  
  // Show loading indicator in toast
  showToast('Deleting sale...', 'info');
  
  fetch('api/sales.php', {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ id: saleId })
  })
  .then(response => {
    if (!response.ok) {
      return response.json().then(data => {
        throw new Error(data.message || `Server responded with status ${response.status}`);
      });
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      showToast('Sale deleted successfully');
      loadSales(); // Refresh the list
      loadProducts(); // Refresh products to update stock counts
    } else {
      showToast(data.message || 'Error deleting sale', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Failed to delete sale: ' + error.message, 'error');
  });
}

// Update an existing order
function updateOrder(orderData) {
  // Validate that we have an ID
  if (!orderData.id) {
    showToast('Error: Missing sale ID', 'error');
    return;
  }
  
  // Show loading indicator
  showToast('Updating order...', 'info');
  
  // Make sure note is properly formatted
  if (orderData.note === '') {
    orderData.note = null; // Use null instead of empty string for consistency
  }
  
  // Log what we're sending to help with debugging
  console.log('Updating order with data:', JSON.stringify(orderData));
  
  fetch('api/sales.php', {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(orderData)
  })
  .then(response => {
    if (!response.ok) {
      return response.json()
        .then(data => {
          throw new Error(data.message || `Server responded with status ${response.status}`);
        })
        .catch(e => {
          // If JSON parsing fails, throw a more general error with the status
          if (e instanceof SyntaxError) {
            throw new Error(`Server error (${response.status}). Please try again or contact support.`);
          }
          throw e;
        });
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      showToast('Order updated successfully');
      closeAddOrderModal();
      loadSales(); // Refresh the sales list
      loadProducts(); // Refresh products to get updated stock values
    } else {
      showToast(data.message || 'Error updating order', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    
    // Extract and display useful error message
    let errorMsg = error.message || 'Unknown error';
    
    // Handle known error types with more user-friendly messages
    if (errorMsg.includes('Invalid update data')) {
      errorMsg = 'Invalid update data. Please check all fields and try again.';
    }
    
    showToast('Failed to update order: ' + errorMsg, 'error');
  });
}

// Open the add order modal
function openAddOrderModal() {
  document.getElementById('addOrderModal').classList.remove('hidden');
  
  // Reset form
  document.getElementById('addOrderForm').reset();
  document.getElementById('productRows').innerHTML = '';
  selectedProducts = [];
  
  // Remove any existing edit sale ID
  const editSaleIdInput = document.getElementById('editSaleId');
  if (editSaleIdInput) {
    editSaleIdInput.remove();
  }
  
  // Reset submit button text
  const submitBtn = document.querySelector('#addOrderForm button[type="submit"]');
  if (submitBtn) {
    submitBtn.textContent = 'Create Order';
  }
  
  // Show loading indicator
  showToast('Loading product data...', 'info');
  
  // First load products, then add product row
  loadProducts()
    .then(() => {
      // Add initial product row
      addProductRow();
      
      // Reset totals display
      const subtotalElement = document.getElementById('orderFormSubtotal');
      const discountElement = document.getElementById('discountDisplay');
      const totalElement = document.getElementById('totalDisplay');
      
      if (subtotalElement) {
        subtotalElement.textContent = '৳ 0.00';
      }
      
      if (discountElement) {
        discountElement.textContent = '৳ 0.00';
      }
      
      if (totalElement) {
        totalElement.textContent = '৳ 0.00';
      }
      
      // Reset global totals
      subtotal = 0;
      discount = 0;
      total = 0;
      
      // Calculate totals (important for initialization)
      calculateTotals();
    })
    .catch(error => {
      console.error('Error initializing form:', error);
      showToast('Error loading products. Please try again.', 'error');
    });
}

// Close the add order modal
function closeAddOrderModal() {
  document.getElementById('addOrderModal').classList.add('hidden');
}

// Show a toast message
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  
  // Set toast color based on type
  if (type === 'success') {
    toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg z-50';
  } else if (type === 'error') {
    toast.className = 'fixed bottom-4 right-4 bg-red-500 text-white px-4 py-2 rounded-md shadow-lg z-50';
  } else if (type === 'info') {
    toast.className = 'fixed bottom-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-md shadow-lg z-50';
  } else {
    toast.className = 'fixed bottom-4 right-4 bg-gray-700 text-white px-4 py-2 rounded-md shadow-lg z-50';
  }
  
  // Show the toast
  toast.classList.remove('hidden');
  
  // Hide after 3 seconds
  setTimeout(() => {
    toast.classList.add('hidden');
  }, 3000);
}

// Utility function to capitalize the first letter of a string
function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

// Fetch product data by ID
function getProduct(productId) {
  if (!productId) return;
  
  showToast('Loading product information...', 'info');
  
  fetch(`api/products.php?id=${productId}`)
  .then(response => {
    if (!response.ok) {
      return response.json().then(data => {
        throw new Error(data.message || `Server responded with status ${response.status}`);
      });
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      // Fill in product details
      document.getElementById('unitPrice').value = data.product.price;
      
      // Set max quantity based on current stock
      const quantityInput = document.getElementById('quantity');
      quantityInput.max = data.product.quantity;
      
      // Reset quantity to 1 or max if stock is less
      const newQuantity = Math.min(1, data.product.quantity);
      quantityInput.value = newQuantity;
      
      // Update subtotal
      calculateSubtotal();
    } else {
      showToast(data.message || 'Failed to load product details', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Error loading product: ' + error.message, 'error');
  });
}

function addToOrder() {
  const productId = document.getElementById('product').value;
  const quantity = parseInt(document.getElementById('quantity').value);
  const unitPrice = parseFloat(document.getElementById('unitPrice').value);

  // Validate inputs
  if (!productId) {
    showToast('Please select a product', 'error');
    return;
  }
  
  if (!quantity || quantity <= 0) {
    showToast('Please enter a valid quantity', 'error');
    return;
  }
  
  if (!unitPrice || unitPrice <= 0) {
    showToast('Invalid unit price', 'error');
    return;
  }

  // Show loading indicator
  showToast('Adding to order...', 'info');
  
  const formData = new FormData();
  formData.append('sale_id', currentSaleId);
  formData.append('product_id', productId);
  formData.append('quantity', quantity);
  formData.append('unit_price', unitPrice);
  formData.append('action', 'add_item');

  fetch('api/sales.php', {
    method: 'POST',
    body: formData
  })
  .then(response => {
    if (!response.ok) {
      return response.json().then(data => {
        throw new Error(data.message || `Server responded with status ${response.status}`);
      });
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      showToast('Item added to order', 'success');
      
      // Reset form
      document.getElementById('product').value = '';
      document.getElementById('quantity').value = '1';
      document.getElementById('unitPrice').value = '';
      
      // Refresh order items and product list
      loadSaleItems(currentSaleId);
      loadProducts(); // Refresh product list to show updated quantities
    } else {
      showToast(data.message || 'Failed to add item to order', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Error adding item: ' + error.message, 'error');
  });
}

function finalizeSale() {
  // Confirm with user before finalizing
  if (!confirm('Are you sure you want to finalize this sale? This action cannot be undone.')) {
    return;
  }
  
  // Check if we have orderItems element (we might be on sales.php page which doesn't have this)
  const orderItemsTable = document.querySelector('#orderItems tbody');
  if (!orderItemsTable) {
    showToast('Cannot locate order items. Please try again or contact support.', 'error');
    return;
  }
  
  // Validate if sale has items
  if (!orderItemsTable.querySelector('tr')) {
    showToast('Cannot finalize an empty order', 'error');
    return;
  }
  
  const totalAmount = calculateTotal();
  if (totalAmount <= 0) {
    showToast('Invalid order total', 'error');
    return;
  }
  
  // Check if finalizeBtn exists (we might be on the sales.php page)
  const finalizeBtn = document.getElementById('finalizeBtn');
  
  // Show loading
  showToast('Finalizing sale...', 'info');
  if (finalizeBtn) {
    finalizeBtn.disabled = true;
  }
  
  const formData = new FormData();
  formData.append('sale_id', currentSaleId);
  formData.append('total_amount', totalAmount);
  formData.append('action', 'finalize_sale');

  fetch('api/sales.php', {
    method: 'POST',
    body: formData
  })
  .then(response => {
    if (!response.ok) {
      return response.json().then(data => {
        throw new Error(data.message || `Server responded with status ${response.status}`);
      });
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      showToast('Sale finalized successfully', 'success');
      
      // Print receipt or download invoice options
      const options = [
        'Do you want to print the receipt?',
        'Do you want to download the invoice?',
        'No action needed'
      ];
      
      const selectedOption = confirm(`Sale finalized successfully. Would you like to print the receipt or download the invoice?\n\nClick OK to select an action, or Cancel to continue without any action.`);
      
      if (selectedOption) {
        const action = prompt('Enter your choice:\n1. Print Receipt\n2. Download Invoice\n3. Cancel', '1');
        
        if (action === '1') {
          printReceipt(currentSaleId);
        } else if (action === '2') {
          downloadInvoice(currentSaleId);
        }
      }
      
      // Reset and start a new sale
      setTimeout(() => {
        if (typeof initNewSale === 'function') {
          initNewSale();
        } else {
          // If we're on sales.php, just reload the page
          loadSales();
        }
      }, 2000);
    } else {
      if (finalizeBtn) {
        finalizeBtn.disabled = false;
      }
      showToast(data.message || 'Failed to finalize sale', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    if (finalizeBtn) {
      finalizeBtn.disabled = false;
    }
    showToast('Error finalizing sale: ' + error.message, 'error');
  });
}

function calculateTotal() {
  let total = 0;
  const rows = document.querySelectorAll('#orderItems tbody tr');
  
  rows.forEach(row => {
    const subtotal = parseFloat(row.querySelector('td:last-child').textContent.replace('$', ''));
    if (!isNaN(subtotal)) {
      total += subtotal;
    }
  });
  
  return total;
}

// Function to download invoice as PDF
function downloadInvoice(saleId) {
  // Show loading toast
  showToast('Generating invoice...', 'info');
  
  // Fetch sale details
  fetch(`api/sales.php?id=${saleId}`)
    .then(response => {
      if (!response.ok) {
        throw new Error('Error fetching sale details');
      }
      return response.json();
    })
    .then(data => {
      if (!data.success || !data.data) {
        showToast('Invalid sale ID', 'error');
        return;
      }
      
      const sale = data.data;
      
      // Use generateInvoiceHTML function to get the HTML content
      const invoiceHTML = generateInvoiceHTML(sale);
      
      // Create a Blob from the HTML content
      const blob = new Blob([invoiceHTML], { type: 'text/html' });
      
      // Create a download link
      const downloadLink = document.createElement('a');
      downloadLink.href = URL.createObjectURL(blob);
      
      // Set the filename with order ID and date
      const date = new Date(sale.created_at);
      const dateStr = date.toISOString().split('T')[0]; // YYYY-MM-DD format
      downloadLink.download = `Invoice-${sale.order_id || sale.id}-${dateStr}.html`;
      
      // Append to the document, click it, and remove it
      document.body.appendChild(downloadLink);
      downloadLink.click();
      document.body.removeChild(downloadLink);
      
      showToast('Invoice downloaded successfully', 'success');
    })
    .catch(error => {
      console.error('Error downloading invoice:', error);
      showToast('Failed to download invoice: ' + error.message, 'error');
    });
}

// Generate a professional invoice HTML for download
function generateInvoiceHTML(sale) {
  // Format date
  const date = new Date(sale.created_at);
  const formattedDate = date.toLocaleDateString();
  const formattedTime = date.toLocaleTimeString();
  
  // Format invoice number
  const invoiceNumber = sale.order_id ? sale.order_id : `INV-${String(sale.id).padStart(3, '0')}`;
  
  // Calculate subtotal (total + discount)
  const subtotal = parseFloat(sale.total) + parseFloat(sale.discount_total);
  
  // Format customer info
  const customerInfo = `
    <div class="customer-details">
      <h3>Bill To:</h3>
      <p><strong>${sale.customer_name || 'Customer'}</strong></p>
      ${sale.customer_phone ? `<p>Phone: ${sale.customer_phone}</p>` : ''}
      ${sale.customer_email ? `<p>Email: ${sale.customer_email}</p>` : ''}
      ${sale.customer_address ? `<p>Address: ${sale.customer_address}</p>` : ''}
    </div>
  `;
  
  // Format items
  let itemsHTML = '';
  let itemNumber = 1;
  
  sale.items.forEach(item => {
    const itemName = item.size_name ? `${item.product_name} (${item.size_name})` : item.product_name;
    const unitPrice = (item.subtotal / item.quantity).toFixed(2);
    const itemSubtotal = parseFloat(item.subtotal).toFixed(2);
    
    itemsHTML += `
      <tr>
        <td>${itemNumber++}</td>
        <td>${itemName}</td>
        <td>${item.quantity}</td>
        <td class="text-right">৳ ${unitPrice}</td>
        <td class="text-right">৳ ${itemSubtotal}</td>
      </tr>
    `;
  });
  
  // Complete HTML with improved styling
  return `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <title>Invoice #${invoiceNumber}</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          margin: 0;
          padding: 20px;
          font-size: 14px;
          color: #333;
          background-color: #f9f9f9;
        }
        .invoice-container {
          max-width: 800px;
          margin: 0 auto;
          background-color: #fff;
          box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
          padding: 40px;
        }
        .invoice-header {
          display: flex;
          justify-content: space-between;
          margin-bottom: 40px;
          border-bottom: 2px solid #333;
          padding-bottom: 20px;
        }
        .company-details {
          flex: 2;
        }
        .company-name {
          font-size: 28px;
          font-weight: bold;
          margin-bottom: 5px;
          color: #2c3e50;
        }
        .invoice-details {
          flex: 1;
          text-align: right;
        }
        .invoice-id {
          font-size: 20px;
          font-weight: bold;
          margin-bottom: 8px;
          color: #2c3e50;
        }
        .invoice-date {
          margin-bottom: 5px;
        }
        .customer-details {
          margin-bottom: 30px;
        }
        h3 {
          font-size: 16px;
          margin-bottom: 10px;
          color: #2c3e50;
          border-bottom: 1px solid #eee;
          padding-bottom: 5px;
        }
        table {
          width: 100%;
          border-collapse: collapse;
          margin-bottom: 30px;
        }
        th {
          background-color: #f2f2f2;
          text-align: left;
          padding: 10px;
          border-bottom: 2px solid #ddd;
          font-weight: bold;
        }
        td {
          padding: 10px;
          border-bottom: 1px solid #ddd;
        }
        .text-right {
          text-align: right;
        }
        .totals-table {
          width: 350px;
          margin-left: auto;
          margin-bottom: 30px;
        }
        .totals-table td {
          padding: 5px 10px;
        }
        .totals-table .total-row {
          font-weight: bold;
          font-size: 16px;
          border-top: 2px solid #333;
        }
        .footer {
          margin-top: 50px;
          padding-top: 20px;
          border-top: 1px solid #ddd;
          text-align: center;
          font-size: 12px;
          color: #777;
        }
        .notes {
          margin-top: 30px;
          padding: 15px;
          background-color: #f9f9f9;
          border-radius: 5px;
        }
        @media print {
          body {
            background-color: #fff;
          }
          .invoice-container {
            box-shadow: none;
            padding: 0;
          }
        }
      </style>
    </head>
    <body>
      <div class="invoice-container">
        <div class="invoice-header">
          <div class="company-details">
            <div class="company-name">Inventory System</div>
            <p>123 Business Street, City</p>
            <p>Phone: +123-456-7890</p>
            <p>Email: contact@inventorysystem.com</p>
          </div>
          <div class="invoice-details">
            <div class="invoice-id">INVOICE #${invoiceNumber}</div>
            <div class="invoice-date">Date: ${formattedDate}</div>
            <div>Time: ${formattedTime}</div>
            <div>Status: ${capitalizeFirstLetter(sale.status)}</div>
          </div>
        </div>
        
        ${customerInfo}
        
        <h3>Invoice Items</h3>
        <table>
          <thead>
            <tr>
              <th width="5%">No.</th>
              <th width="45%">Item</th>
              <th width="10%">Qty</th>
              <th width="20%" class="text-right">Unit Price</th>
              <th width="20%" class="text-right">Amount</th>
            </tr>
          </thead>
          <tbody>
            ${itemsHTML}
          </tbody>
        </table>
        
        <table class="totals-table">
          <tr>
            <td>Subtotal:</td>
            <td class="text-right">৳ ${subtotal.toFixed(2)}</td>
          </tr>
          <tr>
            <td>Discount:</td>
            <td class="text-right">৳ ${parseFloat(sale.discount_total).toFixed(2)}</td>
          </tr>
          <tr class="total-row">
            <td>Total:</td>
            <td class="text-right">৳ ${parseFloat(sale.total).toFixed(2)}</td>
          </tr>
        </table>
        
        ${sale.note ? `
        <div class="notes">
          <h3>Notes</h3>
          <p>${sale.note}</p>
        </div>
        ` : ''}
        
        <div class="footer">
          <p>Thank you for your business!</p>
          <p>Invoice generated on ${new Date().toLocaleString()}</p>
        </div>
      </div>
    </body>
    </html>
  `;
}

/**
 * Loads products from the API
 * @returns {Promise} - Promise that resolves to products data
 */
function loadProducts() {
  console.log('Loading products...');
  return fetch('api/products.php')
    .then(response => {
      if (!response.ok) {
        return response.json().then(data => {
          throw new Error(data.message || `Server responded with status ${response.status}`);
        });
      }
      return response.json();
    })
    .then(data => {
      // API returns the products array directly, not inside a 'products' property
      if (Array.isArray(data)) {
        console.log('Products loaded:', data.length);
        // Store products in global variable
        products = data;
        
        // Update product dropdown in the form
        const discountProductSelect = document.getElementById('discountProduct');
        if (discountProductSelect) {
          // Save current value
          const currentValue = discountProductSelect.value;
          
          // Clear existing options (except first one)
          while (discountProductSelect.options.length > 1) {
            discountProductSelect.remove(1);
          }
          
          // Add product options
          products.forEach(product => {
            const option = document.createElement('option');
            option.value = product.id;
            option.textContent = product.name;
            discountProductSelect.appendChild(option);
          });
          
          // Restore previous selection if it exists
          if (currentValue) {
            discountProductSelect.value = currentValue;
          }
        }
        
        return products;
      } else if (data.success && data.products) {
        // Fallback for alternative API response format
        console.log('Alternative format products loaded:', data.products.length);
        products = data.products;
        return products;
      } else {
        console.error('Unexpected API response format:', data);
        throw new Error('Unexpected API response format');
      }
    })
    .catch(error => {
      console.error('Error loading products:', error);
      showToast('Failed to load products: ' + error.message, 'error');
      return [];
    });
}

/**
 * Adds a new product row to the order form
 * Creates a new row with product selection, size, quantity, and price
 */
function addProductRow() {
  const productRows = document.getElementById('productRows');
  const rowIndex = productRows.children.length;
  
  // Create the row
  const row = document.createElement('tr');
  row.className = 'product-row';
  row.dataset.index = rowIndex;
  
  // Create the row content with all required cells and inputs
  row.innerHTML = `
    <td class="px-4 py-2">
      <select class="product-select w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500 text-sm" required>
        <option value="">Select Product</option>
        ${products.map(product => `<option value="${product.id}">${product.name}</option>`).join('')}
      </select>
    </td>
    <td class="px-4 py-2">
      <select class="size-select w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500 text-sm" disabled>
        <option value="">Select Size</option>
      </select>
    </td>
    <td class="px-4 py-2">
      <input type="number" class="quantity-input w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500 text-sm text-center" 
             value="1" min="1" max="100" disabled required>
    </td>
    <td class="px-4 py-2">
      <input type="number" class="price-input w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500 text-sm text-right" 
             value="0.00" min="0" step="0.01" disabled required>
    </td>
    <td class="px-4 py-2 text-right font-medium row-total">
      ৳ 0.00
    </td>
    <td class="px-4 py-2 text-center">
      <button type="button" class="remove-row-btn text-red-500 hover:text-red-700" ${rowIndex === 0 ? 'disabled style="opacity: 0.5;"' : ''}>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
      </button>
    </td>
  `;
  
  productRows.appendChild(row);
  
  // Add event listeners to the new row elements
  const productSelect = row.querySelector('.product-select');
  const sizeSelect = row.querySelector('.size-select');
  const quantityInput = row.querySelector('.quantity-input');
  const priceInput = row.querySelector('.price-input');
  const removeButton = row.querySelector('.remove-row-btn');
  
  // Product selection handler
  productSelect.addEventListener('change', function() {
    handleProductSelect(this, rowIndex);
  });
  
  // Size selection handler
  sizeSelect.addEventListener('change', function() {
    handleSizeSelect(this, rowIndex);
  });
  
  // Quantity change handler
  quantityInput.addEventListener('input', function() {
    handleQuantityChange(this, rowIndex);
  });
  
  // Price change handler
  priceInput.addEventListener('input', function() {
    handlePriceChange(this, rowIndex);
  });
  
  // Remove row handler
  if (removeButton) {
    removeButton.addEventListener('click', function() {
      handleRemoveRow(this, rowIndex);
    });
  }
  
  // Add some spacing for better UX
  selectedProducts[rowIndex] = null;
  return row;
}

/**
 * Handles product selection in the order form
 * Loads size options for the selected product
 * 
 * @param {HTMLElement} selectElement - The product select element
 * @param {number} rowIndex - Index of the row in the form
 */
function handleProductSelect(selectElement, rowIndex) {
  const row = selectElement.closest('tr');
  const sizeSelect = row.querySelector('.size-select');
  const quantityInput = row.querySelector('.quantity-input');
  const priceInput = row.querySelector('.price-input');
  
  // Clear selected product data
  selectedProducts[rowIndex] = null;
  
  // Reset and disable size, quantity, and price fields
  sizeSelect.innerHTML = '<option value="">Select Size</option>';
  sizeSelect.disabled = true;
  quantityInput.value = 1;
  quantityInput.disabled = true;
  priceInput.value = "0.00";
  priceInput.disabled = true;
  
  // Update row total
  updateRowTotal(rowIndex);
  
  // If no product selected, exit
  if (!selectElement.value) {
    return;
  }
  
  // Find the selected product
  const productId = parseInt(selectElement.value);
  const product = products.find(p => p.id === productId);
  
  if (!product) {
    console.error('Product not found:', productId);
    return;
  }
  
  // If product has sizes, populate size options
  if (product.sizes && product.sizes.length > 0) {
    product.sizes.forEach(size => {
      const option = document.createElement('option');
      option.value = size.id;
      option.textContent = size.size_name;
      option.dataset.price = size.price || product.selling_price;
      option.dataset.stock = size.stock || 0;
      sizeSelect.appendChild(option);
    });
    sizeSelect.disabled = false;
  } else {
    // If no sizes, enable quantity and set price directly
    quantityInput.disabled = false;
    quantityInput.max = product.stock || 100;
    priceInput.value = parseFloat(product.selling_price).toFixed(2);
    priceInput.disabled = false;
    
    // Store selected product data
    selectedProducts[rowIndex] = {
      product_id: productId,
      product_name: product.name,
      product_size_id: null,
      size_name: null,
      price: parseFloat(product.selling_price),
      quantity: parseInt(quantityInput.value),
      total: parseFloat(product.selling_price) * parseInt(quantityInput.value)
    };
    
    // Update row total
    updateRowTotal(rowIndex);
  }
  
  // Update totals after product selection
  calculateTotals();
}

/**
 * Handles size selection in the order form
 * Sets price based on the selected size and enables quantity input
 * 
 * @param {HTMLElement} selectElement - The size select element
 * @param {number} rowIndex - Index of the row in the form
 */
function handleSizeSelect(selectElement, rowIndex) {
  const row = selectElement.closest('tr');
  const productSelect = row.querySelector('.product-select');
  const quantityInput = row.querySelector('.quantity-input');
  const priceInput = row.querySelector('.price-input');
  
  // Reset quantity and price inputs
  quantityInput.value = 1;
  quantityInput.disabled = true;
  priceInput.value = "0.00";
  priceInput.disabled = true;
  
  // Clear selected product data
  selectedProducts[rowIndex] = null;
  
  // If no size selected, exit
  if (!selectElement.value) {
    updateRowTotal(rowIndex);
    return;
  }
  
  // Get selected option
  const selectedOption = selectElement.options[selectElement.selectedIndex];
  const price = parseFloat(selectedOption.dataset.price);
  const stock = parseInt(selectedOption.dataset.stock);
  
  // Enable quantity input with max limit
  quantityInput.max = stock;
  quantityInput.disabled = false;
  
  // Set price for the selected size
  priceInput.value = price.toFixed(2);
  priceInput.disabled = false;
  
  // Find the selected product
  const productId = parseInt(productSelect.value);
  const product = products.find(p => p.id === productId);
  
  // Store selected product data
  selectedProducts[rowIndex] = {
    product_id: productId,
    product_name: product ? product.name : 'Unknown Product',
    product_size_id: parseInt(selectElement.value),
    size_name: selectedOption.textContent,
    price: price,
    quantity: parseInt(quantityInput.value),
    total: price * parseInt(quantityInput.value)
  };
  
  // Update row total
  updateRowTotal(rowIndex);
  
  // Update totals
  calculateTotals();
}

/**
 * Handles quantity changes in the order form
 * Updates row total and overall totals
 * 
 * @param {HTMLElement} inputElement - The quantity input element
 * @param {number} rowIndex - Index of the row in the form
 */
function handleQuantityChange(inputElement, rowIndex) {
  // Ensure non-negative integer value
  let quantity = parseInt(inputElement.value) || 0;
  if (quantity < 1) {
    quantity = 1;
    inputElement.value = 1;
  }
  
  const max = parseInt(inputElement.max) || 100;
  if (quantity > max) {
    quantity = max;
    inputElement.value = max;
    showToast(`Maximum available quantity is ${max}`, 'warning');
  }
  
  // Update selected product data
  if (selectedProducts[rowIndex]) {
    selectedProducts[rowIndex].quantity = quantity;
    selectedProducts[rowIndex].total = selectedProducts[rowIndex].price * quantity;
  }
  
  // Update row total
  updateRowTotal(rowIndex);
  
  // Update totals
  calculateTotals();
}

/**
 * Handles price changes in the order form
 * Updates row total and overall totals
 * 
 * @param {HTMLElement} inputElement - The price input element
 * @param {number} rowIndex - Index of the row in the form
 */
function handlePriceChange(inputElement, rowIndex) {
  // Ensure non-negative value
  let price = parseFloat(inputElement.value) || 0;
  if (price < 0) {
    price = 0;
    inputElement.value = "0.00";
  }
  
  // Format price to 2 decimal places
  inputElement.value = price.toFixed(2);
  
  // Update selected product data
  if (selectedProducts[rowIndex]) {
    selectedProducts[rowIndex].price = price;
    selectedProducts[rowIndex].total = price * selectedProducts[rowIndex].quantity;
  }
  
  // Update row total
  updateRowTotal(rowIndex);
  
  // Update totals
  calculateTotals();
}

/**
 * Handles removing a row from the order form
 * Updates row indices and recalculates totals
 * 
 * @param {HTMLElement} buttonElement - The remove button element
 * @param {number} rowIndex - Index of the row to remove
 */
function handleRemoveRow(buttonElement, rowIndex) {
  const row = buttonElement.closest('tr');
  const productRows = document.getElementById('productRows');
  
  // Can't remove the last row
  if (productRows.children.length <= 1) {
    showToast('Cannot remove the last row', 'warning');
    return;
  }
  
  // Remove row and update selectedProducts array
  row.remove();
  selectedProducts.splice(rowIndex, 1);
  
  // Update indices for remaining rows
  Array.from(productRows.children).forEach((row, index) => {
    row.dataset.index = index;
    
    // Update event listeners with new indices
    const productSelect = row.querySelector('.product-select');
    const sizeSelect = row.querySelector('.size-select');
    const quantityInput = row.querySelector('.quantity-input');
    const priceInput = row.querySelector('.price-input');
    const removeButton = row.querySelector('.remove-row-btn');
    
    // Remove old event listeners (not strictly necessary due to closure scope)
    
    // Add new event listeners with updated index
    productSelect.addEventListener('change', function() {
      handleProductSelect(this, index);
    });
    
    sizeSelect.addEventListener('change', function() {
      handleSizeSelect(this, index);
    });
    
    quantityInput.addEventListener('input', function() {
      handleQuantityChange(this, index);
    });
    
    priceInput.addEventListener('input', function() {
      handlePriceChange(this, index);
    });
    
    removeButton.addEventListener('click', function() {
      handleRemoveRow(this, index);
    });
    
    // Disable remove button for the first row
    if (index === 0) {
      removeButton.disabled = true;
      removeButton.style.opacity = '0.5';
    } else {
      removeButton.disabled = false;
      removeButton.style.opacity = '1';
    }
  });
  
  // Update totals
  calculateTotals();
}

/**
 * Updates the total for a specific row
 * 
 * @param {number} rowIndex - Index of the row to update
 */
function updateRowTotal(rowIndex) {
  const rows = document.querySelectorAll('#productRows tr');
  if (rowIndex >= rows.length) return;
  
  const row = rows[rowIndex];
  const totalCell = row.querySelector('.row-total');
  
  if (selectedProducts[rowIndex]) {
    totalCell.textContent = '৳ ' + selectedProducts[rowIndex].total.toFixed(2);
  } else {
    totalCell.textContent = '৳ 0.00';
  }
}

/**
 * Handles the add order form submission
 * Creates a new order or updates an existing one
 * 
 * @param {Event} e - The form submission event
 */
function handleAddOrder(e) {
  e.preventDefault();
  
  // Check if we have products selected
  const hasProducts = selectedProducts.some(product => product !== null);
  if (!hasProducts) {
    showToast('Please add at least one product to the order', 'error');
    return;
  }
  
  // Get customer information
  const customerName = document.getElementById('customerName').value;
  if (!customerName) {
    showToast('Customer name is required', 'error');
    return;
  }
  
  // Prepare the order data
  const orderData = {
    customer_name: customerName,
    customer_phone: document.getElementById('customerPhone').value,
    customer_email: document.getElementById('customerEmail').value,
    customer_address: document.getElementById('customerAddress').value,
    status: document.getElementById('orderStatus').value,
    discount_percentage: parseFloat(document.getElementById('discountPercentage').value) || 0,
    note: document.getElementById('orderNote').value,
    items: selectedProducts.filter(product => product !== null).map(product => ({
      product_id: product.product_id,
      product_size_id: product.product_size_id,
      quantity: product.quantity,
      price: product.price
    }))
  };
  
  // Check if this is an edit (update) or a new order
  const editSaleId = document.getElementById('editSaleId');
  
  if (editSaleId && editSaleId.value) {
    // This is an edit/update
    orderData.id = editSaleId.value;
    updateOrder(orderData);
  } else {
    // This is a new order
    createOrder(orderData);
  }
}

/**
 * Creates a new order via API
 * 
 * @param {Object} orderData - The order data to send
 */
function createOrder(orderData) {
  // Show loading indicator
  showToast('Creating order...', 'info');
  
  fetch('api/sales.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(orderData)
  })
  .then(response => {
    if (!response.ok) {
      return response.json().then(data => {
        throw new Error(data.message || `Server responded with status ${response.status}`);
      });
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      showToast('Order created successfully', 'success');
      closeAddOrderModal();
      loadSales(); // Refresh the sales list
      loadProducts(); // Refresh products to get updated stock values
    } else {
      showToast(data.message || 'Error creating order', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Failed to create order: ' + error.message, 'error');
  });
}