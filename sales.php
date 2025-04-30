<?php
include 'authcheck.php'; // Adjust path as needed
requireLogin();           // Ensures the user is logged in
allowRoles(['admin', 'staff']); // Both roles can access
?>

<?php
// sales.php
include 'header.php';
include 'sidebar.php';
?>

<main class="min-h-screen p-6 bg-gray-50">
  <!-- Toast container -->
  <div id="toast"
       class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg hidden z-50">
  </div>

  <!-- Topbar with statistics cards -->
  <div class="mb-8">
    <div class="flex flex-wrap justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Sales Management</h2>
      <button onclick="openAddOrderModal()"
              class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2.5 rounded-md text-sm font-medium transition duration-200 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Add Order
      </button>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-indigo-500 hover:shadow-md transition duration-200 cursor-pointer" onclick="filterByStatus('All Statuses')">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 font-medium">Total Sales</p>
            <p class="text-2xl font-bold text-gray-800" id="totalSalesCount">0</p>
          </div>
          <div class="bg-indigo-100 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-green-500 hover:shadow-md transition duration-200 cursor-pointer" onclick="filterByStatus('Delivered')">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 font-medium">Delivered Orders</p>
            <p class="text-2xl font-bold text-gray-800" id="deliveredOrdersCount">0</p>
          </div>
          <div class="bg-green-100 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-yellow-500 hover:shadow-md transition duration-200 cursor-pointer" onclick="filterByStatus('Pending')">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 font-medium">Pending Orders</p>
            <p class="text-2xl font-bold text-gray-800" id="pendingOrdersCount">0</p>
          </div>
          <div class="bg-yellow-100 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-red-500 hover:shadow-md transition duration-200 cursor-pointer" onclick="filterByStatus('Canceled')">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500 font-medium">Canceled Orders</p>
            <p class="text-2xl font-bold text-gray-800" id="canceledOrdersCount">0</p>
          </div>
          <div class="bg-red-100 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters & Search -->
  <div class="bg-white rounded-lg shadow-sm p-5 mb-6">
    <div class="flex flex-col md:flex-row md:items-center gap-4 justify-between">
      <div class="relative flex-1">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
          </svg>
        </div>
        <input type="text" id="searchInput"
               placeholder="Search by order ID or customer name..."
               class="pl-10 w-full border border-gray-300 rounded-md py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500" />
      </div>
      <div class="flex flex-col md:flex-row gap-4">
        <select id="timeSelect" class="border border-gray-300 rounded-md px-4 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
          <option>All Time</option>
          <option>Today</option>
          <option>This Week</option>
          <option>This Month</option>
        </select>
        <select id="statusSelect" class="border border-gray-300 rounded-md px-4 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
          <option>All Statuses</option>
          <option>Pending</option>
          <option>Confirmed</option>
          <option>Delivered</option>
          <option>Canceled</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Sales Table -->
  <div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex justify-between items-center">
      <h3 class="font-medium text-gray-700">Sales List</h3>
      
      <!-- Bulk Actions -->
      <div id="bulkActionsContainer" class="flex items-center space-x-4 opacity-50 pointer-events-none transition-opacity duration-200">
        <span class="text-sm text-gray-500 font-medium" id="selectedCount">0 selected</span>
        <select id="bulkActionSelect" class="border border-gray-300 rounded-md px-3 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500 text-sm">
          <option value="">Bulk Actions</option>
          <option value="status_pending">Change Status: Pending</option>
          <option value="status_confirmed">Change Status: Confirmed</option>
          <option value="status_delivered">Change Status: Delivered</option>
          <option value="status_canceled">Change Status: Canceled</option>
          <option value="delete">Delete Selected</option>
        </select>
        <button id="applyBulkAction" 
                class="bg-gray-800 text-white px-3 py-1.5 rounded-md text-sm font-medium hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                disabled>
          Apply
        </button>
      </div>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="px-3 py-4">
              <div class="flex items-center">
                <input type="checkbox" id="selectAllSales" 
                       class="w-4 h-4 text-gray-800 border-gray-300 rounded focus:ring-gray-500 focus:ring-offset-1">
              </div>
            </th>
            <th class="px-6 py-4 text-left font-medium">Sales ID</th>
            <th class="px-6 py-4 text-left font-medium">Customer</th>
            <th class="px-6 py-4 text-left font-medium">Product(s)</th>
            <th class="px-6 py-4 text-right font-medium">Total</th>
            <th class="px-6 py-4 text-center font-medium">
              <div class="flex items-center justify-center">
                Status
                <span class="ml-1 text-gray-400 text-xs" title="Click on the status badge to change it">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </span>
              </div>
            </th>
            <th class="px-6 py-4 text-center font-medium">Date & Time</th>
            <th class="px-6 py-4 text-center font-medium">Actions</th>
          </tr>
        </thead>
        <tbody id="sales-list" class="divide-y divide-gray-100">
          <!-- injected by JS -->
        </tbody>
      </table>
    </div>
    
    <!-- Pagination -->
    <div id="pagination-container" class="flex justify-between items-center p-4 border-t border-gray-100 hidden">
      <div class="text-sm text-gray-500">
        Showing <span id="pagination-from">1</span> to <span id="pagination-to">10</span> of <span id="pagination-total">0</span> entries
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
        <span class="text-sm text-gray-500">Items per page:</span>
        <select id="pagination-limit" class="border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-500">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
      </div>
    </div>
    
    <!-- Empty state placeholder for when no sales are found -->
    <div id="empty-sales-placeholder" class="hidden p-8 text-center">
      <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-1">No sales records found</h3>
      <p class="text-gray-500 mb-6">Your sales will appear here once you add them.</p>
      <button onclick="openAddOrderModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200">
        Add Your First Order
      </button>
    </div>

    <!-- Loading state -->
    <div id="sales-loading" class="hidden p-8 text-center">
      <div class="animate-spin mx-auto h-8 w-8 border-4 border-indigo-500 rounded-full border-t-transparent"></div>
      <p class="mt-4 text-gray-500">Loading sales data...</p>
    </div>
  </div>

  <!-- Order Details Modal -->
  <div id="viewOrderModal" class="fixed inset-0 hidden bg-gray-900 bg-opacity-70 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-auto">
      <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800" id="orderDetailTitle">Order Details</h3>
        <button onclick="closeViewOrderModal()" class="text-gray-400 hover:text-gray-600 transition duration-150">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      <div id="orderDetailContent">
        <!-- Order ID and Status -->
        <div class="flex justify-between items-center mb-4">
          <h4 class="text-lg font-medium" id="orderIdDisplay"></h4>
          <span id="orderStatusBadge" class="px-3 py-1 rounded-full text-xs inline-flex items-center"></span>
        </div>
        
        <!-- Customer and Date Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div>
            <h5 class="text-sm text-gray-500 font-medium mb-1">Customer</h5>
            <p class="text-gray-800 font-medium" id="customerNameDisplay"></p>
          </div>
          <div>
            <h5 class="text-sm text-gray-500 font-medium mb-1">Date & Time</h5>
            <p class="text-gray-800" id="orderDateDisplay"></p>
          </div>
          <div>
            <h5 class="text-sm text-gray-500 font-medium mb-1">Phone</h5>
            <p class="text-gray-800" id="customerPhoneDisplay"></p>
          </div>
          <div>
            <h5 class="text-sm text-gray-500 font-medium mb-1">Email</h5>
            <p class="text-gray-800" id="customerEmailDisplay"></p>
          </div>
          <div class="md:col-span-2">
            <h5 class="text-sm text-gray-500 font-medium mb-1">Address</h5>
            <p class="text-gray-800" id="customerAddressDisplay"></p>
          </div>
        </div>
        
        <!-- Order Items -->
        <h5 class="text-sm font-medium text-gray-700 mb-2">Order Items</h5>
        <div class="overflow-x-auto bg-white rounded-md border border-gray-200 mb-4">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50 text-gray-600 font-medium">
                <th class="px-4 py-2 text-left">Product</th>
                <th class="px-4 py-2 text-center">Size</th>
                <th class="px-4 py-2 text-center">Qty</th>
                <th class="px-4 py-2 text-right">Price</th>
                <th class="px-4 py-2 text-right">Total</th>
              </tr>
            </thead>
            <tbody id="orderItemsDisplay" class="divide-y divide-gray-100">
              <!-- Items will be inserted here dynamically -->
            </tbody>
          </table>
        </div>
        
        <!-- Order Totals -->
        <div class="flex justify-end">
          <div class="w-full md:w-64">
            <div class="flex justify-between py-2 text-gray-600">
              <span>Subtotal:</span>
              <span id="subtotalDisplay" class="font-medium"></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-200">
              <span>Discount:</span>
              <span id="discountDisplayView" class="font-medium"></span>
            </div>
            <div class="flex justify-between py-2 text-lg">
              <span class="font-medium">Total:</span>
              <span id="totalDisplayView" class="font-bold"></span>
            </div>
          </div>
        </div>
        
        <!-- Order Note (if present) -->
        <div id="orderNoteContainer" class="mt-6 bg-gray-50 p-3 rounded-md border-l-4 border-blue-400 hidden">
          <h5 class="text-sm font-medium text-gray-700 mb-1">Note</h5>
          <p id="orderNoteDisplay" class="text-gray-600 text-sm"></p>
        </div>
      </div>
      
      <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
        <button type="button" onclick="closeViewOrderModal()"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150">
          Close
        </button>
        <button type="button" onclick="downloadInvoice(document.getElementById('orderIdDisplay').textContent.replace(/Order #(\d+)/g, '$1'))"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Download Invoice
        </button>
        <button type="button" onclick="printReceipt(document.getElementById('orderIdDisplay').textContent.replace(/Order #(\d+)/g, '$1'))"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z" />
          </svg>
          Print
        </button>
      </div>
    </div>
  </div>
</main>

<!-- Add New Order Modal -->
<div id="addOrderModal"
     class="fixed inset-0 hidden bg-gray-900 bg-opacity-70 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-xl p-4 sm:p-5 w-full max-w-7xl max-h-[96vh] overflow-auto">
    <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-200">
      <h3 class="text-xl font-semibold text-gray-800">Add New Order</h3>
      <button onclick="closeAddOrderModal()" class="text-gray-400 hover:text-gray-600 transition duration-150">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <form id="addOrderForm" class="space-y-4">
      <!-- Two column layout for main content -->
      <div class="grid grid-cols-1 lg:grid-cols-5 gap-3 md:gap-4">
        <div class="lg:col-span-3 space-y-5">
          <!-- Customer Information -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-700 mb-2 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Customer Information
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
                <input type="text" name="customer_name" id="customerName"
                       class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500" required />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <input type="text" name="customer_phone" id="customerPhone"
                       class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="customer_email" id="customerEmail"
                       class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <input type="text" name="customer_address" id="customerAddress"
                       class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500" />
              </div>
            </div>
          </div>
          
          <!-- Products -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex justify-between items-center mb-3">
              <h4 class="font-medium text-gray-700 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Products
              </h4>
              <button type="button" onclick="addProductRow()" 
                      class="bg-gray-200 text-gray-700 hover:bg-gray-300 px-3 py-1.5 rounded-md text-sm font-medium transition duration-150 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Product
              </button>
            </div>
            <div class="overflow-x-auto bg-white rounded-md border border-gray-300 shadow-sm">
              <table class="w-full text-sm">
                <thead>
                  <tr class="bg-gray-100 text-gray-700 border-b border-gray-300">
                    <th class="px-4 py-3 text-left font-medium w-1/3">Product</th>
                    <th class="px-4 py-3 text-left font-medium w-1/5">Size</th>
                    <th class="px-4 py-3 text-center font-medium w-20">Quantity</th>
                    <th class="px-4 py-3 text-right font-medium w-24">Price</th>
                    <th class="px-4 py-3 text-right font-medium w-24">Total</th>
                    <th class="w-12"></th>
                  </tr>
                </thead>
                <tbody id="productRows" class="divide-y divide-gray-200 min-h-[150px]">
                  <!-- Product rows will be added here -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <div class="lg:col-span-2 space-y-5">
          <!-- Discount and Order Summary -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-700 mb-2 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
              </svg>
              Discount
            </h4>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Discount %</label>
                <input type="number" name="discount_percentage" id="discountPercentage"
                       min="0" max="100" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500" value="0" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Apply To <span class="text-xs text-gray-500">(Optional)</span></label>
                <select name="discount_product" id="discountProduct"
                        class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                  <option value="">All Products</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Select a specific product or leave empty for all</p>
              </div>
            </div>
          </div>
          
          <!-- Order Summary -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-700 mb-3 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              Order Summary
            </h4>
            <div class="bg-white p-3 rounded-md border border-gray-200">
              <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-600">Subtotal:</span>
                <span class="font-medium" id="orderFormSubtotal">0.00</span>
              </div>
              <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-600">Discount:</span>
                <span class="font-medium text-gray-700" id="discountDisplay">0.00</span>
              </div>
              <div class="flex justify-between py-2 text-lg">
                <span class="font-medium text-gray-700">Total:</span>
                <span class="font-bold text-gray-800" id="totalDisplay">0.00</span>
              </div>
            </div>
          </div>
          
          <!-- Order Details -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-700 mb-3 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Order Details
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="orderStatus"
                        class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                  <option value="pending">Pending</option>
                  <option value="confirmed">Confirmed</option>
                  <option value="delivered">Delivered</option>
                  <option value="canceled">Canceled</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input type="text" disabled
                       value="<?php echo date('F jS, Y'); ?>"
                       class="w-full border border-gray-200 rounded-md py-2 px-3 bg-gray-100 text-gray-500" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Note (optional)</label>
              <textarea name="note" id="orderNote"
                        class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500 h-20"
                        placeholder="Enter any additional notes about this order"></textarea>
            </div>
          </div>
        </div>
      </div>
      
      <div class="flex justify-end gap-3 border-t border-gray-200 pt-4">
        <button type="button" onclick="closeAddOrderModal()"
                class="px-4 py-2.5 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-150">
          Cancel
        </button>
        <button type="submit"
                class="px-5 py-2.5 bg-gray-800 hover:bg-gray-900 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-150">
          Create Order
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Include modularized JavaScript files -->
<script src="js/sales/sales-utils.js"></script>
<script src="js/sales/sales-api.js"></script>
<script src="js/sales/sales-ui.js"></script>
<script src="js/sales/sales-pagination.js"></script>
<script src="js/sales/sales.js"></script>

<?php include 'footer.php'; ?>



