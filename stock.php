<?php
include 'authcheck.php'; // Adjust path as needed
requireLogin();           // Ensures the user is logged in
allowRoles(['admin', 'staff']); // Both roles can access
?>

<?php
include 'header.php';
include 'sidebar.php';
?>

<main class="min-h-screen p-6 bg-gray-100">
  <!-- Toast Notification -->
  <div id="toast" class="fixed bottom-4 right-4 bg-gray-700 text-white px-4 py-2 rounded-lg shadow-lg hidden z-50"></div>

  <!-- Header -->
  <div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-bold text-gray-900">Inventory Stock</h2>
        <p class="text-gray-600 mt-1">Monitor and manage your inventory stock levels</p>
      </div>
      <div class="flex gap-3">
        <button onclick="openStockLogsModal()" class="flex items-center gap-2 px-4 py-2.5 border-2 border-gray-700 text-gray-700 font-medium rounded-md hover:bg-gray-700 hover:text-white transition-colors">
          <i class="fas fa-history"></i>
          <span>View History</span>
      </button>
        <button onclick="openAdjustStockModal(null, 'add')" class="flex items-center gap-2 px-4 py-2.5 bg-gray-700 text-white font-medium rounded-md hover:bg-gray-600 transition-colors">
          <i class="fas fa-plus"></i>
          <span>Adjust Stock</span>
      </button>
    </div>
  </div>

  <!-- Filters -->
    <div class="bg-white p-6 rounded-lg shadow mb-8 border border-gray-200">
      <div class="flex flex-col md:flex-row md:items-center gap-5">
        <div class="flex-1">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" id="searchStockInput" placeholder="Search by product name..." 
                  class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" />
          </div>
        </div>
        <div class="flex gap-3 flex-1 md:flex-none">
          <select id="stockStatusSelect" class="border-2 border-gray-300 rounded-md px-3 py-2.5 text-gray-700 focus:border-black focus:ring-1 focus:ring-black transition-all w-full md:w-auto appearance-none bg-no-repeat bg-[right_0.5rem_center] pr-8" style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e'); background-size: 1em">
            <option value="">All Stock Status</option>
          <option value="in_stock">In Stock</option>
          <option value="low_stock">Low Stock</option>
          <option value="critical">Critical</option>
            <option value="out_of_stock">Out of Stock</option>
        </select>
          <select id="locationSelect" class="border-2 border-gray-300 rounded-md px-3 py-2.5 text-gray-700 focus:border-black focus:ring-1 focus:ring-black transition-all w-full md:w-auto appearance-none bg-no-repeat bg-[right_0.5rem_center] pr-8" style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e'); background-size: 1em">
          <option value="">All Locations</option>
        </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Stock Table -->
  <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
          <tr>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-left">Product</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-left">Size & Stock</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-left">Location</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-center">Total Stock</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-center">Min Stock</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-center">Status</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-center">Barcode</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-center">Actions</th>
        </tr>
      </thead>
        <tbody id="stock-list" class="divide-y divide-gray-200">
        <!-- Stock details will be dynamically populated via JS -->
          <tr>
            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
              <div class="flex flex-col items-center">
                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <p class="text-lg">Loading inventory data...</p>
              </div>
            </td>
          </tr>
      </tbody>
    </table>
    </div>
    
    <!-- Pagination for Stock Table -->
    <div id="stock-pagination" class="flex justify-between items-center p-4 border-t border-gray-200 hidden">
      <div class="text-sm text-gray-600">
        Showing <span id="pagination-from">1</span> to <span id="pagination-to">10</span> of <span id="pagination-total">0</span> products
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

<!-- Adjust Stock Modal -->
<div id="adjustStockModal" class="fixed inset-0 hidden bg-gray-700 bg-opacity-75 flex items-center justify-center z-50 overflow-y-auto">
  <div class="bg-white rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-auto m-4 shadow-xl">
    <div class="flex justify-between items-center mb-6 pb-3 border-b border-gray-200">
      <h3 class="text-2xl font-bold text-gray-900" id="modalTitle">Adjust Stock</h3>
      <button onclick="closeAdjustStockModal()" class="text-gray-400 hover:text-black transition-colors">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>

    <p class="text-gray-600 mb-6">Update stock quantities for better inventory management</p>

    <form id="adjustStockForm" class="space-y-5">
      <input type="hidden" name="mode" id="formMode">
      <input type="hidden" name="product_id" id="productIdInput">

      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Product</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-box text-gray-400"></i>
          </div>
          <input type="text" id="productSearch" placeholder="Select a product" 
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" 
                autocomplete="off" required>
          <ul id="productDropdown" class="absolute z-10 w-full bg-white border-2 border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto hidden mt-1"></ul>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Size</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-tag text-gray-400"></i>
          </div>
          <select name="product_size_id" id="sizeSelect" 
                 class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all appearance-none bg-no-repeat bg-[right_0.5rem_center] pr-8" 
                 style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e'); background-size: 1em"
                 required>
          <!-- Options will be populated dynamically -->
        </select>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Quantity</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-hashtag text-gray-400"></i>
          </div>
          <input type="number" name="quantity" placeholder="Enter quantity" 
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" 
                min="1" required />
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Location</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-map-marker-alt text-gray-400"></i>
          </div>
          <input type="text" name="location" placeholder="e.g., Shelf A1, Warehouse B" 
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" />
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Reason</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-clipboard-list text-gray-400"></i>
          </div>
          <input type="text" name="reason" placeholder="Why are you adjusting stock?" 
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" 
                required />
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Adjustment Type</label>
        <div class="flex gap-3">
          <label class="flex-1 cursor-pointer">
            <input type="radio" name="type" value="in" class="hidden peer" checked>
            <div class="peer-checked:bg-gray-700 peer-checked:text-white border-2 border-gray-300 peer-checked:border-gray-700 rounded-md py-3 px-4 text-center transition-colors">
              <i class="fas fa-plus-circle mr-2"></i> Add Stock
            </div>
          </label>
          <label class="flex-1 cursor-pointer">
            <input type="radio" name="type" value="out" class="hidden peer">
            <div class="peer-checked:bg-gray-700 peer-checked:text-white border-2 border-gray-300 peer-checked:border-gray-700 rounded-md py-3 px-4 text-center transition-colors">
              <i class="fas fa-minus-circle mr-2"></i> Reduce Stock
            </div>
          </label>
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-5 mt-3 border-t border-gray-200">
        <button type="button" onclick="closeAdjustStockModal()" 
                class="px-5 py-2.5 border-2 border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition-colors">
          Cancel
        </button>
        <button type="submit" 
                class="px-5 py-2.5 bg-gray-700 text-white rounded-md hover:bg-gray-600 transition-colors">
          Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Barcode Preview Modal -->
<div id="barcodeModal" class="fixed inset-0 hidden bg-gray-700 bg-opacity-75 flex items-center justify-center z-50">
  <div class="bg-white p-6 rounded-lg shadow-xl max-w-lg w-full m-4">
    <div class="flex justify-between items-center mb-6 pb-3 border-b border-gray-200">
      <h3 class="text-2xl font-bold text-gray-900">Barcode</h3>
      <button onclick="closeBarcodeModal()" class="text-gray-400 hover:text-black transition-colors">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    <div class="bg-gray-100 p-8 rounded border-2 border-gray-200 flex items-center justify-center">
      <img id="barcodeModalImg" src="" alt="Barcode" class="mx-auto max-h-[40vh] object-contain" />
    </div>
    <div class="mt-6 flex justify-center">
      <button onclick="closeBarcodeModal()" class="px-5 py-2.5 bg-gray-700 text-white rounded-md hover:bg-gray-600 transition-colors">
        Close
      </button>
    </div>
  </div>
</div>

<!-- Stock Logs Modal -->
<div id="stockLogsModal" class="fixed inset-0 hidden bg-gray-700 bg-opacity-75 flex items-center justify-center z-50 overflow-y-auto">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl max-h-[90vh] overflow-auto m-4">
    <div class="flex justify-between items-center p-6 border-b border-gray-200">
      <h3 class="text-2xl font-bold text-gray-900">Stock Movement History</h3>
      <button onclick="closeStockLogsModal()" class="text-gray-400 hover:text-black transition-colors">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    
    <div class="p-6 bg-gray-100">
      <div class="flex flex-col md:flex-row gap-3">
        <div class="relative flex-1">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
          <input type="text" id="stockLogsSearch" placeholder="Search by product or reason..." 
                class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" />
        </div>
        <div class="flex gap-3 md:w-auto">
          <select id="stockLogsTypeFilter" 
                 class="border-2 border-gray-300 rounded-md px-3 py-2.5 text-gray-700 focus:border-black focus:ring-1 focus:ring-black transition-all appearance-none bg-no-repeat bg-[right_0.5rem_center] pr-8" 
                 style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e'); background-size: 1em">
          <option value="">All Changes</option>
            <option value="Added">Stock Added</option>
            <option value="Reduced">Stock Reduced</option>
        </select>
          <button id="refreshStockLogs" 
                 class="px-3 py-2.5 border-2 border-gray-700 text-gray-700 rounded-md hover:bg-gray-700 hover:text-white transition-colors flex items-center justify-center w-12">
          <i class="fas fa-sync-alt"></i>
        </button>
        </div>
      </div>
    </div>
    
    <div class="px-6 py-4">
      <table class="w-full text-sm">
        <thead>
          <tr>
            <th class="px-4 py-3 bg-gray-700 text-white font-semibold text-left">Date & Time</th>
            <th class="px-4 py-3 bg-gray-700 text-white font-semibold text-left">Product</th>
            <th class="px-4 py-3 bg-gray-700 text-white font-semibold text-center">Change</th>
            <th class="px-4 py-3 bg-gray-700 text-white font-semibold text-left">Reason</th>
            <th class="px-4 py-3 bg-gray-700 text-white font-semibold text-left">User</th>
          </tr>
        </thead>
        <tbody id="stockLogsList" class="divide-y divide-gray-200">
          <!-- Stock logs will be populated here -->
          <tr>
            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
              <div class="flex flex-col items-center">
                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-lg">Loading stock history...</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <div class="p-5 border-t border-gray-200 bg-gray-100 flex justify-center">
      <div id="stockLogsPagination" class="space-x-2">
      <!-- Pagination will be added here -->
      </div>
    </div>
  </div>
</div>

<script type="module">
import { apiGet } from './js/ajax.js';

const toast = document.getElementById('toast');
const modal = document.getElementById('adjustStockModal');
const stockLogsModal = document.getElementById('stockLogsModal');
const form  = document.getElementById('adjustStockForm');
const stockList = document.getElementById('stock-list');
const searchStockInput = document.getElementById('searchStockInput');
const stockStatusSelect = document.getElementById('stockStatusSelect');
const locationSelect = document.getElementById('locationSelect');
const modalTitle = document.getElementById('modalTitle');
const productSearch = document.getElementById('productSearch');
const productDropdown = document.getElementById('productDropdown');
const productIdInput = document.getElementById('productIdInput');
const sizeSelect = document.getElementById('sizeSelect');
const barcodeModalImg = document.getElementById('barcodeModalImg');
const stockLogsList = document.getElementById('stockLogsList');
const stockLogsSearch = document.getElementById('stockLogsSearch');
const stockLogsTypeFilter = document.getElementById('stockLogsTypeFilter');
const stockLogsPagination = document.getElementById('stockLogsPagination');
const refreshStockLogsBtn = document.getElementById('refreshStockLogs');

// Pagination elements
const stockPagination = document.getElementById('stock-pagination');
const paginationFrom = document.getElementById('pagination-from');
const paginationTo = document.getElementById('pagination-to');
const paginationTotal = document.getElementById('pagination-total');
const paginationPrev = document.getElementById('pagination-prev');
const paginationNext = document.getElementById('pagination-next');
const paginationNumbers = document.getElementById('pagination-numbers');
const paginationLimit = document.getElementById('pagination-limit');

let products = [];
let currentPage = 1;
let logsPerPage = 15;
let itemsPerPage = 10;
let totalProducts = 0;

/**
 * Show a toast notification
 */
function showToast(message, type = 'success') {
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

// Open and Close Modals
window.openAdjustStockModal = (productId, mode = 'edit') => {
  modal.classList.remove('hidden');
  form.reset();
  document.getElementById('formMode').value = mode;
  modalTitle.textContent = mode === 'add' ? 'Add Stock' : 'Adjust Stock';
  productSearch.value = '';
  productIdInput.value = '';
  sizeSelect.innerHTML = ''; // Clear size options
  populateProductDropdown(productId);
};

window.closeAdjustStockModal = () => {
  modal.classList.add('hidden');
  form.reset();
  productDropdown.classList.add('hidden');
};

window.openStockLogsModal = () => {
  stockLogsModal.classList.remove('hidden');
  currentPage = 1;
  loadStockLogs();
};

window.closeStockLogsModal = () => {
  stockLogsModal.classList.add('hidden');
};

window.openBarcodeModal = () => document.getElementById('barcodeModal').classList.remove('hidden');
window.closeBarcodeModal = () => document.getElementById('barcodeModal').classList.add('hidden');

// Fetch and load stock data with pagination
async function loadStock() {
  try {
    const params = new URLSearchParams({
      search: searchStockInput.value,
      stock_filter: stockStatusSelect.value,
      location: locationSelect.value,
      page: currentPage,
      limit: itemsPerPage
    });
    
    // Clear loading state
    stockList.innerHTML = `
      <tr>
        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
          <div class="flex flex-col items-center">
            <svg class="w-12 h-12 text-gray-300 animate-spin mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" stroke="none" fill="currentColor"></path>
            </svg>
            <p class="text-lg">Loading inventory data...</p>
          </div>
        </td>
      </tr>
    `;
    
    const response = await apiGet(`./api/products.php?${params}`);
    
    // Handle both paginated and non-paginated responses
    const prods = Array.isArray(response) ? response : (response.products || []);
    totalProducts = Array.isArray(response) ? prods.length : (response.pagination?.total || prods.length);
    
    if (prods.length === 0) {
      stockList.innerHTML = `
        <tr>
          <td colspan="8" class="px-6 py-8 text-center text-gray-500">
            <div class="flex flex-col items-center">
              <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="text-lg">No products found</p>
              <p class="text-sm text-gray-400 mt-1">Try changing your search criteria</p>
            </div>
          </td>
        </tr>
      `;
      stockPagination.classList.add('hidden');
      return;
    }
    
    stockList.innerHTML = prods.map(p => {
      const total = p.sizes.reduce((sum, s) => sum + +s.stock, 0);
      const badges = p.sizes.map(s => 
        `<span class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium border-2 border-gray-300 bg-white text-gray-700">${s.size_name}: ${s.stock}</span>`
      ).join(' ');
      
      let status, statusClass, statusIcon;
      if (total === 0) {
        status = 'OUT OF STOCK';
        statusClass = 'bg-gray-200 text-gray-500';
        statusIcon = '<i class="fas fa-times-circle mr-1"></i>';
      } else if (total <= 2) {
        status = 'CRITICAL';
        statusClass = 'bg-red-100 text-red-700';
        statusIcon = '<i class="fas fa-exclamation-triangle mr-1"></i>';
      } else if (total <= p.min_stock) {
        status = 'LOW STOCK';
        statusClass = 'bg-yellow-100 text-yellow-700';
        statusIcon = '<i class="fas fa-exclamation-circle mr-1"></i>';
      } else {
        status = 'IN STOCK';
        statusClass = 'bg-green-100 text-green-700';
        statusIcon = '<i class="fas fa-check-circle mr-1"></i>';
      }
      
      return `
        <tr class="hover:bg-gray-50 transition-colors">
          <td class="px-6 py-4 font-semibold text-gray-900">
            <div class="flex items-center">
              <i class="fas fa-box text-gray-500 mr-2"></i>${p.name}
            </div>
          </td>
          <td class="px-6 py-4 flex flex-wrap gap-1.5">${badges}</td>
          <td class="px-6 py-4 text-gray-700">
            ${p.location ? `<div class="flex items-center"><i class="fas fa-map-marker-alt text-gray-500 mr-2"></i>${p.location}</div>` : '—'}
          </td>
          <td class="px-6 py-4 font-bold text-center">
            <div class="flex items-center justify-center">
              <i class="fas fa-cubes text-gray-500 mr-2"></i>${total}
            </div>
          </td>
          <td class="px-6 py-4 text-center text-gray-700">
            <div class="flex items-center justify-center">
              <i class="fas fa-level-down-alt text-gray-500 mr-2"></i>${p.min_stock}
            </div>
          </td>
          <td class="px-6 py-4 text-center">
            <span class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium ${statusClass}">
              ${statusIcon}${status}
            </span>
          </td>
          <td class="px-6 py-4 text-center">
            ${p.barcode ? 
              `<img src="./${p.barcode}" alt="Barcode" class="barcode-img h-10 mx-auto cursor-pointer border border-gray-300 p-1 hover:border-black transition-colors"/>` : 
              '<span class="text-gray-400">—</span>'}
          </td>
          <td class="px-6 py-4 text-center">
            <button onclick="openAdjustStockModal(${p.id}, 'edit')" 
                    class="p-2 text-gray-700 hover:text-black border-2 border-gray-200 hover:border-black rounded-md transition-colors">
              <i class="fas fa-edit"></i>
            </button>
          </td>
        </tr>`;
    }).join('');

    // Attach click handlers to barcode images
    document.querySelectorAll('.barcode-img').forEach(img => {
      img.onclick = () => {
        barcodeModalImg.src = img.src;
        openBarcodeModal();
      };
    });
    
    // Update pagination
    updatePagination();
  } catch (err) {
    console.error(err);
    showToast('Error loading stock', 'error');
  }
}

// Update pagination display and controls
function updatePagination() {
  // Use the calculated total pages or use the API-provided value if available
  const totalPages = Math.ceil(totalProducts / itemsPerPage);
  const startItem = (currentPage - 1) * itemsPerPage + 1;
  const endItem = Math.min(currentPage * itemsPerPage, totalProducts);
  
  if (totalProducts > 0) {
    stockPagination.classList.remove('hidden');
    paginationFrom.textContent = startItem;
    paginationTo.textContent = endItem;
    paginationTotal.textContent = totalProducts;
    
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
    stockPagination.classList.add('hidden');
  }
}

// Go to specific page
function goToPage(page) {
  currentPage = page;
  loadStock();
}

// Populate the product dropdown with search functionality
async function populateProductDropdown(selectedProductId = null) {
  try {
    products = await apiGet('./api/products.php');
    if (selectedProductId) {
      const selectedProduct = products.find(p => p.id == selectedProductId);
      if (selectedProduct) {
        productSearch.value = selectedProduct.name;
        productIdInput.value = selectedProduct.id;
        updateSizes(selectedProduct);
      }
    }

    productSearch.oninput = () => {
      const query = productSearch.value.toLowerCase();
      const filtered = products.filter(p => p.name.toLowerCase().includes(query));
      productDropdown.innerHTML = filtered.map(p => `
        <li class="px-4 py-3 hover:bg-gray-100 cursor-pointer transition-colors" data-id="${p.id}">${p.name}</li>
      `).join('');
      productDropdown.classList.remove('hidden');

      productDropdown.querySelectorAll('li').forEach(item => {
        item.onclick = () => {
          productSearch.value = item.textContent;
          productIdInput.value = item.dataset.id;
          productDropdown.classList.add('hidden');
          const selectedProduct = products.find(p => p.id == item.dataset.id);
          updateSizes(selectedProduct);
        };
      });
    };

    productSearch.onclick = () => {
      productSearch.oninput();
    };

    document.addEventListener('click', e => {
      if (!productSearch.contains(e.target) && !productDropdown.contains(e.target)) {
        productDropdown.classList.add('hidden');
      }
    });
  } catch (err) {
    console.error(err);
    showToast('Could not load product list', 'error');
  }
}

// Update sizes based on selected product
function updateSizes(product) {
  sizeSelect.innerHTML = ''; // Clear existing options
  if (product.sizes && product.sizes.length > 0) {
    product.sizes.forEach(s => {
      const opt = document.createElement('option');
      opt.value = s.id;
      opt.textContent = s.size_name;
      sizeSelect.appendChild(opt);
    });
    // If there's only one size, select it automatically
    if (product.sizes.length === 1) {
      sizeSelect.value = product.sizes[0].id;
    }
  } else {
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = 'No sizes available';
    opt.disabled = true;
    sizeSelect.appendChild(opt);
  }
}

// Populate the location dropdown
async function populateLocationDropdown() {
  try {
    const locations = await apiGet('./api/stock.php?action=get_locations');
    locationSelect.innerHTML = '<option value="">All Locations</option>';
    locations.forEach(loc => {
      const opt = document.createElement('option');
      opt.value = loc;
      opt.textContent = loc;
      locationSelect.appendChild(opt);
    });
  } catch (err) {
    console.error(err);
    showToast('Could not load locations', 'error');
  }
}

// Handle form submission for stock adjustment
form.onsubmit = async e => {
  e.preventDefault();
  const formData = new FormData(form);

  // Validate required fields
  if (!formData.get('product_id')) {
    showToast('Please select a product', 'error');
    return;
  }
  if (!formData.get('product_size_id')) {
    showToast('Please select a size', 'error');
    return;
  }
  if (!formData.get('quantity') || formData.get('quantity') <= 0) {
    showToast('Please enter a valid quantity greater than 0', 'error');
    return;
  }
  if (!formData.get('reason')) {
    showToast('Please enter a reason', 'error');
    return;
  }

  try {
    const res = await fetch('./api/stock.php', {
      method: 'POST',
      body: formData // Send as FormData to use application/x-www-form-urlencoded
    });
    if (!res.ok) {
      const errorData = await res.json();
      throw new Error(errorData.message || `${res.status} ${res.statusText}`);
    }
    const data = await res.json();
    if (data.success) {
      showToast('Stock adjusted successfully!');
      form.reset();
      closeAdjustStockModal();
      loadStock();
    } else {
      showToast(data.message || 'Adjustment failed', 'error');
    }
  } catch (err) {
    console.error(err);
    showToast(err.message || 'An error occurred while adjusting stock', 'error');
  }
};

// Add filter event listeners
[searchStockInput, stockStatusSelect, locationSelect].forEach(el => {
  el.addEventListener('input', () => {
    currentPage = 1; // Reset to first page when filters change
    loadStock();
  });
});

// Pagination event listeners
paginationPrev.addEventListener('click', () => {
  if (currentPage > 1) {
    currentPage--;
    loadStock();
  }
});

paginationNext.addEventListener('click', () => {
  const totalPages = Math.ceil(totalProducts / itemsPerPage);
  if (currentPage < totalPages) {
    currentPage++;
    loadStock();
  }
});

paginationLimit.addEventListener('change', () => {
  itemsPerPage = parseInt(paginationLimit.value);
  currentPage = 1; // Reset to first page when limit changes
  loadStock();
});

// Initial load
document.addEventListener('DOMContentLoaded', () => {
  loadStock();
  populateLocationDropdown();
  
  // Add stock logs event listeners
  stockLogsSearch.addEventListener('input', () => {
    currentPage = 1;
    loadStockLogs();
  });
  
  stockLogsTypeFilter.addEventListener('change', () => {
    currentPage = 1;
    loadStockLogs();
  });
  
  refreshStockLogsBtn.addEventListener('click', () => {
    currentPage = 1;
    loadStockLogs();
  });
});

// Function to load stock logs
async function loadStockLogs() {
  try {
    stockLogsList.innerHTML = `
      <tr>
        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
          <div class="flex flex-col items-center">
            <svg class="w-12 h-12 text-gray-300 animate-spin mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" stroke="none" fill="currentColor"></path>
            </svg>
            <p class="text-lg">Loading stock movement history...</p>
          </div>
        </td>
      </tr>
    `;
    
    const params = new URLSearchParams({
      search: stockLogsSearch.value,
      type: stockLogsTypeFilter.value,
      page: currentPage,
      per_page: logsPerPage
    });
    
    const response = await apiGet(`./api/stock.php?action=get_logs&${params}`);
    
    if (response.success) {
      renderStockLogs(response.logs, response.pagination);
    } else {
      showToast(response.message || 'Failed to load stock logs', 'error');
    }
  } catch (err) {
    console.error(err);
    showToast('Error loading stock logs', 'error');
  }
}

// Function to render stock logs
function renderStockLogs(logs, pagination) {
  if (logs.length === 0) {
    stockLogsList.innerHTML = `
      <tr>
        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
          <div class="flex flex-col items-center">
            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <p class="text-lg">No stock movement records found</p>
            <p class="text-sm text-gray-400 mt-1">Try changing your filter options</p>
          </div>
        </td>
      </tr>
    `;
    stockLogsPagination.innerHTML = '';
    return;
  }
  
  stockLogsList.innerHTML = logs.map(log => {
    // Determine the CSS class based on the type of change
    const changeClass = log.changes.includes('Added') 
      ? 'bg-green-100 text-green-700' 
      : 'bg-red-100 text-red-700';
      
    const changeIcon = log.changes.includes('Added')
      ? '<i class="fas fa-arrow-circle-up mr-1"></i>'
      : '<i class="fas fa-arrow-circle-down mr-1"></i>';
      
    return `
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-3.5 text-gray-600">${formatDate(log.timestamp)}</td>
        <td class="px-4 py-3.5 font-medium text-gray-900">${log.product_name}</td>
        <td class="px-4 py-3.5 text-center">
          <span class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium ${changeClass}">
            ${changeIcon}${log.changes}
          </span>
        </td>
        <td class="px-4 py-3.5 text-gray-700">${log.reason}</td>
        <td class="px-4 py-3.5 text-gray-700">${log.username}</td>
      </tr>
    `;
  }).join('');
  
  // Render pagination
  if (pagination.total_pages > 1) {
    let paginationHtml = '';
    
    // Add previous page button
    paginationHtml += `
      <button onclick="changePage(${Math.max(1, pagination.current_page - 1)})" 
              class="${pagination.current_page === 1 ? 'opacity-50 cursor-not-allowed' : ''} px-3 py-2 border-2 border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition-colors">
        <i class="fas fa-chevron-left"></i>
      </button>
    `;
    
    // Add page numbers
    for (let i = 1; i <= pagination.total_pages; i++) {
      const activeClass = i === pagination.current_page 
        ? 'bg-gray-700 text-white border-gray-700' 
        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100';
      
      // Only show a few pages around the current page
      if (
        i === 1 || 
        i === pagination.total_pages || 
        (i >= pagination.current_page - 1 && i <= pagination.current_page + 1)
      ) {
      paginationHtml += `
          <button onclick="changePage(${i})" class="${activeClass} px-3 py-2 border-2 rounded-md transition-colors">
          ${i}
        </button>
      `;
      } else if (
        i === pagination.current_page - 2 || 
        i === pagination.current_page + 2
      ) {
        paginationHtml += `<span class="px-1 self-end">...</span>`;
      }
    }
    
    // Add next page button
    paginationHtml += `
      <button onclick="changePage(${Math.min(pagination.total_pages, pagination.current_page + 1)})" 
              class="${pagination.current_page === pagination.total_pages ? 'opacity-50 cursor-not-allowed' : ''} px-3 py-2 border-2 border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition-colors">
        <i class="fas fa-chevron-right"></i>
      </button>
    `;
    
    stockLogsPagination.innerHTML = paginationHtml;
  } else {
    stockLogsPagination.innerHTML = '';
  }
}

// Pagination function for stock logs
window.changePage = (page) => {
  currentPage = page;
  loadStockLogs();
};

// Format date for display
function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

// Global access to pagination function for stock table
window.goToPage = goToPage;
</script>

<?php include 'footer.php'; ?>