<!-- products.php -->
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<main class="lg:ml-64 min-h-screen p-6 bg-gray-100">
  <!-- Topbar -->
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">Product Management</h2>
    <div class="flex gap-2">
      <button onclick="openCategoryModal()" class="border border-gray-400 rounded px-4 py-2 text-sm hover:bg-gray-100">Manage Categories</button>
      <button onclick="openEditProductModal()" class="bg-black text-white px-4 py-2 rounded text-sm">+ Add Product</button>
    </div>
  </div>

  <!-- Filters and Search -->
  <div class="bg-white p-4 rounded-md shadow-sm mb-4">
    <div class="flex flex-col md:flex-row md:items-center gap-4 justify-between">
      <input type="text" placeholder="Search Orders..." class="border px-4 py-2 rounded w-full md:w-1/3" />

      <div class="flex gap-2 w-full md:w-auto">
        <!-- Stock Filter -->
        <div class="relative">
          <select class="border rounded px-3 py-2 text-sm">
            <option>All Stock</option>
            <option>In Stock</option>
            <option>Low Stock</option>
            <option>Out of Stock</option>
          </select>
        </div>

        <!-- Category Filter -->
        <div class="relative">
          <select class="border rounded px-3 py-2 text-sm">
            <option>All Categories</option>
            <option>Khadi Panjabi</option>
            <option>Payjama</option>
            <option>Smart Watch</option>
            <option>T Shirt</option>
          </select>
        </div>
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
          <th class="px-4 py-3">Cost Price</th>
          <th class="px-4 py-3">Selling Price</th>
          <th class="px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Repeat this row with loop -->
        <tr class="border-t hover:bg-gray-50">
          <td class="px-4 py-3 font-semibold">Cream Khadi (P21)</td>
          <td class="px-4 py-3">Khadi Panjabi</td>
          <td class="px-4 py-3 flex flex-wrap gap-2">
            <span class="bg-gray-100 px-2 py-1 rounded text-xs">S:3</span>
            <span class="bg-gray-100 px-2 py-1 rounded text-xs">M:10</span>
            <span class="bg-gray-100 px-2 py-1 rounded text-xs">L:5</span>
            <span class="bg-gray-100 px-2 py-1 rounded text-xs">XL:9</span>
          </td>
          <td class="px-4 py-3 font-bold text-center">20</td>
          <td class="px-4 py-3">৳ 850</td>
          <td class="px-4 py-3">৳ 1550</td>
          <td class="px-4 py-3">
            <button onclick="openEditProductModal()" class="text-blue-600 mr-2"><i class="fas fa-edit"></i></button>
            <button onclick="confirmDelete()" class="text-red-500"><i class="fas fa-trash-alt"></i></button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</main>
<!-- Category Modal -->
<div id="categoryModal" class="fixed inset-0 hidden bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-full max-w-md">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Manage Categories</h3>
      <button onclick="closeCategoryModal()"><i class="fas fa-times text-gray-600"></i></button>
    </div>
    <div class="flex gap-2 mb-4">
      <input type="text" placeholder="Enter new category name" class="flex-1 border px-3 py-2 rounded" />
      <button class="bg-gray-700 text-white px-4 rounded">+ Add</button>
    </div>
    <ul class="divide-y text-sm">
      <li class="flex justify-between items-center py-2">
        smart watch
        <div class="flex gap-2">
          <i class="fas fa-edit text-gray-500 cursor-pointer"></i>
          <i class="fas fa-trash text-red-500 cursor-pointer"></i>
        </div>
      </li>
      <!-- Repeat other categories -->
    </ul>
  </div>
</div>
<!-- Edit Product Modal -->
<div id="editProductModal" class="fixed inset-0 hidden bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-full max-w-xl overflow-y-auto max-h-screen">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Edit Product</h3>
      <button onclick="closeEditProductModal()"><i class="fas fa-times text-gray-600"></i></button>
    </div>
    <form class="space-y-4">
      <input type="text" placeholder="Product Name" class="w-full border px-4 py-2 rounded" />
      <div class="grid grid-cols-2 gap-4">
        <input type="text" placeholder="SKU" class="border px-4 py-2 rounded" />
        <input type="text" placeholder="Color" class="border px-4 py-2 rounded" />
        <input type="text" placeholder="Minimum Stock Level" class="border px-4 py-2 rounded" />
        <input type="text" placeholder="Category" class="border px-4 py-2 rounded" />
      </div>
      <div>
        <label class="block font-medium mb-1">Sizes & Stock</label>
        <div class="flex flex-wrap gap-2">
          <span class="bg-gray-100 px-2 py-1 rounded text-sm">S:3</span>
          <span class="bg-gray-100 px-2 py-1 rounded text-sm">M:10</span>
          <span class="bg-gray-100 px-2 py-1 rounded text-sm">L:5</span>
          <span class="bg-gray-100 px-2 py-1 rounded text-sm">XL:9</span>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <input type="text" placeholder="Cost Price (৳)" class="border px-4 py-2 rounded" />
        <input type="text" placeholder="Selling Price (৳)" class="border px-4 py-2 rounded" />
      </div>
      <button type="submit" class="bg-black text-white px-6 py-2 rounded w-full">Update Product</button>
    </form>
  </div>
</div>

<script>
  function openCategoryModal() {
    document.getElementById('categoryModal').classList.remove('hidden');
  }

  function closeCategoryModal() {
    document.getElementById('categoryModal').classList.add('hidden');
  }

  function openEditProductModal() {
    document.getElementById('editProductModal').classList.remove('hidden');
  }

  function closeEditProductModal() {
    document.getElementById('editProductModal').classList.add('hidden');
  }

  function confirmDelete() {
    if (confirm("Are you sure you want to delete this item?")) {
      // handle deletion later via AJAX or backend call
    }
  }
</script>
