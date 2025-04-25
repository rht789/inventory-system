// Global download variables
const DOWNLOAD_BUTTON_ID = 'downloadTypeBtn';
const DOWNLOAD_DROPDOWN_ID = 'downloadTypeDropdown';
const CSV_BUTTON_ID = 'downloadCsvBtn';
const PDF_BUTTON_ID = 'downloadPdfBtn';
const EXCEL_BUTTON_ID = 'downloadExcelBtn';
const ALL_DOWNLOAD_BUTTON_ID = 'allDownloadBtn';

// Download report in the specified format
function downloadReport(format) {
  console.log(`Starting download in ${format} format`);
  
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
  
  // Get the report data - check both local and global variable
  const reportDataToUse = window.reportData || reportData;
  
  // Check if we should handle the download in the browser or request from server
  if (format === 'csv' && reportDataToUse) {
    // Generate and download CSV in the browser
    exportToCSV(reportDataToUse, reportType);
  } else {
    // Open download URL in a new tab/window for other formats
    const downloadUrl = `../api/reports.php?${params.toString()}`;
    console.log(`Opening download URL: ${downloadUrl}`);
    window.open(downloadUrl, '_blank');
  }
}

// Export report data to CSV
function exportToCSV(data, reportType) {
  console.log('Exporting to CSV:', reportType);
  
  let csvContent = '';
  let filename = '';
  
  // Different handling based on report type
  if (reportType === 'sales') {
    filename = `sales_report_${new Date().toISOString().slice(0, 10)}.csv`;
    
    // Add headers
    csvContent = 'Invoice Number,Date,Customer,Total,Discount,Status,Items\n';
    
    // Add rows
    data.sales.forEach(sale => {
      const row = [
        sale.invoice_number,
        sale.date,
        sale.customer_name,
        sale.total,
        sale.discount_total || 0,
        sale.status,
        sale.item_count || 0
      ];
      
      // Escape values and convert to CSV row
      csvContent += row.map(value => `"${String(value).replace(/"/g, '""')}"`).join(',') + '\n';
    });
  } 
  else if (reportType === 'product_sales') {
    filename = `product_sales_report_${new Date().toISOString().slice(0, 10)}.csv`;
    
    // Add headers
    csvContent = 'Product,Category,Quantity Sold,Revenue,Average Price\n';
    
    // Add rows
    data.product_sales.forEach(product => {
      const row = [
        product.product_name,
        product.category_name || 'Uncategorized',
        product.quantity_sold,
        product.total_sales,
        product.average_price
      ];
      
      // Escape values and convert to CSV row
      csvContent += row.map(value => `"${String(value).replace(/"/g, '""')}"`).join(',') + '\n';
    });
  }
  else if (reportType === 'stock_movement') {
    filename = `stock_movement_report_${new Date().toISOString().slice(0, 10)}.csv`;
    
    // Add headers
    csvContent = 'Date,Product,Type,Quantity,User,Notes\n';
    
    // Add rows
    data.movements.forEach(movement => {
      const row = [
        movement.date,
        movement.product_name,
        movement.type,
        movement.quantity,
        movement.username || 'System',
        movement.notes || ''
      ];
      
      // Escape values and convert to CSV row
      csvContent += row.map(value => `"${String(value).replace(/"/g, '""')}"`).join(',') + '\n';
    });
  }
  else if (reportType === 'user_sales') {
    filename = `user_sales_report_${new Date().toISOString().slice(0, 10)}.csv`;
    
    // Add headers
    csvContent = 'User,Total Sales,Total Items,Revenue,Average Sale\n';
    
    // Add rows
    data.user_sales.forEach(user => {
      const row = [
        user.username,
        user.sale_count,
        user.total_items,
        user.total_revenue,
        user.average_sale
      ];
      
      // Escape values and convert to CSV row
      csvContent += row.map(value => `"${String(value).replace(/"/g, '""')}"`).join(',') + '\n';
    });
  }
  else {
    console.error('Unknown report type for CSV export:', reportType);
    showToast('CSV export not supported for this report type', 'error');
    return;
  }
  
  // Create download link and trigger download
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  
  const link = document.createElement('a');
  link.setAttribute('href', url);
  link.setAttribute('download', filename);
  link.style.visibility = 'hidden';
  
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  
  console.log('CSV download complete');
  showToast('CSV file downloaded successfully', 'success');
}

// Simple utility to show the dropdown
function showDropdown() {
  const dropdown = document.getElementById(DOWNLOAD_DROPDOWN_ID);
  if (dropdown) {
    dropdown.classList.remove('hidden');
    console.log('Dropdown shown');
  } else {
    console.error('Dropdown element not found');
  }
}

// Simple utility to hide the dropdown
function hideDropdown() {
  const dropdown = document.getElementById(DOWNLOAD_DROPDOWN_ID);
  if (dropdown) {
    dropdown.classList.add('hidden');
    console.log('Dropdown hidden');
  }
}

// Toggle dropdown visibility
function toggleDropdown(event) {
  event.preventDefault();
  event.stopPropagation();
  
  const dropdown = document.getElementById(DOWNLOAD_DROPDOWN_ID);
  if (!dropdown) {
    console.error('Dropdown element not found');
    return;
  }
  
  if (dropdown.classList.contains('hidden')) {
    showDropdown();
  } else {
    hideDropdown();
  }
  
  console.log('Dropdown toggled:', !dropdown.classList.contains('hidden'));
}

// Initialize download button handlers
function initDownloadButtons() {
  console.log('Initializing download buttons');
  
  // Get button elements
  const downloadBtn = document.getElementById(DOWNLOAD_BUTTON_ID);
  const csvBtn = document.getElementById(CSV_BUTTON_ID);
  const pdfBtn = document.getElementById(PDF_BUTTON_ID);
  const excelBtn = document.getElementById(EXCEL_BUTTON_ID);
  const allDownloadBtn = document.getElementById(ALL_DOWNLOAD_BUTTON_ID);
  
  // Check for existence of elements
  if (!downloadBtn) {
    console.error(`Download button (${DOWNLOAD_BUTTON_ID}) not found`);
    return;
  }
  
  // Add click handler to download type button
  downloadBtn.onclick = toggleDropdown;
  console.log('Download button click handler attached');
  
  // Add handlers for format buttons
  if (csvBtn) {
    csvBtn.onclick = function(e) {
      e.preventDefault();
      e.stopPropagation();
      downloadReport('csv');
      hideDropdown();
    };
  }
  
  if (pdfBtn) {
    pdfBtn.onclick = function(e) {
      e.preventDefault();
      e.stopPropagation();
      downloadReport('pdf');
      hideDropdown();
    };
  }
  
  if (excelBtn) {
    excelBtn.onclick = function(e) {
      e.preventDefault();
      e.stopPropagation();
      downloadReport('excel');
      hideDropdown();
    };
  }
  
  // All download button
  if (allDownloadBtn) {
    allDownloadBtn.onclick = function(e) {
      e.preventDefault();
      const reportType = document.getElementById('reportType').value;
      if (!reportType) {
        showToast('Please generate a report first', 'error');
        return;
      }
      
      downloadReport('pdf');
    };
  }
  
  // Close dropdown when clicking outside
  document.addEventListener('click', function(event) {
    const dropdown = document.getElementById(DOWNLOAD_DROPDOWN_ID);
    if (!dropdown) return;
    
    const isClickInsideDropdown = dropdown.contains(event.target);
    const isClickOnButton = downloadBtn.contains(event.target);
    
    if (!isClickInsideDropdown && !isClickOnButton && !dropdown.classList.contains('hidden')) {
      hideDropdown();
    }
  });
  
  console.log('Download buttons successfully initialized');
}

// Utility function to check if we have the showToast function
function showToast(message, type = 'success') {
  if (window.showToast) {
    // Use the global showToast if available
    window.showToast(message, type);
  } else {
    // Fallback implementation
    console.log(`Toast (${type}): ${message}`);
    const toast = document.getElementById('toast');
    if (toast) {
      toast.textContent = message;
      toast.className = `fixed bottom-4 right-4 px-4 py-2 rounded-md shadow-lg z-50 ${
        type === 'error' ? 'bg-red-500' : 'bg-green-500'
      } text-white`;
      toast.classList.remove('hidden');
      
      setTimeout(() => {
        toast.classList.add('hidden');
      }, 3000);
    }
  }
}

// Initialize when the document is ready
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOMContentLoaded in download-reports.js');
  initDownloadButtons();
}); 