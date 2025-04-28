// Utility functions for product management

// Toast notification element
let toastElement;

// Show a toast notification
export function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  
  // Reset toast state
  toast.className = "fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50";
  
  // Set icon and color based on message type
  let icon = '';
  
  switch(type) {
    case 'success':
      toast.classList.add('bg-green-500', 'text-white');
      icon = '<i class="fas fa-check-circle mr-2"></i>';
      break;
    case 'error':
      toast.classList.add('bg-red-500', 'text-white');
      icon = '<i class="fas fa-exclamation-circle mr-2"></i>';
      break;
    case 'warning':
      toast.classList.add('bg-yellow-500', 'text-white');
      icon = '<i class="fas fa-exclamation-triangle mr-2"></i>';
      break;
    default:
      toast.classList.add('bg-gray-700', 'text-white');
      icon = '<i class="fas fa-info-circle mr-2"></i>';
  }
  
  // Set toast content with icon
  toast.innerHTML = `${icon}<span>${message}</span>`;
  
  // Show toast
  toast.classList.remove('hidden');
  
  // Hide after 3 seconds
  setTimeout(() => {
    toast.classList.add('hidden');
  }, 3000);
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