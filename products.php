<?php
include 'authcheck.php'; // Adjust path as needed
requireLogin();           // Ensures the user is logged in
allowRoles(['admin', 'staff']); // Both roles can access
?>

<?php
// products.php
include 'header.php';
include 'sidebar.php';
?>

<main class="lg:ml-64 min-h-screen p-6 bg-gray-100">

  <div id="toast"
       class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg hidden">
  </div>

  <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">Product Management</h2>
    <div class="flex gap-2">
      <button onclick="openCategoryModal()"
              class="border border-gray-400 rounded px-4 py-2 text-sm hover:bg-gray-100">
        Manage Categories
      </button>
      <button onclick="openEditProductModal()"
              class="bg-black text-white px-4 py-2 rounded text-sm">
        + Add Product
      </button>
    </div>
  </div>

  <div class="bg-white p-4 rounded-md shadow-sm mb-4">
    <div class="flex flex-col md:flex-row md:items-center gap-4 justify-between">
      <input type="text" id="searchInput"
             placeholder="Search Orders..."
             class="border px-4 py-2 rounded w-full md:w-1/3" />
      <div class="flex gap-2 w-full md:w-auto">
        <select id="stockSelect" class="border rounded px-3 py-2 text-sm">
          <option>All Stock</option>
          <option>In Stock</option>
          <option>Low Stock</option>
          <option>Out of Stock</option>
        </select>
        <select id="categorySelect" class="border rounded px-3 py-2 text-sm">
          <option>All Categories</option>
        </select>
      </div>
    </div>
  </div>

  <div class="bg-white rounded shadow-sm overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-gray-100 text-gray-600">
        <tr>
          <th class="px-4 py-3 text-center">Name</th>
          <th class="px-4 py-3 text-center">Category</th>
          <th class="px-4 py-3 text-left">Size & Stock</th>
          <th class="px-4 py-3 text-center">Total Stock</th>
          <th class="px-4 py-3 text-center">Batches</th>
          <th class="px-4 py-3 text-center">Barcode</th>
          <th class="px-4 py-3 text-center">Cost Price (৳)</th>
          <th class="px-4 py-3 text-center">Selling Price (৳)</th>
          <th class="px-4 py-3 text-center">Actions</th>
        </tr>
      </thead>
      <tbody id="product-list">
      </tbody>
    </table>
  </div>
</main>

<div id="categoryModal"
     class="fixed inset-0 hidden bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-full max-w-md">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Manage Categories</h3>
      <button onclick="closeCategoryModal()">
        <i class="fas fa-times text-gray-600"></i>
      </button>
    </div>
    <div class="flex gap-2 mb-4">
      <input type="text" id="newCategoryInput"
             placeholder="Enter new category name"
             class="flex-1 border px-3 py-2 rounded" />
      <button id="addCategoryBtn"
              class="bg-gray-700 text-white px-4 rounded">
        + Add
      </button>
    </div>
    <ul id="categoryList" class="divide-y text-sm"></ul>
  </div>
</div>

<div id="editProductModal"
     class="fixed inset-0 hidden bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-full max-w-md overflow-auto max-h-screen">
    <div class="flex justify-between items-center mb-2">
      <h3 class="text-lg font-semibold" id="editProductModalTitle">Edit Product</h3>
      <button onclick="closeEditProductModal()">
        <i class="fas fa-times text-gray-600"></i>
      </button>
    </div>
    <p class="text-sm text-gray-500 mb-4">Add/Update product information.</p>
    <form id="productForm" class="space-y-4">
      <input type="hidden" name="id" />
      <input type="hidden" name="barcode" />

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Product Name</label>
          <input type="text" name="name"
                 class="w-full border px-3 py-2 rounded"
                 required />
        </div>
        <div>
          <label class="block text-sm font-medium">Category</label>
          <select name="category_id"
                  class="w-full border px-3 py-2 rounded"
                  required></select>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium">Description</label>
        <input type="text" name="description"
               placeholder="Enter product description"
               class="w-full border px-3 py-2 rounded" />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Sizes & Stock</label>
        <div class="flex gap-2 mb-2">
          <input type="text" id="newSizeInput"
                 placeholder="Enter size (e.g., S, M, L, XL)"
                 class="flex-1 border px-3 py-2 rounded" />
          <button type="button" id="addSizeBtn"
                  class="bg-gray-700 text-white px-4 py-2 rounded">
            Add Size
          </button>
        </div>
        <ul id="sizeList"
            class="space-y-2 max-h-40 overflow-y-auto"></ul>
        <div class="text-sm font-medium mt-2">
          Total <span id="totalUnits">0</span> units
        </div>
      </div>
      <input type="hidden" name="stock" id="stockInput" />

      <div id="initialBatchSection">
        <label class="block text-sm font-medium mb-1">Initial Batch</label>
        <div class="space-y-2">
          <div>
            <label class="block text-sm">Size</label>
            <select id="initialBatchSize" name="initial_batch_size" class="w-full border px-3 py-2 rounded">
            </select>
          </div>
          <div>
            <label class="block text-sm">Batch Number</label>
            <input type="text" name="batch_number"
                   placeholder="e.g., BATCH-2025-001"
                   class="w-full border px-3 py-2 rounded" />
          </div>
          <div>
            <label class="block text-sm">Manufactured Date</label>
            <input type="date" name="manufactured_date" id="initialManufacturedDate"
                   class="w-full border px-3 py-2 rounded" />
          </div>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium">Location</label>
        <input type="text" name="location"
               placeholder="Enter location (e.g., Shelf A, Side B)"
               class="w-full border px-3 py-2 rounded" />
      </div>

      <div>
        <label class="block text-sm font-medium">Minimum Stock Level</label>
        <input type="number" name="min_stock"
               class="w-full border px-3 py-2 rounded"
               min="0" value="5" />
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">
            Cost Price (৳)
          </label>
          <input type="number" name="price"
                 step="0.01"
                 class="w-full border px-3 py-2 rounded"
                 required />
        </div>
        <div>
          <label class="block text-sm font-medium">
            Selling Price (৳)
          </label>
          <input type="number" name="selling_price"
                 step="0.01"
                 class="w-full border px-3 py-2 rounded"
                 required />
        </div>
      </div>

      <button type="submit"
              class="bg-black text-white w-full py-2 rounded">
        Save Product
      </button>
    </form>
  </div>
</div>

<div id="batchModal"
     class="fixed inset-0 hidden bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-full max-w-lg overflow-auto max-h-screen">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold" id="batchModalTitle">Manage Batches</h3>
      <button onclick="closeBatchModal()">
        <i class="fas fa-times text-gray-600"></i>
      </button>
    </div>
    <button onclick="openAddBatchForm()" class="bg-gray-700 text-white px-4 py-2 rounded text-sm mb-4">
      + Add Batch
    </button>
    <form id="addBatchForm" class="space-y-4 hidden">
      <div>
        <label class="block text-sm font-medium">Size</label>
        <select id="batchSizeInput" name="product_size_id" class="w-full border px-3 py-2 rounded" required>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium">Batch Number</label>
        <input type="text" id="batchNumberInput" name="batch_number"
               placeholder="e.g., BATCH-2025-001"
               class="w-full border px-3 py-2 rounded" required />
      </div>
      <div>
        <label class="block text-sm font-medium">Manufactured Date</label>
        <input type="date" id="batchManufacturedDateInput" name="manufactured_date"
               class="w-full border px-3 py-2 rounded" required />
      </div>
      <div>
        <label class="block text-sm font-medium">Stock</label>
        <input type="number" id="batchStockInput" name="stock"
               min="0"
               class="w-full border px-3 py-2 rounded" required />
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" onclick="closeAddBatchForm()" class="border border-gray-300 px-4 py-2 rounded text-sm">Cancel</button>
        <button type="submit" class="bg-black text-white px-4 py-2 rounded text-sm">Add Batch</button>
      </div>
    </form>
    <table class="w-full text-sm">
      <thead class="bg-gray-100 text-gray-600">
        <tr>
          <th class="px-4 py-3 text-left">Size</th>
          <th class="px-4 py-3 text-left">Batch Number</th>
          <th class="px-4 py-3 text-center">Manufactured Date</th>
          <th class="px-4 py-3 text-center">Stock</th>
          <th class="px-4 py-3 text-center">Actions</th>
        </tr>
      </thead>
      <tbody id="batchList"></tbody>
    </table>
  </div>
</div>

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
      <input type="hidden" id="editBatchId" />
      <div>
        <label class="block text-sm font-medium">Size</label>
        <input type="text" id="editBatchSizeName" class="w-full border px-3 py-2 rounded" disabled />
      </div>
      <div>
        <label class="block text-sm font-medium">Batch Number</label>
        <input type="text" id="editBatchNumberInput"
               class="w-full border px-3 py-2 rounded" required />
      </div>
      <div>
        <label class="block text-sm font-medium">Manufactured Date</label>
        <input type="date" id="editBatchManufacturedDateInput"
               class="w-full border px-3 py-2 rounded" required />
      </div>
      <div>
        <label class="block text-sm font-medium">Stock</label>
        <input type="number" id="editBatchStockInput"
               min="0"
               class="w-full border px-3 py-2 rounded" required />
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" onclick="closeEditBatchModal()" class="border border-gray-300 px-4 py-2 rounded text-sm">Cancel</button>
        <button type="submit" class="bg-black text-white px-4 py-2 rounded text-sm">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<div id="barcodeModal"
     class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white p-6 rounded shadow-lg max-w-lg w-full">
    <div class="flex justify-end mb-4">
      <button onclick="closeBarcodeModal()" class="text-gray-600 hover:text-black">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    <img
      id="barcodeModalImg"
      src=""
      alt="Barcode"
      class="mx-auto w-full max-h-[80vh] object-contain"
    />
  </div>
</div>

<script type="module">
import { apiGet, apiPost } from './js/ajax.js';

// Modal control functions
window.openCategoryModal = () => document.getElementById('categoryModal').classList.remove('hidden');
window.closeCategoryModal = () => document.getElementById('categoryModal').classList.add('hidden');
window.openEditProductModal = () => {
    editingProductId = null;
    sizes = [];
    productForm.reset();
    renderSizes();
    document.getElementById('editProductModalTitle').textContent = 'Add Product';
    document.getElementById('initialBatchSection').classList.remove('hidden');
    updateInitialBatchSizes();
    document.getElementById('editProductModal').classList.remove('hidden');
};
window.closeEditProductModal = () => {
    document.getElementById('editProductModal').classList.add('hidden');
    editingProductId = null;
    sizes = [];
    productForm.reset();
    renderSizes();
};
window.openBarcodeModal = () => document.getElementById('barcodeModal').classList.remove('hidden');
window.closeBarcodeModal = () => document.getElementById('barcodeModal').classList.add('hidden');
window.openBatchModal = async (productId, productName) => {
    currentProductId = productId;
    document.getElementById('batchModalTitle').textContent = `Manage Batches for ${productName}`;
    await loadBatches(productId);
    await populateBatchSizes(productId);
    document.getElementById('batchModal').classList.remove('hidden');
};
window.closeBatchModal = () => {
    document.getElementById('batchModal').classList.add('hidden');
    document.getElementById('addBatchForm').classList.add('hidden');
};
window.openAddBatchForm = () => {
    document.getElementById('addBatchForm').classList.remove('hidden');
};
window.closeAddBatchForm = () => {
    document.getElementById('addBatchForm').classList.add('hidden');
    document.getElementById('batchSizeInput').value = '';
    document.getElementById('batchNumberInput').value = '';
    document.getElementById('batchManufacturedDateInput').value = '';
    document.getElementById('batchStockInput').value = '';
};
window.openEditBatchModal = (batch) => {
    document.getElementById('editBatchId').value = batch.id;
    document.getElementById('editBatchSizeName').value = batch.size_name;
    document.getElementById('editBatchNumberInput').value = batch.batch_number;
    document.getElementById('editBatchManufacturedDateInput').value = batch.manufactured_date;
    document.getElementById('editBatchStockInput').value = batch.stock;
    document.getElementById('editBatchModal').classList.remove('hidden');
};
window.closeEditBatchModal = () => {
    document.getElementById('editBatchModal').classList.add('hidden');
};

// DOM elements
const
  toast = document.getElementById('toast'),
  searchInput = document.getElementById('searchInput'),
  stockSelect = document.getElementById('stockSelect'),
  categorySelect = document.getElementById('categorySelect'),
  productList = document.getElementById('product-list'),
  newSizeInput = document.getElementById('newSizeInput'),
  addSizeBtn = document.getElementById('addSizeBtn'),
  sizeList = document.getElementById('sizeList'),
  totalUnitsSpan = document.getElementById('totalUnits'),
  stockInput = document.getElementById('stockInput'),
  categoryList = document.getElementById('categoryList'),
  newCategoryInput = document.getElementById('newCategoryInput'),
  addCategoryBtn = document.getElementById('addCategoryBtn'),
  productForm = document.getElementById('productForm'),
  barcodeModalImg = document.getElementById('barcodeModalImg'),
  batchList = document.getElementById('batchList'),
  editBatchForm = document.getElementById('editBatchForm'),
  addBatchForm = document.getElementById('addBatchForm'),
  initialBatchSizeSelect = document.getElementById('initialBatchSize'),
  batchSizeInput = document.getElementById('batchSizeInput');

let sizes = [], editingProductId = null, editingCategoryId = null, currentProductId = null;

function showToast(msg, success = true) {
    toast.textContent = msg;
    toast.classList.toggle('bg-green-500', success);
    toast.classList.toggle('bg-red-500', !success);
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3000);
}

function updateInitialBatchSizes() {
    initialBatchSizeSelect.innerHTML = sizes.length > 0
        ? sizes.map(s => `<option value="${s.size}">${s.size} (${s.stock} units)</option>`).join('')
        : '<option value="">No sizes available</option>';
}

async function populateBatchSizes(productId) {
    const product = (await apiGet(`./api/products.php`)).find(p => p.id === productId);
    batchSizeInput.innerHTML = product.sizes.map(s => `
        <option value="${s.id}">${s.size_name} (${s.stock} units)</option>
    `).join('');
}

document.addEventListener('DOMContentLoaded', async () => {
    await loadCategories();
    fetchProducts();
});

[searchInput, stockSelect, categorySelect].forEach(el =>
    el.addEventListener('input', fetchProducts)
);

async function fetchProducts() {
    try {
        const params = new URLSearchParams({
            search: searchInput.value,
            stock_filter: stockSelect.value.toLowerCase().replace(/ /g, '_'),
            category_id: categorySelect.value === 'All Categories' ? '' : categorySelect.value
        });
        const prods = await apiGet(`./api/products.php?${params}`);
        productList.innerHTML = prods.map(p => {
            const cost = Number(p.price).toFixed(2);
            const sell = Number(p.selling_price).toFixed(2);
            const badges = p.sizes.map(s =>
                `<span class="bg-gray-100 px-2 py-1 rounded text-xs">
                   ${s.size_name}:${s.stock}
                 </span>`
            ).join('');
            const total = p.sizes.reduce((sum, s) => sum + +s.stock, 0);

            return `
                <tr class="border-t hover:bg-gray-50">
                  <td class="px-4 py-3 font-semibold text-center">${p.name}</td>
                  <td class="px-4 py-3 text-center">${p.category_name}</td>
                  <td class="px-4 py-3 flex flex-wrap gap-2">${badges}</td>
                  <td class="px-4 py-3 font-bold text-center">${total}</td>
                  <td class="px-4 py-3 text-center">${p.batches.length}</td>
                  <td class="px-4 py-3 text-center">
                    <img src="./${p.barcode}"
                         alt="Barcode"
                         class="barcode-img h-8 mx-auto cursor-pointer"/>
                  </td>
                  <td class="px-4 py-3 text-center">৳ ${cost}</td>
                  <td class="px-4 py-3 text-center">৳ ${sell}</td>
                  <td class="px-4 py-3 text-center">
                    <button onclick="startEditProduct(${p.id})" class="text-blue-600 mr-2">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="openBatchModal(${p.id}, '${p.name}')" class="text-green-600 mr-2">
                      <i class="fa-solid fa-box-archive"></i>
                    </button>
                    <button onclick="deleteProduct(${p.id})" class="text-red-500">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </td>
                </tr>`;
        }).join('');

        document.querySelectorAll('.barcode-img').forEach(img => {
            img.onclick = () => {
                barcodeModalImg.src = img.src;
                openBarcodeModal();
            };
        });
    } catch (err) {
        console.error(err);
        showToast(`Could not load products: ${err.message}`, false);
    }
}

async function loadBatches(productId) {
    try {
        const batches = await apiGet(`./api/products.php?action=get_batches&product_id=${productId}`);
        batchList.innerHTML = batches.map(b => `
            <tr class="border-t">
              <td class="px-4 py-3 text-left">${b.size_name}</td>
              <td class="px-4 py-3 text-left">${b.batch_number}</td>
              <td class="px-4 py-3 text-center">${b.manufactured_date}</td>
              <td class="px-4 py-3 text-center ${b.stock === 0 ? 'text-gray-500' : ''}">
                ${b.stock}
              </td>
              <td class="px-4 py-3 text-center">
                <button onclick='openEditBatchModal(${JSON.stringify(b)})' class="text-blue-600 mr-2">
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

addBatchForm.onsubmit = async e => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(addBatchForm).entries());

    if (parseInt(data.stock) < 0) {
        showToast('Stock cannot be negative', false);
        return;
    }

    try {
        const res = await apiPost('./api/products.php', {
            action: 'create_batch',
            product_id: currentProductId,
            product_size_id: data.product_size_id,
            batch_number: data.batch_number,
            manufactured_date: data.manufactured_date,
            stock: parseInt(data.stock)
        });
        if (res.success) {
            showToast('Batch added!');
            closeAddBatchForm();
            await loadBatches(currentProductId);
            fetchProducts();
        } else {
            showToast(res.message || 'Failed to add batch', false);
        }
    } catch (err) {
        console.error(err);
        showToast('Error adding batch', false);
    }
};

editBatchForm.onsubmit = async e => {
    e.preventDefault();
    const batchId = document.getElementById('editBatchId').value;
    const batchNumber = document.getElementById('editBatchNumberInput').value.trim();
    const manufacturedDate = document.getElementById('editBatchManufacturedDateInput').value;
    const stock = document.getElementById('editBatchStockInput').value;

    if (!batchNumber || !stock || stock < 0 || !manufacturedDate) {
        showToast('Please enter a valid batch number, manufactured date, and stock', false);
        return;
    }

    try {
        const res = await apiPost('./api/products.php', {
            action: 'update_batch',
            batch_id: batchId,
            batch_number: batchNumber,
            manufactured_date: manufacturedDate,
            stock: parseInt(stock)
        });
        if (res.success) {
            showToast('Batch updated!');
            closeEditBatchModal();
            await loadBatches(currentProductId);
            fetchProducts();
        } else {
            showToast(res.message || 'Failed to update batch', false);
        }
    } catch (err) {
        console.error(err);
        showToast('Error updating batch', false);
    }
};

window.deleteBatch = async (batchId) => {
    if (!confirm('Delete this batch?')) return;
    try {
        const res = await apiPost('./api/products.php', {
            action: 'delete_batch',
            batch_id: batchId
        });
        if (res.success) {
            showToast('Batch deleted');
            await loadBatches(currentProductId);
            fetchProducts();
        } else {
            showToast(res.message || 'Delete failed', false);
        }
    } catch (err) {
        console.error(err);
        showToast('Delete error', false);
    }
};

window.deleteProduct = async id => {
    if (!confirm('Delete this product?')) return;
    try {
        const res = await apiPost('./api/products.php', { action: 'delete', id });
        if (res.success) {
            showToast('Product deleted');
            fetchProducts();
        } else {
            showToast(res.message || 'Delete failed', false);
        }
    } catch (err) {
        console.error(err);
        showToast(`Delete error: ${err.message}`, false);
    }
};

window.startEditProduct = async id => {
    sizes = [];
    productForm.reset();
    editingProductId = id || null;

    if (id) {
        const ps = await apiGet(`./api/products.php`);
        const p = ps.find(x => x.id === id);
        productForm.id.value = p.id;
        productForm.name.value = p.name;
        productForm.description.value = p.description || '';
        productForm.location.value = p.location || '';
        productForm.min_stock.value = p.min_stock;
        productForm.price.value = p.price;
        productForm.selling_price.value = p.selling_price;
        productForm.category_id.value = p.category_id;
        productForm.barcode.value = p.barcode || ''; // Set barcode value
        sizes = p.sizes.map(s => ({ size: s.size_name, stock: +s.stock }));
        document.getElementById('editProductModalTitle').textContent = 'Edit Product';
        document.getElementById('initialBatchSection').classList.add('hidden');
        // Clear initial batch fields to avoid validation issues
        document.getElementById('initialBatchSize').value = '';
        document.getElementById('initialManufacturedDate').value = '';
        productForm.batch_number.value = '';
    } else {
        document.getElementById('editProductModalTitle').textContent = 'Add Product';
        document.getElementById('initialBatchSection').classList.remove('hidden');
    }

    renderSizes();
    document.getElementById('editProductModal').classList.remove('hidden');
};

addSizeBtn.onclick = () => {
    const sz = newSizeInput.value.trim();
    if (!sz || sizes.some(x => x.size === sz)) {
        showToast('Please enter a unique size', false);
        return;
    }
    sizes.push({ size: sz, stock: 0 });
    newSizeInput.value = '';
    renderSizes();
    updateInitialBatchSizes();
};

function renderSizes() {
    sizeList.innerHTML = sizes.map((s, i) => `
        <li class="flex items-center gap-2">
          <span class="bg-gray-100 px-2 py-1 rounded text-sm">${s.size}</span>
          <input type="number" min="0" value="${s.stock}"
                 data-idx="${i}"
                 class="border px-2 py-1 rounded w-16 sizeStock"/>
          <span>units</span>
          <button data-idx="${i}"
                  class="ml-auto text-red-500 removeSize">×</button>
        </li>`).join('');

    sizeList.querySelectorAll('.sizeStock').forEach(inp => {
        inp.oninput = e => {
            sizes[+e.target.dataset.idx].stock = +e.target.value || 0;
            updateTotal();
            updateInitialBatchSizes();
        };
    });
    sizeList.querySelectorAll('.removeSize').forEach(btn => {
        btn.onclick = () => {
            sizes.splice(+btn.dataset.idx, 1);
            renderSizes();
            updateInitialBatchSizes();
        };
    });
    updateTotal();
}

function updateTotal() {
    const total = sizes.reduce((sum, s) => sum + (s.stock || 0), 0);
    totalUnitsSpan.textContent = total;
    stockInput.value = total;
}

productForm.onsubmit = async e => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(productForm).entries());

    // Validate sizes and stock
    if (sizes.length === 0) {
        showToast('Please add at least one size', false);
        return;
    }
    const totalStock = sizes.reduce((sum, s) => sum + (s.stock || 0), 0);
    if (totalStock <= 0) {
        showToast('Total stock must be greater than 0', false);
        return;
    }

    // For adding a new product, validate batch fields
    if (!editingProductId) {
        if (!data.initial_batch_size || !data.batch_number || !data.manufactured_date) {
            showToast('Initial batch size, batch number, and manufactured date are required for new products', false);
            return;
        }
    }

    const payload = {
        action: editingProductId ? 'update' : undefined,
        id: editingProductId,
        name: data.name,
        category_id: +data.category_id,
        description: data.description || null,
        location: data.location || null,
        min_stock: +data.min_stock || 5,
        price: parseFloat(data.price),
        selling_price: parseFloat(data.selling_price),
        stock: totalStock,
        barcode: data.barcode || null, // Include barcode in payload
        sizes: sizes.map(s => ({ size: s.size, stock: s.stock || 0 }))
    };

    // Only include batch fields for new products
    if (!editingProductId) {
        payload.batch_number = data.batch_number || null;
        payload.manufactured_date = data.manufactured_date || null;
    }

    try {
        const res = await apiPost('./api/products.php', payload);
        if (res.success) {
            showToast(editingProductId ? 'Product updated!' : 'Product added!');
            closeEditProductModal();
            fetchProducts();
        } else {
            showToast(res.message || 'Save failed', false);
        }
    } catch (err) {
        console.error(err);
        showToast(`Save error: ${err.message}`, false);
    }
};

async function loadCategories() {
    try {
        const cats = await apiGet('./api/categories.php');
        categorySelect.innerHTML =
            '<option>All Categories</option>' +
            cats.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
        productForm.category_id.innerHTML =
            cats.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
        categoryList.innerHTML = cats.map(c => `
            <li class="flex justify-between items-center py-2" data-category-id="${c.id}">
              <span class="category-name">${c.name}</span>
              <input type="text" class="category-edit-input hidden border px-2 py-1 rounded w-2/3" value="${c.name}" />
              <div class="flex gap-2">
                <button onclick="startEditCategory(${c.id})" class="edit-btn text-gray-500 cursor-pointer">
                  <i class="fas fa-edit"></i>
                </button>
                <button onclick="confirmEditCategory(${c.id})" class="confirm-btn hidden text-green-500 cursor-pointer">
                  <i class="fas fa-check"></i>
                </button>
                <button onclick="deleteCategory(${c.id})" class="text-red-500 cursor-pointer">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </li>`).join('');
    } catch (err) {
        console.error(err);
        showToast(`Could not load categories: ${err.message}`, false);
    }
}

window.startEditCategory = id => {
    if (editingCategoryId) {
        cancelEditCategory(editingCategoryId);
    }
    editingCategoryId = id;
    const li = document.querySelector(`li[data-category-id="${id}"]`);
    li.querySelector('.category-name').classList.add('hidden');
    li.querySelector('.category-edit-input').classList.remove('hidden');
    li.querySelector('.edit-btn').classList.add('hidden');
    li.querySelector('.confirm-btn').classList.remove('hidden');
    li.querySelector('.category-edit-input').focus();
};

window.confirmEditCategory = async id => {
    const li = document.querySelector(`li[data-category-id="${id}"]`);
    const newName = li.querySelector('.category-edit-input').value.trim();
    if (!newName) {
        showToast('Category name cannot be empty', false);
        return;
    }
    try {
        const res = await apiPost('./api/categories.php', { action: 'update', id, name: newName });
        if (res.success) {
            showToast('Category updated!');
            editingCategoryId = null;
            await loadCategories();
        } else {
            showToast(res.message || 'Update failed', false);
        }
    } catch (err) {
        console.error(err);
        showToast(`Update error: ${err.message}`, false);
    }
};

window.cancelEditCategory = id => {
    const li = document.querySelector(`li[data-category-id="${id}"]`);
    li.querySelector('.category-name').classList.remove('hidden');
    li.querySelector('.category-edit-input').classList.add('hidden');
    li.querySelector('.edit-btn').classList.remove('hidden');
    li.querySelector('.confirm-btn').classList.add('hidden');
};

addCategoryBtn.onclick = async () => {
    const name = newCategoryInput.value.trim();
    if (!name) {
        showToast('Category name cannot be empty', false);
        return;
    }
    try {
        const res = await apiPost('./api/categories.php', { name });
        if (res.success) {
            showToast('Category added!');
            newCategoryInput.value = '';
            await loadCategories();
        } else {
            showToast(res.message || 'Add failed', false);
        }
    } catch (err) {
        console.error(err);
        showToast(`Add error: ${err.message}`, false);
    }
};

window.deleteCategory = async id => {
    if (!confirm('Delete this category?')) return;
    try {
        const res = await apiPost('./api/categories.php', { action: 'delete', id });
        if (res.success) {
            showToast('Category deleted!');
            await loadCategories();
        } else {
            showToast(res.message || 'Delete failed', false);
        }
    } catch (err) {
        console.error(err);
        showToast(`Delete error: ${err.message}`, false);
    }
};
</script>