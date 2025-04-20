<?php
// products.php
include 'header.php';
include 'sidebar.php';
?>

<main class="lg:ml-64 min-h-screen p-6 bg-gray-100">
  <!-- Topbar -->
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">Product Management</h2>
    <div class="flex gap-2">
      <button
        onclick="openCategoryModal()"
        class="border border-gray-400 rounded px-4 py-2 text-sm hover:bg-gray-100"
      >
        Manage Categories
      </button>
      <button
        onclick="openEditProductModal()"
        class="bg-black text-white px-4 py-2 rounded text-sm"
      >
        + Add Product
      </button>
    </div>
  </div>

  <!-- Filters & Search -->
  <div class="bg-white p-4 rounded-md shadow-sm mb-4">
    <div class="flex flex-col md:flex-row md:items-center gap-4 justify-between">
      <input
        type="text" id="searchInput"
        placeholder="Search Orders..."
        class="border px-4 py-2 rounded w-full md:w-1/3"
      />
      <div class="flex gap-2 w-full md:w-auto">
        <select id="stockSelect"
          class="border rounded px-3 py-2 text-sm"
        >
          <option>All Stock</option>
          <option>In Stock</option>
          <option>Low Stock</option>
          <option>Out of Stock</option>
        </select>
        <select id="categorySelect"
          class="border rounded px-3 py-2 text-sm"
        >
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
        <!-- JS‑injected rows -->
      </tbody>
    </table>
  </div>
</main>

<!-- Manage Categories Modal -->
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
      <input
        type="text" id="newCategoryInput"
        placeholder="Enter new category name"
        class="flex-1 border px-3 py-2 rounded"
      />
      <button
        id="addCategoryBtn"
        class="bg-gray-700 text-white px-4 rounded"
      >
        + Add
      </button>
    </div>
    <ul id="categoryList" class="divide-y text-sm">
      <!-- JS‑injected -->
    </ul>
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
      <!-- we'll keep a hidden ID field when editing -->
      <input type="hidden" name="id" />

      <!-- Name & Category -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Product Name</label>
          <input
            type="text" name="name"
            class="w-full border px-3 py-2 rounded"
            required
          />
        </div>
        <div>
          <label class="block text-sm font-medium">Category</label>
          <select
            name="category_id"
            class="w-full border px-3 py-2 rounded"
            required
          ></select>
        </div>
      </div>

      <!-- SKU & Color -->
      <!-- SKU & Description -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Barcode</label>
          <input
            type="text"
            name="barcode"
            placeholder="Scan or enter barcode"
            class="w-full border px-3 py-2 rounded"
          />
        </div>
        <div>
          <label class="block text-sm font-medium">Description</label>
          <input
            type="text"
            name="description"
            placeholder="Enter product description"
            class="w-full border px-3 py-2 rounded"
          />
        </div>
      </div>


      <!-- Sizes & Stock -->
      <div>
        <label class="block text-sm font-medium mb-1">Sizes & Stock</label>
        <div class="flex gap-2 mb-2">
          <input
            type="text" id="newSizeInput"
            placeholder="Enter size (e.g., S, M, L, XL)"
            class="flex-1 border px-3 py-2 rounded"
          />
          <button
            type="button" id="addSizeBtn"
            class="bg-gray-700 text-white px-4 py-2 rounded"
          >
            Add Size
          </button>
        </div>
        <ul id="sizeList" class="space-y-2 max-h-40 overflow-y-auto">
          <!-- JS‑injected size items -->
        </ul>
        <div class="text-sm font-medium mt-2">
          Total <span id="totalUnits">0</span> units
        </div>
      </div>

      <!-- hidden total stock for backend -->
      <input type="hidden" name="stock" id="stockInput" />

      <!-- Minimum Stock -->
      <div>
        <label class="block text-sm font-medium">Minimum Stock Level</label>
        <input
          type="number" name="min_stock"
          class="w-full border px-3 py-2 rounded"
          min="0" value="5"
        />
      </div>

      <!-- Cost & Selling Price -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Cost Price (৳)</label>
          <input
            type="number" name="cost_price"
            step="0.01"
            class="w-full border px-3 py-2 rounded"
            required
          />
        </div>
        <div>
          <label class="block text-sm font-medium">Selling Price (৳)</label>
          <input
            type="number" name="selling_price"
            step="0.01"
            class="w-full border px-3 py-2 rounded"
            required
          />
        </div>
      </div>

      <button
        type="submit"
        class="bg-black text-white w-full py-2 rounded"
      >
        Update Product
      </button>
    </form>
  </div>
</div>

<script type="module">
import { apiGet, apiPost } from './js/ajax.js';

// re–expose for your inline onclicks
window.openCategoryModal   = () => document.getElementById('categoryModal').classList.remove('hidden');
window.closeCategoryModal  = () => document.getElementById('categoryModal').classList.add('hidden');
window.openEditProductModal  = () => document.getElementById('editProductModal').classList.remove('hidden');
window.closeEditProductModal = () => document.getElementById('editProductModal').classList.add('hidden');

const searchInput    = document.getElementById('searchInput');
const stockSelect    = document.getElementById('stockSelect');
const categorySelect = document.getElementById('categorySelect');
const productList    = document.getElementById('product-list');

const newSizeInput   = document.getElementById('newSizeInput');
const addSizeBtn     = document.getElementById('addSizeBtn');
const sizeList       = document.getElementById('sizeList');
const totalUnitsSpan = document.getElementById('totalUnits');
const stockInput     = document.getElementById('stockInput');
const categoryList   = document.getElementById('categoryList');
const newCategoryInput = document.getElementById('newCategoryInput');
const addCategoryBtn = document.getElementById('addCategoryBtn');
const productForm    = document.getElementById('productForm');

let sizes = [];
let editingProductId = null;

// on load
document.addEventListener('DOMContentLoaded', async () => {
  await loadCategories();    // for both dropdown & modal
  fetchProducts();
});

// re-fetch on filter change
[ searchInput, stockSelect, categorySelect ].forEach(el =>
  el.addEventListener('input', fetchProducts)
);

// load & render products
async function fetchProducts() {
  try {
    const params = new URLSearchParams({
      search:       searchInput.value,
      stock_filter: stockSelect.value.toLowerCase().replace(/ /g,'_'),
      category_id:  categorySelect.value === 'All Categories' ? '' : categorySelect.value
    });
    const prods = await apiGet(`api/products.php?${params}`);
    productList.innerHTML = prods.map(p => {
      const badges = p.sizes.map(s =>
        `<span class="bg-gray-100 px-2 py-1 rounded text-xs">${s.size}:${s.stock}</span>`
      ).join('');
      const total = p.sizes.reduce((sum,s)=> sum + +s.stock, 0);
      // selling = cost * (1 + discount/100)
      const selling = (p.price*(1 + p.discount/100)).toFixed(2);
      return `
        <tr class="border-t hover:bg-gray-50">
          <td class="px-4 py-3 font-semibold">${p.name}</td>
          <td class="px-4 py-3">${p.category_name}</td>
          <td class="px-4 py-3 flex flex-wrap gap-2">${badges}</td>
          <td class="px-4 py-3 font-bold text-center">${total}</td>
          <td class="px-4 py-3">৳ ${p.price.toFixed(2)}</td>
          <td class="px-4 py-3">৳ ${selling}</td>
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
  } catch (e) {
    console.error(e);
    alert('Could not load products.');
  }
}

// delete
window.deleteProduct = async id => {
  if (!confirm('Delete this product?')) return;
  try {
    await apiPost('api/products.php', { action:'delete', id });
    fetchProducts();
  } catch {
    alert('Delete failed.');
  }
};

// start edit (or add)
window.startEditProduct = async id => {
  // reset sizes
  sizes = [];
  productForm.reset();
  editingProductId = id || null;
  if (id) {
    const prods = await apiGet('api/products.php?search=&stock_filter=&category_id=');
    const p = prods.find(x=>x.id===id);
    productForm.id.value          = p.id;
    productForm.name.value        = p.name;
    productForm.barcode.value     = p.barcode;
    productForm.description.value = p.description;
    productForm.min_stock.value   = p.min_stock;
    productForm.cost_price.value  = p.price;
    // compute markup discount back
    productForm.selling_price.value = (p.price*(1+p.discount/100)).toFixed(2);
    productForm.category_id.value = p.category_id;
    // load existing sizes
    sizes = p.sizes.map(s=>({ size:s.size, stock:+s.stock }));
  }
  renderSizes();
  openEditProductModal();
};

// add a size
addSizeBtn.onclick = () => {
  const sz = newSizeInput.value.trim();
  if (!sz || sizes.find(x=>x.size===sz)) return;
  sizes.push({ size: sz, stock: 0 });
  newSizeInput.value = '';
  renderSizes();
};

function renderSizes() {
  sizeList.innerHTML = sizes.map((s,i)=>`
    <li class="flex items-center gap-2">
      <span class="bg-gray-100 px-2 py-1 rounded text-sm">${s.size}</span>
      <input
        type="number"
        min="0"
        value="${s.stock}"
        data-idx="${i}"
        class="border px-2 py-1 rounded w-16 sizeStock"
      />
      <span>units</span>
      <button
        type="button"
        data-idx="${i}"
        class="ml-auto text-red-500 removeSize"
      >&times;</button>
    </li>
  `).join('');
  // bind events
  sizeList.querySelectorAll('.sizeStock').forEach(inp => {
    inp.oninput = e => {
      const i = +e.target.dataset.idx;
      sizes[i].stock = +e.target.value;
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

function updateTotal() {
  const total = sizes.reduce((sum,s)=> sum + s.stock, 0);
  totalUnitsSpan.textContent = total;
  stockInput.value = total;
}

// handle save
productForm.onsubmit = async e => {
  e.preventDefault();
  const fd = new FormData(productForm);
  const data = Object.fromEntries(fd.entries());
  const cost = parseFloat(data.cost_price);
  const sell = parseFloat(data.selling_price);
  const payload = {
    action:    editingProductId ? 'update' : undefined,
    id:        editingProductId,
    name:      data.name,
    category_id: data.category_id,
    barcode:   data.barcode,
    description: data.description,
    min_stock: parseInt(data.min_stock),
    price:     cost,
    discount:  ((sell - cost)/cost)*100,
    stock:     parseInt(data.stock),
    sizes:     sizes
  };
  try {
    const res = await apiPost('api/products.php', payload);
    if (res.success) {
      closeEditProductModal();
      fetchProducts();
    } else {
      alert(res.message || 'Could not save product.');
    }
  } catch (err) {
    console.error(err);
    alert('Could not save product.');
  }
};

// load categories into both the modal list and the two dropdowns
async function loadCategories() {
  const cats = await apiGet('api/categories.php');
  // filter dropdown
  categorySelect.innerHTML =
    '<option>All Categories</option>' +
    cats.map(c=>`<option value="${c.id}">${c.name}</option>`).join('');
  // form dropdown
  productForm.category_id.innerHTML =
    cats.map(c=>`<option value="${c.id}">${c.name}</option>`).join('');
  // modal list
  categoryList.innerHTML = cats.map(c=>`
    <li class="flex justify-between items-center py-2" data-id="${c.id}">
      ${c.name}
      <div class="flex gap-2">
        <i class="fas fa-edit text-gray-500 cursor-pointer"
           onclick="alert('Edit not yet')" ></i>
        <i class="fas fa-trash text-red-500 cursor-pointer"
           onclick="deleteCategory(${c.id})"></i>
      </div>
    </li>
  `).join('');
}

// add category
addCategoryBtn.onclick = async () => {
  const name = newCategoryInput.value.trim();
  if (!name) return alert('Enter category name');
  await apiPost('api/categories.php', { name });
  newCategoryInput.value = '';
  loadCategories();
};

// delete category
window.deleteCategory = async id => {
  if (!confirm('Delete this category?')) return;
  await apiPost('api/categories.php', { action:'delete', id });
  loadCategories();
};

</script>
