<?php
// batches.php
include 'header.php';
include 'sidebar.php';
?>

<main class="min-h-screen p-6 bg-gray-100">

  <!-- Toast container -->
  <div id="toast"
       class="fixed bottom-4 right-4 bg-gray-700 text-white px-4 py-2 rounded-lg shadow-lg hidden z-50">
  </div>

  <!-- Header -->
  <div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-bold text-gray-900">Batch Management</h2>
        <p class="text-gray-600 mt-1">Track and manage product batches for better inventory control</p>
      </div>
      <button onclick="openAddBatchModal()"
              class="flex items-center gap-2 px-4 py-2.5 bg-gray-700 text-white font-medium rounded-md hover:bg-gray-600 transition-colors">
        <i class="fas fa-plus"></i>
        <span>Add Batch</span>
      </button>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-6 rounded-lg shadow mb-8 border border-gray-200">
      <div class="flex flex-col md:flex-row md:items-center gap-5">
        <div class="flex-1">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" id="searchInput"
                  placeholder="Search batches by product name, batch number..."
                  class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" />
          </div>
        </div>
        <div class="flex-1 md:flex-none">
          <select id="productSelect" 
                 class="w-full border-2 border-gray-300 rounded-md px-3 py-2.5 text-gray-700 focus:border-black focus:ring-1 focus:ring-black transition-all appearance-none bg-no-repeat bg-[right_0.5rem_center] pr-8"
                 style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e'); background-size: 1em">
            <option value="">All Products</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Batch Table -->
  <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-left">Product</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-left">Size</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-left">Batch Number</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-center">Manufactured Date</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-center">Stock</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="batch-list" class="divide-y divide-gray-200">
          <!-- Loading placeholder -->
          <tr>
            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
              <div class="flex flex-col items-center">
                <svg class="w-12 h-12 text-gray-300 animate-spin mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" stroke="none" fill="currentColor"></path>
                </svg>
                <p class="text-lg">Loading batch data...</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Pagination for Batches -->
    <div id="batch-pagination" class="flex justify-between items-center p-4 border-t border-gray-200 hidden">
      <div class="text-sm text-gray-600">
        Showing <span id="pagination-from">1</span> to <span id="pagination-to">10</span> of <span id="pagination-total">0</span> batches
      </div>
      <div class="flex space-x-1">
        <button id="pagination-prev" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <div id="pagination-numbers" class="flex space-x-1">
          <!-- Pagination numbers will be injected here -->
        </div>
        <button id="pagination-next" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
      <div class="flex items-center space-x-2">
        <span class="text-sm text-gray-600">Items per page:</span>
        <select id="pagination-limit" class="border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-500">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
      </div>
    </div>
  </div>
</main>

<!-- Add Batch Modal -->
<div id="addBatchModal"
     class="fixed inset-0 hidden bg-gray-700 bg-opacity-75 flex items-center justify-center z-50 overflow-y-auto">
  <div class="bg-white rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-auto m-4 shadow-xl">
    <div class="flex justify-between items-center mb-6 pb-3 border-b border-gray-200">
      <h3 class="text-2xl font-bold text-gray-900">Add New Batch</h3>
      <button onclick="closeAddBatchModal()" class="text-gray-400 hover:text-black transition-colors">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    <form id="addBatchForm" class="space-y-5">
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Product</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-box text-gray-400"></i>
          </div>
          <select id="addBatchProductSelect" name="product_id" 
                 class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all appearance-none bg-no-repeat bg-[right_0.5rem_center] pr-8" 
                 style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e'); background-size: 1em" 
                 required>
            <option value="">Select a product</option>
          </select>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Size</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-tag text-gray-400"></i>
          </div>
          <select id="addBatchSizeSelect" name="product_size_id" 
                 class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all appearance-none bg-no-repeat bg-[right_0.5rem_center] pr-8" 
                 style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e'); background-size: 1em" 
                 required>
            <option value="">Select a size</option>
          </select>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Batch Number</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-hashtag text-gray-400"></i>
          </div>
          <input type="text" name="batch_number"
                placeholder="e.g., BATCH-2025-001"
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" required />
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Manufactured Date</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-calendar-alt text-gray-400"></i>
          </div>
          <input type="date" name="manufactured_date"
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" required />
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Stock</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-cubes text-gray-400"></i>
          </div>
          <input type="number" name="stock"
                min="0"
                placeholder="Enter quantity"
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" required />
        </div>
      </div>
      <div class="pt-5 mt-3 border-t border-gray-200">
        <button type="submit"
                class="w-full bg-gray-700 text-white py-2.5 rounded-md hover:bg-gray-600 transition-colors font-medium">
          Create Batch
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Batch Modal -->
<div id="editBatchModal"
     class="fixed inset-0 hidden bg-gray-700 bg-opacity-75 flex items-center justify-center z-50 overflow-y-auto">
  <div class="bg-white rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-auto m-4 shadow-xl">
    <div class="flex justify-between items-center mb-6 pb-3 border-b border-gray-200">
      <h3 class="text-2xl font-bold text-gray-900">Edit Batch</h3>
      <button onclick="closeEditBatchModal()" class="text-gray-400 hover:text-black transition-colors">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    <form id="editBatchForm" class="space-y-5">
      <input type="hidden" name="id" />
      
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Product</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-box text-gray-400"></i>
          </div>
          <input type="text" name="product_name" 
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 bg-gray-50 text-gray-500" disabled />
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Size</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-tag text-gray-400"></i>
          </div>
          <input type="text" name="size_name" 
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 bg-gray-50 text-gray-500" disabled />
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Batch Number</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-hashtag text-gray-400"></i>
          </div>
          <input type="text" name="batch_number"
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" required />
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Manufactured Date</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-calendar-alt text-gray-400"></i>
          </div>
          <input type="date" name="manufactured_date"
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" required />
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Stock</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-cubes text-gray-400"></i>
          </div>
          <input type="number" name="stock"
                min="0"
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" required />
        </div>
      </div>
      
      <div class="pt-5 mt-3 border-t border-gray-200 flex justify-between">
        <button type="button" onclick="deleteBatch(editBatchForm.id.value)" 
                class="px-4 py-2.5 border-2 border-red-300 text-red-600 rounded-md hover:bg-red-50 transition-colors">
          <i class="fas fa-trash-alt mr-2"></i> Delete
        </button>
        <button type="submit"
                class="px-6 py-2.5 bg-gray-700 text-white rounded-md hover:bg-gray-600 transition-colors font-medium">
          Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

<script type="module">
import { apiGet, apiPost } from './js/ajax.js';

// Modal controls
window.openAddBatchModal = () => {
    addBatchForm.reset();
    document.getElementById('addBatchModal').classList.remove('hidden');
};
window.closeAddBatchModal = () => {
    document.getElementById('addBatchModal').classList.add('hidden');
};
window.openEditBatchModal = (batch) => {
    editBatchForm.id.value = batch.id;
    editBatchForm.product_name.value = batch.product_name;
    editBatchForm.size_name.value = batch.size_name;
    editBatchForm.batch_number.value = batch.batch_number;
    editBatchForm.manufactured_date.value = batch.manufactured_date;
    editBatchForm.stock.value = batch.stock;
    document.getElementById('editBatchModal').classList.remove('hidden');
};
window.closeEditBatchModal = () => {
    document.getElementById('editBatchModal').classList.add('hidden');
};

// DOM refs
const
  toast             = document.getElementById('toast'),
  searchInput       = document.getElementById('searchInput'),
  productSelect     = document.getElementById('productSelect'),
  batchList         = document.getElementById('batch-list'),
  addBatchForm      = document.getElementById('addBatchForm'),
  editBatchForm     = document.getElementById('editBatchForm'),
  addBatchProductSelect = document.getElementById('addBatchProductSelect'),
  addBatchSizeSelect = document.getElementById('addBatchSizeSelect');

// Pagination elements
const batchPagination = document.getElementById('batch-pagination');
const paginationFrom = document.getElementById('pagination-from');
const paginationTo = document.getElementById('pagination-to');
const paginationTotal = document.getElementById('pagination-total');
const paginationPrev = document.getElementById('pagination-prev');
const paginationNext = document.getElementById('pagination-next');
const paginationNumbers = document.getElementById('pagination-numbers');
const paginationLimit = document.getElementById('pagination-limit');

// Pagination state
let currentPage = 1;
let itemsPerPage = 10;
let totalBatches = 0;

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

// Initial load
document.addEventListener('DOMContentLoaded', async () => {
  await loadProducts();
  fetchBatches();
  
  // Add pagination event listeners
  paginationPrev.addEventListener('click', () => {
    if (currentPage > 1) {
      currentPage--;
      fetchBatches();
    }
  });
  
  paginationNext.addEventListener('click', () => {
    const totalPages = Math.ceil(totalBatches / itemsPerPage);
    if (currentPage < totalPages) {
      currentPage++;
      fetchBatches();
    }
  });
  
  paginationLimit.addEventListener('change', () => {
    itemsPerPage = parseInt(paginationLimit.value);
    currentPage = 1; // Reset to first page when limit changes
    fetchBatches();
  });
});

// Re-fetch on filter change
[searchInput, productSelect].forEach(el =>
  el.addEventListener('input', () => {
    currentPage = 1; // Reset to first page when filters change
    fetchBatches();
  })
);

// Load products for filter and add form
async function loadProducts() {
  try {
    const products = await apiGet('./api/products.php');
    productSelect.innerHTML = '<option value="">All Products</option>' +
      products.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
    addBatchProductSelect.innerHTML = '<option value="">Select a product</option>' +
      products.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
  } catch (err) {
    console.error(err);
    showToast('Could not load products', 'error');
  }
}

// Populate sizes when product is selected
addBatchProductSelect.onchange = async () => {
  const productId = addBatchProductSelect.value;
  addBatchSizeSelect.innerHTML = '<option value="">Select a size</option>';
  if (!productId) return;
  try {
    const products = await apiGet('./api/products.php');
    const product = products.find(p => p.id == productId);
    if (product && product.sizes) {
      addBatchSizeSelect.innerHTML = '<option value="">Select a size</option>' +
        product.sizes.map(s => `<option value="${s.id}">${s.size_name} (${s.stock} units)</option>`).join('');
    }
  } catch (err) {
    console.error(err);
    showToast('Could not load sizes', 'error');
  }
};

// Fetch & render batches
async function fetchBatches() {
  try {
    // Show loading state
    batchList.innerHTML = `
      <tr>
        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
          <div class="flex flex-col items-center">
            <svg class="w-12 h-12 text-gray-300 animate-spin mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" stroke="none" fill="currentColor"></path>
            </svg>
            <p class="text-lg">Loading batch data...</p>
          </div>
        </td>
      </tr>
    `;
    
    const params = new URLSearchParams({
      search: searchInput.value,
      product_id: productSelect.value,
      page: currentPage,
      limit: itemsPerPage
    });
    
    const response = await apiGet(`./api/batches.php?${params}`);
    
    // Handle both paginated and non-paginated responses
    const batches = Array.isArray(response) ? response : (response.batches || []);
    totalBatches = Array.isArray(response) ? batches.length : (response.pagination?.total || batches.length);
    
    if (batches.length === 0) {
      batchList.innerHTML = `
        <tr>
          <td colspan="6" class="px-6 py-8 text-center text-gray-500">
            <div class="flex flex-col items-center">
              <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="text-lg">No batches found</p>
              <p class="text-sm text-gray-400 mt-1"><i class="fas fa-filter mr-1"></i>Try changing your search criteria</p>
            </div>
          </td>
        </tr>
      `;
      batchPagination.classList.add('hidden');
      return;
    }
    
    batchList.innerHTML = batches.map(b => `
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-6 py-4 text-left font-semibold text-gray-900">
          <div class="flex items-center">
            <i class="fas fa-box text-gray-500 mr-2"></i>${b.product_name}
          </div>
        </td>
        <td class="px-6 py-4 text-left text-gray-700">
          <span class="inline-flex items-center px-2.5 py-1 rounded-md border-2 border-gray-200 text-xs font-medium bg-white">
            <i class="fas fa-tag text-gray-500 mr-1"></i>${b.size_name}
          </span>
        </td>
        <td class="px-6 py-4 text-left text-gray-700">
          <div class="flex items-center">
            <i class="fas fa-hashtag text-gray-500 mr-2"></i>${b.batch_number}
          </div>
        </td>
        <td class="px-6 py-4 text-center text-gray-700">
          <div class="flex items-center justify-center">
            <i class="fas fa-calendar-day text-gray-500 mr-2"></i>${formatDate(b.manufactured_date)}
          </div>
        </td>
        <td class="px-6 py-4 text-center ${b.stock === 0 ? 'text-gray-400' : 'font-bold text-gray-900'}">
          ${b.stock === 0 ? 
            '<span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-500"><i class="fas fa-times-circle mr-1"></i>Out of stock</span>' : 
            `<div class="flex items-center justify-center"><i class="fas fa-cubes text-gray-500 mr-2"></i>${b.stock}</div>`
          }
        </td>
        <td class="px-6 py-4 text-center">
          <div class="flex justify-center gap-2">
            <button onclick="openEditBatchModal({
                id: ${b.id},
                product_name: '${b.product_name.replace(/'/g, "\\'")}',
                size_name: '${b.size_name.replace(/'/g, "\\'")}',
                batch_number: '${b.batch_number.replace(/'/g, "\\'")}',
                manufactured_date: '${b.manufactured_date}',
                stock: ${b.stock}
            })" class="p-2 text-gray-700 hover:text-black border-2 border-gray-200 hover:border-black rounded-md transition-colors">
              <i class="fas fa-edit"></i>
            </button>
            <button onclick="deleteBatch(${b.id})" class="p-2 text-red-600 hover:text-red-700 border-2 border-red-200 hover:border-red-300 rounded-md transition-colors">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        </td>
      </tr>`).join('');
      
    // Update pagination
    updatePagination();
  } catch (err) {
    console.error(err);
    showToast('Could not load batches', 'error');
  }
}

// Update pagination display and controls
function updatePagination() {
  const totalPages = Math.ceil(totalBatches / itemsPerPage);
  const startItem = (currentPage - 1) * itemsPerPage + 1;
  const endItem = Math.min(currentPage * itemsPerPage, totalBatches);
  
  if (totalBatches > 0) {
    batchPagination.classList.remove('hidden');
    paginationFrom.textContent = startItem;
    paginationTo.textContent = endItem;
    paginationTotal.textContent = totalBatches;
    
    // Update pagination buttons state
    paginationPrev.disabled = currentPage <= 1;
    paginationNext.disabled = currentPage >= totalPages;
    
    // Generate page numbers
    paginationNumbers.innerHTML = '';
    
    // Determine range of page numbers to show
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);
    
    // Adjust if we're near the end
    if (endPage - startPage < 4) {
      startPage = Math.max(1, endPage - 4);
    }
    
    // Add first page if not in range
    if (startPage > 1) {
      const pageBtn = document.createElement('button');
      pageBtn.className = `px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-50 ${1 === currentPage ? 'bg-gray-700 text-white border-gray-700' : ''}`;
      pageBtn.textContent = '1';
      pageBtn.onclick = () => goToPage(1);
      paginationNumbers.appendChild(pageBtn);
      
      // Add ellipsis if there's a gap
      if (startPage > 2) {
        const ellipsis = document.createElement('span');
        ellipsis.className = 'px-2 py-1 text-gray-500';
        ellipsis.textContent = '...';
        paginationNumbers.appendChild(ellipsis);
      }
    }
    
    // Add page numbers in range
    for (let i = startPage; i <= endPage; i++) {
      const pageBtn = document.createElement('button');
      pageBtn.className = `px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-50 ${i === currentPage ? 'bg-gray-700 text-white border-gray-700' : ''}`;
      pageBtn.textContent = i;
      pageBtn.onclick = () => goToPage(i);
      paginationNumbers.appendChild(pageBtn);
    }
    
    // Add last page if not in range
    if (endPage < totalPages) {
      // Add ellipsis if there's a gap
      if (endPage < totalPages - 1) {
        const ellipsis = document.createElement('span');
        ellipsis.className = 'px-2 py-1 text-gray-500';
        ellipsis.textContent = '...';
        paginationNumbers.appendChild(ellipsis);
      }
      
      const pageBtn = document.createElement('button');
      pageBtn.className = `px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-50 ${totalPages === currentPage ? 'bg-gray-700 text-white border-gray-700' : ''}`;
      pageBtn.textContent = totalPages;
      pageBtn.onclick = () => goToPage(totalPages);
      paginationNumbers.appendChild(pageBtn);
    }
  } else {
    batchPagination.classList.add('hidden');
  }
}

// Go to specific page
function goToPage(page) {
  currentPage = page;
  fetchBatches();
}

// Format date display
function formatDate(dateString) {
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
}

// Add batch
addBatchForm.onsubmit = async e => {
  e.preventDefault();
  const data = Object.fromEntries(new FormData(addBatchForm).entries());

  if (parseInt(data.stock) < 0) {
    showToast('Stock cannot be negative', 'error');
    return;
  }

  // Validate manufactured date
  const currentDate = new Date('2025-04-22');
  const manufacturedDate = new Date(data.manufactured_date);
  if (manufacturedDate > currentDate) {
    showToast('Manufactured date cannot be in the future', 'error');
    return;
  }

  try {
    const res = await apiPost('./api/batches.php', {
      action: 'create',
      product_id: data.product_id,
      product_size_id: data.product_size_id,
      batch_number: data.batch_number,
      manufactured_date: data.manufactured_date,
      stock: parseInt(data.stock)
    });
    if (res.success) {
      showToast('Batch added successfully!');
      closeAddBatchModal();
      fetchBatches();
    } else {
      showToast(res.message || 'Failed to add batch', 'error');
    }
  } catch (err) {
    console.error(err);
    showToast('Error adding batch: ' + (err.message || 'Unknown error'), 'error');
  }
};

// Edit batch
editBatchForm.onsubmit = async e => {
  e.preventDefault();
  const data = Object.fromEntries(new FormData(editBatchForm).entries());

  if (parseInt(data.stock) < 0) {
    showToast('Stock cannot be negative', 'error');
    return;
  }

  // Validate manufactured date
  const currentDate = new Date('2025-04-22');
  const manufacturedDate = new Date(data.manufactured_date);
  if (manufacturedDate > currentDate) {
    showToast('Manufactured date cannot be in the future', 'error');
    return;
  }

  try {
    const res = await apiPost('./api/batches.php', {
      action: 'update',
      id: data.id,
      batch_number: data.batch_number,
      manufactured_date: data.manufactured_date,
      stock: parseInt(data.stock)
    });
    if (res.success) {
      showToast('Batch updated successfully!');
      closeEditBatchModal();
      fetchBatches();
    } else {
      showToast(res.message || 'Failed to update batch', 'error');
    }
  } catch (err) {
    console.error(err);
    showToast('Error updating batch: ' + (err.message || 'Unknown error'), 'error');
  }
};

// Delete batch
window.deleteBatch = async id => {
  if (!confirm('Are you sure you want to delete this batch? This action cannot be undone.')) return;
  try {
    const res = await apiPost('./api/batches.php', { action: 'delete', id });
    if (res.success) {
      showToast('Batch deleted successfully');
      closeEditBatchModal();
      fetchBatches();
    } else {
      showToast(res.message || 'Delete failed', 'error');
    }
  } catch (err) {
    console.error(err);
    showToast('Delete error: ' + (err.message || 'Unknown error'), 'error');
  }
};

// Make goToPage function available to window for pagination buttons
window.goToPage = goToPage;
</script>

<?php include 'footer.php'; ?>