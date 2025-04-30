/**
 * sales-pagination.js
 * Handles pagination for the sales list
 */

// Pagination state
let paginationState = {
  currentPage: 1,
  totalPages: 1,
  itemsPerPage: 10,
  totalItems: 0,
  allSales: []
};

// DOM references for pagination elements
const paginationElements = {
  container: () => document.getElementById('pagination-container'),
  fromCount: () => document.getElementById('pagination-from'),
  toCount: () => document.getElementById('pagination-to'),
  totalCount: () => document.getElementById('pagination-total'),
  prevButton: () => document.getElementById('pagination-prev'),
  nextButton: () => document.getElementById('pagination-next'),
  numbersContainer: () => document.getElementById('pagination-numbers'),
  limitSelect: () => document.getElementById('pagination-limit')
};

/**
 * Initialize pagination component
 */
function initPagination() {
  // Set default items per page value from local storage or default to 10
  const savedLimit = localStorage.getItem('sales_items_per_page');
  if (savedLimit) {
    paginationState.itemsPerPage = parseInt(savedLimit);
    if (paginationElements.limitSelect()) {
      paginationElements.limitSelect().value = savedLimit;
    }
  }

  // Add event listeners
  if (paginationElements.prevButton()) {
    paginationElements.prevButton().addEventListener('click', () => goToPage(paginationState.currentPage - 1));
  }
  
  if (paginationElements.nextButton()) {
    paginationElements.nextButton().addEventListener('click', () => goToPage(paginationState.currentPage + 1));
  }
  
  if (paginationElements.limitSelect()) {
    paginationElements.limitSelect().addEventListener('change', function() {
      paginationState.itemsPerPage = parseInt(this.value);
      localStorage.setItem('sales_items_per_page', this.value);
      paginationState.currentPage = 1; // Reset to first page
      renderPaginatedSales();
    });
  }
}

/**
 * Set all sales data and initialize pagination
 * @param {Array} sales - All sales data from API
 */
function initPaginationWithData(sales) {
  // Store full data set
  paginationState.allSales = sales || [];
  paginationState.totalItems = paginationState.allSales.length;
  
  // Calculate total pages
  paginationState.totalPages = Math.max(1, Math.ceil(paginationState.totalItems / paginationState.itemsPerPage));
  
  // Ensure current page is in valid range
  if (paginationState.currentPage > paginationState.totalPages) {
    paginationState.currentPage = paginationState.totalPages;
  }
  
  // Update pagination UI and render paginated data
  updatePaginationUI();
  return getPagedSales();
}

/**
 * Get current page's sales data
 * @returns {Array} - Paginated sales data for current page
 */
function getPagedSales() {
  const startIndex = (paginationState.currentPage - 1) * paginationState.itemsPerPage;
  const endIndex = startIndex + paginationState.itemsPerPage;
  return paginationState.allSales.slice(startIndex, endIndex);
}

/**
 * Render sales with pagination
 */
function renderPaginatedSales() {
  // Get data for current page
  const pagedSales = getPagedSales();
  
  // Render the sales list with the paged data
  originalRenderSalesList(pagedSales);
  
  // Update pagination UI
  updatePaginationUI();
}

/**
 * Updates pagination UI components based on current state
 */
function updatePaginationUI() {
  const { currentPage, totalPages, totalItems, itemsPerPage } = paginationState;
  
  // Show pagination only if we have items
  if (totalItems > 0) {
    paginationElements.container().classList.remove('hidden');
  } else {
    paginationElements.container().classList.add('hidden');
    return;
  }
  
  // Update counter text
  const startItem = Math.min(((currentPage - 1) * itemsPerPage) + 1, totalItems);
  const endItem = Math.min(startItem + itemsPerPage - 1, totalItems);
  
  paginationElements.fromCount().textContent = startItem;
  paginationElements.toCount().textContent = endItem;
  paginationElements.totalCount().textContent = totalItems;
  
  // Update button states
  paginationElements.prevButton().disabled = currentPage === 1;
  paginationElements.nextButton().disabled = currentPage === totalPages;
  
  // Generate page numbers
  updatePageNumbers();
}

/**
 * Go to a specific page
 * @param {number} pageNumber - Page to navigate to
 */
function goToPage(pageNumber) {
  if (pageNumber < 1 || pageNumber > paginationState.totalPages) {
    return;
  }
  
  paginationState.currentPage = pageNumber;
  renderPaginatedSales();
}

/**
 * Update page number buttons
 */
function updatePageNumbers() {
  const numbersContainer = paginationElements.numbersContainer();
  if (!numbersContainer) return;
  
  // Clear existing buttons
  numbersContainer.innerHTML = '';
  
  const { currentPage, totalPages } = paginationState;
  
  // Determine range of page numbers to show
  let startPage = Math.max(1, currentPage - 2);
  let endPage = Math.min(totalPages, startPage + 4);
  
  // Adjust if we're near the end
  if (endPage - startPage < 4 && startPage > 1) {
    startPage = Math.max(1, endPage - 4);
  }
  
  // Add first page and ellipsis if needed
  if (startPage > 1) {
    addPageButton(1);
    if (startPage > 2) {
      addEllipsis();
    }
  }
  
  // Add page number buttons
  for (let i = startPage; i <= endPage; i++) {
    addPageButton(i, i === currentPage);
  }
  
  // Add last page and ellipsis if needed
  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      addEllipsis();
    }
    addPageButton(totalPages);
  }
  
  function addPageButton(pageNum, isActive = false) {
    const btn = document.createElement('button');
    btn.className = `px-3 py-1 rounded text-sm ${isActive 
      ? 'bg-gray-800 text-white' 
      : 'border border-gray-300 hover:bg-gray-50'}`;
    btn.textContent = pageNum;
    btn.addEventListener('click', () => goToPage(pageNum));
    numbersContainer.appendChild(btn);
  }
  
  function addEllipsis() {
    const span = document.createElement('span');
    span.className = 'px-3 py-1 text-gray-500';
    span.textContent = '...';
    numbersContainer.appendChild(span);
  }
} 