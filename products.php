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

<main class="min-h-screen p-6 bg-gray-100">

  <!-- Toast container -->
  <div id="toast"
       class="fixed bottom-4 right-4 bg-gray-700 text-white px-4 py-2 rounded-lg shadow-lg hidden z-50">
  </div>

  <!-- Header -->
  <div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-bold text-gray-900">Product Management</h2>
        <p class="text-gray-600 mt-1">Manage your inventory products and categories</p>
      </div>
      <div class="flex flex-wrap gap-3">
        <button onclick="openCategoryModal()"
                class="flex items-center gap-2 px-4 py-2.5 border-2 border-gray-300 text-gray-700 font-medium rounded-md hover:border-gray-400 hover:bg-gray-50 transition-colors">
          <i class="fas fa-tags"></i>
          <span>Manage Categories</span>
        </button>
        <button onclick="openAddProductModal()"
                class="flex items-center gap-2 px-4 py-2.5 bg-gray-700 text-white font-medium rounded-md hover:bg-gray-600 transition-colors">
          <i class="fas fa-plus"></i>
          <span>Add Product</span>
        </button>
      </div>
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
                  placeholder="Search products by name, category..."
                  class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" />
          </div>
        </div>
        <div class="flex gap-3">
          <select id="stockSelect" 
                 class="border-2 border-gray-300 rounded-md px-3 py-2.5 text-gray-700 focus:border-black focus:ring-1 focus:ring-black transition-all appearance-none bg-no-repeat bg-[right_0.5rem_center] pr-8"
                 style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e'); background-size: 1em">
            <option value="">All Stock</option>
            <option value="in_stock">In Stock</option>
            <option value="low_stock">Low Stock</option>
            <option value="out_of_stock">Out of Stock</option>
          </select>
          <select id="categorySelect" 
                 class="border-2 border-gray-300 rounded-md px-3 py-2.5 text-gray-700 focus:border-black focus:ring-1 focus:ring-black transition-all appearance-none bg-no-repeat bg-[right_0.5rem_center] pr-8"
                 style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e'); background-size: 1em">
            <option value="">All Categories</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Product Table -->
  <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-left">Product</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-center">Category</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-left">Size & Stock</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-center">Total Stock</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-center">Barcode</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-center">Cost Price (৳)</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-center">Selling Price (৳)</th>
            <th class="px-6 py-4 bg-gray-700 text-white font-semibold text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="product-list" class="divide-y divide-gray-200">
          <!-- Loading placeholder -->
          <tr>
            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
              <div class="flex flex-col items-center">
                <svg class="w-12 h-12 text-gray-300 animate-spin mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" stroke="none" fill="currentColor"></path>
                </svg>
                <p class="text-lg">Loading product data...</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Category Modal -->
<div id="categoryModal"
     class="fixed inset-0 hidden bg-gray-700 bg-opacity-75 flex items-center justify-center z-50 overflow-y-auto">
  <div class="bg-white rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-auto m-4 shadow-xl">
    <div class="flex justify-between items-center mb-6 pb-3 border-b border-gray-200">
      <h3 class="text-2xl font-bold text-gray-900">Manage Categories</h3>
      <button onclick="closeCategoryModal()" class="text-gray-400 hover:text-black transition-colors">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    <div class="flex gap-2 mb-4">
      <div class="relative flex-1">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
          <i class="fas fa-tag text-gray-400"></i>
        </div>
        <input type="text" id="newCategoryInput"
               placeholder="Enter new category name"
               class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" />
      </div>
      <button id="addCategoryBtn"
              class="bg-gray-700 text-white px-4 py-2.5 rounded-md hover:bg-gray-600 transition-colors font-medium">
        + Add
      </button>
    </div>
    <ul id="categoryList" class="divide-y divide-gray-200 text-sm"></ul>
  </div>
</div>

<!-- Add Product Modal -->
<div id="addProductModal"
     class="fixed inset-0 hidden bg-gray-700 bg-opacity-75 flex items-center justify-center z-50 overflow-y-auto">
  <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-auto m-4 shadow-xl">
    <div class="flex justify-between items-center mb-6 pb-3 border-b border-gray-200">
      <div>
        <h3 class="text-2xl font-bold text-gray-900">Add Product</h3>
        <p class="text-gray-600 text-sm mt-1">Add a new product to your inventory</p>
      </div>
      <button onclick="closeAddProductModal()" class="text-gray-400 hover:text-black transition-colors">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    <form id="addProductForm" class="space-y-5">
      <input type="hidden" name="id" />
      <input type="hidden" name="barcode" />

      <!-- Name & Category -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700">Product Name</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <i class="fas fa-box text-gray-400"></i>
            </div>
            <input type="text" name="name"
                  class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all"
                  required />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700">Category</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <i class="fas fa-tags text-gray-400"></i>
            </div>
            <select name="category_id"
                  class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all appearance-none bg-no-repeat bg-[right_0.5rem_center] pr-8"
                  style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e'); background-size: 1em"
                  required></select>
          </div>
        </div>
      </div>

      <!-- Description & Image -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700">Description</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <i class="fas fa-align-left text-gray-400"></i>
            </div>
            <input type="text" name="description"
                  placeholder="Enter product description"
                  class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700">Product Image</label>
          <div class="flex items-center gap-2">
            <input type="file" name="product_image" 
                  accept="image/*" 
                  class="text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:bg-gray-700 file:text-white hover:file:bg-gray-600" />
          </div>
          <div id="image-preview-add" class="mt-2 h-20 flex items-center justify-center border-2 border-dashed border-gray-300 rounded-md">
            <span class="text-xs text-gray-500">No image selected</span>
          </div>
        </div>
      </div>

      <!-- Sizes & Stock -->
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Sizes & Stock</label>
        <div class="flex gap-2 mb-2">
          <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <i class="fas fa-ruler text-gray-400"></i>
            </div>
            <input type="text" id="newSizeInput"
                  placeholder="Enter size (e.g., S, M, L, XL)"
                  class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" />
          </div>
          <button type="button" id="addSizeBtn"
                 class="bg-gray-700 text-white px-4 py-2.5 rounded-md hover:bg-gray-600 transition-colors font-medium flex items-center gap-1">
            <i class="fas fa-plus"></i>
            <span>Add Size</span>
          </button>
        </div>
        <ul id="sizeList"
           class="space-y-2 max-h-40 overflow-y-auto p-2 border-2 border-gray-200 rounded-md"></ul>
        <div class="text-sm font-medium mt-2 flex items-center">
          <i class="fas fa-cubes text-gray-500 mr-2"></i>
          Total <span id="totalUnits" class="mx-1 font-bold">0</span> units
        </div>
      </div>
      <input type="hidden" name="stock" id="stockInput" />

      <!-- Initial Batch -->
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Initial Batch</label>
        <div class="space-y-4 p-4 border-2 border-gray-200 rounded-md">
          <div>
            <label class="block text-sm mb-1 text-gray-700">Size</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i class="fas fa-tag text-gray-400"></i>
              </div>
              <select id="initialBatchSize" name="initial_batch_size" 
                     class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all appearance-none bg-no-repeat bg-[right_0.5rem_center] pr-8"
                     style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e'); background-size: 1em">
                <option value="">Select a size</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm mb-1 text-gray-700">Batch Number</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i class="fas fa-hashtag text-gray-400"></i>
              </div>
              <input type="text" name="batch_number"
                    placeholder="e.g., BATCH-2025-001"
                    class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" />
            </div>
          </div>
          <div>
            <label class="block text-sm mb-1 text-gray-700">Manufactured Date</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i class="fas fa-calendar-alt text-gray-400"></i>
              </div>
              <input type="date" name="manufactured_date"
                    class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" />
            </div>
          </div>
        </div>
      </div>

      <!-- Location -->
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Location</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-map-marker-alt text-gray-400"></i>
          </div>
          <input type="text" name="location"
                placeholder="Enter location (e.g., Shelf A, Side B)"
                class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" />
        </div>
      </div>

      <!-- Pricing -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700">Cost Price (৳)</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <i class="fas fa-tags text-gray-400"></i>
            </div>
            <input type="number" step="0.01" min="0" name="price"
                  placeholder="0.00"
                  class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" required />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700">Selling Price (৳)</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <i class="fas fa-tag text-gray-400"></i>
            </div>
            <input type="number" step="0.01" min="0" name="selling_price"
                  placeholder="0.00"
                  class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" required />
          </div>
        </div>
      </div>

      <!-- Minimum Stock -->
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Minimum Stock Level</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-exclamation-triangle text-gray-400"></i>
          </div>
          <input type="number" name="min_stock"
                min="0" value="5"
                class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" />
          <p class="text-xs text-gray-500 mt-1 ml-1">Alert will be triggered when stock falls below this value</p>
        </div>
      </div>

      <div class="pt-5 mt-3 border-t border-gray-200">
        <button type="submit"
                class="w-full bg-gray-700 text-white py-2.5 rounded-md hover:bg-gray-600 transition-colors font-medium flex items-center justify-center gap-2">
          <i class="fas fa-save"></i>
          <span>Save Product</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Product Modal -->
<div id="editProductModal"
     class="fixed inset-0 hidden bg-gray-700 bg-opacity-75 flex items-center justify-center z-50 overflow-y-auto">
  <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-auto m-4 shadow-xl">
    <div class="flex justify-between items-center mb-6 pb-3 border-b border-gray-200">
      <div>
        <h3 class="text-2xl font-bold text-gray-900">Edit Product</h3>
        <p class="text-gray-600 text-sm mt-1">Update product information</p>
      </div>
      <button onclick="closeEditProductModal()" class="text-gray-400 hover:text-black transition-colors">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    <form id="editProductForm" class="space-y-5">
      <input type="hidden" name="id" />
      <input type="hidden" name="barcode" />
      <input type="hidden" name="current_image" />

      <!-- Name & Category -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700">Product Name</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <i class="fas fa-box text-gray-400"></i>
            </div>
            <input type="text" name="name"
                  class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all"
                  required />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700">Category</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <i class="fas fa-tags text-gray-400"></i>
            </div>
            <select name="category_id"
                  class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all appearance-none bg-no-repeat bg-[right_0.5rem_center] pr-8"
                  style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e'); background-size: 1em"
                  required></select>
          </div>
        </div>
      </div>

      <!-- Description & Image -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700">Description</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <i class="fas fa-align-left text-gray-400"></i>
            </div>
            <input type="text" name="description"
                  placeholder="Enter product description"
                  class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700">Product Image</label>
          <div class="flex items-center gap-2">
            <input type="file" name="product_image" 
                  accept="image/*" 
                  class="text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:bg-gray-700 file:text-white hover:file:bg-gray-600" />
          </div>
          <div id="image-preview-edit" class="mt-2 h-20 flex items-center justify-center border-2 border-dashed border-gray-300 rounded-md">
            <span class="text-xs text-gray-500">No image selected</span>
          </div>
        </div>
      </div>

      <!-- Sizes & Stock -->
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Sizes & Stock</label>
        <div class="flex gap-2 mb-2">
          <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <i class="fas fa-ruler text-gray-400"></i>
            </div>
            <input type="text" id="editSizeInput"
                  placeholder="Enter size (e.g., S, M, L, XL)"
                  class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" />
          </div>
          <button type="button" id="addEditSizeBtn"
                 class="bg-gray-700 text-white px-4 py-2.5 rounded-md hover:bg-gray-600 transition-colors font-medium flex items-center gap-1">
            <i class="fas fa-plus"></i>
            <span>Add Size</span>
          </button>
        </div>
        <ul id="editSizeList"
           class="space-y-2 max-h-40 overflow-y-auto p-2 border-2 border-gray-200 rounded-md"></ul>
        <div class="text-sm font-medium mt-2 flex items-center">
          <i class="fas fa-cubes text-gray-500 mr-2"></i>
          Total <span id="editTotalUnits" class="mx-1 font-bold">0</span> units
        </div>
      </div>
      <input type="hidden" name="stock" id="editStockInput" />

      <!-- Location -->
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Location</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-map-marker-alt text-gray-400"></i>
          </div>
          <input type="text" name="location"
                placeholder="Enter location (e.g., Shelf A, Side B)"
                class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" />
        </div>
      </div>

      <!-- Pricing -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700">Cost Price (৳)</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <i class="fas fa-tags text-gray-400"></i>
            </div>
            <input type="number" step="0.01" min="0" name="price"
                  placeholder="0.00"
                  class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" required />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium mb-2 text-gray-700">Selling Price (৳)</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <i class="fas fa-tag text-gray-400"></i>
            </div>
            <input type="number" step="0.01" min="0" name="selling_price"
                  placeholder="0.00"
                  class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" required />
          </div>
        </div>
      </div>

      <!-- Minimum Stock -->
      <div>
        <label class="block text-sm font-medium mb-2 text-gray-700">Minimum Stock Level</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fas fa-exclamation-triangle text-gray-400"></i>
          </div>
          <input type="number" name="min_stock"
                min="0" value="5"
                class="pl-10 w-full border-2 border-gray-300 rounded-md py-2.5 px-4 focus:border-black focus:ring-1 focus:ring-black transition-all" />
          <p class="text-xs text-gray-500 mt-1 ml-1">Alert will be triggered when stock falls below this value</p>
        </div>
      </div>

      <div class="pt-5 mt-3 border-t border-gray-200 flex justify-between">
        <button type="button" onclick="confirmDeleteProduct(editProductForm.id.value)" 
                class="px-4 py-2.5 border-2 border-red-300 text-red-600 rounded-md hover:bg-red-50 transition-colors flex items-center gap-2">
          <i class="fas fa-trash-alt"></i>
          <span>Delete</span>
        </button>
        <button type="submit"
                class="px-6 py-2.5 bg-gray-700 text-white rounded-md hover:bg-gray-600 transition-colors font-medium flex items-center gap-2">
          <i class="fas fa-save"></i>
          <span>Save Changes</span>
        </button>
      </div>
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

// Expose for onclick handlers
window.openCategoryModal     = () => document.getElementById('categoryModal').classList.remove('hidden');
window.closeCategoryModal    = () => document.getElementById('categoryModal').classList.add('hidden');
window.openAddProductModal   = () => {
    // Reset state for adding a new product
    addSizes = [];
    addProductForm.reset();
    renderAddSizes();
    updateInitialBatchSizes();
    document.getElementById('addProductModal').classList.remove('hidden');
};
window.closeAddProductModal  = () => {
    document.getElementById('addProductModal').classList.add('hidden');
    // Reset state when closing the modal
    addSizes = [];
    addProductForm.reset();
    renderAddSizes();
};
window.openEditProductModal  = () => {
    document.getElementById('editProductModal').classList.remove('hidden');
};
window.closeEditProductModal = () => {
    document.getElementById('editProductModal').classList.add('hidden');
    // Reset state when closing the modal
    editSizes = [];
    editProductForm.reset();
    renderEditSizes();
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
  addProductForm    = document.getElementById('addProductForm'),
  barcodeModalImg   = document.getElementById('barcodeModalImg'),
  initialBatchSizeSelect = document.getElementById('initialBatchSize'),
  editSizeInput     = document.getElementById('editSizeInput'),
  editAddSizeBtn    = document.getElementById('addEditSizeBtn'),
  editSizeList      = document.getElementById('editSizeList'),
  editTotalUnitsSpan = document.getElementById('editTotalUnits'),
  editStockInput    = document.getElementById('editStockInput'),
  editProductForm   = document.getElementById('editProductForm');

let addSizes = [], editSizes = [], editingProductId = null, editingCategoryId = null;

// Image preview handlers
document.querySelector('input[name="product_image"]').addEventListener('change', function(e) {
  handleImagePreview(e, 'image-preview-add');
});

document.querySelector('#editProductForm input[name="product_image"]').addEventListener('change', function(e) {
  handleImagePreview(e, 'image-preview-edit');
});

function handleImagePreview(event, previewId) {
  const preview = document.getElementById(previewId);
  preview.innerHTML = '';
  
  if (event.target.files && event.target.files[0]) {
    const reader = new FileReader();
    
    reader.onload = function(e) {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.className = 'h-full object-contain';
      preview.appendChild(img);
    }
    
    reader.readAsDataURL(event.target.files[0]);
  } else {
    preview.innerHTML = '<span class="text-xs text-gray-500">No image selected</span>';
  }
}

// Show toast
function showToast(msg, success = true) {
  toast.textContent = msg;
  toast.classList.toggle('bg-green-500', success);
  toast.classList.toggle('bg-red-500', !success);
  toast.classList.remove('hidden');
  setTimeout(() => toast.classList.add('hidden'), 3000);
}

// Update initial batch size dropdown (Add Product Modal)
function updateInitialBatchSizes() {
    initialBatchSizeSelect.innerHTML = addSizes.length > 0
        ? addSizes.map(s => `<option value="${s.size}">${s.size} (${s.stock} units)</option>`).join('')
        : '<option value="">No sizes available</option>';
}

// Initial load
document.addEventListener('DOMContentLoaded', async () => {
  await loadCategories();
  fetchProducts();
});

// Re-fetch on filter change
[searchInput, stockSelect, categorySelect].forEach(el =>
  el.addEventListener('input', fetchProducts)
);

// Fetch & render products
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
      
      // Product image display
      const imageHtml = p.image ? 
        `<img src="./${p.image}" alt="${p.name}" class="h-10 w-10 object-cover rounded">` :
        `<div class="h-10 w-10 bg-gray-200 rounded flex items-center justify-center">
           <i class="fas fa-image text-gray-400"></i>
         </div>`;

      return `
        <tr class="border-t hover:bg-gray-50">
          <td class="px-4 py-3">
            <div class="flex items-center gap-2">
              ${imageHtml}
              <span class="font-semibold">${p.name}</span>
            </div>
          </td>
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

    // Attach click handlers to barcode thumbnails
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

// Delete product
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

// Start edit product
window.startEditProduct = async id => {
  // Reset state
  editSizes = [];
  editProductForm.reset();
  editingProductId = id;

  try {
    const ps = await apiGet(`./api/products.php`);
    const p = ps.find(x => x.id === id);
    if (!p) {
      showToast('Product not found', false);
      return;
    }
    
    // Set form values
    editProductForm.id.value = p.id;
    editProductForm.name.value = p.name;
    editProductForm.description.value = p.description || '';
    editProductForm.location.value = p.location || '';
    editProductForm.min_stock.value = p.min_stock;
    editProductForm.price.value = p.price;
    editProductForm.selling_price.value = p.selling_price;
    editProductForm.category_id.value = p.category_id;
    
    // Handle image preview
    const imagePreview = document.getElementById('image-preview-edit');
    if (p.image) {
      imagePreview.innerHTML = `<img src="./${p.image}" class="h-full object-contain" alt="${p.name}">`;
      editProductForm.querySelector('input[name="current_image"]').value = p.image;
    } else {
      imagePreview.innerHTML = '<span class="text-xs text-gray-500">No image</span>';
      editProductForm.querySelector('input[name="current_image"]').value = '';
    }
    
    // Store sizes
    editSizes = p.sizes.map(s => ({ size: s.size_name, stock: +s.stock }));
    renderEditSizes();
    openEditProductModal();
  } catch (err) {
    console.error(err);
    showToast(`Could not load product data: ${err.message}`, false);
  }
};

// Add Product Modal: Add a size
addSizeBtn.onclick = () => {
  const sz = newSizeInput.value.trim();
  if (!sz || addSizes.some(x => x.size === sz)) {
    showToast('Please enter a unique size', false);
    return;
  }
  addSizes.push({ size: sz, stock: 0 });
  newSizeInput.value = '';
  renderAddSizes();
  updateInitialBatchSizes();
};

// Add Product Modal: Render sizes
function renderAddSizes() {
  sizeList.innerHTML = addSizes.map((s, i) => `
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
      const value = +e.target.value;
      if (value < 0) {
        e.target.value = 0;
        showToast('Stock cannot be negative', false);
      }
      addSizes[+e.target.dataset.idx].stock = Math.max(0, value || 0);
      updateAddTotal();
      updateInitialBatchSizes();
    };
  });
  sizeList.querySelectorAll('.removeSize').forEach(btn => {
    btn.onclick = () => {
      addSizes.splice(+btn.dataset.idx, 1);
      renderAddSizes();
      updateInitialBatchSizes();
    };
  });
  updateAddTotal();
}

// Add Product Modal: Update total units
function updateAddTotal() {
  const total = addSizes.reduce((sum, s) => sum + (s.stock || 0), 0);
  totalUnitsSpan.textContent = total;
  stockInput.value = total;
}

// Edit Product Modal: Add a size
editAddSizeBtn.onclick = () => {
  const sz = editSizeInput.value.trim();
  if (!sz || editSizes.some(x => x.size === sz)) {
    showToast('Please enter a unique size', false);
    return;
  }
  editSizes.push({ size: sz, stock: 0 });
  editSizeInput.value = '';
  renderEditSizes();
};

// Edit Product Modal: Render sizes
function renderEditSizes() {
  editSizeList.innerHTML = editSizes.map((s, i) => `
    <li class="flex items-center gap-2">
      <span class="bg-gray-100 px-2 py-1 rounded text-sm">${s.size}</span>
      <input type="number" min="0" value="${s.stock}"
             data-idx="${i}"
             class="border px-2 py-1 rounded w-16 sizeStock"/>
      <span>units</span>
      <button data-idx="${i}"
              class="ml-auto text-red-500 removeSize">×</button>
    </li>`).join('');

  editSizeList.querySelectorAll('.sizeStock').forEach(inp => {
    inp.oninput = e => {
      const value = +e.target.value;
      if (value < 0) {
        e.target.value = 0;
        showToast('Stock cannot be negative', false);
      }
      editSizes[+e.target.dataset.idx].stock = Math.max(0, value || 0);
      updateEditTotal();
    };
  });
  editSizeList.querySelectorAll('.removeSize').forEach(btn => {
    btn.onclick = () => {
      editSizes.splice(+btn.dataset.idx, 1);
      renderEditSizes();
    };
  });
  updateEditTotal();
}

// Edit Product Modal: Update total units
function updateEditTotal() {
  const total = editSizes.reduce((sum, s) => sum + (s.stock || 0), 0);
  editTotalUnitsSpan.textContent = total;
  editStockInput.value = total;
}

// Add Product Modal: Save new product
addProductForm.onsubmit = async e => {
  e.preventDefault();
  
  // Create FormData object to handle file uploads
  const formData = new FormData(addProductForm);

  if (addSizes.length === 0) {
    showToast('Please add at least one size', false);
    return;
  }
  const totalStock = addSizes.reduce((sum, s) => sum + (s.stock || 0), 0);
  if (totalStock <= 0) {
    showToast('Total stock must be greater than 0', false);
    return;
  }

  if (!formData.get('initial_batch_size') || !formData.get('batch_number') || !formData.get('manufactured_date')) {
    showToast('Initial batch size, batch number, and manufactured date are required', false);
    return;
  }

  // Validate manufactured date
  const currentDate = new Date('2025-04-22');
  const manufacturedDate = new Date(formData.get('manufactured_date'));
  if (manufacturedDate > currentDate) {
    showToast('Manufactured date cannot be in the future', false);
    return;
  }

  const selectedSize = addSizes.find(s => s.size === formData.get('initial_batch_size'));
  if (!selectedSize) {
    showToast('Selected initial batch size not found in sizes list', false);
    return;
  }

  // Add sizes to FormData
  formData.append('sizes_json', JSON.stringify(addSizes));
  formData.append('initial_batch_stock', addSizes.find(s => s.size === formData.get('initial_batch_size')).stock);

  try {
    const res = await fetch('./api/products.php', {
      method: 'POST',
      body: formData
    }).then(response => response.json());
    
    if (res.success) {
      showToast('Product added!');
      closeAddProductModal();
      fetchProducts();
    } else {
      showToast(res.message || 'Save failed', false);
    }
  } catch (err) {
    console.error(err);
    showToast(`Save error: ${err.message}`, false);
  }
};

// Edit Product Modal: Update existing product
editProductForm.onsubmit = async e => {
  e.preventDefault();
  
  // Create FormData object to handle file uploads
  const formData = new FormData(editProductForm);
  formData.append('action', 'update');
  formData.append('id', editingProductId);

  if (editSizes.length === 0) {
    showToast('Please add at least one size', false);
    return;
  }
  const totalStock = editSizes.reduce((sum, s) => sum + (s.stock || 0), 0);
  if (totalStock <= 0) {
    showToast('Total stock must be greater than 0', false);
    return;
  }

  // Add sizes to FormData
  formData.append('sizes_json', JSON.stringify(editSizes));

  console.log('Sending payload for edit:', Object.fromEntries(formData));

  try {
    const res = await fetch('./api/products.php', {
      method: 'POST',
      body: formData
    }).then(response => response.json());
    
    if (res.success) {
      showToast('Product updated!');
      closeEditProductModal();
      fetchProducts();
    } else {
      showToast(res.message || 'Update failed', false);
    }
  } catch (err) {
    console.error(err);
    showToast(`Update error: ${err.message}`, false);
  }
};

// Load categories
async function loadCategories() {
  try {
    const cats = await apiGet('./api/categories.php');
    categorySelect.innerHTML =
      '<option>All Categories</option>' +
      cats.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
    addProductForm.category_id.innerHTML =
      cats.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
    editProductForm.category_id.innerHTML =
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

// Start editing a category
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

// Confirm editing a category
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

// Cancel editing a category
window.cancelEditCategory = id => {
  const li = document.querySelector(`li[data-category-id="${id}"]`);
  li.querySelector('.category-name').classList.remove('hidden');
  li.querySelector('.category-edit-input').classList.add('hidden');
  li.querySelector('.edit-btn').classList.remove('hidden');
  li.querySelector('.confirm-btn').classList.add('hidden');
};

// Add category
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

// Delete category
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