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
  
  // Add extensive debugging to help identify the data structure
  console.log(`Download report data structure:`, reportDataToUse);
  
  // Detailed logging of the report structure to help debug
  if (reportDataToUse) {
    console.log(`Report type: ${reportType}`);
    console.log(`Data has sales: ${!!reportDataToUse.sales || !!reportDataToUse.data?.sales}`);
    console.log(`Data has products: ${!!reportDataToUse.products || !!reportDataToUse.data?.products}`);
    console.log(`Data has movements: ${!!reportDataToUse.movements || !!reportDataToUse.data?.movements}`);
    console.log(`Data has users: ${!!reportDataToUse.users || !!reportDataToUse.data?.users}`);
    console.log(`Data has summary: ${!!reportDataToUse.summary}`);
    
    // Log the keys of the data object
    console.log(`Data root keys: ${Object.keys(reportDataToUse)}`);
    if (reportDataToUse.data) {
      console.log(`Data.data keys: ${Object.keys(reportDataToUse.data)}`);
    }
  }
  
  // Check if we should handle the download in the browser or request from server
  if (format === 'csv' && reportDataToUse) {
    // Generate and download CSV in the browser
    exportToCSV(reportDataToUse, reportType);
  } 
  else if (format === 'pdf' && reportDataToUse) {
    // Generate and download PDF in the browser
    exportToPDF(reportDataToUse, reportType);
  }
  else if (format === 'excel' && reportDataToUse) {
    // Generate and download Excel in the browser
    exportToExcel(reportDataToUse, reportType);
  }
  else {
    // Open download URL in a new tab/window for other formats
    const downloadUrl = `../api/reports.php?${params.toString()}`;
    console.log(`Opening download URL: ${downloadUrl}`);
    window.open(downloadUrl, '_blank');
  }
}

// Export report data to CSV
function exportToCSV(data, reportType) {
  console.log('Exporting to CSV:', reportType);
  console.log('Data structure:', data);
  
  let csvContent = '';
  let filename = '';
  let hasData = false;
  
  // Different handling based on report type
  if (reportType === 'sales') {
    filename = `sales_report_${new Date().toISOString().slice(0, 10)}.csv`;
    
    // Get sales data from the appropriate location
    const salesData = data.sales || data.data?.sales || [];
    
    if (salesData && salesData.length > 0) {
      hasData = true;
      
      // Add headers
      csvContent = 'Invoice Number,Date,Customer,Total,Discount,Status,Items\n';
      
      // Add rows
      salesData.forEach(sale => {
        const row = [
          sale.invoice_number || '',
          sale.date || '',
          sale.customer_name || '',
          sale.total || 0,
          sale.discount_total || 0,
          sale.status || '',
          sale.item_count || 0
        ];
        
        // Escape values and convert to CSV row
        csvContent += row.map(value => `"${String(value).replace(/"/g, '""')}"`).join(',') + '\n';
      });
    }
  } 
  else if (reportType === 'product_sales') {
    filename = `product_sales_report_${new Date().toISOString().slice(0, 10)}.csv`;
    
    // Get product data from the appropriate location
    const productData = data.products || data.data?.products || [];
    
    if (productData && productData.length > 0) {
      hasData = true;
      
      // Add headers
      csvContent = 'Product,Category,Quantity Sold,Revenue,Average Price\n';
      
      // Add rows
      productData.forEach(product => {
        const row = [
          product.name || '',
          product.category || '',
          product.quantity_sold || 0,
          product.revenue || 0,
          product.average_price || 0
        ];
        
        // Escape values and convert to CSV row
        csvContent += row.map(value => `"${String(value).replace(/"/g, '""')}"`).join(',') + '\n';
      });
    }
  }
  else if (reportType === 'stock_movement') {
    filename = `stock_movement_report_${new Date().toISOString().slice(0, 10)}.csv`;
    
    // Get movement data from the appropriate location
    const movementData = data.movements || data.data?.movements || [];
    
    if (movementData && movementData.length > 0) {
      hasData = true;
      
      // Add headers
      csvContent = 'Date,Product,Size,Type,Quantity,User,Reference\n';
      
      // Add rows
      movementData.forEach(movement => {
        const row = [
          movement.date || '',
          movement.product_name || '',
          movement.size_name || 'Default',
          movement.type || '',
          movement.quantity || 0,
          movement.user_name || '',
          movement.reason || ''
        ];
        
        // Escape values and convert to CSV row
        csvContent += row.map(value => `"${String(value).replace(/"/g, '""')}"`).join(',') + '\n';
      });
    }
  }
  else if (reportType === 'user_sales') {
    filename = `user_sales_report_${new Date().toISOString().slice(0, 10)}.csv`;
    
    // Get user sales data from the appropriate location
    const userSalesData = data.users || data.data?.users || [];
    
    if (userSalesData && userSalesData.length > 0) {
      hasData = true;
      
      // Add headers
      csvContent = 'User,Role,Total Sales,Total Items,Revenue,Average Sale\n';
      
      // Add rows
      userSalesData.forEach(user => {
        const row = [
          user.username || '',
          user.role || '',
          user.sales_count || 0,
          user.items_sold || 0,
          user.revenue || 0,
          user.average_sale || 0
        ];
        
        // Escape values and convert to CSV row
        csvContent += row.map(value => `"${String(value).replace(/"/g, '""')}"`).join(',') + '\n';
      });
    }
  }
  else {
    console.error('Unknown report type for CSV export:', reportType);
    showToast('CSV export not supported for this report type', 'error');
    return;
  }
  
  if (!hasData) {
    console.error('No data available to export for', reportType);
    showToast('No data available to export', 'error');
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

// Export report data to PDF
function exportToPDF(data, reportType) {
  console.log('Exporting to PDF:', reportType);
  console.log('Data structure:', data);
  
  try {
    // Make sure jsPDF is loaded
    if (typeof jspdf === 'undefined' && typeof jsPDF === 'undefined') {
      console.error('jsPDF library not loaded');
      showToast('PDF generation library not loaded', 'error');
      return;
    }
    
    // Create new jsPDF instance with proper orientation
    let doc;
    if (typeof jspdf !== 'undefined') {
      const { jsPDF } = jspdf;
      doc = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4'
      });
    } else {
      doc = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4'
      });
    }
    
    // Set filename based on report type
    let filename = `${reportType}_report_${new Date().toISOString().slice(0, 10)}.pdf`;
    
    // Better table styling with grid lines and improved colors
    const tableStyle = {
      theme: 'grid',
      headStyles: { 
        fillColor: [41, 59, 95], // Dark blue
        textColor: [255, 255, 255],
        fontStyle: 'bold',
        halign: 'center'
      },
      bodyStyles: {
        lineColor: [220, 220, 220],
        lineWidth: 0.1
      },
      alternateRowStyles: {
        fillColor: [240, 242, 245]
      },
      margin: { top: 10, bottom: 10 },
      styles: { 
        overflow: 'linebreak',
        cellPadding: 4,
        fontSize: 8,
        font: 'helvetica'
      }
    };
    
    // Get report date and time
    const reportDate = new Date().toLocaleDateString();
    const reportTime = new Date().toLocaleTimeString();

    // Create header with better design
    // Add a colored header bar
    doc.setFillColor(41, 59, 95); // Dark blue header
    doc.rect(0, 0, doc.internal.pageSize.width, 25, 'F');

    // Add report title
    let title = 'Report';
    switch(reportType) {
      case 'sales': title = 'SALES REPORT'; break;
      case 'product_sales': title = 'PRODUCT SALES REPORT'; break;
      case 'stock_movement': title = 'STOCK MOVEMENT REPORT'; break;
      case 'user_sales': title = 'USER SALES REPORT'; break;
    }

    // Title
    doc.setTextColor(255, 255, 255); // White text
    doc.setFontSize(18);
    doc.setFont('helvetica', 'bold');
    doc.text(title, doc.internal.pageSize.width / 2, 12, { align: 'center' });

    // Subtitle with date
    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    doc.text(`Generated on: ${reportDate} at ${reportTime}`, doc.internal.pageSize.width / 2, 18, { align: 'center' });

    // Reset text color for rest of document
    doc.setTextColor(0, 0, 0);

    // Y position tracker - start after header
    let yPos = 35;
    
    // Add footer with page numbers - simplified version without colors or undefined variables
    const totalPages = doc.internal.getNumberOfPages();
    for (let i = 1; i <= totalPages; i++) {
      doc.setPage(i);
      doc.setFontSize(8);
      doc.setTextColor(100, 100, 100); // Gray color
      
      // Page numbers
      doc.text(`Page ${i} of ${totalPages}`, doc.internal.pageSize.width / 2, doc.internal.pageSize.height - 10, { align: 'center' });
      
      // System name
      doc.text("Inventory Management System", 15, doc.internal.pageSize.height - 10);
      
      // Date on the right
      doc.text(reportDate, doc.internal.pageSize.width - 15, doc.internal.pageSize.height - 10, { align: 'right' });
    }
    
    // Different handling based on report type
    if (reportType === 'sales') {
      // Get sales data
      const salesData = data.sales || data.data?.sales || [];
      
      // Add summary section with improved styling
      // Create a summary box with shadow effect
      doc.setDrawColor(180, 180, 180);
      doc.setFillColor(248, 249, 250);
      doc.roundedRect(15, yPos, doc.internal.pageSize.width-30, 26, 3, 3, 'FD');
      
      // Add summary heading
      yPos += 7;
      doc.setFontSize(11);
      doc.setTextColor(41, 59, 95);
      doc.setFont('helvetica', 'bold');
      doc.text('SUMMARY', 20, yPos);
      
      // Add summary data in columns
      yPos += 7;
      doc.setFontSize(9);
      doc.setTextColor(60, 60, 60);
      doc.setFont('helvetica', 'normal');
      
      // Split into three columns
      const colWidth = (doc.internal.pageSize.width-40)/3;
      
      // Column 1: Total Sales
      doc.text('Total Sales:', 20, yPos);
      doc.setFont('helvetica', 'bold');
      doc.text(`${data.summary.totalSales || 0}`, 20, yPos+6);
      
      // Column 2: Total Revenue
      doc.setFont('helvetica', 'normal');
      doc.text('Total Revenue:', 20 + colWidth, yPos);
      doc.setFont('helvetica', 'bold');
      doc.text(`${parseFloat(data.summary.totalRevenue || 0).toFixed(2)} Taka`, 20 + colWidth, yPos+6);
      
      // Column 3: Average Sale
      doc.setFont('helvetica', 'normal');
      doc.text('Average Sale:', 20 + (colWidth*2), yPos);
      doc.setFont('helvetica', 'bold');
      doc.text(`${parseFloat(data.summary.averageSale || 0).toFixed(2)} Taka`, 20 + (colWidth*2), yPos+6);
      
      // Reset font and position for next section
      doc.setFont('helvetica', 'normal');
      doc.setTextColor(0, 0, 0);
      yPos += 15;
      
      // Sales data heading
      doc.setFillColor(230, 236, 245);
      doc.rect(15, yPos, doc.internal.pageSize.width-30, 8, 'F');
      
      doc.setTextColor(41, 59, 95);
      doc.setFontSize(11);
      doc.setFont('helvetica', 'bold');
      doc.text('SALES DATA', 20, yPos+5.5);
      
      yPos += 12;
      
      // Check if we have data to display
      if (salesData && salesData.length > 0) {
        // Setup column headers
        const headers = [['Invoice #', 'Date', 'Customer', 'Status', 'Items', 'Total']];
        
        // Format data for table
        const tableBody = salesData.map(sale => [
          sale.invoice_number || '',
          sale.date || '',
          sale.customer_name || '',
          sale.status || '',
          sale.item_count || 0,
          `${parseFloat(sale.total || 0).toFixed(2)} Taka`
        ]);
        
        // Enhanced table styling
        const enhancedTableStyle = {
          ...tableStyle,
          startY: yPos,
          columnStyles: {
            0: { cellWidth: 25 },
            1: { cellWidth: 25 },
            2: { cellWidth: 45 },
            3: { cellWidth: 25, halign: 'center' },
            4: { cellWidth: 15, halign: 'center' },
            5: { cellWidth: 35, halign: 'right' }
          },
          didDrawCell: (data) => {
            // Add colored background for status cells
            if (data.section === 'body' && data.column.index === 3) {
              const status = data.cell.text[0].toLowerCase();
              let fillColor;
              
              switch(status) {
                case 'delivered':
                  fillColor = [200, 250, 200]; // Light green
                  break;
                case 'confirmed':
                  fillColor = [200, 230, 255]; // Light blue
                  break;
                case 'pending':
                  fillColor = [255, 240, 200]; // Light yellow
                  break;
                case 'canceled':
                  fillColor = [255, 200, 200]; // Light red
                  break;
                default:
                  fillColor = [240, 240, 240]; // Light gray
              }
              
              doc.setFillColor(...fillColor);
              doc.rect(data.cell.x, data.cell.y, data.cell.width, data.cell.height, 'F');
              
              // Re-add text since we covered it
              doc.setTextColor(0, 0, 0);
              doc.text(
                data.cell.text,
                data.cell.x + data.cell.width / 2,
                data.cell.y + data.cell.height / 2,
                { align: 'center', baseline: 'middle' }
              );
            }
          }
        };
        
        // Add the table
        doc.autoTable({
          head: headers,
          body: tableBody,
          ...enhancedTableStyle
        });
      } else {
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text('No sales data found for the selected filters.', 20, yPos + 10);
      }
    } 
    else if (reportType === 'product_sales') {
      // Get product sales data
      const productSalesData = data.productSales || data.data?.productSales || [];
      
      // Add summary section with modern styling
      // Create a summary box
      doc.setDrawColor(180, 180, 180);
      doc.setFillColor(248, 249, 250);
      doc.roundedRect(15, yPos, doc.internal.pageSize.width-30, 26, 3, 3, 'FD');
      
      // Add summary heading
      yPos += 7;
      doc.setFontSize(11);
      doc.setTextColor(41, 59, 95);
      doc.setFont('helvetica', 'bold');
      doc.text('PRODUCT SALES SUMMARY', 20, yPos);
      
      // Add summary data in columns
      yPos += 7;
      doc.setFontSize(9);
      doc.setTextColor(60, 60, 60);
      doc.setFont('helvetica', 'normal');
      
      // Split into three columns
      const colWidth = (doc.internal.pageSize.width-40)/3;
      
      // Column 1: Total Products
      doc.text('Total Products:', 20, yPos);
      doc.setFont('helvetica', 'bold');
      doc.text(`${data.summary.totalProducts || 0}`, 20, yPos+6);
      
      // Column 2: Total Units Sold
      doc.setFont('helvetica', 'normal');
      doc.text('Total Units Sold:', 20 + colWidth, yPos);
      doc.setFont('helvetica', 'bold');
      doc.text(`${data.summary.totalUnitsSold || 0}`, 20 + colWidth, yPos+6);
      
      // Column 3: Total Revenue
      doc.setFont('helvetica', 'normal');
      doc.text('Total Revenue:', 20 + (colWidth*2), yPos);
      doc.setFont('helvetica', 'bold');
      doc.text(`${parseFloat(data.summary.totalRevenue || 0).toFixed(2)} Taka`, 20 + (colWidth*2), yPos+6);
      
      // Reset font and position for next section
      doc.setFont('helvetica', 'normal');
      doc.setTextColor(0, 0, 0);
      yPos += 15;
      
      // Product sales data heading
      doc.setFillColor(230, 236, 245);
      doc.rect(15, yPos, doc.internal.pageSize.width-30, 8, 'F');
      
      doc.setTextColor(41, 59, 95);
      doc.setFontSize(11);
      doc.setFont('helvetica', 'bold');
      doc.text('PRODUCT SALES DATA', 20, yPos+5.5);
      
      yPos += 12;
      
      // Check if we have data to display
      if (productSalesData && productSalesData.length > 0) {
        // Setup column headers for product sales
        const headers = [['Product Name', 'SKU', 'Category', 'Quantity Sold', 'Unit Price', 'Total Revenue']];
        
        // Format data for table
        const tableBody = productSalesData.map(product => [
          product.product_name || '',
          product.sku || '',
          product.category || '',
          product.quantity_sold || 0,
          `${parseFloat(product.unit_price || 0).toFixed(2)} Taka`,
          `${parseFloat(product.total_revenue || 0).toFixed(2)} Taka`
        ]);
        
        // Enhanced styling for product sales table
        const productTableStyle = {
          ...tableStyle,
          startY: yPos,
          columnStyles: {
            0: { cellWidth: 45 },
            1: { cellWidth: 25 },
            2: { cellWidth: 30 },
            3: { cellWidth: 25, halign: 'center' },
            4: { cellWidth: 25, halign: 'right' },
            5: { cellWidth: 30, halign: 'right' }
          },
          didDrawCell: (data) => {
            // Add visual indicators for top-selling products
            if (data.section === 'body' && data.row.index < 3 && data.column.index === 0) {
              // Highlight top 3 products with a star or badge
              const x = data.cell.x + 2;
              const y = data.cell.y + 3;
              
              // Draw star indicator for top 3 products
              doc.setFillColor(255, 193, 7); // Gold/amber color
              
              if (data.row.index === 0) {
                // Gold star for #1 product
                doc.circle(x, y, 2, 'F');
                doc.setTextColor(255, 193, 7);
                doc.setFontSize(7);
                doc.text('★', x-1.3, y+1.8);
              } else if (data.row.index === 1) {
                // Silver for #2 product
                doc.setFillColor(200, 200, 200);
                doc.circle(x, y, 1.8, 'F');
                doc.setTextColor(200, 200, 200);
                doc.setFontSize(6);
                doc.text('★', x-1.1, y+1.5);
              } else if (data.row.index === 2) {
                // Bronze for #3 product
                doc.setFillColor(176, 141, 87);
                doc.circle(x, y, 1.5, 'F');
                doc.setTextColor(176, 141, 87);
                doc.setFontSize(5);
                doc.text('★', x-0.9, y+1.2);
              }
              
              // Reset text color
              doc.setTextColor(0, 0, 0);
              doc.setFontSize(10);
            }
          }
        };
        
        // Add the table
        doc.autoTable({
          head: headers,
          body: tableBody,
          ...productTableStyle
        });
        
        // If there's enough data, add a mini bar chart for top 5 products
        if (productSalesData.length >= 5) {
          const finalY = doc.previousAutoTable.finalY;
          doc.setFontSize(11);
          doc.setTextColor(41, 59, 95);
          doc.setFont('helvetica', 'bold');
          doc.text('TOP 5 PRODUCTS BY SALES', 20, finalY + 15);
          
          // Draw simple bar chart
          const top5Products = productSalesData.slice(0, 5);
          const maxRevenue = Math.max(...top5Products.map(p => parseFloat(p.total_revenue || 0)));
          const chartWidth = doc.internal.pageSize.width - 60;
          const barHeight = 8;
          const gapBetweenBars = 5;
          
          let chartY = finalY + 25;
          
          top5Products.forEach((product, index) => {
            const revenue = parseFloat(product.total_revenue || 0);
            const barWidth = (revenue / maxRevenue) * chartWidth;
            
            // Draw product name
            doc.setFontSize(8);
            doc.setTextColor(60, 60, 60);
            doc.setFont('helvetica', 'normal');
            doc.text(product.product_name || 'Unknown', 20, chartY);
            
            // Draw bar
            doc.setFillColor(41 + (index * 30), 59 + (index * 10), 95);
            doc.rect(20, chartY + 2, barWidth, barHeight, 'F');
            
            // Draw value at end of bar
            doc.setFontSize(8);
            doc.setTextColor(0, 0, 0);
            doc.text(`${revenue.toFixed(2)} Taka`, 20 + barWidth + 5, chartY + 7);
            
            chartY += barHeight + gapBetweenBars;
          });
        }
      } else {
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text('No product sales data found for the selected filters.', 20, yPos + 10);
      }
    }
    else if (reportType === 'stock_movement') {
      // Get movement data
      const movementData = data.movements || data.data?.movements || [];
      
      // Add summary section with improved styling
      // Create a summary box with shadow effect
      doc.setDrawColor(180, 180, 180);
      doc.setFillColor(248, 249, 250);
      doc.roundedRect(15, yPos, doc.internal.pageSize.width-30, 26, 3, 3, 'FD');
      
      // Add summary heading
      yPos += 7;
      doc.setFontSize(11);
      doc.setTextColor(41, 59, 95);
      doc.setFont('helvetica', 'bold');
      doc.text('STOCK MOVEMENT SUMMARY', 20, yPos);
      
      // Add summary data in columns
      yPos += 7;
      doc.setFontSize(9);
      doc.setTextColor(60, 60, 60);
      doc.setFont('helvetica', 'normal');
      
      // Split into three columns
      const colWidth = (doc.internal.pageSize.width-40)/3;
      
      // Column 1: Total Movements
      doc.text('Total Movements:', 20, yPos);
      doc.setFont('helvetica', 'bold');
      doc.text(`${data.summary.totalMovements || 0}`, 20, yPos+6);
      
      // Column 2: Stock In
      doc.setFont('helvetica', 'normal');
      doc.text('Stock In:', 20 + colWidth, yPos);
      doc.setFont('helvetica', 'bold');
      doc.text(`${data.summary.totalStockIn || 0}`, 20 + colWidth, yPos+6);
      
      // Column 3: Stock Out
      doc.setFont('helvetica', 'normal');
      doc.text('Stock Out:', 20 + (colWidth*2), yPos);
      doc.setFont('helvetica', 'bold');
      doc.text(`${data.summary.totalStockOut || 0}`, 20 + (colWidth*2), yPos+6);
      
      // Reset font and position for next section
      doc.setFont('helvetica', 'normal');
      doc.setTextColor(0, 0, 0);
      yPos += 15;
      
      // Stock movement data heading
      doc.setFillColor(230, 236, 245);
      doc.rect(15, yPos, doc.internal.pageSize.width-30, 8, 'F');
      
      doc.setTextColor(41, 59, 95);
      doc.setFontSize(11);
      doc.setFont('helvetica', 'bold');
      doc.text('STOCK MOVEMENT DATA', 20, yPos+5.5);
      
      yPos += 12;
      
      // Add data table
      if (movementData && movementData.length > 0) {
        const headers = [['Date', 'Product', 'Type', 'Quantity', 'User', 'Reference']];
        
        const tableBody = movementData.map(movement => [
          movement.date || '',
          movement.product_name || '',
          movement.type || '',
          movement.quantity || 0,
          movement.user_name || '',
          movement.reason || ''
        ]);
        
        doc.autoTable({
          startY: yPos,
          head: headers,
          body: tableBody,
          ...tableStyle,
          columnStyles: { 
            0: { cellWidth: 25 },
            1: { cellWidth: 45 },
            2: { cellWidth: 20, halign: 'center' },
            3: { cellWidth: 20, halign: 'center' },
            4: { cellWidth: 25 },
            5: { cellWidth: 35 }
          },
          didDrawCell: (data) => {
            // Add a colored background for the movement type cell
            if (data.section === 'body' && data.column.index === 2) {
              const type = data.cell.text[0].toLowerCase();
              let fillColor;
              
              switch(type) {
                case 'in':
                case 'stock in':
                  fillColor = [200, 250, 200]; // Light green
                  break;
                case 'out':
                case 'stock out':
                  fillColor = [255, 200, 200]; // Light red
                  break;
                case 'adjustment':
                  fillColor = [200, 230, 255]; // Light blue
                  break;
                default:
                  fillColor = [240, 240, 240]; // Light gray
              }
              
              doc.setFillColor(...fillColor);
              doc.rect(data.cell.x, data.cell.y, data.cell.width, data.cell.height, 'F');
              
              // Re-add text since we covered it
              doc.setTextColor(0, 0, 0);
              doc.text(
                data.cell.text,
                data.cell.x + data.cell.width / 2,
                data.cell.y + data.cell.height / 2,
                { align: 'center', baseline: 'middle' }
              );
            }
          }
        });
      } else {
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text('No stock movement data found for the selected filters.', 20, yPos + 10);
      }
    }
    else if (reportType === 'user_sales') {
      // Get user sales data
      const userSalesData = data.users || data.data?.users || [];
      
      // Add summary section with improved styling
      // Create a summary box with shadow effect
      doc.setDrawColor(180, 180, 180);
      doc.setFillColor(248, 249, 250);
      doc.roundedRect(15, yPos, doc.internal.pageSize.width-30, 26, 3, 3, 'FD');
      
      // Add summary heading
      yPos += 7;
      doc.setFontSize(11);
      doc.setTextColor(41, 59, 95);
      doc.setFont('helvetica', 'bold');
      doc.text('USER SALES SUMMARY', 20, yPos);
      
      // Add summary data in columns
      yPos += 7;
      doc.setFontSize(9);
      doc.setTextColor(60, 60, 60);
      doc.setFont('helvetica', 'normal');
      
      // Split into three columns
      const colWidth = (doc.internal.pageSize.width-40)/3;
      
      // Column 1: Total Users
      doc.text('Total Users:', 20, yPos);
      doc.setFont('helvetica', 'bold');
      doc.text(`${data.summary.totalUsers || 0}`, 20, yPos+6);
      
      // Column 2: Total Sales
      doc.setFont('helvetica', 'normal');
      doc.text('Total Sales:', 20 + colWidth, yPos);
      doc.setFont('helvetica', 'bold');
      doc.text(`${data.summary.totalSales || 0}`, 20 + colWidth, yPos+6);
      
      // Column 3: Total Revenue
      doc.setFont('helvetica', 'normal');
      doc.text('Total Revenue:', 20 + (colWidth*2), yPos);
      doc.setFont('helvetica', 'bold');
      doc.text(`${parseFloat(data.summary.totalRevenue || 0).toFixed(2)} Taka`, 20 + (colWidth*2), yPos+6);
      
      // Reset font and position for next section
      doc.setFont('helvetica', 'normal');
      doc.setTextColor(0, 0, 0);
      yPos += 15;
      
      // User sales data heading
      doc.setFillColor(230, 236, 245);
      doc.rect(15, yPos, doc.internal.pageSize.width-30, 8, 'F');
      
      doc.setTextColor(41, 59, 95);
      doc.setFontSize(11);
      doc.setFont('helvetica', 'bold');
      doc.text('USER SALES DATA', 20, yPos+5.5);
      
      yPos += 12;
      
      // Add data table
      if (userSalesData && userSalesData.length > 0) {
        const headers = [['User', 'Role', 'Total Sales', 'Total Items', 'Revenue', 'Avg. Sale']];
        
        const tableBody = userSalesData.map(user => [
          user.username || '',
          user.role || '',
          user.sales_count || 0,
          user.items_sold || 0,
          `${parseFloat(user.revenue || 0).toFixed(2)} Taka`,
          `${parseFloat(user.average_sale || 0).toFixed(2)} Taka`
        ]);
        
        doc.autoTable({
          startY: yPos,
          head: headers,
          body: tableBody,
          ...tableStyle,
          columnStyles: { 
            0: { cellWidth: 30 },
            1: { cellWidth: 25, halign: 'center' },
            2: { cellWidth: 25, halign: 'center' },
            3: { cellWidth: 25, halign: 'center' },
            4: { cellWidth: 40, halign: 'right' },
            5: { cellWidth: 30, halign: 'right' }
          }
        });
      } else {
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(10);
        doc.setTextColor(100, 100, 100);
        doc.text('No user sales data found for the selected filters.', 20, yPos + 10);
      }
    }
    
    // Save the PDF
    doc.save(filename);
    console.log('PDF download complete');
    showToast('PDF file downloaded successfully', 'success');
  } catch (error) {
    console.error('Error generating PDF:', error);
    showToast('Error generating PDF file', 'error');
  }
}

// Export report data to Excel
function exportToExcel(data, reportType) {
  console.log('Exporting to Excel:', reportType);
  console.log('Full data structure:', data);
  
  try {
    // Check if XLSX library is available
    if (typeof XLSX === 'undefined') {
      console.error('XLSX library not loaded');
      showToast('Excel generation library not loaded', 'error');
      return;
    }
    
    // Generate filename
    const filename = `${reportType}_report_${new Date().toISOString().slice(0, 10)}.xlsx`;
    
    // Create new workbook
    const wb = XLSX.utils.book_new();
    
    // Debug available data with simplified logging
    console.log('Data keys:', Object.keys(data));
    if (data.data) console.log('data.data keys:', Object.keys(data.data));
    if (data.summary) console.log('summary keys:', Object.keys(data.summary));
    
    // Find data source based on report type
    let reportData = [];
    let summaryData = [];
    
    // Extract the correct data based on report type
    switch (reportType) {
      case 'sales':
        // Try all possible locations for sales data
        if (Array.isArray(data.sales)) {
          reportData = data.sales;
        } else if (data.data && Array.isArray(data.data.sales)) {
          reportData = data.data.sales;
        } else if (data.data && data.data.data && Array.isArray(data.data.data.sales)) {
          reportData = data.data.data.sales;
        }
        console.log(`Found ${reportData.length} sales records`);
        
        // Create sales data worksheet
        if (reportData.length > 0) {
          // Create data rows
          const rows = [];
          rows.push(['Invoice Number', 'Date', 'Customer', 'Total', 'Discount', 'Status', 'Items']);
          
          reportData.forEach(sale => {
            rows.push([
              sale.invoice_number || '',
              sale.date || '',
              sale.customer_name || '',
              parseFloat(sale.total || 0).toFixed(2),
              parseFloat(sale.discount_total || 0).toFixed(2),
              sale.status || '',
              sale.item_count || 0
            ]);
          });
          
          // Add to workbook
          const dataWS = XLSX.utils.aoa_to_sheet(rows);
          XLSX.utils.book_append_sheet(wb, dataWS, 'Sales Data');
        }
        
        // Add summary if available
        if (data.summary) {
          summaryData = [
            ['Total Sales', 'Total Revenue', 'Average Sale'],
            [
              data.summary.totalSales || reportData.length || 0,
              parseFloat(data.summary.totalRevenue || 0).toFixed(2),
              parseFloat(data.summary.averageSale || 0).toFixed(2)
            ]
          ];
          
          const summaryWS = XLSX.utils.aoa_to_sheet(summaryData);
          XLSX.utils.book_append_sheet(wb, summaryWS, 'Summary');
        }
        break;
        
      case 'product_sales':
        // Try all possible locations for product sales data
        if (Array.isArray(data.products)) {
          reportData = data.products;
        } else if (data.data && Array.isArray(data.data.products)) {
          reportData = data.data.products;
        } else if (Array.isArray(data.productSales)) {
          reportData = data.productSales;
        } else if (data.data && Array.isArray(data.data.productSales)) {
          reportData = data.data.productSales;
        } else if (data.data && data.data.data && Array.isArray(data.data.data.products)) {
          reportData = data.data.data.products;
        } else if (data.data && data.data.data && Array.isArray(data.data.data.productSales)) {
          reportData = data.data.data.productSales;
        }
        
        console.log(`Found ${reportData.length} product sales records`);
        
        // Create product sales data worksheet
        if (reportData.length > 0) {
          // Create data rows
          const rows = [];
          rows.push(['Product', 'SKU', 'Category', 'Quantity Sold', 'Revenue', 'Average Price']);
          
          reportData.forEach(product => {
            rows.push([
              product.name || product.product_name || '',
              product.sku || '',
              product.category || '',
              product.quantity_sold || 0,
              parseFloat(product.revenue || product.total_revenue || 0).toFixed(2),
              parseFloat(product.average_price || product.unit_price || 0).toFixed(2)
            ]);
          });
          
          // Add to workbook
          const dataWS = XLSX.utils.aoa_to_sheet(rows);
          XLSX.utils.book_append_sheet(wb, dataWS, 'Product Sales Data');
        }
        
        // Add summary if available
        if (data.summary) {
          summaryData = [
            ['Total Products', 'Total Units Sold', 'Total Revenue'],
            [
              data.summary.totalProducts || reportData.length || 0,
              data.summary.totalUnitsSold || 0,
              parseFloat(data.summary.totalRevenue || 0).toFixed(2)
            ]
          ];
          
          const summaryWS = XLSX.utils.aoa_to_sheet(summaryData);
          XLSX.utils.book_append_sheet(wb, summaryWS, 'Summary');
        }
        break;
        
      case 'stock_movement':
        // Try all possible locations for movement data
        if (Array.isArray(data.movements)) {
          reportData = data.movements;
        } else if (data.data && Array.isArray(data.data.movements)) {
          reportData = data.data.movements;
        } else if (data.data && data.data.data && Array.isArray(data.data.data.movements)) {
          reportData = data.data.data.movements;
        }
        
        console.log(`Found ${reportData.length} stock movement records`);
        
        // Create stock movement data worksheet
        if (reportData.length > 0) {
          // Create data rows
          const rows = [];
          rows.push(['Date', 'Product', 'Size', 'Type', 'Quantity', 'User', 'Reference']);
          
          reportData.forEach(movement => {
            rows.push([
              movement.date || '',
              movement.product_name || '',
              movement.size_name || 'Default',
              movement.type || '',
              movement.quantity || 0,
              movement.user_name || '',
              movement.reason || ''
            ]);
          });
          
          // Add to workbook
          const dataWS = XLSX.utils.aoa_to_sheet(rows);
          XLSX.utils.book_append_sheet(wb, dataWS, 'Stock Movement Data');
        }
        
        // Add summary if available
        if (data.summary) {
          summaryData = [
            ['Total Movements', 'Stock In', 'Stock Out'],
            [
              data.summary.totalMovements || reportData.length || 0,
              data.summary.totalStockIn || 0,
              data.summary.totalStockOut || 0
            ]
          ];
          
          const summaryWS = XLSX.utils.aoa_to_sheet(summaryData);
          XLSX.utils.book_append_sheet(wb, summaryWS, 'Summary');
        }
        break;
        
      case 'user_sales':
        // Try all possible locations for user sales data
        if (Array.isArray(data.users)) {
          reportData = data.users;
        } else if (data.data && Array.isArray(data.data.users)) {
          reportData = data.data.users;
        } else if (data.data && data.data.data && Array.isArray(data.data.data.users)) {
          reportData = data.data.data.users;
        }
        
        console.log(`Found ${reportData.length} user sales records`);
        
        // Create user sales data worksheet
        if (reportData.length > 0) {
          // Create data rows
          const rows = [];
          rows.push(['User', 'Role', 'Total Sales', 'Total Items', 'Revenue', 'Average Sale']);
          
          reportData.forEach(user => {
            rows.push([
              user.username || '',
              user.role || '',
              user.sales_count || 0,
              user.items_sold || 0,
              parseFloat(user.revenue || 0).toFixed(2),
              parseFloat(user.average_sale || 0).toFixed(2)
            ]);
          });
          
          // Add to workbook
          const dataWS = XLSX.utils.aoa_to_sheet(rows);
          XLSX.utils.book_append_sheet(wb, dataWS, 'User Sales Data');
        }
        
        // Add summary if available
        if (data.summary) {
          summaryData = [
            ['Total Users', 'Total Sales', 'Total Revenue'],
            [
              data.summary.totalUsers || reportData.length || 0,
              data.summary.totalSales || 0,
              parseFloat(data.summary.totalRevenue || 0).toFixed(2)
            ]
          ];
          
          const summaryWS = XLSX.utils.aoa_to_sheet(summaryData);
          XLSX.utils.book_append_sheet(wb, summaryWS, 'Summary');
        }
        break;
        
      default:
        console.error('Unknown report type:', reportType);
        showToast('Unknown report type for Excel export', 'error');
        return;
    }
    
    // Write and save the file
    XLSX.writeFile(wb, filename);
    console.log('Excel file successfully generated and downloaded');
    showToast('Excel file downloaded successfully', 'success');
    
  } catch (error) {
    console.error('Error generating Excel file:', error);
    showToast('Error generating Excel file: ' + error.message, 'error');
  }
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