// Download report in the specified format
function downloadReport(format) {
  const reportType = document.getElementById('reportType').value;
  const timeRange = document.getElementById('timeRange').value;
  
  // Get common filters
  const params = new URLSearchParams();
  params.append('reportType', reportType);
  params.append('timeRange', timeRange);
  params.append('format', format);
  params.append('download', 'true');
  
  // Handle custom date range if selected
  if (timeRange === 'custom') {
    const startDate = document.getElementById('customStartDate').value;
    const endDate = document.getElementById('customEndDate').value;
    
    if (!startDate || !endDate) {
      showToast('Please select both start and end dates for custom range', 'error');
      return;
    }
    
    params.append('startDate', startDate);
    params.append('endDate', endDate);
  }
  
  // Add report-specific filters
  if (reportType === 'sales') {
    const customerId = document.getElementById('customerFilter').value;
    const status = document.getElementById('statusFilter').value;
    
    if (customerId) params.append('customerId', customerId);
    if (status) params.append('status', status);
  } 
  else if (reportType === 'product_sales') {
    const productId = document.getElementById('productFilter').value;
    const categoryId = document.getElementById('categoryFilter').value;
    
    if (productId) params.append('productId', productId);
    if (categoryId) params.append('categoryId', categoryId);
  } 
  else if (reportType === 'stock_movement') {
    const productId = document.getElementById('stockProductFilter').value;
    const movementType = document.getElementById('movementTypeFilter').value;
    const userId = document.getElementById('userFilter').value;
    
    if (productId) params.append('productId', productId);
    if (movementType) params.append('movementType', movementType);
    if (userId) params.append('userId', userId);
  } 
  else if (reportType === 'user_sales') {
    const userId = document.getElementById('userSalesFilter').value;
    const status = document.getElementById('userSalesStatusFilter').value;
    
    if (userId) params.append('userId', userId);
    if (status) params.append('status', status);
  }
  
  // Open download URL in a new tab/window
  window.open(`api/reports.php?${params.toString()}`, '_blank');
}

// Initialize download button handlers
function initDownloadButtons() {
  // Download dropdown toggle
  document.getElementById('downloadTypeBtn').addEventListener('click', function() {
    const dropdown = document.getElementById('downloadTypeDropdown');
    dropdown.classList.toggle('hidden');
  });
  
  // Close dropdown when clicking outside
  document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('downloadTypeDropdown');
    const button = document.getElementById('downloadTypeBtn');
    if (!dropdown.contains(event.target) && !button.contains(event.target) && !dropdown.classList.contains('hidden')) {
      dropdown.classList.add('hidden');
    }
  });
  
  // Download buttons click handlers
  document.getElementById('downloadCsvBtn').addEventListener('click', function() {
    downloadReport('csv');
    document.getElementById('downloadTypeDropdown').classList.add('hidden');
  });
  
  document.getElementById('downloadPdfBtn').addEventListener('click', function() {
    downloadReport('pdf');
    document.getElementById('downloadTypeDropdown').classList.add('hidden');
  });
  
  document.getElementById('downloadExcelBtn').addEventListener('click', function() {
    downloadReport('excel');
    document.getElementById('downloadTypeDropdown').classList.add('hidden');
  });
  
  // All download button
  document.getElementById('allDownloadBtn').addEventListener('click', function() {
    // Get the current report type and download it as PDF by default
    const reportType = document.getElementById('reportType').value;
    if (!reportType) {
      showToast('Please generate a report first', 'error');
      return;
    }
    
    downloadReport('pdf');
  });
} 