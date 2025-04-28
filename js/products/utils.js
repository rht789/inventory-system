// Utility functions for product management

// Toast notification element
let toastElement;

// Show a toast notification
export function showToast(message, success = true) {
  // Get the toast element if we don't have it yet
  if (!toastElement) {
    toastElement = document.getElementById('toast');
  }
  
  if (!toastElement) {
    console.error('Toast element not found');
    return;
  }
  
  // Set toast content and styling
  toastElement.textContent = message;
  toastElement.className = `fixed bottom-4 right-4 z-50 text-white px-4 py-2 rounded-lg shadow-lg ${success ? 'bg-gray-700' : 'bg-red-600'}`;
  toastElement.classList.remove('hidden');
  
  // Auto-hide after 3 seconds
  setTimeout(() => toastElement.classList.add('hidden'), 3000);
}

// Format currency
export function formatCurrency(amount) {
  return '৳' + parseFloat(amount).toFixed(2);
}

// Format date
export function formatDate(dateString) {
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
} 