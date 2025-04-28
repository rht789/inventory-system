// Product listing functionality
import { apiGet, apiPost } from '../ajax.js';
import { showToast } from './utils.js';

// DOM Elements
let searchInput, stockSelect, categorySelect, productList;

// Initialize product list functionality
export function initProductList() {
  // Get DOM references
  searchInput = document.getElementById('searchInput');
  stockSelect = document.getElementById('stockSelect');
  categorySelect = document.getElementById('categorySelect');
  productList = document.getElementById('product-list');
  
  // Add event listeners for filters
  if (searchInput) searchInput.addEventListener('input', loadProducts);
  if (stockSelect) stockSelect.addEventListener('input', loadProducts);
  if (categorySelect) categorySelect.addEventListener('input', loadProducts);
  
  // Register global product functions if they don't exist
  if (!window.confirmDeleteProduct) {
    window.confirmDeleteProduct = confirmDeleteProduct;
  }
  
  // Initial load of products
  loadProducts();
}

// Load products data based on filters
export async function loadProducts() {
  try {
    // Show loading state
    if (productList) {
      productList.innerHTML = `
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
      `;
    }
    
    // Build URL with filters
    const params = new URLSearchParams();
    if (searchInput && searchInput.value) params.append('search', searchInput.value);
    if (stockSelect && stockSelect.value) params.append('stock_filter', stockSelect.value);
    if (categorySelect && categorySelect.value) params.append('category_id', categorySelect.value);
    
    const products = await apiGet(`./api/products.php?${params.toString()}`);
    
    if (products.length === 0) {
      if (productList) {
        productList.innerHTML = `
          <tr>
            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
              <div class="flex flex-col items-center">
                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-lg">No products found</p>
                <p class="text-sm text-gray-400 mt-1"><i class="fas fa-filter mr-1"></i>Try changing your search criteria</p>
              </div>
            </td>
          </tr>
        `;
      }
      return;
    }
    
    if (productList) {
      productList.innerHTML = products.map(product => `
        <tr class="hover:bg-gray-50 transition-colors">
          <td class="px-6 py-4 text-left">
            <div class="flex items-center gap-3">
              ${product.image ? 
                `<img src="${product.image}" alt="${product.name}" class="w-10 h-10 object-cover rounded border border-gray-200">` : 
                `<div class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center border border-gray-200">
                  <i class="fas fa-box text-gray-400"></i>
                </div>`
              }
              <div>
                <div class="font-semibold text-gray-900">${product.name}</div>
                <div class="text-xs text-gray-500">${product.description || 'No description'}</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 text-center">
            <span class="inline-flex items-center px-2.5 py-1 rounded-md border-2 border-gray-200 text-xs font-medium bg-white">
              <i class="fas fa-tag text-gray-500 mr-1"></i>${product.category_name}
            </span>
          </td>
          <td class="px-6 py-4 text-left">
            <div class="flex flex-wrap gap-1">
              ${product.sizes.map(size => `
                <div class="inline-flex items-center px-2 py-1 rounded-md text-xs ${
                  size.stock > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500'
                }">
                  ${size.size_name}: ${size.stock}
                </div>
              `).join('')}
            </div>
          </td>
          <td class="px-6 py-4 text-center text-gray-700">
            <div class="flex items-center justify-center">
              <i class="fas fa-cubes text-gray-500 mr-2"></i>${product.stock}
            </div>
          </td>
          <td class="px-6 py-4 text-center">
            ${product.barcode ? 
              `<img src="${product.barcode}" alt="Barcode" class="h-8 mx-auto">` : 
              `<span class="text-gray-400">No barcode</span>`
            }
          </td>
          <td class="px-6 py-4 text-center font-semibold text-gray-700">৳${parseFloat(product.price).toFixed(2)}</td>
          <td class="px-6 py-4 text-center font-semibold text-gray-900">৳${parseFloat(product.selling_price).toFixed(2)}</td>
          <td class="px-6 py-4 text-center">
            <div class="flex justify-center gap-2">
              <button onclick="openEditProductModal(${JSON.stringify(product).replace(/"/g, '&quot;')})" 
                      class="p-2 text-gray-700 hover:text-black border-2 border-gray-200 hover:border-black rounded-md transition-colors">
                <i class="fas fa-edit"></i>
              </button>
              <button onclick="confirmDeleteProduct(${product.id})" 
                      class="p-2 text-red-600 hover:text-red-700 border-2 border-red-200 hover:border-red-300 rounded-md transition-colors">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          </td>
        </tr>
      `).join('');
    }
  } catch (err) {
    console.error('Error loading products:', err);
    showToast('Failed to load products', false);
  }
}

// Delete a product
export async function confirmDeleteProduct(id) {
  if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) return;
  
  try {
    const result = await apiPost('api/products.php', {
      action: 'delete',
      id
    });
    
    if (result.success) {
      showToast('Product deleted successfully');
      if (window.closeEditProductModal) {
        window.closeEditProductModal();
      }
      loadProducts();
    } else {
      showToast(result.message || 'Failed to delete product', false);
    }
  } catch (err) {
    console.error('Error deleting product:', err);
    showToast('Failed to delete product', false);
  }
} 