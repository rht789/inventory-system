/**
 * sales-utils.js
 * Contains utility functions for the sales module
 */

/**
 * Centralized error handling function
 * @param {Error|Object} error - The error object
 * @param {string} defaultMessage - Default message to show if error has no message property
 */
function handleError(error, defaultMessage = 'An error occurred') {
  console.error('Error:', error);
  const message = error.message || defaultMessage;
  showToast(message, 'error');
}

/**
 * Debounces a function to limit how often it can be called
 * Useful for search inputs and other frequently triggered events
 * 
 * @param {Function} func - The function to debounce
 * @param {number} delay - Delay in milliseconds
 * @returns {Function} - Debounced function
 */
function debounce(func, delay) {
  let timeoutId;
  return function(...args) {
    const context = this;
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
      func.apply(context, args);
    }, delay);
  };
}

/**
 * Capitalizes the first letter of a string
 * 
 * @param {string} string - The input string
 * @returns {string} - String with first letter capitalized
 */
function capitalizeFirstLetter(string) {
  if (!string) return '';
  return string.charAt(0).toUpperCase() + string.slice(1);
}

/**
 * Format date to locale string
 * 
 * @param {string|Date} date - Date to format
 * @returns {string} - Formatted date string
 */
function formatDate(date) {
  if (!date) return '';
  const dateObj = new Date(date);
  return dateObj.toLocaleDateString();
}

/**
 * Format time to locale string
 * 
 * @param {string|Date} date - Date to extract time from
 * @param {Object} options - Formatting options
 * @returns {string} - Formatted time string
 */
function formatTime(date, options = {}) {
  if (!date) return '';
  const dateObj = new Date(date);
  return dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', ...options });
}

/**
 * Format currency value
 * 
 * @param {number} value - Monetary value to format
 * @param {string} currency - Currency symbol
 * @returns {string} - Formatted currency string
 */
function formatCurrency(value, currency = '৳') {
  return `${currency} ${parseFloat(value).toFixed(2)}`;
}

// If we were using ES6 modules, we'd export these functions
// export { handleError, debounce, capitalizeFirstLetter, formatDate, formatTime, formatCurrency }; 