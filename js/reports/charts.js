// Display report data in the content area
function displayReportData(data, reportType) {
  const reportContent = document.getElementById('reportContent');
  
  // Clear previous content
  reportContent.innerHTML = '';
  
  // Log the report type and data for debugging
  console.log(`Displaying ${reportType} report with data:`, data);
  
  // Create report content based on report type
  if (reportType === 'sales') {
    displaySalesReport(data, reportContent);
    displaySalesChart(data);
  } else if (reportType === 'product_sales') {
    displayProductSalesReport(data, reportContent);
    displayProductSalesChart(data);
  } else if (reportType === 'stock_movement') {
    displayStockMovementReport(data, reportContent);
    displayStockMovementChart(data);
  } else if (reportType === 'user_sales') {
    displayUserSalesReport(data, reportContent);
    displayUserSalesChart(data);
  } else {
    reportContent.innerHTML = '<div class="text-center p-8 text-gray-500">Unknown report type selected.</div>';
    hideChart();
  }
}

// Function to show the chart container
function showChart() {
  document.getElementById('chartContainer').classList.remove('hidden');
}

// Function to hide the chart container
function hideChart() {
  document.getElementById('chartContainer').classList.add('hidden');
}

// Create sales report chart
function displaySalesChart(data) {
  const salesData = data.sales || data.data.sales || [];
  
  if (!salesData || salesData.length === 0) {
    hideChart();
    return;
  }
  
  // Show chart container
  showChart();
  
  // Destroy existing chart if it exists
  if (reportChart) {
    reportChart.destroy();
  }
  
  // Prepare data for chart - group by date
  const dateMap = {};
  const statusMap = {};
  
  salesData.forEach(sale => {
    // Create date groups
    if (!dateMap[sale.date]) {
      dateMap[sale.date] = 0;
    }
    dateMap[sale.date] += parseFloat(sale.total);
    
    // Create status groups
    if (!statusMap[sale.status]) {
      statusMap[sale.status] = 0;
    }
    statusMap[sale.status] += 1;
  });
  
  // Sort dates
  const sortedDates = Object.keys(dateMap).sort();
  
  // Create a colors array for status chart
  const statusColors = {
    'pending': 'rgba(255, 193, 7, 0.7)',
    'confirmed': 'rgba(13, 110, 253, 0.7)',
    'delivered': 'rgba(25, 135, 84, 0.7)',
    'canceled': 'rgba(220, 53, 69, 0.7)'
  };
  
  // Create chart with two sections (a bar chart and a pie chart)
  const ctx = document.getElementById('reportChart').getContext('2d');
  
  reportChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: sortedDates,
      datasets: [{
        label: 'Daily Sales (৳)',
        data: sortedDates.map(date => dateMap[date]),
        backgroundColor: 'rgba(75, 192, 192, 0.5)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Sales Amount (৳)'
          }
        },
        x: {
          title: {
            display: true,
            text: 'Date'
          }
        }
      },
      plugins: {
        title: {
          display: true,
          text: 'Sales Over Time',
          font: {
            size: 16
          }
        },
        legend: {
          position: 'top'
        }
      }
    }
  });
}

// Create product sales report chart
function displayProductSalesChart(data) {
  const productData = data.products || data.data.products || [];
  
  if (!productData || productData.length === 0) {
    hideChart();
    return;
  }
  
  // Show chart container
  showChart();
  
  // Destroy existing chart if it exists
  if (reportChart) {
    reportChart.destroy();
  }
  
  // Limit to top 10 products for readability
  const topProducts = productData.slice(0, 10);
  
  // Prepare data for charts
  const productNames = topProducts.map(product => product.name);
  const quantities = topProducts.map(product => product.quantity_sold);
  const revenues = topProducts.map(product => product.revenue);
  
  // Generate colors
  const backgroundColors = generateColors(topProducts.length, 0.7);
  const borderColors = generateColors(topProducts.length, 1);
  
  // Create chart
  const ctx = document.getElementById('reportChart').getContext('2d');
  
  reportChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: productNames,
      datasets: [
        {
          label: 'Quantity Sold',
          data: quantities,
          backgroundColor: backgroundColors,
          borderColor: borderColors,
          borderWidth: 1,
          yAxisID: 'y'
        },
        {
          label: 'Revenue (৳)',
          data: revenues,
          type: 'line',
          fill: false,
          borderColor: 'rgba(255, 99, 132, 1)',
          backgroundColor: 'rgba(255, 99, 132, 0.2)',
          borderWidth: 2,
          tension: 0.1,
          pointRadius: 5,
          pointHoverRadius: 7,
          yAxisID: 'y1'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          type: 'linear',
          position: 'left',
          beginAtZero: true,
          title: {
            display: true,
            text: 'Quantity Sold'
          }
        },
        y1: {
          type: 'linear',
          position: 'right',
          beginAtZero: true,
          grid: {
            drawOnChartArea: false
          },
          title: {
            display: true,
            text: 'Revenue (৳)'
          }
        },
        x: {
          title: {
            display: true,
            text: 'Products'
          }
        }
      },
      plugins: {
        title: {
          display: true,
          text: 'Top 10 Products by Sales',
          font: {
            size: 16
          }
        },
        legend: {
          position: 'top'
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              let label = context.dataset.label || '';
              if (label) {
                label += ': ';
              }
              if (context.datasetIndex === 1) {
                label += '৳' + context.raw.toFixed(2);
              } else {
                label += context.raw;
              }
              return label;
            }
          }
        }
      }
    }
  });
}

// Create stock movement report chart
function displayStockMovementChart(data) {
  const movementData = data.movements || data.data.movements || [];
  
  if (!movementData || movementData.length === 0) {
    hideChart();
    return;
  }
  
  // Show chart container
  showChart();
  
  // Destroy existing chart if it exists
  if (reportChart) {
    reportChart.destroy();
  }
  
  // Prepare data for pie chart - get actual movement types from data
  const movementTypes = {};
  
  movementData.forEach(movement => {
    if (!movementTypes[movement.type]) {
      movementTypes[movement.type] = 0;
    }
    movementTypes[movement.type] += parseInt(movement.quantity);
  });
  
  // Prepare data for product movement chart
  const productMap = {};
  
  movementData.forEach(movement => {
    if (!productMap[movement.product_name]) {
      productMap[movement.product_name] = {};
    }
    
    if (!productMap[movement.product_name][movement.type]) {
      productMap[movement.product_name][movement.type] = 0;
    }
    
    productMap[movement.product_name][movement.type] += parseInt(movement.quantity);
  });
  
  // Get top 5 products with most movement
  const productEntries = Object.entries(productMap);
  const topProducts = productEntries
    .map(([name, typeData]) => ({
      name,
      total: Object.values(typeData).reduce((sum, qty) => sum + qty, 0)
    }))
    .sort((a, b) => b.total - a.total)
    .slice(0, 5);
  
  // Generate colors for movement types
  const movementTypeLabels = Object.keys(movementTypes);
  const backgroundColors = generateColors(movementTypeLabels.length, 0.7);
  const borderColors = generateColors(movementTypeLabels.length, 1);
  
  // Create chart
  const ctx = document.getElementById('reportChart').getContext('2d');
  
  reportChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: movementTypeLabels,
      datasets: [{
        label: 'Stock Movement Distribution',
        data: Object.values(movementTypes),
        backgroundColor: backgroundColors,
        borderColor: borderColors,
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        title: {
          display: true,
          text: 'Stock Movement Distribution',
          font: {
            size: 16
          }
        },
        legend: {
          position: 'top'
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const label = context.label || '';
              const value = context.raw;
              const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
              const percentage = Math.round((value / total) * 100);
              return `${label}: ${value} (${percentage}%)`;
            }
          }
        }
      }
    }
  });
}

// Create user sales report chart
function displayUserSalesChart(data) {
  const userData = data.users || data.data.users || [];
  
  if (!userData || userData.length === 0) {
    hideChart();
    return;
  }
  
  // Show chart container
  showChart();
  
  // Destroy existing chart if it exists
  if (reportChart) {
    reportChart.destroy();
  }
  
  // Prepare data for chart
  const usernames = userData.map(user => user.username);
  const salesCounts = userData.map(user => user.sales_count);
  const revenues = userData.map(user => user.revenue);
  
  // Generate colors
  const backgroundColors = generateColors(userData.length, 0.7);
  
  // Create chart
  const ctx = document.getElementById('reportChart').getContext('2d');
  
  reportChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: usernames,
      datasets: [
        {
          label: 'Number of Sales',
          data: salesCounts,
          backgroundColor: backgroundColors,
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1,
          yAxisID: 'y'
        },
        {
          label: 'Revenue (৳)',
          data: revenues,
          type: 'line',
          fill: false,
          borderColor: 'rgba(255, 99, 132, 1)',
          backgroundColor: 'rgba(255, 99, 132, 0.2)',
          borderWidth: 2,
          tension: 0.1,
          pointRadius: 5,
          pointHoverRadius: 7,
          yAxisID: 'y1'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          type: 'linear',
          position: 'left',
          beginAtZero: true,
          title: {
            display: true,
            text: 'Number of Sales'
          }
        },
        y1: {
          type: 'linear',
          position: 'right',
          beginAtZero: true,
          grid: {
            drawOnChartArea: false
          },
          title: {
            display: true,
            text: 'Revenue (৳)'
          }
        },
        x: {
          title: {
            display: true,
            text: 'Users'
          }
        }
      },
      plugins: {
        title: {
          display: true,
          text: 'Sales Performance by User',
          font: {
            size: 16
          }
        },
        legend: {
          position: 'top'
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              let label = context.dataset.label || '';
              if (label) {
                label += ': ';
              }
              if (context.datasetIndex === 1) {
                label += '৳' + context.raw.toFixed(2);
              } else {
                label += context.raw;
              }
              return label;
            }
          }
        }
      }
    }
  });
}

// Helper function to generate colors
function generateColors(count, alpha) {
  const colors = [];
  for (let i = 0; i < count; i++) {
    const hue = (i * 137) % 360; // Use golden angle for good distribution
    colors.push(`hsla(${hue}, 70%, 60%, ${alpha})`);
  }
  return colors;
} 