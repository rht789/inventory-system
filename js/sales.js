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
  
  return `<span class="px-3 py-1 rounded-full text-xs inline-flex items-center ${badgeClass}">${capitalizeFirstLetter(status)}</span>`;
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
  
  newRow.innerHTML = `
    <td class="px-4 py-3">
      <select name="product_id[]" class="product-select w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
              onchange="handleProductSelect(this, ${rowIndex})">
        <option value="">Select product</option>
        ${products.map(p => `<option value="${p.id}">${p.name}</option>`).join('')}
      </select>
    </td>
    <td class="px-4 py-3">
      <select name="product_size_id[]" class="size-select w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
              onchange="handleSizeSelect(this, ${rowIndex})" disabled>
        <option value="">Select size</option>
      </select>
    </td>
    <td class="px-4 py-3">
      <input type="number" name="quantity[]" min="1" value="1" 
             class="quantity-input w-full border border-gray-300 rounded-md py-2 px-3 text-center focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
             onchange="updateRowTotal(${rowIndex})" disabled />
    </td>
    <td class="px-4 py-3">
      <input type="number" name="price[]" step="0.01" value="0.00" 
             class="price-input w-full border border-gray-300 rounded-md py-2 px-3 text-right focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50" readonly />
    </td>
    <td class="px-4 py-3">
      <input type="number" name="total[]" step="0.01" value="0.00" 
             class="total-input w-full border border-gray-300 rounded-md py-2 px-3 text-right focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 font-medium" readonly />
    </td>
    <td class="px-4 py-3 text-center">
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
  document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
  document.getElementById('discountDisplay').textContent = discount.toFixed(2);
  document.getElementById('totalDisplay').textContent = total.toFixed(2);
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
  
  // Prepare complete order data
  const orderData = {
    customer: customer,
    items: items,
    discount: parseFloat(document.getElementById('discountPercentage').value) || 0,
    status: document.getElementById('orderStatus').value,
    note: document.getElementById('orderNote').value
  };
  
  // Submit order
  createOrder(orderData);
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
      loadSales(); // Refresh the list
    } else {
      showToast(data.message || 'Error creating order', 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Failed to create order', 'error');
  });
}

// View sale details (to be implemented)
function viewSaleDetails(saleId) {
  showToast('View functionality will be implemented in a future update');
}

// Edit sale (to be implemented)
function editSale(saleId) {
  showToast('Edit functionality will be implemented in a future update');
}

// Delete sale (to be implemented)
function deleteSale(saleId) {
  showToast('Delete functionality will be implemented in a future update');
}

// Open the add order modal
function openAddOrderModal() {
  document.getElementById('addOrderModal').classList.remove('hidden');
  
  // Reset form
  document.getElementById('addOrderForm').reset();
  document.getElementById('productRows').innerHTML = '';
  selectedProducts = [];
  
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