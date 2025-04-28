/**
 * sales-api.js
 * Contains functions for API communication in the sales module
 */

// Import utilities if we were using ES6 modules
// import { handleError, showToast } from './sales-utils.js';

/**
 * Fetches sales data from the API with optional filters
 * 
 * @param {Object} filters - Object containing filter parameters
 * @param {string} filters.search - Search term for filtering sales
 * @param {string} filters.status - Status filter for sales
 * @param {string} filters.time - Time period filter (today, week, month)
 * @returns {Promise} - Promise that resolves to sales data
 */
function fetchSales(filters = {}) {
  const { search = '', status = '', time = '' } = filters;
  
  let timeParam = '';
  if (time === 'Today') {
    timeParam = 'today';
  } else if (time === 'This Week') {
    timeParam = 'week';
  } else if (time === 'This Month') {
    timeParam = 'month';
  }
  
  const url = `api/sales.php?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}${timeParam ? '&time=' + timeParam : ''}`;
  
  return fetch(url)
    .then(response => {
      if (!response.ok) {
        return response.json().then(data => {
          throw new Error(data.message || `Server responded with status ${response.status}`);
        });
      }
      return response.json();
    });
}

/**
 * Fetches details for a specific sale
 * 
 * @param {number} saleId - ID of the sale to fetch
 * @returns {Promise} - Promise that resolves to sale details
 */
function fetchSaleDetails(saleId) {
  return fetch(`api/sales.php?id=${saleId}`)
    .then(response => {
      if (!response.ok) {
        return response.json()
          .then(data => {
            throw new Error(data.message || `Server responded with status ${response.status}`);
          })
          .catch(e => {
            // If JSON parsing fails, throw a more general error with the status
            if (e instanceof SyntaxError) {
              throw new Error(`Server error (${response.status}). Please try again or contact support.`);
            }
            throw e;
          });
      }
      return response.json();
    });
}

/**
 * Updates the status of a sale
 * 
 * @param {number} saleId - ID of the sale to update
 * @param {string} status - New status (pending, confirmed, delivered, canceled)
 * @returns {Promise} - Promise that resolves to API response
 */
function updateSaleStatus(saleId, status) {
  return fetch('api/sales.php', {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      id: saleId,
      status: status
    })
  })
  .then(response => {
    if (!response.ok) {
      return response.json().then(data => {
        throw new Error(data.message || `Server responded with status ${response.status}`);
      });
    }
    return response.json();
  });
}

/**
 * Creates a new sale/order
 * 
 * @param {Object} orderData - Order data to submit
 * @returns {Promise} - Promise that resolves to API response
 */
function createOrder(orderData) {
  return fetch('api/sales.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(orderData)
  })
  .then(response => {
    if (!response.ok) {
      return response.json().then(data => {
        throw new Error(data.message || `Server responded with status ${response.status}`);
      });
    }
    return response.json();
  });
}

/**
 * Updates an existing sale/order
 * 
 * @param {Object} orderData - Updated order data
 * @returns {Promise} - Promise that resolves to API response
 */
function updateOrder(orderData) {
  return fetch('api/sales.php', {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(orderData)
  })
  .then(response => {
    if (!response.ok) {
      return response.json().then(data => {
        throw new Error(data.message || `Server responded with status ${response.status}`);
      });
    }
    return response.json();
  });
}

/**
 * Deletes a sale
 * 
 * @param {number} saleId - ID of the sale to delete
 * @returns {Promise} - Promise that resolves to API response
 */
function deleteSale(saleId) {
  return fetch('api/sales.php', {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ id: saleId })
  })
  .then(response => {
    if (!response.ok) {
      return response.json().then(data => {
        throw new Error(data.message || `Server responded with status ${response.status}`);
      });
    }
    return response.json();
  });
}

/**
 * Fetches products data
 * 
 * @returns {Promise} - Promise that resolves to products data
 */
function fetchProducts() {
  return fetch('api/products.php')
    .then(response => {
      if (!response.ok) {
        return response.json().then(data => {
          throw new Error(data.message || `Server responded with status ${response.status}`);
        });
      }
      return response.json();
    });
}

/**
 * Searches for customers with a given query
 * 
 * @param {string} query - Search term
 * @returns {Promise} - Promise that resolves to customers data
 */
function searchCustomers(query) {
  return fetch(`api/customers.php?search=${encodeURIComponent(query)}`)
    .then(response => {
      if (!response.ok) {
        return response.json().then(data => {
          throw new Error(data.message || `Server responded with status ${response.status}`);
        });
      }
      return response.json();
    });
}

// If we were using ES6 modules, we'd export these functions
// export { fetchSales, fetchSaleDetails, updateSaleStatus, createOrder, updateOrder, deleteSale, fetchProducts, searchCustomers }; 