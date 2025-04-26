<?php
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SmartInventory - Sales Analysis</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .spinner {
      display: none;
      border: 4px solid #f3f3f3;
      border-top: 4px solid #000;
      border-radius: 50%;
      width: 24px;
      height: 24px;
      animation: spin 1s linear infinite;
      margin-left: 10px;
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-60 bg-white border-r border-gray-200 p-5">
      <h1 class="text-xl font-bold text-gray-900 mb-6">SmartInventory</h1>
      <nav class="flex flex-col space-y-4">
        <a href="../dashboard.php" class="text-gray-700 hover:text-black">Dashboard</a>
        <a href="../orders.php" class="text-gray-700 hover:text-black">Orders</a>
        <a href="../products.php" class="text-gray-700 hover:text-black">Products</a>
        <a href="../stock.php" class="text-gray-700 hover:text-black">Stock</a>
        <a href="../reports.php" class="text-gray-700 hover:text-black">Reports</a>
        <a href="Sales-Analysis.php" class="text-black font-semibold">Sales Analysis</a>
        <a href="../users.php" class="text-gray-700 hover:text-black">Users</a>
        <a href="../settings.php" class="text-gray-700 hover:text-black">Settings</a>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-10">
      <!-- Sales Analysis Section -->
      <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Sales Analysis</h2>
        <p class="text-gray-600 mb-6">Upload a CSV file with sales data for multiple products to view total sales by product (sorted by popularity).</p>

        <!-- File Upload -->
        <form id="analysisForm" enctype="multipart/form-data">
          <div class="mb-4 flex items-center">
            <div class="mr-4">
              <label for="salesFile" class="block text-sm font-medium text-gray-700 mb-2">Upload Sales Data (CSV):</label>
              <input type="file" id="salesFile" name="sales_file" accept=".csv" class="border px-3 py-2 rounded text-sm w-64" required>
            </div>
            <button type="submit" id="analysisBtn" class="bg-black text-white px-4 py-2 rounded text-sm flex items-center">
              Analyze Sales
              <span id="spinner" class="spinner"></span>
            </button>
          </div>
        </form>

        <!-- Sales Results -->
        <div class="bg-white p-6 rounded-xl shadow mb-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Sales Results (Sorted by Total Sales)</h3>
            <button id="exportBtn" class="bg-gray-200 text-gray-700 px-3 py-1 rounded text-sm" disabled>Export to CSV</button>
          </div>
          <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
              <tr>
                <th class="px-4 py-2">Product</th>
                <th class="px-4 py-2">Total Sales</th>
              </tr>
            </thead>
            <tbody id="salesTable"></tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <script>
    let salesData = [];

    async function analyzeSales(formData) {
      const spinner = document.getElementById('spinner');
      const exportBtn = document.getElementById('exportBtn');
      spinner.style.display = 'block';
      exportBtn.disabled = true;

      try {
        // Ensure the fetch URL matches the directory structure
        const response = await fetch('predict-demand.php', {
          method: 'POST',
          body: formData,
        });

        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Fetched sales data:', data);

        const tableBody = document.getElementById('salesTable');
        tableBody.innerHTML = '';

        if (data.error) {
          alert(data.error);
          return;
        }

        salesData = data;

        data.forEach(item => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="px-4 py-2">${item.Product}</td>
            <td class="px-4 py-2">${item.Total_Sales}</td>
          `;
          tableBody.appendChild(row);
        });

        exportBtn.disabled = false;
      } catch (error) {
        console.error('Error analyzing sales:', error);
        alert(`Failed to analyze sales: ${error.message}`);
      } finally {
        spinner.style.display = 'none';
      }
    }

    function exportToCSV() {
      console.log('Exporting salesData:', salesData);

      if (!salesData || !salesData.length) {
        console.error('No sales data available to export');
        alert('No data to export. Please analyze sales first.');
        return;
      }

      const escapeCsvField = (field) => {
        if (typeof field === 'string' && (field.includes(',') || field.includes('"'))) {
          return `"${field.replace(/"/g, '""')}"`;
        }
        return field;
      };

      const headers = ['Product', 'Total Sales'];
      const csvRows = [headers.join(',')];

      salesData.forEach(item => {
        const row = [
          escapeCsvField(item.Product),
          escapeCsvField(item.Total_Sales)
        ].join(',');
        csvRows.push(row);
      });

      const csv = csvRows.join('\n');
      console.log('Generated CSV:', csv);

      try {
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.setAttribute('href', url);
        a.setAttribute('download', 'sales_analysis.csv');
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        console.log('Download triggered successfully');
      } catch (error) {
        console.error('Error triggering CSV download:', error);
        alert('Failed to export CSV. Check the console for details.');
      }
    }

    // Handle form submission
    document.getElementById('analysisForm').addEventListener('submit', function(event) {
      event.preventDefault();
      const formData = new FormData(this);
      analyzeSales(formData);
    });

    document.getElementById('exportBtn').addEventListener('click', exportToCSV);
  </script>
</body>
</html>
<?php
ob_end_flush();
?>