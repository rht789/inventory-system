<?php
include 'authcheck.php'; // Adjust path as needed
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

  <!-- Reports Header -->
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Reports</h2>
    <div class="flex items-center gap-2">
      <div class="relative group">
        <button class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-md text-sm flex items-center gap-2">
          <i class="fas fa-info-circle"></i> Download Options
    </button>
        <div class="hidden group-hover:block absolute right-0 top-full mt-2 bg-white rounded-lg shadow-lg p-4 w-64 z-50 text-sm">
          <h4 class="font-semibold text-gray-800 mb-2">Available Download Formats:</h4>
          <ul class="text-gray-600 space-y-1">
            <li class="flex items-center"><i class="fas fa-file-csv text-green-500 mr-2"></i> CSV - For spreadsheet analysis</li>
            <li class="flex items-center"><i class="fas fa-file-pdf text-red-500 mr-2"></i> PDF - For formal reports & printing</li>
            <li class="flex items-center"><i class="fas fa-file-excel text-blue-500 mr-2"></i> Excel - For detailed data manipulation</li>
          </ul>
          <div class="mt-2 pt-2 border-t border-gray-200 text-xs text-gray-500">
            Generate a report then select your preferred format from the download dropdown button.
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters Card -->
  <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold mb-4">Reports Filters</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Report Type -->
      <div>
        <label for="reportType" class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
        <select id="reportType" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
          <option value="sales">Sales Report</option>
          <option value="product_sales">Product Sales Report</option>
          <option value="stock_movement">Stock Movement Report</option>
          <option value="user_sales">User Sales Report</option>
        </select>
      </div>
      
      <!-- Time Range -->
      <div>
        <label for="timeRange" class="block text-sm font-medium text-gray-700 mb-1">Time Range</label>
        <select id="timeRange" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
          <option value="all_time">All Time</option>
          <option value="today">Today</option>
          <option value="this_week">This Week</option>
          <option value="this_month">This Month</option>
          <option value="last_month">Last Month</option>
          <option value="custom">Custom Range</option>
        </select>
      </div>
      
      <!-- Custom Date Range (hidden by default) -->
      <div id="customDateRange" class="hidden md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="customStartDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
          <input type="date" id="customStartDate" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
        </div>
        <div>
          <label for="customEndDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input type="date" id="customEndDate" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
        </div>
      </div>
      
      <!-- Download Type -->
      <div class="self-end hidden" id="downloadTypeContainer">
        <div class="relative inline-block text-left">
          <button id="downloadTypeBtn" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
            Download Type
            <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
          <div id="downloadTypeDropdown" class="hidden origin-top-right absolute right-0 mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50" style="position: absolute;">
            <div class="py-1" role="menu" aria-orientation="vertical">
              <button id="downloadCsvBtn" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" role="menuitem">
                <i class="fas fa-file-csv mr-2"></i> CSV
              </button>
              <button id="downloadPdfBtn" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" role="menuitem">
                <i class="fas fa-file-pdf mr-2"></i> PDF
              </button>
              <button id="downloadExcelBtn" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" role="menuitem">
                <i class="fas fa-file-excel mr-2"></i> Excel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Dynamic Sales Report Filters -->
    <div class="mt-6 pt-4 border-t border-gray-200">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Customer Filter (Always visible for Sales Report) -->
        <div>
          <label for="customerFilter" class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
          <select id="customerFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Customers</option>
            <!-- Will be populated dynamically -->
          </select>
        </div>
        
        <!-- Status Filter (Always visible for Sales Report) -->
        <div>
          <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select id="statusFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="delivered">Delivered</option>
            <option value="canceled">Canceled</option>
          </select>
        </div>
      </div>

      <!-- Hidden dynamic filters (these should still be in the document for dynamic toggling) -->
      <div id="salesFilters" class="dynamic-filter hidden"></div>
      <div id="productSalesFilters" class="dynamic-filter hidden grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="categoryFilter" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select id="categoryFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Categories</option>
            <!-- Will be populated dynamically -->
          </select>
        </div>
        <div>
          <label for="productFilter" class="block text-sm font-medium text-gray-700 mb-1">Product</label>
          <select id="productFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Products</option>
            <!-- Will be populated dynamically -->
          </select>
        </div>
      </div>

      <div id="stockMovementFilters" class="dynamic-filter hidden grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="stockProductFilter" class="block text-sm font-medium text-gray-700 mb-1">Product</label>
          <select id="stockProductFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Products</option>
            <!-- Will be populated dynamically -->
          </select>
        </div>
        <div>
          <label for="userFilter" class="block text-sm font-medium text-gray-700 mb-1">User</label>
          <select id="userFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Users</option>
            <!-- Will be populated dynamically -->
          </select>
        </div>
        <div>
          <label for="movementTypeFilter" class="block text-sm font-medium text-gray-700 mb-1">Movement Type</label>
          <select id="movementTypeFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Types</option>
            <option value="in">Stock In</option>
            <option value="out">Stock Out</option>
            <option value="adjustment">Adjustment</option>
          </select>
        </div>
      </div>

      <div id="batchFilters" class="dynamic-filter hidden grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="userSalesFilter" class="block text-sm font-medium text-gray-700 mb-1">User</label>
          <select id="userSalesFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Users</option>
            <!-- Will be populated dynamically -->
          </select>
        </div>
        <div>
          <label for="userSalesStatusFilter" class="block text-sm font-medium text-gray-700 mb-1">Order Status</label>
          <select id="userSalesStatusFilter" class="block w-full border border-gray-300 rounded-md px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="delivered">Delivered</option>
            <option value="canceled">Canceled</option>
          </select>
        </div>
      </div>
      
      <!-- Generate Report Button - Placed at the very bottom of all filters -->
      <div class="mt-6 flex justify-center">
        <button id="generateReportBtn" class="bg-gray-800 hover:bg-gray-900 text-white px-6 py-3 rounded-md text-sm font-medium w-full max-w-md">
          Generate Report
        </button>
      </div>
    </div>
  </div>

  <!-- Report Summary Cards -->
  <div id="reportSummary" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 hidden">
    <!-- Cards will be added dynamically based on report type -->
  </div>
  
  <!-- Report Content -->
  <div id="reportContent" class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <!-- Report will be rendered here -->
    <div class="text-center py-12 text-gray-500">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="text-lg font-medium">No Report Generated</h3>
      <p class="mt-1">Select a report type and click "Generate Report" to view data.</p>
    </div>
  </div>

  <!-- Chart Container -->
  <div id="chartContainer" class="bg-white rounded-lg shadow-sm p-6 mb-6 hidden">
    <h3 class="text-lg font-semibold mb-4">Data Visualization</h3>
    <div class="chart-wrapper" style="position: relative; height: 350px">
      <canvas id="reportChart"></canvas>
    </div>
  </div>

  <!-- Loading Indicator -->
  <div id="loadingIndicator" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-5 rounded-lg shadow-lg flex flex-col items-center">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-gray-900 mb-3"></div>
      <p class="text-gray-700 font-medium">Generating Report...</p>
    </div>
  </div>
</main>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Include jsPDF for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
<!-- Include SheetJS for Excel export -->
<script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>

<!-- Include our JavaScript files -->
<script src="js/reports/reports.js"></script>
<script src="js/reports/charts.js"></script>
<script src="js/reports/display-reports.js"></script>
<script src="js/reports/download-reports.js"></script>

<?php include 'footer.php'; ?>