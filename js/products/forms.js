// Product form functionality
import { apiPost } from '../ajax.js';
import { showToast } from './utils.js';
import { loadProducts } from './list.js';
import { resetPagination } from './pagination.js';

// DOM Elements
let addProductForm, editProductForm, 
    newSizeInput, addSizeBtn, sizeList, totalUnits, stockInput,
    editSizeInput, addEditSizeBtn, editSizeList, editTotalUnits, editStockInput;

// Initialize product form functionality
export function initProductForms() {
  // Get DOM references
  addProductForm = document.getElementById('addProductForm');
  editProductForm = document.getElementById('editProductForm');
  newSizeInput = document.getElementById('newSizeInput');
  addSizeBtn = document.getElementById('addSizeBtn');
  sizeList = document.getElementById('sizeList');
  totalUnits = document.getElementById('totalUnits');
  stockInput = document.getElementById('stockInput');
  editSizeInput = document.getElementById('editSizeInput');
  addEditSizeBtn = document.getElementById('addEditSizeBtn');
  editSizeList = document.getElementById('editSizeList');
  editTotalUnits = document.getElementById('editTotalUnits');
  editStockInput = document.getElementById('editStockInput');
  
  // Add event listeners
  if (addSizeBtn) {
    addSizeBtn.addEventListener('click', addSize);
  }
  
  if (addEditSizeBtn) {
    addEditSizeBtn.addEventListener('click', addEditSize);
  }
  
  if (addProductForm) {
    addProductForm.addEventListener('submit', handleAddProductSubmit);
  }
  
  if (editProductForm) {
    editProductForm.addEventListener('submit', handleEditProductSubmit);
  }
  
  // Register global size management functions if they don't exist
  if (!window.removeSize) {
    window.removeSize = removeSize;
  }
  
  if (!window.removeEditSize) {
    window.removeEditSize = removeEditSize;
  }
  
  if (!window.updateTotalUnits) {
    window.updateTotalUnits = updateTotalUnits;
  }
  
  if (!window.updateEditTotalUnits) {
    window.updateEditTotalUnits = updateEditTotalUnits;
  }
  
  if (!window.updateInitialBatchSizes) {
    window.updateInitialBatchSizes = updateInitialBatchSizes;
  }
}

// Add size to product (add form)
export function addSize() {
  const sizeName = newSizeInput.value.trim();
  if (!sizeName) return;
  
  // Check if size already exists
  const existingSize = Array.from(sizeList.children).find(li => 
    li.dataset.size.toLowerCase() === sizeName.toLowerCase()
  );
  
  if (existingSize) {
    // Focus the stock input of the existing size
    existingSize.querySelector('input[type="number"]').focus();
    return;
  }
  
  // Add new size
  const li = document.createElement('li');
  li.dataset.size = sizeName;
  li.dataset.stock = 0;
  li.className = 'flex items-center justify-between p-2 bg-gray-50 rounded border border-gray-200';
  li.innerHTML = `
    <div class="flex items-center">
      <i class="fas fa-tag text-gray-500 mr-2"></i>
      <span class="font-medium">${sizeName}</span>
    </div>
    <div class="flex items-center gap-2">
      <input type="number" min="0" value="0" class="size-stock-input border rounded w-20 py-1 px-2 text-right"
             onchange="updateTotalUnits()" />
      <button type="button" class="text-red-500 hover:text-red-700" onclick="removeSize(this.parentNode.parentNode)">
        <i class="fas fa-times"></i>
      </button>
    </div>
  `;
  
  sizeList.appendChild(li);
  newSizeInput.value = '';
  newSizeInput.focus();
  updateTotalUnits();
  updateInitialBatchSizes();
}

// Add size to product (edit form)
export function addEditSize() {
  const sizeName = editSizeInput.value.trim();
  if (!sizeName) return;
  
  // Check if size already exists
  const existingSize = Array.from(editSizeList.children).find(li => 
    li.dataset.size.toLowerCase() === sizeName.toLowerCase()
  );
  
  if (existingSize) {
    // Focus the stock input of the existing size
    existingSize.querySelector('input[type="number"]').focus();
    return;
  }
  
  // Add new size
  const li = document.createElement('li');
  li.dataset.size = sizeName;
  li.dataset.stock = 0;
  li.className = 'flex items-center justify-between p-2 bg-gray-50 rounded border border-gray-200';
  li.innerHTML = `
    <div class="flex items-center">
      <i class="fas fa-tag text-gray-500 mr-2"></i>
      <span class="font-medium">${sizeName}</span>
    </div>
    <div class="flex items-center gap-2">
      <input type="number" min="0" value="0" class="size-stock-input border rounded w-20 py-1 px-2 text-right"
             onchange="updateEditTotalUnits()" />
      <button type="button" class="text-red-500 hover:text-red-700" onclick="removeEditSize(this.parentNode.parentNode)">
        <i class="fas fa-times"></i>
      </button>
    </div>
  `;
  
  editSizeList.appendChild(li);
  editSizeInput.value = '';
  editSizeInput.focus();
  updateEditTotalUnits();
}

// Remove size from product (add form)
export function removeSize(li) {
  li.remove();
  updateTotalUnits();
  updateInitialBatchSizes();
}

// Remove size from product (edit form)
export function removeEditSize(li) {
  li.remove();
  updateEditTotalUnits();
}

// Update total units display (add form)
export function updateTotalUnits() {
  const sizes = Array.from(sizeList.children);
  let total = 0;
  
  const sizesData = sizes.map(li => {
    const stockInput = li.querySelector('.size-stock-input');
    const stock = parseInt(stockInput.value) || 0;
    total += stock;
    return {
      size: li.dataset.size,
      stock
    };
  });
  
  totalUnits.textContent = total;
  stockInput.value = total;
  
  // Update hidden input with JSON data
  if (addProductForm) {
    addProductForm.sizes_json = JSON.stringify(sizesData);
  }
  
  return sizesData;
}

// Update total units display (edit form)
export function updateEditTotalUnits() {
  const sizes = Array.from(editSizeList.children);
  let total = 0;
  
  const sizesData = sizes.map(li => {
    const stockInput = li.querySelector('.size-stock-input');
    const stock = parseInt(stockInput.value) || 0;
    total += stock;
    return {
      size: li.dataset.size,
      stock
    };
  });
  
  editTotalUnits.textContent = total;
  editStockInput.value = total;
  
  // Update hidden input with JSON data
  if (editProductForm) {
    editProductForm.sizes_json = JSON.stringify(sizesData);
  }
  
  return sizesData;
}

// Update initial batch size options based on sizes added
export function updateInitialBatchSizes() {
  const sizes = updateTotalUnits();
  const initialBatchSize = document.getElementById('initialBatchSize');
  
  if (initialBatchSize) {
    initialBatchSize.innerHTML = '<option value="">Select a size</option>' +
      sizes.map(s => `<option value="${s.size}">${s.size}</option>`).join('');
  }
}

// Add product form submission
export async function handleAddProductSubmit(e) {
  e.preventDefault();
  
  const formData = new FormData(addProductForm);
  
  // Add sizes JSON data
  const sizesData = updateTotalUnits();
  formData.append('sizes_json', JSON.stringify(sizesData));
  
  // Make sure we have at least one size
  if (sizesData.length === 0) {
    showToast('Please add at least one size', false);
    return;
  }
  
  // Validate batch information
  const initialBatchSize = formData.get('initial_batch_size');
  const batchNumber = formData.get('batch_number');
  const manufacturedDate = formData.get('manufactured_date');
  
  if (!initialBatchSize || !batchNumber || !manufacturedDate) {
    showToast('Initial batch size, batch number, and manufactured date are required', false);
    return;
  }
  
  // Get the initial batch size and its corresponding stock
  const sizeData = sizesData.find(s => s.size === initialBatchSize);
  if (sizeData) {
    formData.append('initial_batch_stock', sizeData.stock);
  } else {
    showToast('Selected batch size not found in size list', false);
    return;
  }
  
  // Validate manufactured date is not in the future
  const currentDate = new Date();
  const selectedDate = new Date(manufacturedDate);
  if (selectedDate > currentDate) {
    showToast('Manufactured date cannot be in the future', false);
    return;
  }
  
  try {
    // Show loading state in button
    const submitBtn = addProductForm.querySelector('button[type="submit"]');
    const originalBtnContent = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin"></i> Saving...';
    
    const response = await fetch('api/products.php', {
      method: 'POST',
      body: formData
    });
    
    let result;
    try {
      result = await response.json();
    } catch (err) {
      console.error('Failed to parse response:', err);
      throw new Error('Invalid response from server');
    }
    
    if (result.success) {
      showToast('Product added successfully');
      if (window.closeAddProductModal) {
        window.closeAddProductModal();
      }
      loadProducts();
      resetPagination();
    } else {
      showToast(result.message || 'Failed to add product', false);
    }
    
    // Restore button state
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalBtnContent;
  } catch (err) {
    console.error('Error adding product:', err);
    showToast('Failed to add product: ' + (err.message || 'Unknown error'), false);
    
    // Restore button state if there was an error
    const submitBtn = addProductForm.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="fas fa-save"></i> <span>Save Product</span>';
    }
  }
}

// Edit product form submission
export async function handleEditProductSubmit(e) {
  e.preventDefault();
  
  const formData = new FormData(editProductForm);
  
  // Add sizes JSON data
  const sizesData = updateEditTotalUnits();
  formData.append('sizes_json', JSON.stringify(sizesData));
  
  // Make sure we have at least one size
  if (sizesData.length === 0) {
    showToast('Please add at least one size', false);
    return;
  }
  
  try {
    // Show loading state in button
    const submitBtn = editProductForm.querySelector('button[type="submit"]');
    const originalBtnContent = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin"></i> Saving...';
    
    formData.append('action', 'update');
    
    const result = await fetch('api/products.php', {
      method: 'POST',
      body: formData
    }).then(res => res.json());
    
    if (result.success) {
      showToast('Product updated successfully');
      if (window.closeEditProductModal) {
        window.closeEditProductModal();
      }
      loadProducts();
      resetPagination();
    } else {
      showToast(result.message || 'Failed to update product', false);
    }
    
    // Restore button state
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalBtnContent;
  } catch (err) {
    console.error('Error updating product:', err);
    showToast('Failed to update product', false);
  }
} 