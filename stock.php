<?php
include 'header.php';
include 'sidebar.php';
?>

<main class="lg:ml-64 min-h-screen p-6 bg-gray-100">
  <!-- Toast Notification -->
  <div id="toast" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg hidden"></div>

  <!-- Header -->
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">Stock Management</h2>
    <button onclick="openAdjustStockModal()" class="bg-black text-white px-4 py-2 rounded text-sm">+ Adjust Stock</button>
  </div>

  <!-- Filters -->
  <div class="bg-white p-4 rounded-md shadow-sm mb-4">
    <div class="flex flex-col md:flex-row md:items-center gap-4 justify-between">
      <input type="text" id="searchStockInput" placeholder="Search Product..." class="border px-4 py-2 rounded w-full md:w-1/3" />
      <div class="flex gap-2 w-full md:w-auto">
        <select id="stockStatusSelect" class="border rounded px-3 py-2 text-sm">
          <option value="">All Stock</option>
          <option value="in_stock">In Stock</option>
          <option value="low_stock">Low Stock</option>
          <option value="out_of_stock">Out of Stock</option>
        </select>
        <select id="locationSelect" class="border rounded px-3 py-2 text-sm">
          <option value="">All Locations</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Stock Table -->
  <div class="bg-white rounded shadow-sm overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-gray-100 text-gray-600">
        <tr class="text-left">
          <th class="px-4 py-3">SKU</th>
          <th class="px-4 py-3">Name</th>
          <th class="px-4 py-3">Sizes</th>
          <th class="px-4 py-3">Total Stock</th>
          <th class="px-4 py-3">Min Stock</th>
          <th class="px-4 py-3">Location</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody id="stock-list">
        <!-- Stock details will be dynamically populated via JS -->
      </tbody>
    </table>
  </div>
</main>

<!-- Adjust Stock Modal -->
<div id="adjustStockModal" class="fixed inset-0 hidden bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-full max-w-md overflow-auto max-h-screen">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Adjust Stock</h3>
      <button onclick="closeAdjustStockModal()"><i class="fas fa-times text-gray-600"></i></button>
    </div>

    <form id="adjustStockForm" class="space-y-4">
      <select name="product_id" required class="w-full border px-3 py-2 rounded">
        <option value="">Select Product</option>
      </select>

      <select name="product_size_id" required class="w-full border px-3 py-2 rounded">
        <option value="">Select Size</option>
      </select>

      <input type="number" name="quantity" placeholder="Quantity (use negative to reduce)" required class="w-full border px-3 py-2 rounded" />
      <input type="text" name="location" placeholder="Location (e.g., Warehouse A)" class="w-full border px-3 py-2 rounded" />
      <input type="text" name="reason" placeholder="Reason for adjustment" required class="w-full border px-3 py-2 rounded" />

      <button type="submit" class="bg-black text-white w-full py-2 rounded">
        Save Stock Adjustment
      </button>
    </form>
  </div>
</div>

<script type="module">
import { apiGet, apiPost } from './js/ajax.js';

const toast = document.getElementById('toast');
const modal = document.getElementById('adjustStockModal');
const form  = document.getElementById('adjustStockForm');
const stockList = document.getElementById('stock-list');

// Function to show toast notifications
function showToast(msg, success = true) {
  toast.textContent = msg;
  toast.className = `fixed bottom-4 right-4 text-white px-4 py-2 rounded shadow-lg ${success ? 'bg-green-500' : 'bg-red-500'}`;
  toast.classList.remove('hidden');
  setTimeout(() => toast.classList.add('hidden'), 3000);
}

// Open and Close Modal
window.openAdjustStockModal = () => {
  modal.classList.remove('hidden');
  populateProductDropdown();
};

window.closeAdjustStockModal = () => modal.classList.add('hidden');

// Fetch and load stock data
async function loadStock() {
  try {
    const prods = await apiGet('./api/products.php');
    stockList.innerHTML = prods.map(p => {
      const total = p.sizes.reduce((sum, s) => sum + +s.stock, 0);
      const badges = p.sizes.map(s => 
        `<span class="bg-gray-100 px-2 py-1 rounded text-xs">${s.size_name}:${s.stock}</span>`
      ).join(' ');
      const status = total === 0 ? 'Out of Stock' : total <= p.min_stock ? 'Low Stock' : 'In Stock';
      return `
        <tr class="border-t hover:bg-gray-50">
          <td class="px-4 py-3">${p.barcode || '-'}</td>
          <td class="px-4 py-3 font-medium">${p.name}</td>
          <td class="px-4 py-3 flex flex-wrap gap-2">${badges}</td>
          <td class="px-4 py-3 font-bold text-center">${total}</td>
          <td class="px-4 py-3 text-center">${p.min_stock}</td>
          <td class="px-4 py-3 text-center">${p.location || '-'}</td>
          <td class="px-4 py-3 text-center">${status}</td>
          <td class="px-4 py-3 text-center">
            <button onclick="openAdjustStockModal()" class="text-blue-600"><i class="fas fa-edit"></i></button>
          </td>
        </tr>`;
    }).join('');
  } catch (err) {
    console.error(err);
    showToast('Error loading stock', false);
  }
}

// Populate the product dropdown
async function populateProductDropdown() {
  const productSelect = form.querySelector('select[name="product_id"]');
  const sizeSelect = form.querySelector('select[name="product_size_id"]');
  productSelect.innerHTML = '<option value="">Select Product</option>';
  sizeSelect.innerHTML = '<option value="">Select Size</option>';

  try {
    const products = await apiGet('./api/products.php');
    products.forEach(p => {
      const opt = document.createElement('option');
      opt.value = p.id;
      opt.textContent = p.name;
      opt.dataset.sizes = JSON.stringify(p.sizes);
      productSelect.appendChild(opt);
    });

    productSelect.onchange = () => {
      const selected = productSelect.options[productSelect.selectedIndex];
      const sizes = JSON.parse(selected.dataset.sizes || '[]');
      sizeSelect.innerHTML = '<option value="">Select Size</option>';
      sizes.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.size_name;
        sizeSelect.appendChild(opt);
      });
    };
  } catch (err) {
    console.error(err);
    showToast('Could not load product list', false);
  }
}

// Handle form submission for stock adjustment
form.onsubmit = async e => {
  e.preventDefault();
  const formData = Object.fromEntries(new FormData(form));
  try {
    const res = await apiPost('./api/stock.php', formData);
    if (res.success) {
      showToast('Stock adjusted!');
      form.reset();
      closeAdjustStockModal();
      loadStock();
    } else {
      showToast(res.message || 'Adjustment failed', false);
    }
  } catch (err) {
    console.error(err);
    showToast('Server error', false);
  }
}

// Initial load
document.addEventListener('DOMContentLoaded', () => {
  loadStock();
});
</script>
