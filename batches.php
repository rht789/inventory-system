<?php
// batches.php
include 'header.php';
include 'sidebar.php';
?>

<main class="lg:ml-64 min-h-screen p-6 bg-gray-100">

  <!-- Toast container -->
  <div id="toast"
       class="fixed bottom-4 right-4 bg-black text-white px-4 py-2 rounded-lg shadow-lg hidden z-50">
  </div>

  <!-- Header -->
  <div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-bold text-gray-900">Batch Management</h2>
        <p class="text-gray-600 mt-1">Track and manage product batches for better inventory control</p>
      </div>
      <button onclick="openAddBatchModal()"
              class="flex items-center gap-2 px-4 py-2.5 bg-black text-white font-medium rounded-md hover:bg-gray-800 transition-colors">
        <i class="fas fa-plus"></i>
        <span>Add Batch</span>
      </button>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-6 rounded-lg shadow mb-8 border border-gray-200">
      <div class="flex flex-col md:flex-row md:items-center gap-5">
        <div class="flex-1">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" id="searchInput"
                  placeholder="Search batches by product name, batch number..."
                  class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" />
          </div>
        </div>
        <div class="flex-1 md:flex-none">
          <select id="productSelect" 
                 class="w-full border-2 border-gray-300 rounded-md px-3 py-2.5 text-gray-700 focus:border-black focus:ring-1 focus:ring-black transition-all appearance-none bg-no-repeat bg-[right_0.5rem_center] pr-8"
                 style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e'); background-size: 1em">
            <option value="">All Products</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Batch Table -->
  <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr>
            <th class="px-6 py-4 bg-black text-white font-semibold text-left">Product</th>
            <th class="px-6 py-4 bg-black text-white font-semibold text-left">Size</th>
            <th class="px-6 py-4 bg-black text-white font-semibold text-left">Batch Number</th>
            <th class="px-6 py-4 bg-black text-white font-semibold text-center">Manufactured Date</th>
            <th class="px-6 py-4 bg-black text-white font-semibold text-center">Stock</th>
            <th class="px-6 py-4 bg-black text-white font-semibold text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="batch-list" class="divide-y divide-gray-200">
          <!-- Loading placeholder -->
          <tr>
            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
              <div class="flex flex-col items-center">
                <svg class="w-12 h-12 text-gray-300 animate-spin mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" stroke="none" fill="currentColor"></path>
                </svg>
                <p class="text-lg">Loading batch data...</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Add Batch Modal -->
<div id="addBatchModal"
     class="fixed inset-0 hidden bg-black bg-opacity-75 flex items-center justify-center z-50 overflow-y-auto">
  <div class="bg-white rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-auto m-4 shadow-xl">
    <div class="flex justify-between items-center mb-6 pb-3 border-b border-gray-200">
      <h3 class="text-2xl font-bold text-gray-900">Add New Batch</h3>
      <button onclick="closeAddBatchModal()" class="text-gray-400 hover:text-black transition-colors">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    <form id="addBatchForm" class="space-y-5">
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Product</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-box text-gray-400"></i>
          </div>
          <select id="addBatchProductSelect" name="product_id" 
                 class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all appearance-none bg-no-repeat bg-[right_0.5rem_center] pr-8" 
                 style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e'); background-size: 1em" 
                 required>
            <option value="">Select a product</option>
          </select>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Size</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-tag text-gray-400"></i>
          </div>
          <select id="addBatchSizeSelect" name="product_size_id" 
                 class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all appearance-none bg-no-repeat bg-[right_0.5rem_center] pr-8" 
                 style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e'); background-size: 1em" 
                 required>
            <option value="">Select a size</option>
          </select>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Batch Number</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-hashtag text-gray-400"></i>
          </div>
          <input type="text" name="batch_number"
                placeholder="e.g., BATCH-2025-001"
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" required />
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Manufactured Date</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-calendar-alt text-gray-400"></i>
          </div>
          <input type="date" name="manufactured_date"
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" required />
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Stock</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-cubes text-gray-400"></i>
          </div>
          <input type="number" name="stock"
                min="0"
                placeholder="Enter quantity"
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" required />
        </div>
      </div>
      <div class="pt-5 mt-3 border-t border-gray-200">
        <button type="submit"
                class="w-full bg-black text-white py-2.5 rounded-md hover:bg-gray-800 transition-colors font-medium">
          Create Batch
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Batch Modal -->
<div id="editBatchModal"
     class="fixed inset-0 hidden bg-black bg-opacity-75 flex items-center justify-center z-50 overflow-y-auto">
  <div class="bg-white rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-auto m-4 shadow-xl">
    <div class="flex justify-between items-center mb-6 pb-3 border-b border-gray-200">
      <h3 class="text-2xl font-bold text-gray-900">Edit Batch</h3>
      <button onclick="closeEditBatchModal()" class="text-gray-400 hover:text-black transition-colors">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    <form id="editBatchForm" class="space-y-5">
      <input type="hidden" name="id" />
      
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Product</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-box text-gray-400"></i>
          </div>
          <input type="text" name="product_name" 
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 bg-gray-50 text-gray-500" disabled />
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Size</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-tag text-gray-400"></i>
          </div>
          <input type="text" name="size_name" 
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 bg-gray-50 text-gray-500" disabled />
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Batch Number</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-hashtag text-gray-400"></i>
          </div>
          <input type="text" name="batch_number"
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" required />
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Manufactured Date</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-calendar-alt text-gray-400"></i>
          </div>
          <input type="date" name="manufactured_date"
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" required />
        </div>
      </div>
      
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Stock</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-cubes text-gray-400"></i>
          </div>
          <input type="number" name="stock"
                min="0"
                class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" required />
        </div>
      </div>
      
      <div class="pt-5 mt-3 border-t border-gray-200 flex justify-between">
        <button type="button" onclick="deleteBatch(editBatchForm.id.value)" 
                class="px-4 py-2.5 border-2 border-red-300 text-red-600 rounded-md hover:bg-red-50 transition-colors">
          <i class="fas fa-trash-alt mr-2"></i> Delete
        </button>
        <button type="submit"
                class="px-6 py-2.5 bg-black text-white rounded-md hover:bg-gray-800 transition-colors font-medium">
          Save Changes
        </button>
      </div>
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
  toast.className = `fixed bottom-4 right-4 z-50 text-white px-4 py-2 rounded-lg shadow-lg ${success ? 'bg-black' : 'bg-red-600'}`;
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
    // Show loading state
    batchList.innerHTML = `
      <tr>
        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
          <div class="flex flex-col items-center">
            <svg class="w-12 h-12 text-gray-300 animate-spin mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" stroke="none" fill="currentColor"></path>
            </svg>
            <p class="text-lg">Loading batch data...</p>
          </div>
        </td>
      </tr>
    `;
    
    const params = new URLSearchParams({
      search: searchInput.value,
      product_id: productSelect.value
    });
    
    const batches = await apiGet(`./api/batches.php?${params}`);
    
    if (batches.length === 0) {
      batchList.innerHTML = `
        <tr>
          <td colspan="6" class="px-6 py-8 text-center text-gray-500">
            <div class="flex flex-col items-center">
              <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="text-lg">No batches found</p>
              <p class="text-sm text-gray-400 mt-1">Try changing your search criteria</p>
            </div>
          </td>
        </tr>
      `;
      return;
    }
    
    batchList.innerHTML = batches.map(b => `
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-6 py-4 text-left font-semibold text-gray-900">${b.product_name}</td>
        <td class="px-6 py-4 text-left text-gray-700">
          <span class="inline-flex items-center px-2.5 py-1 rounded-md border-2 border-gray-200 text-xs font-medium bg-white">
            ${b.size_name}
          </span>
        </td>
        <td class="px-6 py-4 text-left text-gray-700">${b.batch_number}</td>
        <td class="px-6 py-4 text-center text-gray-700">${formatDate(b.manufactured_date)}</td>
        <td class="px-6 py-4 text-center ${b.stock === 0 ? 'text-gray-400' : 'font-bold text-gray-900'}">
          ${b.stock === 0 ? 
            '<span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-500">Out of stock</span>' : 
            b.stock
          }
        </td>
        <td class="px-6 py-4 text-center">
          <div class="flex justify-center gap-2">
            <button onclick="openEditBatchModal({
                id: ${b.id},
                product_name: '${b.product_name.replace(/'/g, "\\'")}',
                size_name: '${b.size_name.replace(/'/g, "\\'")}',
                batch_number: '${b.batch_number.replace(/'/g, "\\'")}',
                manufactured_date: '${b.manufactured_date}',
                stock: ${b.stock}
            })" class="p-2 text-gray-700 hover:text-black border-2 border-gray-200 hover:border-black rounded-md transition-colors">
              <i class="fas fa-edit"></i>
            </button>
            <button onclick="deleteBatch(${b.id})" class="p-2 text-red-600 hover:text-red-700 border-2 border-red-200 hover:border-red-300 rounded-md transition-colors">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        </td>
      </tr>`).join('');
  } catch (err) {
    console.error(err);
    showToast('Could not load batches', false);
  }
}

// Format date display
function formatDate(dateString) {
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
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
      showToast('Batch added successfully!');
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
      showToast('Batch updated successfully!');
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
  if (!confirm('Are you sure you want to delete this batch? This action cannot be undone.')) return;
  try {
    const res = await apiPost('./api/batches.php', { action: 'delete', id });
    if (res.success) {
      showToast('Batch deleted successfully');
      closeEditBatchModal();
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