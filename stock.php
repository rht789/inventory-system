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
    <button onclick="openAdjustStockModal(null, 'add')" class="bg-black text-white px-4 py-2 rounded text-sm">+ Add Stock</button>
  </div>

  <!-- Filters -->
  <div class="bg-white p-4 rounded-md shadow-sm mb-4">
    <div class="flex flex-col md:flex-row md:items-center gap-4 justify-between">
      <input type="text" id="searchStockInput" placeholder="Search By Name..." class="border px-4 py-2 rounded w-full md:w-1/3" />
      <div class="flex gap-2 w-full md:w-auto">
        <select id="stockStatusSelect" class="border rounded px-3 py-2 text-sm">
          <option value="">All Statuses</option>
          <option value="in_stock">In Stock</option>
          <option value="low_stock">Low Stock</option>
          <option value="critical">Critical</option>
          <option value="out_of_stock">Stock Out</option>
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
        <tr>
          <th class="px-4 py-3 text-center">Name</th>
          <th class="px-4 py-3 text-left">Size & Stock</th>
          <th class="px-4 py-3 text-center">Location</th>
          <th class="px-4 py-3 text-center">Total Stock</th>
          <th class="px-4 py-3 text-center">Min Stock</th>
          <th class="px-4 py-3 text-center">Status</th>
          <th class="px-4 py-3 text-center">Barcode</th>
          <th class="px-4 py-3 text-center">Actions</th>
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
      <h3 class="text-lg font-semibold" id="modalTitle">Adjust Stock</h3>
      <button onclick="closeAdjustStockModal()"><i class="fas fa-times text-gray-600"></i></button>
    </div>

    <p class="text-sm text-gray-500 mb-4">Make adjustments to your inventory stock levels</p>

    <form id="adjustStockForm" class="space-y-4">
      <input type="hidden" name="mode" id="formMode">
      <input type="hidden" name="product_id" id="productIdInput">

      <div>
        <label class="block text-sm font-medium mb-1">Product</label>
        <div class="relative">
          <input type="text" id="productSearch" placeholder="Select product" class="w-full border px-3 py-2 rounded" autocomplete="off" required>
          <ul id="productDropdown" class="absolute z-10 w-full bg-white border rounded shadow-lg max-h-40 overflow-y-auto hidden"></ul>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Size</label>
        <select name="product_size_id" id="sizeSelect" class="w-full border px-3 py-2 rounded" required>
          <!-- Options will be populated dynamically -->
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Quantity</label>
        <input type="number" name="quantity" placeholder="1" required class="w-full border px-3 py-2 rounded" min="1" />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Location</label>
        <input type="text" name="location" placeholder="e.g., Shelf A1" class="w-full border px-3 py-2 rounded" />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Reason</label>
        <input type="text" name="reason" placeholder="Restock" required class="w-full border px-3 py-2 rounded" />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Adjustment Type</label>
        <div class="flex gap-2">
          <label class="flex items-center cursor-pointer">
            <input type="radio" name="type" value="in" class="hidden peer" checked>
            <span class="peer-checked:bg-gray-600 peer-checked:text-white bg-gray-300 text-gray-700 px-4 py-2 rounded transition-colors">Add Stock</span>
          </label>
          <label class="flex items-center cursor-pointer">
            <input type="radio" name="type" value="out" class="hidden peer">
            <span class="peer-checked:bg-gray-600 peer-checked:text-white bg-gray-300 text-gray-700 px-4 py-2 rounded transition-colors">Reduce Stock</span>
          </label>
        </div>
      </div>

      <div class="flex justify-end gap-2 mt-4">
        <button type="button" onclick="closeAdjustStockModal()" class="border border-gray-300 px-4 py-2 rounded text-sm">Cancel</button>
        <button type="submit" class="bg-black text-white px-4 py-2 rounded text-sm">Add Stock</button>
      </div>
    </form>
  </div>
</div>

<!-- Barcode Preview Modal -->
<div id="barcodeModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white p-6 rounded shadow-lg max-w-lg w-full">
    <div class="flex justify-end mb-4">
      <button onclick="closeBarcodeModal()" class="text-gray-600 hover:text-black">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    <img id="barcodeModalImg" src="" alt="Barcode" class="mx-auto w-full max-h-[80vh] object-contain" />
  </div>
</div>

<script type="module">
import { apiGet } from './js/ajax.js';

const toast = document.getElementById('toast');
const modal = document.getElementById('adjustStockModal');
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

let products = [];

// Function to show toast notifications
function showToast(msg, success = true) {
  toast.textContent = msg;
  toast.className = `fixed bottom-4 right-4 text-white px-4 py-2 rounded shadow-lg ${success ? 'bg-green-500' : 'bg-red-500'}`;
  toast.classList.remove('hidden');
  setTimeout(() => toast.classList.add('hidden'), 3000);
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

window.openBarcodeModal = () => document.getElementById('barcodeModal').classList.remove('hidden');
window.closeBarcodeModal = () => document.getElementById('barcodeModal').classList.add('hidden');

// Fetch and load stock data
async function loadStock() {
  try {
    const params = new URLSearchParams({
      search: searchStockInput.value,
      stock_filter: stockStatusSelect.value,
      location: locationSelect.value
    });
    const prods = await apiGet(`./api/products.php?${params}`);
    stockList.innerHTML = prods.map(p => {
      const total = p.sizes.reduce((sum, s) => sum + +s.stock, 0);
      const badges = p.sizes.map(s => 
        `<span class="bg-gray-100 px-2 py-1 rounded text-xs">${s.size_name}:${s.stock}</span>`
      ).join(' ');
      let status, statusClass;
      if (total === 0) {
        status = 'Stock Out';
        statusClass = 'bg-gray-300 text-gray-600 px-2 py-1 rounded opacity-75';
      } else if (total <= 2) {
        status = 'Critical';
        statusClass = 'bg-[#fc0f32] text-white px-2 py-1 rounded';
      } else if (total <= p.min_stock) {
        status = 'Low Stock';
        statusClass = 'bg-[#dcd906] text-white px-2 py-1 rounded';
      } else {
        status = 'In Stock';
        statusClass = 'bg-green-500 text-white px-2 py-1 rounded';
      }
      return `
        <tr class="border-t hover:bg-gray-50">
          <td class="px-4 py-3 font-medium text-center">${p.name}</td>
          <td class="px-4 py-3 flex flex-wrap gap-2">${badges}</td>
          <td class="px-4 py-3 text-center">${p.location || '-'}</td>
          <td class="px-4 py-3 font-bold text-center">${total}</td>
          <td class="px-4 py-3 text-center">${p.min_stock}</td>
          <td class="px-4 py-3 text-center"><span class="${statusClass}">${status}</span></td>
          <td class="px-4 py-3 text-center">
            ${p.barcode ? `<img src="./${p.barcode}" alt="Barcode" class="barcode-img h-8 mx-auto cursor-pointer"/>` : '-'}
          </td>
          <td class="px-4 py-3 text-center">
            <button onclick="openAdjustStockModal(${p.id}, 'edit')" class="text-blue-600"><i class="fas fa-edit"></i></button>
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
  } catch (err) {
    console.error(err);
    showToast('Error loading stock', false);
  }
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
        <li class="px-3 py-2 hover:bg-gray-100 cursor-pointer" data-id="${p.id}">${p.name}</li>
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
    showToast('Could not load product list', false);
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
    showToast('Could not load locations', false);
  }
}

// Handle form submission for stock adjustment
form.onsubmit = async e => {
  e.preventDefault();
  const formData = new FormData(form);

  // Validate required fields
  if (!formData.get('product_id')) {
    showToast('Please select a product', false);
    return;
  }
  if (!formData.get('product_size_id')) {
    showToast('Please select a size', false);
    return;
  }
  if (!formData.get('quantity') || formData.get('quantity') <= 0) {
    showToast('Please enter a valid quantity greater than 0', false);
    return;
  }
  if (!formData.get('reason')) {
    showToast('Please enter a reason', false);
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
      showToast('Stock adjusted!');
      form.reset();
      closeAdjustStockModal();
      loadStock();
    } else {
      showToast(data.message || 'Adjustment failed', false);
    }
  } catch (err) {
    console.error(err);
    showToast(err.message || 'An error occurred while adjusting stock', false);
  }
};

// Add filter event listeners
[searchStockInput, stockStatusSelect, locationSelect].forEach(el => {
  el.addEventListener('input', loadStock);
});

// Initial load
document.addEventListener('DOMContentLoaded', () => {
  loadStock();
  populateLocationDropdown();
});
</script>