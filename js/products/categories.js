// Category management functionality
import { apiGet, apiPost } from '../ajax.js';
import { showToast } from './utils.js';
import { loadProducts } from './list.js';
import { resetPagination } from './pagination.js';

// DOM Elements
let categorySelect, newCategoryInput, addCategoryBtn, categoryList;

// Initialize category functionality
export function initCategories() {
  // Get DOM references
  categorySelect = document.getElementById('categorySelect');
  newCategoryInput = document.getElementById('newCategoryInput');
  addCategoryBtn = document.getElementById('addCategoryBtn');
  categoryList = document.getElementById('categoryList');
  
  // Add event listener for category button
  if (addCategoryBtn) {
    addCategoryBtn.addEventListener('click', addCategory);
  }
  
  // Register global category functions if they don't exist
  if (!window.editCategory) {
    window.editCategory = editCategory;
  }
  
  if (!window.deleteCategory) {
    window.deleteCategory = deleteCategory;
  }
  
  // Load categories on init
  loadCategories();
}

// Load categories data
export async function loadCategories(forProductForm = false) {
  try {
    const response = await apiGet('api/categories.php');
    
    // For category selection dropdowns
    const options = response.map(c => 
      `<option value="${c.id}">${c.name}</option>`
    );
    
    // For filter dropdown
    if (categorySelect) {
      categorySelect.innerHTML = '<option value="">All Categories</option>' + options.join('');
    }
    
    // For add/edit product form dropdowns
    if (forProductForm) {
      const addProductForm = document.getElementById('addProductForm');
      const editProductForm = document.getElementById('editProductForm');
      
      if (addProductForm && addProductForm.category_id) {
        addProductForm.category_id.innerHTML = '<option value="">Select category</option>' + options.join('');
      }
      
      if (editProductForm && editProductForm.category_id) {
        editProductForm.category_id.innerHTML = '<option value="">Select category</option>' + options.join('');
      }
    }
    
    // For category management modal
    if (!forProductForm && categoryList) {
      categoryList.innerHTML = response.map(c => `
        <li class="py-3 flex items-center justify-between">
          <span class="flex items-center">
            <i class="fas fa-tag text-gray-500 mr-2"></i>
            ${c.name}
          </span>
          <div class="flex gap-2">
            <button onclick="editCategory(${c.id}, '${c.name}')" class="text-gray-700 hover:text-black">
              <i class="fas fa-edit"></i>
            </button>
            <button onclick="deleteCategory(${c.id})" class="text-red-600 hover:text-red-700">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        </li>
      `).join('');
    }
  } catch (err) {
    console.error('Error loading categories:', err);
    showToast('Failed to load categories', false);
  }
}

// Add a new category
export async function addCategory() {
  const name = newCategoryInput.value.trim();
  if (!name) return;
  
  try {
    const result = await apiPost('api/categories.php', {
      action: 'create',
      name
    });
    
    if (result.success) {
      showToast('Category added successfully');
      newCategoryInput.value = '';
      loadCategories();
      resetPagination();
    } else {
      showToast(result.message || 'Failed to add category', false);
    }
  } catch (err) {
    console.error('Error adding category:', err);
    showToast('Failed to add category', false);
  }
}

// Edit an existing category
export async function editCategory(id, name) {
  const newName = prompt('Enter new category name:', name);
  if (!newName || newName === name) return;
  
  try {
    const result = await apiPost('api/categories.php', {
      action: 'update',
      id,
      name: newName
    });
    
    if (result.success) {
      showToast('Category updated successfully');
      loadCategories();
      loadProducts(); // Refresh products to reflect the category change
      resetPagination();
    } else {
      showToast(result.message || 'Failed to update category', false);
    }
  } catch (err) {
    console.error('Error updating category:', err);
    showToast('Failed to update category', false);
  }
}

// Delete a category
export async function deleteCategory(id) {
  if (!confirm('Are you sure you want to delete this category? Products using this category will be affected.')) return;
  
  try {
    const result = await apiPost('api/categories.php', {
      action: 'delete',
      id
    });
    
    if (result.success) {
      showToast('Category deleted successfully');
      loadCategories();
      loadProducts(); // Refresh products to reflect the category change
      resetPagination();
    } else {
      showToast(result.message || 'Failed to delete category', false);
    }
  } catch (err) {
    console.error('Error deleting category:', err);
    showToast('Failed to delete category', false);
  }
} 