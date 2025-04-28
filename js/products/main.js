// Main entry point for product management functionality
import { initModals } from './modals.js';
import { initProductList } from './list.js';
import { initCategories } from './categories.js';
import { initProductForms } from './forms.js';

// Initialize all product management functionality
document.addEventListener('DOMContentLoaded', () => {
  // Initialize modules
  initModals();
  initProductList();
  initCategories();
  initProductForms();
}); 