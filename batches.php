<?php
// batches.php
include 'header.php';
include 'sidebar.php';
?>

<main class="lg:ml-64 min-h-screen p-6 bg-gray-100">

  <!-- Toast container -->
  <div id="toast"
       class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg hidden">
  </div>

  <!-- Topbar -->
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">Batch Management</h2>
    <button onclick="openAddBatchModal()"
            class="bg-black text-white px-4 py-2 rounded text-sm">
      + Add Batch
    </button>
  </div>

  <!-- Filters & Search -->
  <div class="bg-white p-4 rounded-md shadow-sm mb-4">
    <div class="flex flex-col md:flex-row md:items-center gap-4 justify-between">
      <input type="text" id="searchInput"
             placeholder="Search Batches..."
             class="border px-4 py-2 rounded w-full md:w-1/3" />
      <div class="flex gap-2 w-full md:w-auto">
        <select id="productSelect" class="border rounded px-3 py-2 text-sm">
          <option value="">All Products</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Batch Table -->
  <div class="bg-white rounded shadow-sm overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-gray-100 text-gray-600">
        <tr>
          <th class="px-4 py-3 text-left">Product</th>
          <th class="px-4 py-3 text-left">Size</th>
          <th class="px-4 py-3 text-left">Batch Number</th>
          <th class="px-4 py-3 text-center">Manufactured Date</th>
          <th class="px-4 py-3 text-center">Stock</th>
          <th class="px-4 py-3 text-center">Actions</th>
        </tr>
      </thead>
      <tbody id="batch-list">
        <!-- injected by JS -->
      </tbody>
    </table>
  </div>
</main>

<!-- Add Batch Modal -->
<div id="addBatchModal"
     class="fixed inset-0 hidden bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-full max-w-md">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Add Batch</h3>
      <button onclick="closeAddBatchModal()">
        <i class="fas fa-times text-gray-600"></i>
      </button>
    </div>
    <form id="addBatchForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium">Product</label>
        <select id="addBatchProductSelect" name="product_id" class="w-full border px-3 py-2 rounded" required>
          <option value="">Select a product</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium">Size</label>
        <select id="addBatchSizeSelect" name="product_size_id" class="w-full border px-3 py-2 rounded" required>
          <option value="">Select a size</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium">Batch Number</label>
        <input type="text" name="batch_number"
               placeholder="e.g., BATCH-2025-001"
               class="w-full border px-3 py-2 rounded" required />
      </div>
      <div>
        <label class="block text-sm font-medium">Manufactured Date</label>
        <input type="date" name="manufactured_date"
               class="w-full border px-3 py-2 rounded" required />
      </div>
      <div>
        <label class="block text-sm font-medium">Stock</label>
        <input type="number" name="stock"
               min="0"
               class="w-full border px-3 py-2 rounded" required />
      </div>
      <button type="submit"
              class="bg-black text-white w-full py-2 rounded">
        Add Batch
      </button>
    </form>
  </div>
</div>

<!-- Edit Batch Modal -->
<div id="editBatchModal"
     class="fixed inset-0 hidden bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-full max-w-md">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Edit Batch</h3>
      <button onclick="closeEditBatchModal()">
        <i class="fas fa-times text-gray-600"></i>
      </button>
    </div>
    <form id="editBatchForm" class="space-y-4">
      <input type="hidden" name="id" />
      <div>
        <label class="block text-sm font-medium">Product</label>
        <input type="text" name="product_name" class="w-full border px-3 py-2 rounded" disabled />
      </div>
      <div>
        <label class="block text-sm font-medium">Size</label>
        <input type="text" name="size_name" class="w-full border px-3 py-2 rounded" disabled />
      </div>
      <div>
        <label class="block text-sm font-medium">Batch Number</label>
        <input type="text" name="batch_number"
               class="w-full border px-3 py-2 rounded" required />
      </div>
      <div>
        <label class="block text-sm font-medium">Manufactured Date</label>
        <input type="date" name="manufactured_date"
               class="w-full border px-3 py-2 rounded" required />
      </div>
      <div>
        <label class="block text-sm font-medium">Stock</label>
        <input type="number" name="stock"
               min="0"
               class="w-full border px-3 py-2 rounded" required />
      </div>
      <button type="submit"
              class="bg-black text-white w-full py-2 rounded">
        Save Changes
      </button>
    </form>
  </div>
</div>

<script type="module">
import { apiGet, apiPost } from './js/ajax.js';

// Modal controls
window.openAddBatchModal = () => {
    addBatchForm.reset();
    document.getElementById('addBatchModal').classList.remove('hidden');
};
window.closeAddBatchModal = () => {
    document.getElementById('addBatchModal').classList.add('hidden');
};
window.openEditBatchModal = (batch) => {
    editBatchForm.id.value = batch.id;
    editBatchForm.product_name.value = batch.product_name;
    editBatchForm.size_name.value = batch.size_name;
    editBatchForm.batch_number.value = batch.batch_number;
    editBatchForm.manufactured_date.value = batch.manufactured_date;
    editBatchForm.stock.value = batch.stock;
    document.getElementById('editBatchModal').classList.remove('hidden');
};
window.closeEditBatchModal = () => {
    document.getElementById('editBatchModal').classList.add('hidden');
};

// DOM refs
const
  toast             = document.getElementById('toast'),
  searchInput       = document.getElementById('searchInput'),
  productSelect     = document.getElementById('productSelect'),
  batchList         = document.getElementById('batch-list'),
  addBatchForm      = document.getElementById('addBatchForm'),
  editBatchForm     = document.getElementById('editBatchForm'),
  addBatchProductSelect = document.getElementById('addBatchProductSelect'),
  addBatchSizeSelect = document.getElementById('addBatchSizeSelect');

// Show toast
function showToast(msg, success = true) {
  toast.textContent = msg;
  toast.classList.toggle('bg-green-500', success);
  toast.classList.toggle('bg-red-500', !success);
  toast.classList.remove('hidden');
  setTimeout(() => toast.classList.add('hidden'), 3000);
}

// Initial load
document.addEventListener('DOMContentLoaded', async () => {
  await loadProducts();
  fetchBatches();
});

// Re-fetch on filter change
[searchInput, productSelect].forEach(el =>
  el.addEventListener('input', fetchBatches)
);

// Load products for filter and add form
async function loadProducts() {
  try {
    const products = await apiGet('./api/products.php');
    productSelect.innerHTML = '<option value="">All Products</option>' +
      products.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
    addBatchProductSelect.innerHTML = '<option value="">Select a product</option>' +
      products.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
  } catch (err) {
    console.error(err);
    showToast('Could not load products', false);
  }
}

// Populate sizes when product is selected
addBatchProductSelect.onchange = async () => {
  const productId = addBatchProductSelect.value;
  addBatchSizeSelect.innerHTML = '<option value="">Select a size</option>';
  if (!productId) return;
  try {
    const products = await apiGet('./api/products.php');
    const product = products.find(p => p.id == productId);
    if (product && product.sizes) {
      addBatchSizeSelect.innerHTML = '<option value="">Select a size</option>' +
        product.sizes.map(s => `<option value="${s.id}">${s.size_name} (${s.stock} units)</option>`).join('');
    }
  } catch (err) {
    console.error(err);
    showToast('Could not load sizes', false);
  }
};

// Fetch & render batches
async function fetchBatches() {
  try {
    const params = new URLSearchParams({
      search: searchInput.value,
      product_id: productSelect.value
    });
    const batches = await apiGet(`./api/batches.php?${params}`);
    batchList.innerHTML = batches.map(b => `
      <tr class="border-t">
        <td class="px-4 py-3 text-left">${b.product_name}</td>
        <td class="px-4 py-3 text-left">${b.size_name}</td>
        <td class="px-4 py-3 text-left">${b.batch_number}</td>
        <td class="px-4 py-3 text-center">${b.manufactured_date}</td>
        <td class="px-4 py-3 text-center ${b.stock === 0 ? 'text-gray-500' : ''}">
          ${b.stock}
        </td>
        <td class="px-4 py-3 text-center">
          <button onclick="openEditBatchModal({
              id: ${b.id},
              product_name: '${b.product_name.replace(/'/g, "\\'")}',
              size_name: '${b.size_name.replace(/'/g, "\\'")}',
              batch_number: '${b.batch_number.replace(/'/g, "\\'")}',
              manufactured_date: '${b.manufactured_date}',
              stock: ${b.stock}
          })" class="text-blue-600 mr-2">
            <i class="fas fa-edit"></i>
          </button>
          <button onclick="deleteBatch(${b.id})" class="text-red-500">
            <i class="fas fa-trash-alt"></i>
          </button>
        </td>
      </tr>`).join('');
  } catch (err) {
    console.error(err);
    showToast('Could not load batches', false);
  }
}

// Add batch
addBatchForm.onsubmit = async e => {
  e.preventDefault();
  const data = Object.fromEntries(new FormData(addBatchForm).entries());

  if (parseInt(data.stock) < 0) {
    showToast('Stock cannot be negative', false);
    return;
  }

  // Validate manufactured date
  const currentDate = new Date('2025-04-22');
  const manufacturedDate = new Date(data.manufactured_date);
  if (manufacturedDate > currentDate) {
    showToast('Manufactured date cannot be in the future', false);
    return;
  }

  try {
    const res = await apiPost('./api/batches.php', {
      action: 'create',
      product_id: data.product_id,
      product_size_id: data.product_size_id,
      batch_number: data.batch_number,
      manufactured_date: data.manufactured_date,
      stock: parseInt(data.stock)
    });
    if (res.success) {
      showToast('Batch added!');
      closeAddBatchModal();
      fetchBatches();
    } else {
      showToast(res.message || 'Failed to add batch', false);
    }
  } catch (err) {
    console.error(err);
    showToast('Error adding batch: ' + (err.message || 'Unknown error'), false);
  }
};

// Edit batch
editBatchForm.onsubmit = async e => {
  e.preventDefault();
  const data = Object.fromEntries(new FormData(editBatchForm).entries());

  if (parseInt(data.stock) < 0) {
    showToast('Stock cannot be negative', false);
    return;
  }

  // Validate manufactured date
  const currentDate = new Date('2025-04-22');
  const manufacturedDate = new Date(data.manufactured_date);
  if (manufacturedDate > currentDate) {
    showToast('Manufactured date cannot be in the future', false);
    return;
  }

  try {
    const res = await apiPost('./api/batches.php', {
      action: 'update',
      id: data.id,
      batch_number: data.batch_number,
      manufactured_date: data.manufactured_date,
      stock: parseInt(data.stock)
    });
    if (res.success) {
      showToast('Batch updated!');
      closeEditBatchModal();
      fetchBatches();
    } else {
      showToast(res.message || 'Failed to update batch', false);
    }
  } catch (err) {
    console.error(err);
    showToast('Error updating batch: ' + (err.message || 'Unknown error'), false);
  }
};

// Delete batch
window.deleteBatch = async id => {
  if (!confirm('Delete this batch?')) return;
  try {
    const res = await apiPost('./api/batches.php', { action: 'delete', id });
    if (res.success) {
      showToast('Batch deleted');
      fetchBatches();
    } else {
      showToast(res.message || 'Delete failed', false);
    }
  } catch (err) {
    console.error(err);
    showToast('Delete error: ' + (err.message || 'Unknown error'), false);
  }
};
</script>