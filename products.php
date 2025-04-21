<?php
// products.php
include 'header.php';
include 'sidebar.php';
include 'footer.php';
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
        <tr class="text-left">
          <th class="px-4 py-3">Name</th>
          <th class="px-4 py-3">Category</th>
          <th class="px-4 py-3">Size & Stock</th>
          <th class="px-4 py-3">Total Stock</th>
          <th class="px-4 py-3">Cost Price (৳)</th>
          <th class="px-4 py-3">Selling Price (৳)</th>
          <th class="px-4 py-3">Actions</th>
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
      <h3 class="text-lg font-semibold">Edit Product</h3>
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

<script type="module">
import { apiGet, apiPost } from './js/ajax.js';

// expose for onclick handlers
window.openCategoryModal    = () => document.getElementById('categoryModal').classList.remove('hidden');
window.closeCategoryModal   = () => document.getElementById('categoryModal').classList.add('hidden');
window.openEditProductModal = () => document.getElementById('editProductModal').classList.remove('hidden');
window.closeEditProductModal= () => document.getElementById('editProductModal').classList.add('hidden');

// DOM refs
const
  toast          = document.getElementById('toast'),
  searchInput    = document.getElementById('searchInput'),
  stockSelect    = document.getElementById('stockSelect'),
  categorySelect = document.getElementById('categorySelect'),
  productList    = document.getElementById('product-list'),
  newSizeInput   = document.getElementById('newSizeInput'),
  addSizeBtn     = document.getElementById('addSizeBtn'),
  sizeList       = document.getElementById('sizeList'),
  totalUnitsSpan = document.getElementById('totalUnits'),
  stockInput     = document.getElementById('stockInput'),
  categoryList   = document.getElementById('categoryList'),
  newCategoryInput = document.getElementById('newCategoryInput'),
  addCategoryBtn = document.getElementById('addCategoryBtn'),
  productForm    = document.getElementById('productForm');

let sizes = [], editingProductId = null;

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
      // coerce to numbers immediately
      const cost  = Number(p.price);
      const sell  = Number(p.selling_price);
      const badges = p.sizes.map(s =>
        `<span class="bg-gray-100 px-2 py-1 rounded text-xs">
           ${s.size_name}:${s.stock}
         </span>`
      ).join('');
      const total = p.sizes.reduce((sum, s) => sum + +s.stock, 0);
      return `
        <tr class="border-t hover:bg-gray-50">
          <td class="px-4 py-3 font-semibold">${p.name}</td>
          <td class="px-4 py-3">${p.category_name}</td>
          <td class="px-4 py-3 flex flex-wrap gap-2">${badges}</td>
          <td class="px-4 py-3 font-bold text-center">${total}</td>
          <td class="px-4 py-3">৳ ${cost.toFixed(2)}</td>
          <td class="px-4 py-3">৳ ${sell.toFixed(2)}</td>
          <td class="px-4 py-3">
            <button onclick="startEditProduct(${p.id})" class="text-blue-600 mr-2">
              <i class="fas fa-edit"></i>
            </button>
            <button onclick="deleteProduct(${p.id})" class="text-red-500">
              <i class="fas fa-trash-alt"></i>
            </button>
          </td>
        </tr>`;
    }).join('');
  } catch (err) {
    console.error(err);
    showToast(`Could not load products: ${err.message}`, false);
  }
}

// delete
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

// start edit (or add new)
window.startEditProduct = async id => {
  sizes = []; productForm.reset();
  editingProductId = id || null;

  if (id) {
    const ps = await apiGet(`./api/products.php`);
    const p  = ps.find(x => x.id === id);
    productForm.id.value           = p.id;
    productForm.name.value         = p.name;
    productForm.description.value  = p.description;
    productForm.min_stock.value    = p.min_stock;
    productForm.price.value        = p.price;
    productForm.selling_price.value= p.selling_price;
    productForm.category_id.value  = p.category_id;
    sizes = p.sizes.map(s => ({ size:s.size_name, stock:+s.stock }));
  }

  renderSizes();
  openEditProductModal();
};

// add a size
addSizeBtn.onclick = () => {
  const sz = newSizeInput.value.trim();
  if (!sz || sizes.some(x => x.size === sz)) return;
  sizes.push({ size:sz, stock:0 });
  newSizeInput.value = '';
  renderSizes();
};

// render sizes list
function renderSizes() {
  sizeList.innerHTML = sizes.map((s,i) => `
    <li class="flex items-center gap-2">
      <span class="bg-gray-100 px-2 py-1 rounded text-sm">${s.size}</span>
      <input type="number" min="0" value="${s.stock}"
             data-idx="${i}" class="border px-2 py-1 rounded w-16 sizeStock"/>
      <span>units</span>
      <button data-idx="${i}" class="ml-auto text-red-500 removeSize">&times;</button>
    </li>`).join('');

  sizeList.querySelectorAll('.sizeStock').forEach(inp => {
    inp.oninput = e => {
      sizes[+e.target.dataset.idx].stock = +e.target.value;
      updateTotal();
    };
  });
  sizeList.querySelectorAll('.removeSize').forEach(btn => {
    btn.onclick = () => {
      sizes.splice(+btn.dataset.idx,1);
      renderSizes();
    };
  });
  updateTotal();
}

// update total units
function updateTotal() {
  const total = sizes.reduce((sum,s) => sum + s.stock, 0);
  totalUnitsSpan.textContent = total;
  stockInput.value = total;
}

// save (create/update)
productForm.onsubmit = async e => {
  e.preventDefault();
  const data = Object.fromEntries(new FormData(productForm).entries());
  const payload = {
    action: editingProductId ? 'update' : undefined,
    id:     editingProductId,
    name:   data.name,
    category_id: +data.category_id,
    description: data.description,
    min_stock: +data.min_stock,
    price:  parseFloat(data.price),
    selling_price: parseFloat(data.selling_price),
    stock:  +data.stock,
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

// load categories for filter, form & modal
async function loadCategories() {
  const cats = await apiGet('./api/categories.php');
  categorySelect.innerHTML =
    '<option>All Categories</option>' +
    cats.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
  productForm.category_id.innerHTML =
    cats.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
  categoryList.innerHTML =
    cats.map(c => `
      <li class="flex justify-between items-center py-2">${c.name}
        <div class="flex gap-2">
          <i class="fas fa-edit text-gray-500 cursor-pointer"
             onclick="alert('Edit not implemented')"></i>
          <i class="fas fa-trash text-red-500 cursor-pointer"
             onclick="deleteCategory(${c.id})"></i>
        </div>
      </li>`).join('');
}

// add category
addCategoryBtn.onclick = async () => {
  const name = newCategoryInput.value.trim();
  if (!name) return alert('Enter category name');
  await apiPost('./api/categories.php', { name });
  newCategoryInput.value = '';
  loadCategories();
};

// delete category
window.deleteCategory = async id => {
  if (!confirm('Delete this category?')) return;
  await apiPost('./api/categories.php', { action:'delete', id });
  loadCategories();
};
</script>
