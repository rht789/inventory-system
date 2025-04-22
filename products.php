<?php
// products.php
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

  <!-- Filters & Search -->
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

  <!-- Product Table -->
  <div class="bg-white rounded shadow-sm overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-gray-100 text-gray-600">
        <tr>
          <th class="px-4 py-3 text-center">Name</th>
          <th class="px-4 py-3 text-center">Category</th>
          <th class="px-4 py-3 text-left">Size & Stock</th>
          <th class="px-4 py-3 text-center">Total Stock</th>
          <th class="px-4 py-3 text-center">Barcode</th>
          <th class="px-4 py-3 text-center">Cost Price (৳)</th>
          <th class="px-4 py-3 text-center">Selling Price (৳)</th>
          <th class="px-4 py-3 text-center">Actions</th>
        </tr>
      </thead>
      <tbody id="product-list">
        <!-- injected by JS -->
      </tbody>
    </table>
  </div>
</main>

<!-- Category Modal -->
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

<!-- Add/Edit Product Modal -->
<div id="editProductModal"
     class="fixed inset-0 hidden bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-full max-w-md overflow-auto max-h-screen">
    <div class="flex justify-between items-center mb-2">
      <h3 class="text-lg font-semibold" id="editProductModalTitle">Edit Product</h3>
      <button onclick="closeEditProductModal()">
        <i class="fas fa-times text-gray-600"></i>
      </button>
    </div>
    <p class="text-sm text-gray-500 mb-4">Update product information.</p>
    <form id="productForm" class="space-y-4">
      <input type="hidden" name="id" />
      <input type="hidden" name="barcode" />

      <!-- Name & Category -->
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

      <!-- Description -->
      <div>
        <label class="block text-sm font-medium">Description</label>
        <input type="text" name="description"
               placeholder="Enter product description"
               class="w-full border px-3 py-2 rounded" />
      </div>

      <!-- Sizes & Stock -->
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

      <!-- Location -->
      <div>
        <label class="block text-sm font-medium">Location</label>
        <input type="text" name="location"
               placeholder="Enter location (e.g., Shelf A, Side B)"
               class="w-full border px-3 py-2 rounded" />
      </div>

      <!-- Minimum Stock -->
      <div>
        <label class="block text-sm font-medium">Minimum Stock Level</label>
        <input type="number" name="min_stock"
               class="w-full border px-3 py-2 rounded"
               min="0" value="5" />
      </div>

      <!-- Prices -->
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

<!-- Barcode Preview Modal -->
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

// expose for onclick handlers
window.openCategoryModal     = () => document.getElementById('categoryModal').classList.remove('hidden');
window.closeCategoryModal    = () => document.getElementById('categoryModal').classList.add('hidden');
window.openEditProductModal  = () => {
    // Reset state for adding a new product
    editingProductId = null;
    sizes = [];
    productForm.reset();
    renderSizes();
    document.getElementById('editProductModalTitle').textContent = 'Add Product';
    document.getElementById('editProductModal').classList.remove('hidden');
};
window.closeEditProductModal = () => {
    document.getElementById('editProductModal').classList.add('hidden');
    // Reset state when closing the modal
    editingProductId = null;
    sizes = [];
    productForm.reset();
    renderSizes();
};
window.openBarcodeModal      = () => document.getElementById('barcodeModal').classList.remove('hidden');
window.closeBarcodeModal     = () => document.getElementById('barcodeModal').classList.add('hidden');

// DOM refs
const
  toast             = document.getElementById('toast'),
  searchInput       = document.getElementById('searchInput'),
  stockSelect       = document.getElementById('stockSelect'),
  categorySelect    = document.getElementById('categorySelect'),
  productList       = document.getElementById('product-list'),
  newSizeInput      = document.getElementById('newSizeInput'),
  addSizeBtn        = document.getElementById('addSizeBtn'),
  sizeList          = document.getElementById('sizeList'),
  totalUnitsSpan    = document.getElementById('totalUnits'),
  stockInput        = document.getElementById('stockInput'),
  categoryList      = document.getElementById('categoryList'),
  newCategoryInput  = document.getElementById('newCategoryInput'),
  addCategoryBtn    = document.getElementById('addCategoryBtn'),
  productForm       = document.getElementById('productForm'),
  barcodeModalImg   = document.getElementById('barcodeModalImg');

let sizes = [], editingProductId = null, editingCategoryId = null;

// show toast
function showToast(msg, success = true) {
  toast.textContent = msg;
  toast.classList.toggle('bg-green-500', success);
  toast.classList.toggle('bg-red-500', !success);
  toast.classList.remove('hidden');
  setTimeout(() => toast.classList.add('hidden'), 3000);
}

// initial load
document.addEventListener('DOMContentLoaded', async () => {
  await loadCategories();
  fetchProducts();
});

// re‑fetch on filter change
[searchInput, stockSelect, categorySelect].forEach(el =>
  el.addEventListener('input', fetchProducts)
);

// fetch & render products
async function fetchProducts() {
  try {
    const params = new URLSearchParams({
      search:       searchInput.value,
      stock_filter: stockSelect.value.toLowerCase().replace(/ /g, '_'),
      category_id:  categorySelect.value === 'All Categories' ? '' : categorySelect.value
    });
    const prods = await apiGet(`./api/products.php?${params}`);
    productList.innerHTML = prods.map(p => {
      const cost   = Number(p.price).toFixed(2);
      const sell   = Number(p.selling_price).toFixed(2);
      const badges = p.sizes.map(s =>
        `<span class="bg-gray-100 px-2 py-1 rounded text-xs">
           ${s.size_name}:${s.stock}
         </span>`
      ).join('');
      const total  = p.sizes.reduce((sum, s) => sum + +s.stock, 0);

      return `
        <tr class="border-t hover:bg-gray-50">
          <td class="px-4 py-3 font-semibold text-center">${p.name}</td>
          <td class="px-4 py-3 text-center">${p.category_name}</td>
          <td class="px-4 py-3 flex flex-wrap gap-2">${badges}</td>
          <td class="px-4 py-3 font-bold text-center">${total}</td>
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
            <button onclick="deleteProduct(${p.id})" class="text-red-500">
              <i class="fas fa-trash-alt"></i>
            </button>
          </td>
        </tr>`;
    }).join('');

    // attach click handlers to barcode thumbnails
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

// delete product
window.deleteProduct = async id => {
  if (!confirm('Delete this product?')) return;
  try {
    const res = await apiPost('./api/products.php', { action:'delete', id });
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

// start edit (or add)
window.startEditProduct = async id => {
  // Reset state
  sizes = [];
  productForm.reset();
  editingProductId = id || null;

  if (id) {
    // Edit mode: Populate form with product data
    const ps = await apiGet(`./api/products.php`);
    const p = ps.find(x => x.id === id);
    productForm.id.value = p.id;
    productForm.name.value = p.name;
    productForm.description.value = p.description || '';
    productForm.location.value = p.location || ''; // Populate location
    productForm.min_stock.value = p.min_stock;
    productForm.price.value = p.price;
    productForm.selling_price.value = p.selling_price;
    productForm.category_id.value = p.category_id;
    sizes = p.sizes.map(s => ({ size: s.size_name, stock: +s.stock }));
    document.getElementById('editProductModalTitle').textContent = 'Edit Product';
  } else {
    // Add mode: Ensure form is fully reset
    document.getElementById('editProductModalTitle').textContent = 'Add Product';
  }

  renderSizes();
  document.getElementById('editProductModal').classList.remove('hidden');
};

// add a size
addSizeBtn.onclick = () => {
  const sz = newSizeInput.value.trim();
  if (!sz || sizes.some(x => x.size === sz)) return;
  sizes.push({ size: sz, stock: 0 });
  newSizeInput.value = '';
  renderSizes();
};

// render sizes
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
      sizes[+e.target.dataset.idx].stock = +e.target.value;
      updateTotal();
    };
  });
  sizeList.querySelectorAll('.removeSize').forEach(btn => {
    btn.onclick = () => {
      sizes.splice(+btn.dataset.idx, 1);
      renderSizes();
    };
  });
  updateTotal();
}

// update total units
function updateTotal() {
  const total = sizes.reduce((sum, s) => sum + s.stock, 0);
  totalUnitsSpan.textContent = total;
  stockInput.value = total;
}

// save (create/update)
productForm.onsubmit = async e => {
  e.preventDefault();
  const data = Object.fromEntries(new FormData(productForm).entries());
  const payload = {
    action: editingProductId ? 'update' : undefined,
    id: editingProductId,
    name: data.name,
    category_id: +data.category_id,
    description: data.description,
    location: data.location || null, // Include location in payload
    min_stock: +data.min_stock,
    price: parseFloat(data.price),
    selling_price: parseFloat(data.selling_price),
    stock: +data.stock,
    sizes
  };

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

// load categories
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

// start editing a category
window.startEditCategory = id => {
  if (editingCategoryId) {
    // If another category is being edited, cancel that edit
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

// confirm editing a category
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

// cancel editing a category (used when starting a new edit)
window.cancelEditCategory = id => {
  const li = document.querySelector(`li[data-category-id="${id}"]`);
  li.querySelector('.category-name').classList.remove('hidden');
  li.querySelector('.category-edit-input').classList.add('hidden');
  li.querySelector('.edit-btn').classList.remove('hidden');
  li.querySelector('.confirm-btn').classList.add('hidden');
};

// add category
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

// delete category
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