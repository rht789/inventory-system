// js/sales.js

// Global variables
let products = [];
let customers = [];
let selectedProducts = [];
let subtotal = 0;
let discount = 0;
let total = 0;

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
  // Show loading state
  showLoadingState(true);
  
  // Load initial data
  loadSales();
  loadProducts();
  
  // Add event listeners
  document.getElementById('searchInput').addEventListener('input', debounce(loadSales, 300));
  document.getElementById('statusSelect').addEventListener('change', loadSales);
  document.getElementById('timeSelect').addEventListener('change', loadSales);
  document.getElementById('addOrderForm').addEventListener('submit', handleAddOrder);
  document.getElementById('discountPercentage').addEventListener('input', calculateTotals);
  
  // Add initial product row
  addProductRow();
});

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

// Load sales data with filters
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
      console.error('Error:', error);
      showToast('Failed to load sales data', 'error');
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
  const emptyElement = document.getElementById('empty-sales-placeholder');
  const tableElement = document.querySelector('.overflow-x-auto');
  
  tableBody.innerHTML = '';
  
  if (!sales || sales.length === 0) {
    tableElement.classList.add('hidden');
    emptyElement.classList.remove('hidden');
    return;
  }
  
  // Show table and hide empty state
  tableElement.classList.remove('hidden');
  emptyElement.classList.add('hidden');
  
  sales.forEach(sale => {
    const row = document.createElement('tr');
    row.className = 'hover:bg-gray-50 transition-colors duration-150';
    
    // Format products string
    const productsText = sale.items.map(item => {
      const sizeText = item.size_name ? ` (${item.size_name})` : '';
      return `${item.product_name}${sizeText}`;
    }).join(', ');
    
    // Format date and time
    const date = new Date(sale.created_at);
    const formattedDate = date.toISOString().split('T')[0];
    const formattedTime = date.toTimeString().split(' ')[0].substring(0, 5);
    
    // Create status badge element
    const statusBadge = getStatusBadge(sale.status);
    
    row.innerHTML = `
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
    
    // Add dropdown for status change
    const statusCell = row.querySelector('td:nth-child(5)');
    statusCell.addEventListener('click', function(e) {
      e.stopPropagation();
      openStatusDropdown(sale.id, sale.status, statusCell);
    });
    
    tableBody.appendChild(row);
  });
}

// Get status badge HTML based on status
function getStatusBadge(status) {
  let badgeClass = '';
  
  switch(status) {
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
  
  return `
    <div class="group cursor-pointer flex items-center justify-center">
      <span class="px-3 py-1 rounded-full text-xs inline-flex items-center ${badgeClass}">
        ${capitalizeFirstLetter(status)}
      </span>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </div>
  `;
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

// Update the status of a sale
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
    console.error('Error:', error);
    showToast('Failed to update sale status', 'error');
  });
}

// Load products for the order form
function loadProducts() {
  fetch('api/products.php')
    .then(response => response.json())
    .then(data => {
      if (data && Array.isArray(data)) {
        // Handle case where API returns array directly
        products = data;
        // After loading products, initialize any product dropdowns
        initializeProductDropdowns();
      } else if (data && data.products && Array.isArray(data.products)) {
        // Handle case where API returns {success: true, products: [...]}
        products = data.products;
        // After loading products, initialize any product dropdowns
        initializeProductDropdowns();
      } else {
        console.error("Unexpected product data format:", data);
        showToast('Error loading products: Invalid data format', 'error');
      }
    })
    .catch(error => {
      console.error('Error loading products:', error);
      showToast('Failed to load products data', 'error');
    });
}

// Initialize product dropdowns after products are loaded
function initializeProductDropdowns() {
  // Update existing product rows with the loaded products
  const productSelects = document.querySelectorAll('.product-select');
  productSelects.forEach(select => {
    // Save current selection
    const currentValue = select.value;
    
    // Clear and rebuild options
    select.innerHTML = '<option value="">Select product</option>';
    
    // Add product options
    products.forEach(product => {
      const option = document.createElement('option');
      option.value = product.id;
      option.textContent = product.name;
      select.appendChild(option);
    });
    
    // Restore selection if possible
    if (currentValue) {
      select.value = currentValue;
    }
  });
  
  // Update any other elements that depend on the products list
  updateDiscountProductDropdown();
}

// Search customers for autocomplete
function searchCustomers(query) {
  fetch(`api/customers.php?search=${encodeURIComponent(query)}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        customers = data.customers;
        showCustomerAutocomplete(customers);
      }
    })
    .catch(error => {
      console.error('Error:', error);
    });
}

// Show customer autocomplete dropdown
function showCustomerAutocomplete(customers) {
  const inputField = document.getElementById('customerName');
  const rect = inputField.getBoundingClientRect();
  
  // Remove existing dropdown if any
  const existingDropdown = document.querySelector('.customer-autocomplete');
  if (existingDropdown) {
    existingDropdown.remove();
  }
  
  if (customers.length === 0) return;
  
  // Create dropdown
  const dropdown = document.createElement('div');
  dropdown.className = 'customer-autocomplete absolute bg-white shadow-md rounded-md py-1 z-20';
  dropdown.style.width = `${inputField.offsetWidth}px`;
  dropdown.style.maxHeight = '200px';
  dropdown.style.overflowY = 'auto';
  
  customers.forEach(customer => {
    const option = document.createElement('div');
    option.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm';
    option.textContent = customer.name;
    
    option.addEventListener('click', () => {
      inputField.value = customer.name;
      document.getElementById('customerPhone').value = customer.phone || '';
      document.getElementById('customerEmail').value = customer.email || '';
      document.getElementById('customerAddress').value = customer.address || '';
      dropdown.remove();
    });
    
    dropdown.appendChild(option);
  });
  
  // Position dropdown
  dropdown.style.top = `${rect.bottom + window.scrollY}px`;
  dropdown.style.left = `${rect.left + window.scrollX}px`;
  
  // Add click outside listener
  document.addEventListener('click', function closeDropdown(e) {
    if (!dropdown.contains(e.target) && !inputField.contains(e.target)) {
      dropdown.remove();
      document.removeEventListener('click', closeDropdown);
    }
  });
  
  document.body.appendChild(dropdown);
}

// Add customer name input event listener for autocomplete
document.addEventListener('DOMContentLoaded', function() {
  const customerNameInput = document.getElementById('customerName');
  if (customerNameInput) {
    customerNameInput.addEventListener('input', debounce(function() {
      const query = this.value.trim();
      if (query.length >= 2) {
        searchCustomers(query);
      }
    }, 300));
  }
});

// Add a new product row to the order form
function addProductRow() {
  const productRows = document.getElementById('productRows');
  const newRow = document.createElement('tr');
  const rowIndex = productRows.children.length;
  
  newRow.className = 'hover:bg-gray-50 transition-colors duration-150';
  
  newRow.innerHTML = `
    <td class="px-4 py-3 w-1/3">
      <select name="product_id[]" class="product-select w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500 bg-white" 
              onchange="handleProductSelect(this, ${rowIndex})">
        <option value="">Select product</option>
        ${products.map(p => `<option value="${p.id}">${p.name}</option>`).join('')}
      </select>
    </td>
    <td class="px-4 py-3 w-1/5">
      <select name="product_size_id[]" class="size-select w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500 bg-white" 
              onchange="handleSizeSelect(this, ${rowIndex})" disabled>
        <option value="">Select size</option>
      </select>
    </td>
    <td class="px-4 py-3 w-20">
      <input type="number" name="quantity[]" min="1" value="1" 
             class="quantity-input w-full border border-gray-300 rounded-md py-2 px-3 text-center focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500 bg-white"
             onchange="updateRowTotal(${rowIndex})" disabled />
    </td>
    <td class="px-4 py-3 w-24">
      <input type="number" name="price[]" step="0.01" value="0.00" 
             class="price-input w-full border border-gray-300 rounded-md py-2 px-3 text-right focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500 bg-gray-50" readonly />
    </td>
    <td class="px-4 py-3 w-24">
      <input type="number" name="total[]" step="0.01" value="0.00" 
             class="total-input w-full border border-gray-300 rounded-md py-2 px-3 text-right focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500 bg-gray-50 font-medium" readonly />
    </td>
    <td class="px-4 py-3 text-center w-12">
      <button type="button" class="text-red-500 hover:text-red-700 transition-colors" onclick="removeProductRow(this)">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
      </button>
    </td>
  `;
  
  productRows.appendChild(newRow);
  
  // Update discount product dropdown
  updateDiscountProductDropdown();
}

// Handle product selection in order form
function handleProductSelect(select, rowIndex) {
  const row = select.closest('tr');
  const sizeSelect = row.querySelector('.size-select');
  const quantityInput = row.querySelector('.quantity-input');
  const priceInput = row.querySelector('.price-input');
  
  // Reset values
  sizeSelect.innerHTML = '<option value="">Select size</option>';
  sizeSelect.disabled = true;
  quantityInput.disabled = true;
  priceInput.value = '0.00';
  
  const productId = select.value;
  if (!productId) return;
  
  // Find selected product
  const product = products.find(p => p.id == productId);
  if (!product) return;
  
  // Set price from product
  priceInput.value = parseFloat(product.selling_price).toFixed(2);
  
  // Populate size dropdown
  if (product.sizes && product.sizes.length > 0) {
    product.sizes.forEach(size => {
      const option = document.createElement('option');
      option.value = size.id;
      option.textContent = `${size.size_name} (${size.stock} in stock)`;
      option.disabled = size.stock <= 0;
      sizeSelect.appendChild(option);
    });
    sizeSelect.disabled = false;
  } else {
    // If no sizes, enable quantity directly
    quantityInput.disabled = false;
    // Limit quantity to available stock
    quantityInput.max = product.stock || 999;
    updateRowTotal(rowIndex);
  }
  
  // Highlight the row to make it more visible
  row.classList.add('bg-gray-50');
  
  // Update discount product dropdown
  updateDiscountProductDropdown();
}

// Handle size selection in order form
function handleSizeSelect(select, rowIndex) {
  const row = select.closest('tr');
  const productSelect = row.querySelector('.product-select');
  const quantityInput = row.querySelector('.quantity-input');
  const priceInput = row.querySelector('.price-input');
  
  const productId = productSelect.value;
  const sizeId = select.value;
  
  if (!productId || !sizeId) {
    quantityInput.disabled = true;
    return;
  }
  
  // Find selected product and size
  const product = products.find(p => p.id == productId);
  if (!product) return;
  
  // Set price from product
  priceInput.value = parseFloat(product.selling_price).toFixed(2);
  
  // Enable quantity input
  quantityInput.disabled = false;
  quantityInput.max = product.sizes.find(s => s.id == sizeId)?.stock || 999;
  
  // Update row total
  updateRowTotal(rowIndex);
}

// Update the total for a single product row
function updateRowTotal(rowIndex) {
  const row = document.querySelectorAll('#productRows tr')[rowIndex];
  if (!row) return;
  
  const quantityInput = row.querySelector('.quantity-input');
  const priceInput = row.querySelector('.price-input');
  const totalInput = row.querySelector('.total-input');
  
  const quantity = parseInt(quantityInput.value) || 0;
  const price = parseFloat(priceInput.value) || 0;
  const rowTotal = quantity * price;
  
  totalInput.value = rowTotal.toFixed(2);
  
  // Update selected products array
  updateSelectedProduct(rowIndex, {
    product_id: row.querySelector('.product-select').value,
    product_size_id: row.querySelector('.size-select').value,
    quantity: quantity,
    price: price,
    total: rowTotal
  });
  
  // Recalculate order totals
  calculateTotals();
}

// Update the selected products array
function updateSelectedProduct(index, product) {
  selectedProducts[index] = product;
  
  // Filter out empty product selections
  selectedProducts = selectedProducts.filter(p => p && p.product_id);
}

// Remove a product row from the order form
function removeProductRow(button) {
  const row = button.closest('tr');
  const tbody = row.parentNode;
  
  // Don't remove the last row
  if (tbody.children.length <= 1) {
    showToast('At least one product is required', 'error');
    return;
  }
  
  // Get the row index
  const rowIndex = Array.from(tbody.children).indexOf(row);
  
  // Remove from selectedProducts
  if (selectedProducts[rowIndex]) {
    selectedProducts.splice(rowIndex, 1);
  }
  
  // Remove the row from DOM
  row.remove();
  
  // Recalculate totals
  calculateTotals();
  
  // Update discount product dropdown
  updateDiscountProductDropdown();
}

// Calculate subtotal, discount and total
function calculateTotals() {
  // Calculate subtotal
  subtotal = selectedProducts.reduce((sum, product) => sum + (product ? product.total : 0), 0);
  
  // Calculate discount
  const discountPercentage = parseFloat(document.getElementById('discountPercentage').value) || 0;
  discount = subtotal * (discountPercentage / 100);
  
  // Calculate total
  total = subtotal - discount;
  
  // Update display
  document.getElementById('subtotalDisplay').textContent = '৳ ' + subtotal.toFixed(2);
  document.getElementById('discountDisplay').textContent = '৳ ' + discount.toFixed(2);
  document.getElementById('totalDisplay').textContent = '৳ ' + total.toFixed(2);
}

// Update the discount product dropdown
function updateDiscountProductDropdown() {
  const discountProduct = document.getElementById('discountProduct');
  discountProduct.innerHTML = '<option value="">Select product</option>';
  
  // Get all selected products
  const productSelects = document.querySelectorAll('.product-select');
  const selectedProductsMap = new Map();
  
  productSelects.forEach((select, index) => {
    if (select.value) {
      const product = products.find(p => p.id == select.value);
      if (product) {
        selectedProductsMap.set(product.id, product.name);
      }
    }
  });
  
  // Add unique products to dropdown
  for (const [id, name] of selectedProductsMap.entries()) {
    const option = document.createElement('option');
    option.value = id;
    option.textContent = name;
    discountProduct.appendChild(option);
  }
}

// Handle order form submission
function handleAddOrder(e) {
  e.preventDefault();
  
  // Validate form
  if (!validateOrderForm()) {
    return;
  }
  
  // Gather form data
  const customer = {
    name: document.getElementById('customerName').value,
    phone: document.getElementById('customerPhone').value,
    email: document.getElementById('customerEmail').value,
    address: document.getElementById('customerAddress').value
  };
  
  // Prepare items data
  const items = selectedProducts.map(product => {
    return {
      product_id: product.product_id,
      product_size_id: product.product_size_id || null,
      quantity: product.quantity,
      price: product.price,
      subtotal: product.total,
      discount: 0 // Individual item discounts not implemented yet
    };
  });
  
  // Check if we're editing an existing sale
  const editSaleId = document.getElementById('editSaleId')?.value;
  
  // Prepare complete order data
  const orderData = {
    customer: customer,
    items: items,
    discount: parseFloat(document.getElementById('discountPercentage').value) || 0,
    status: document.getElementById('orderStatus').value,
    note: document.getElementById('orderNote').value
  };
  
  // Add the ID if editing
  if (editSaleId) {
    orderData.id = editSaleId; // Add the sale ID to the request data
    updateOrder(orderData);
  } else {
    createOrder(orderData);
  }
}

// Validate the order form
function validateOrderForm() {
  // Check customer name
  if (!document.getElementById('customerName').value.trim()) {
    showToast('Customer name is required', 'error');
    return false;
  }
  
  // Check products
  if (selectedProducts.length === 0) {
    showToast('At least one product is required', 'error');
    return false;
  }
  
  // Make sure all products have quantities
  for (let i = 0; i < selectedProducts.length; i++) {
    const product = selectedProducts[i];
    if (!product || !product.product_id) {
      showToast('Please select a product', 'error');
      return false;
    }
    
    if (product.quantity <= 0) {
      showToast('Quantity must be greater than zero', 'error');
      return false;
    }
  }
  
  // Check discount percentage
  const discountPercentage = parseFloat(document.getElementById('discountPercentage').value);
  if (discountPercentage < 0 || discountPercentage > 100) {
    showToast('Discount percentage must be between 0 and 100', 'error');
    return false;
  }
  
  return true;
}

// Create a new order
function createOrder(orderData) {
  fetch('api/sales.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(orderData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showToast('Order created successfully');
      closeAddOrderModal();
      loadSales(); // Refresh the sales list
      loadProducts(); // Refresh products to get updated stock values
    } else {
      showToast(data.message || 'Error creating order', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Failed to create order', 'error');
  });
}

// View sale details
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
      if (data.success && data.sale) {
        displaySaleDetails(data.sale);
      } else {
        showToast(data.message || 'Error loading sale details', 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showToast('Failed to load sale details: ' + error.message, 'error');
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
  
  document.getElementById('subtotalDisplay').textContent = `৳ ${subtotal.toFixed(2)}`;
  document.getElementById('discountDisplayView').textContent = `৳ ${discountTotal.toFixed(2)}`;
  document.getElementById('totalDisplayView').textContent = `৳ ${total.toFixed(2)}`;
  
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
function printOrderDetails() {
  const printContent = document.getElementById('orderDetailContent').cloneNode(true);
  
  // Create a new window for printing
  const printWindow = window.open('', '_blank', 'height=600,width=800');
  
  // Add print-specific styling
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Order Details</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          margin: 0;
          padding: 20px;
          color: #333;
        }
        .print-header {
          text-align: center;
          margin-bottom: 20px;
          padding-bottom: 10px;
          border-bottom: 1px solid #ddd;
        }
        .company-name {
          font-size: 24px;
          font-weight: bold;
          margin-bottom: 5px;
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
          font-weight: bold;
        }
        .totals {
          width: 300px;
          margin-left: auto;
          margin-right: 0;
        }
        .totals div {
          display: flex;
          justify-content: space-between;
          padding: 5px 0;
        }
        .footer {
          margin-top: 30px;
          text-align: center;
          font-size: 12px;
          color: #777;
        }
      </style>
    </head>
    <body>
      <div class="print-header">
        <div class="company-name">Inventory System</div>
        <div>Order Details</div>
      </div>
      ${printContent.outerHTML}
      <div class="footer">
        <p>Thank you for your business!</p>
      </div>
    </body>
    </html>
  `);
  
  printWindow.document.close();
  
  // Print after a short delay to ensure content is loaded
  setTimeout(() => {
    printWindow.print();
    printWindow.addEventListener('afterprint', () => {
      printWindow.close();
    });
  }, 500);
}

// Edit sale (populate the form with sale data and open modal)
function editSale(saleId) {
  // Show loading indicator in toast
  showToast('Loading sale data...', 'info');
  
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
      if (data.success && data.sale) {
        // Open the add order modal
        openAddOrderModal();
        
        const sale = data.sale;
        
        // Populate customer information - use safe access pattern
        document.getElementById('customerName').value = sale.customer_name || '';
        document.getElementById('customerPhone').value = sale.customer_phone || '';
        document.getElementById('customerEmail').value = sale.customer_email || '';
        document.getElementById('customerAddress').value = sale.customer_address || '';
        
        // Clear product rows and add new ones for each item
        document.getElementById('productRows').innerHTML = '';
        selectedProducts = [];
        
        if (sale.items && sale.items.length > 0) {
          // Ensure products are loaded first
          const waitForProducts = () => {
            if (products.length === 0) {
              loadProducts()
                .then(() => {
                  populateProductItems(sale.items);
                })
                .catch(error => {
                  console.error('Error loading products:', error);
                  showToast('Failed to load products: ' + error.message, 'error');
                });
            } else {
              populateProductItems(sale.items);
            }
          };
          
          waitForProducts();
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
  if (!Array.isArray(items) || items.length === 0) {
    console.log('No items to populate');
    addProductRow();
    return;
  }
  
  items.forEach((item, index) => {
    try {
      addProductRow();
      
      // Set values on the product row
      const rows = document.querySelectorAll('#productRows tr');
      const row = rows[index];
      
      if (!row) {
        console.error(`Row ${index} not found`);
        return;
      }
      
      const productSelect = row.querySelector('.product-select');
      if (!productSelect) {
        console.error(`Product select not found in row ${index}`);
        return;
      }
      
      // Check if product_id exists
      if (!item.product_id) {
        console.error(`Item at index ${index} is missing product_id`);
        return;
      }
      
      productSelect.value = item.product_id;
      
      // Trigger product select handler
      handleProductSelect(productSelect, index);
      
      // Set size if available
      setTimeout(() => {
        try {
          if (item.product_size_id) {
            const sizeSelect = row.querySelector('.size-select');
            if (sizeSelect && !sizeSelect.disabled) {
              sizeSelect.value = item.product_size_id;
              
              // Trigger size select handler
              handleSizeSelect(sizeSelect, index);
            }
          }
          
          // Set quantity (with a slight delay to ensure handlers have finished)
          setTimeout(() => {
            try {
              const quantityInput = row.querySelector('.quantity-input');
              if (quantityInput && !quantityInput.disabled) {
                quantityInput.value = item.quantity || 1;
                
                // Update row total
                updateRowTotal(index);
              }
            } catch (err) {
              console.error(`Error setting quantity for item ${index}:`, err);
            }
          }, 100);
        } catch (err) {
          console.error(`Error setting size for item ${index}:`, err);
        }
      }, 100);
    } catch (err) {
      console.error(`Error populating item ${index}:`, err);
    }
  });
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
  
  // Add initial product row
  addProductRow();
  
  // Reset totals
  calculateTotals();
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
  
  // Validate if sale has items
  if (!document.querySelector('#orderItems tbody tr')) {
    showToast('Cannot finalize an empty order', 'error');
    return;
  }
  
  const totalAmount = calculateTotal();
  if (totalAmount <= 0) {
    showToast('Invalid order total', 'error');
    return;
  }
  
  // Show loading
  showToast('Finalizing sale...', 'info');
  document.getElementById('finalizeBtn').disabled = true;
  
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
      
      // Print receipt option
      if (confirm('Do you want to print the receipt?')) {
        printReceipt(currentSaleId);
      }
      
      // Reset and start a new sale
      setTimeout(() => {
        initNewSale();
      }, 1000);
    } else {
      document.getElementById('finalizeBtn').disabled = false;
      showToast(data.message || 'Failed to finalize sale', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    document.getElementById('finalizeBtn').disabled = false;
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
      if (!data.data) {
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
            <p><strong>Status:</strong> ${sale.status}</p>
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