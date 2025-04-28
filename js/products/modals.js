// Modal functionality for product management
import { loadCategories } from './categories.js';
import { showToast } from './utils.js';

// DOM Elements
let imagePreviewAdd, imagePreviewEdit, sizeList, initialBatchSize;

// Initialize modal functionality
export function initModals() {
  // Get DOM references
  imagePreviewAdd = document.getElementById('image-preview-add');
  imagePreviewEdit = document.getElementById('image-preview-edit');
  sizeList = document.getElementById('sizeList');
  initialBatchSize = document.getElementById('initialBatchSize');
  
  // Register global modal functions if they don't exist
  if (!window.openCategoryModal) {
    window.openCategoryModal = openCategoryModal;
  }
  
  if (!window.closeCategoryModal) {
    window.closeCategoryModal = closeCategoryModal;
  }
  
  if (!window.openAddProductModal) {
    window.openAddProductModal = openAddProductModal;
  }
  
  if (!window.closeAddProductModal) {
    window.closeAddProductModal = closeAddProductModal;
  }
  
  if (!window.openEditProductModal) {
    window.openEditProductModal = openEditProductModal;
  }
  
  if (!window.closeEditProductModal) {
    window.closeEditProductModal = closeEditProductModal;
  }
  
  // Setup file input previews
  setupFileInputPreviews();
}

// Category modal management
export function openCategoryModal() {
  document.getElementById('categoryModal').classList.remove('hidden');
  loadCategories();
}

export function closeCategoryModal() {
  document.getElementById('categoryModal').classList.add('hidden');
}

// Add product modal management
export function openAddProductModal() {
  const addProductForm = document.getElementById('addProductForm');
  addProductForm.reset();
  
  // Reset fields
  if (sizeList) sizeList.innerHTML = '';
  if (window.updateTotalUnits) window.updateTotalUnits();
  
  if (initialBatchSize) {
    initialBatchSize.innerHTML = '<option value="">Select a size</option>';
  }
  
  if (imagePreviewAdd) {
    imagePreviewAdd.innerHTML = '<span class="text-xs text-gray-500">No image selected</span>';
  }
  
  document.getElementById('addProductModal').classList.remove('hidden');
  loadCategories(true);
}

export function closeAddProductModal() {
  document.getElementById('addProductModal').classList.add('hidden');
}

// Edit product modal management
export function openEditProductModal(product) {
  populateEditForm(product);
  document.getElementById('editProductModal').classList.remove('hidden');
}

export function closeEditProductModal() {
  document.getElementById('editProductModal').classList.add('hidden');
}

// Populate edit form with product data
export function populateEditForm(product) {
  const editProductForm = document.getElementById('editProductForm');
  const editSizeList = document.getElementById('editSizeList');
  
  // Reset form
  editProductForm.reset();
  editSizeList.innerHTML = '';
  
  // Set basic product data
  editProductForm.id.value = product.id;
  editProductForm.name.value = product.name;
  editProductForm.category_id.value = product.category_id;
  editProductForm.description.value = product.description || '';
  editProductForm.location.value = product.location || '';
  editProductForm.price.value = product.price;
  editProductForm.selling_price.value = product.selling_price;
  editProductForm.min_stock.value = product.min_stock;
  editProductForm.current_image.value = product.image || '';
  
  // Set image preview
  if (product.image) {
    imagePreviewEdit.innerHTML = `<img src="${product.image}" class="h-full object-contain" />`;
  } else {
    imagePreviewEdit.innerHTML = '<span class="text-xs text-gray-500">No image selected</span>';
  }
  
  // Set sizes
  if (product.sizes && product.sizes.length) {
    product.sizes.forEach(size => {
      const li = document.createElement('li');
      li.dataset.size = size.size_name;
      li.dataset.stock = size.stock;
      li.className = 'flex items-center justify-between p-2 bg-gray-50 rounded border border-gray-200';
      li.innerHTML = `
        <div class="flex items-center">
          <i class="fas fa-tag text-gray-500 mr-2"></i>
          <span class="font-medium">${size.size_name}</span>
        </div>
        <div class="flex items-center gap-2">
          <input type="number" min="0" value="${size.stock}" class="size-stock-input border rounded w-20 py-1 px-2 text-right"
                 onchange="updateEditTotalUnits()" />
          <button type="button" class="text-red-500 hover:text-red-700" onclick="removeEditSize(this.parentNode.parentNode)">
            <i class="fas fa-times"></i>
          </button>
        </div>
      `;
      
      editSizeList.appendChild(li);
    });
    
    if (window.updateEditTotalUnits) {
      window.updateEditTotalUnits();
    }
  }
  
  // Make sure categories are loaded
  loadCategories(true);
}

// Setup image preview functionality for file inputs
function setupFileInputPreviews() {
  const addProductForm = document.getElementById('addProductForm');
  const editProductForm = document.getElementById('editProductForm');
  
  // File input preview for add modal
  if (addProductForm && addProductForm.product_image) {
    addProductForm.product_image.addEventListener('change', function() {
      previewImage(this, imagePreviewAdd);
    });
  }
  
  // File input preview for edit modal
  if (editProductForm && editProductForm.product_image) {
    editProductForm.product_image.addEventListener('change', function() {
      previewImage(this, imagePreviewEdit);
    });
  }
}

// Preview uploaded image
export function previewImage(input, previewElement) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      previewElement.innerHTML = `<img src="${e.target.result}" class="h-full object-contain" />`;
    };
    reader.readAsDataURL(input.files[0]);
  } else {
    previewElement.innerHTML = '<span class="text-xs text-gray-500">No image selected</span>';
  }
} 