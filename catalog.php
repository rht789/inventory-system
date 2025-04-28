<?php
// catalog.php - Public product catalog page that doesn't require authentication
require_once 'db.php';

// Get current category if specified
$currentCategoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
$currentCategory = null;

if ($currentCategoryId) {
    $stmt = $pdo->prepare("SELECT id, name, description FROM categories WHERE id = ?");
    $stmt->execute([$currentCategoryId]);
    $currentCategory = $stmt->fetch();
}

// Fetch all categories for the sidebar
$categoriesStmt = $pdo->prepare("SELECT id, name FROM categories ORDER BY name");
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll();

// Get min and max prices for price filter
$priceStmt = $pdo->prepare("
    SELECT MIN(selling_price) as min_price, MAX(selling_price) as max_price 
    FROM products 
    WHERE deleted_at IS NULL
");
$priceStmt->execute();
$priceRange = $priceStmt->fetch();

// Default page title
$pageTitle = $currentCategory ? $currentCategory['name'] : "All Products";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - SmartInventory</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- noUiSlider CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.6.1/nouislider.min.css">
    <style>
        /* Custom styles for noUiSlider */
        .noUi-connect {
            background: #374151; /* gray-700 */
        }
        .noUi-handle {
            border-radius: 50%;
            background: #374151;
            border: 2px solid white;
            box-shadow: 0 1px 4px rgba(0,0,0,0.3);
            width: 20px !important;
            height: 20px !important;
            top: -8px !important;
            right: -10px !important;
        }
        .noUi-handle:before, .noUi-handle:after {
            display: none;
        }
        .noUi-tooltip {
            background-color: #374151;
            border-color: #374151;
            color: white;
            font-size: 12px;
            padding: 3px 6px;
            border-radius: 4px;
        }
        .noUi-horizontal {
            height: 6px;
        }
        
        /* Custom radio button styles */
        input[type="radio"]:checked ~ .radio-container {
            border-color: #374151;
        }
        input[type="radio"]:checked ~ .radio-container .radio-dot {
            display: block;
        }
        .radio-container {
            transition: all 0.2s ease;
        }
        body {
            background-color: #F3F4F6;
        }
        .product-card {
            transition: all 0.2s ease-in-out;
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .badge {
            position: absolute;
            top: 10px;
            z-index: 10;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 4px 8px;
            border-radius: 4px;
        }
        .badge-new {
            left: 10px;
            background-color: #374151;
            color: white;
        }
        .badge-low {
            right: 10px;
            background-color: #F59E0B;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-6 py-3 flex justify-between items-center">
            <!-- Logo and name -->
            <div class="flex items-center gap-3">
                <i class="fas fa-box-open text-2xl text-blue-600"></i>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">SmartInventory</h1>
                    <span class="text-xs text-gray-500 hidden sm:inline-block">Inventory Management System</span>
                </div>
            </div>
            
            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center space-x-6">
                <a href="catalog.php" class="text-gray-700 hover:text-gray-900 font-medium">Home</a>
                <a href="#" class="text-gray-700 hover:text-gray-900">Products</a>
                <a href="about.php" class="text-gray-700 hover:text-gray-900">About Us</a>
                <a href="contact.php" class="text-gray-700 hover:text-gray-900">Contact Us</a>
                <a href="#" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors">Order Now</a>
            </nav>
            
            <!-- Mobile menu button -->
            <button class="md:hidden text-gray-600 hover:text-gray-900 focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8 min-h-screen">
        <!-- Breadcrumb -->
        <div class="flex items-center mb-6 text-sm">
            <a href="catalog.php" class="text-gray-500 hover:text-gray-700">Home</a>
            <?php if ($currentCategory): ?>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-700 font-medium"><?= htmlspecialchars($currentCategory['name']) ?></span>
            <?php endif; ?>
        </div>
    
        <!-- Category Title -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($pageTitle) ?></h1>
            <?php if ($currentCategory && $currentCategory['description']): ?>
                <p class="text-gray-600"><?= htmlspecialchars($currentCategory['description']) ?></p>
            <?php else: ?>
                <p class="text-gray-600">Browse our complete collection of quality products</p>
            <?php endif; ?>
        </div>
        
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Filter Sidebar -->
            <div class="w-full lg:w-1/4 space-y-6">
                <!-- Categories Card -->
                <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                    <h2 class="font-bold text-lg text-gray-800 mb-4">Categories</h2>
                    
                    <ul class="space-y-3">
                        <li>
                            <a href="catalog.php" class="flex items-center p-2 rounded-md transition-all <?= !$currentCategoryId ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
                                <span class="w-8 h-8 flex items-center justify-center bg-gray-700 text-white rounded-md mr-3">
                                    <i class="fas fa-th-large"></i>
                                </span>
                                All Products
                            </a>
                        </li>
                        <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="catalog.php?category_id=<?= $category['id'] ?>" 
                               class="flex items-center p-2 rounded-md transition-all <?= $currentCategoryId == $category['id'] ? 'bg-gray-100 text-gray-900 font-medium' : 'text-gray-700 hover:bg-gray-50' ?>">
                                <span class="w-8 h-8 flex items-center justify-center rounded-md mr-3 
                                  <?= $currentCategoryId == $category['id'] ? 'bg-gray-700 text-white' : 'bg-gray-200 text-gray-700' ?>">
                                    <i class="fas fa-folder"></i>
                                </span>
                                <?= htmlspecialchars($category['name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Price Range Card -->
                <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                    <h2 class="font-bold text-lg text-gray-800 mb-4">Price Range</h2>
                    
                    <div class="px-1">
                        <div class="flex justify-between text-sm text-gray-600 mb-2">
                            <span>৳<span id="price-range-min"><?= floor($priceRange['min_price']) ?></span></span>
                            <span>৳<span id="price-range-max"><?= ceil($priceRange['max_price']) ?></span></span>
                        </div>
                        
                        <div id="price-slider" class="mb-6"></div>
                        
                        <!-- Manual price input fields -->
                        <div class="flex items-center gap-3">
                            <div class="relative flex-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">৳</span>
                                <input 
                                    type="number" 
                                    id="manual-min-price" 
                                    class="w-full border-2 border-gray-300 rounded-md px-7 py-2 text-sm focus:outline-none focus:border-gray-500 focus:ring-1 focus:ring-gray-500 transition-all" 
                                    min="<?= floor($priceRange['min_price']) ?>" 
                                    max="<?= ceil($priceRange['max_price']) ?>" 
                                    value="<?= floor($priceRange['min_price']) ?>"
                                >
                            </div>
                            <div class="h-px w-3 bg-gray-300"></div>
                            <div class="relative flex-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">৳</span>
                                <input 
                                    type="number" 
                                    id="manual-max-price" 
                                    class="w-full border-2 border-gray-300 rounded-md px-7 py-2 text-sm focus:outline-none focus:border-gray-500 focus:ring-1 focus:ring-gray-500 transition-all" 
                                    min="<?= floor($priceRange['min_price']) ?>" 
                                    max="<?= ceil($priceRange['max_price']) ?>" 
                                    value="<?= ceil($priceRange['max_price']) ?>"
                                >
                            </div>
                        </div>
                        
                        <button id="apply-price-range" class="mt-4 w-full flex justify-center items-center bg-gray-700 text-white px-4 py-2.5 rounded-md text-sm hover:bg-gray-600 transition-colors">
                            <i class="fas fa-filter mr-2"></i> Apply Filter
                        </button>
                    </div>
                </div>
                
                <!-- Availability Card -->
                <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                    <h2 class="font-bold text-lg text-gray-800 mb-4">Availability</h2>
                    
                    <div class="space-y-3">
                        <label class="flex items-center p-2 rounded-md border-2 border-transparent hover:border-gray-200 cursor-pointer">
                            <div class="relative flex items-center">
                                <input type="radio" name="stock" class="stock-filter mr-2 h-4 w-4 opacity-0 absolute" value="all" checked>
                                <div class="w-5 h-5 border-2 rounded-full border-gray-400 flex items-center justify-center radio-container">
                                    <div class="hidden w-3 h-3 bg-gray-700 rounded-full radio-dot"></div>
                                </div>
                            </div>
                            <span class="ml-2 text-gray-800">All Products</span>
                            <span class="ml-auto bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full">All</span>
                        </label>
                        <label class="flex items-center p-2 rounded-md border-2 border-transparent hover:border-gray-200 cursor-pointer">
                            <div class="relative flex items-center">
                                <input type="radio" name="stock" class="stock-filter mr-2 h-4 w-4 opacity-0 absolute" value="in_stock">
                                <div class="w-5 h-5 border-2 rounded-full border-gray-400 flex items-center justify-center radio-container">
                                    <div class="hidden w-3 h-3 bg-gray-700 rounded-full radio-dot"></div>
                                </div>
                            </div>
                            <span class="ml-2 text-gray-800">In Stock Only</span>
                            <span class="ml-auto bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Available</span>
                        </label>
                    </div>
                </div>
                
                <!-- Reset Filters Button -->
                <button id="resetFilters" class="w-full bg-white border-2 border-gray-300 text-gray-700 py-3 rounded-md hover:bg-gray-50 font-medium flex justify-center items-center space-x-2 shadow-sm">
                    <i class="fas fa-undo-alt"></i>
                    <span>Reset Filters</span>
                </button>
            </div>
            
            <!-- Product Content Area -->
            <div class="w-full lg:w-3/4 space-y-6">
                <!-- Controls & Search Bar -->
                <div class="bg-white p-5 rounded-lg shadow border border-gray-200">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input 
                                type="text" 
                                id="searchInput" 
                                placeholder="Search products by name or description..." 
                                class="pl-10 w-full border-2 border-gray-300 rounded-md py-2 px-4 text-sm focus:outline-none focus:border-gray-500 focus:ring-1 focus:ring-gray-500"
                            >
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500 whitespace-nowrap">Sort by:</span>
                            <select id="sortSelect" class="border-2 border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-gray-500 focus:ring-1 focus:ring-gray-500 bg-white">
                                <option value="created_at-DESC">Most Recent</option>
                                <option value="selling_price-ASC">Price: Low to High</option>
                                <option value="selling_price-DESC">Price: High to Low</option>
                                <option value="name-ASC">Name: A to Z</option>
                                <option value="name-DESC">Name: Z to A</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Results Count -->
                <div id="productCount" class="text-sm text-gray-600 flex items-center gap-2 px-2">
                    <i class="fas fa-cubes text-gray-400"></i>
                    <span>Loading products...</span>
                </div>
                
                <!-- Product Grid -->
                <div id="productGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Products will be loaded here via JavaScript -->
                    <div class="bg-white p-5 rounded-lg shadow border border-gray-200 animate-pulse">
                        <div class="bg-gray-300 h-48 rounded-md mb-4"></div>
                        <div class="bg-gray-300 h-4 rounded mb-2"></div>
                        <div class="bg-gray-300 h-4 w-2/3 rounded mb-4"></div>
                        <div class="bg-gray-300 h-8 w-1/3 rounded"></div>
                    </div>
                    <div class="bg-white p-5 rounded-lg shadow border border-gray-200 animate-pulse">
                        <div class="bg-gray-300 h-48 rounded-md mb-4"></div>
                        <div class="bg-gray-300 h-4 rounded mb-2"></div>
                        <div class="bg-gray-300 h-4 w-2/3 rounded mb-4"></div>
                        <div class="bg-gray-300 h-8 w-1/3 rounded"></div>
                    </div>
                    <div class="bg-white p-5 rounded-lg shadow border border-gray-200 animate-pulse">
                        <div class="bg-gray-300 h-48 rounded-md mb-4"></div>
                        <div class="bg-gray-300 h-4 rounded mb-2"></div>
                        <div class="bg-gray-300 h-4 w-2/3 rounded mb-4"></div>
                        <div class="bg-gray-300 h-8 w-1/3 rounded"></div>
                    </div>
                </div>
                
                <!-- Pagination -->
                <div id="pagination" class="flex justify-center mt-8">
                    <!-- Pagination links will be loaded here via JavaScript -->
                </div>
            </div>
        </div>
    </main>
    
    <!-- Catalog Showcase Banner -->
    <section class="py-16 bg-cover bg-center bg-gray-700 text-white relative" style="background-image: url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80');">
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>
        <div class="container mx-auto px-6 relative">
            <div class="text-center max-w-3xl mx-auto">
                <h2 class="text-3xl font-bold mb-4">EXPLORE OUR PRODUCT CATALOG</h2>
                <p class="mb-8 text-gray-200">
                    Browse through our product catalog to find a wide range of great shopping options.
                    From classic looks to the latest trends, there's something for everyone.
                </p>
                <a href="#" class="inline-flex items-center gap-2 border border-white px-6 py-3 rounded-md hover:bg-white hover:text-gray-900 transition-colors">
                    VIEW ALL PRODUCTS <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-white py-8 border-t border-gray-200">
        <div class="container mx-auto px-6">
            <!-- Links -->
            <div class="flex flex-wrap justify-center mb-6">
                <div class="px-4 py-2">
                    <a href="about.php" class="text-gray-600 hover:text-gray-900">About</a>
                </div>
                <div class="px-4 py-2">
                    <a href="contact.php" class="text-gray-600 hover:text-gray-900">Contact</a>
                </div>
                <div class="px-4 py-2">
                    <a href="#" class="text-gray-600 hover:text-gray-900">FAQ</a>
                </div>
                <div class="px-4 py-2">
                    <a href="#" class="text-gray-600 hover:text-gray-900">Privacy</a>
                </div>
                <div class="px-4 py-2">
                    <a href="#" class="text-gray-600 hover:text-gray-900">Shipping</a>
                </div>
            </div>
            
            <!-- Categories -->
            <div class="flex flex-wrap justify-center mb-6">
                <?php foreach (array_slice($categories, 0, 4) as $category): ?>
                <div class="px-4 py-2">
                    <a href="catalog.php?category_id=<?= $category['id'] ?>" class="text-gray-600 hover:text-gray-900">
                        <?= htmlspecialchars($category['name']) ?>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Social Media Icons -->
            <div class="flex justify-center space-x-4 mb-6">
                <a href="#" class="text-gray-400 hover:text-gray-700">
                    <i class="fab fa-instagram text-xl"></i>
                </a>
                <a href="#" class="text-gray-400 hover:text-gray-700">
                    <i class="fab fa-facebook text-xl"></i>
                </a>
                <a href="#" class="text-gray-400 hover:text-gray-700">
                    <i class="fab fa-twitter text-xl"></i>
                </a>
                <a href="#" class="text-gray-400 hover:text-gray-700">
                    <i class="fab fa-youtube text-xl"></i>
                </a>
            </div>
            
            <!-- Copyright -->
            <div class="text-center text-sm text-gray-500">
                &copy; <?= date('Y') ?> SmartInventory. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Toast Message -->
    <div id="toast-message" class="fixed bottom-4 right-4 bg-gray-700 text-white px-4 py-3 rounded-lg shadow-lg hidden z-50 flex items-center">
        <i class="fas fa-info-circle mr-2"></i>
        <span id="toast-text">This feature will be implemented in the future</span>
    </div>

    <!-- JavaScript -->
    <script type="module">
    import { apiGet } from './js/ajax.js';
    
    // Import noUiSlider
    import noUiSlider from 'https://cdn.jsdelivr.net/npm/nouislider@15.6.1/+esm';
    
    document.addEventListener('DOMContentLoaded', function() {
        // Toast functionality
        const toast = document.getElementById('toast-message');
        const toastText = document.getElementById('toast-text');
        
        function showToast(message) {
            toastText.textContent = message || 'This feature will be implemented in the future';
            toast.classList.remove('hidden');
            
            // Hide after 3 seconds
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }
        
        // Add click listener to document to handle future feature clicks
        document.addEventListener('click', function(e) {
            // Check if the clicked element or its parent is meant to be a future feature
            const futureFeature = e.target.closest('.future-feature');
            
            if (futureFeature) {
                e.preventDefault();
                showToast(futureFeature.dataset.message || 'This feature will be implemented in the future');
            }
        });
        
        // Global variables
        let currentPage = 1;
        const urlParams = new URLSearchParams(window.location.search);
        const categoryId = urlParams.get('category_id') || '';
        let minPrice = <?= floor($priceRange['min_price']) ?>;
        let maxPrice = <?= ceil($priceRange['max_price']) ?>;
        let currentMinPrice = minPrice;
        let currentMaxPrice = maxPrice;
        let stockFilter = 'all';
        let searchQuery = '';
        let debounceTimer;
        
        // Elements
        const productGrid = document.getElementById('productGrid');
        const pagination = document.getElementById('pagination');
        const productCount = document.getElementById('productCount');
        const sortSelect = document.getElementById('sortSelect');
        const searchInput = document.getElementById('searchInput');
        const priceRangeMin = document.getElementById('price-range-min');
        const priceRangeMax = document.getElementById('price-range-max');
        const manualMinPrice = document.getElementById('manual-min-price');
        const manualMaxPrice = document.getElementById('manual-max-price');
        const applyPriceRangeBtn = document.getElementById('apply-price-range');
        const stockFilters = document.querySelectorAll('.stock-filter');
        const resetFiltersBtn = document.getElementById('resetFilters');
        
        // Initialize price slider
        const priceSlider = document.getElementById('price-slider');
        if (priceSlider) {
            noUiSlider.create(priceSlider, {
                start: [minPrice, maxPrice],
                connect: true,
                range: {
                    'min': minPrice,
                    'max': maxPrice
                },
                step: 1,
                format: {
                    to: value => Math.round(value),
                    from: value => Number(value)
                },
                tooltips: true
            });
            
            // Update displayed values and manual inputs when slider changes
            priceSlider.noUiSlider.on('update', function(values, handle) {
                const [newMin, newMax] = values.map(Number);
                priceRangeMin.textContent = newMin;
                priceRangeMax.textContent = newMax;
                
                // Update manual input fields (without triggering their change event)
                manualMinPrice.value = newMin;
                manualMaxPrice.value = newMax;
            });
            
            // Trigger filter when slider stops
            priceSlider.noUiSlider.on('change', function(values, handle) {
                const [newMin, newMax] = values.map(Number);
                currentMinPrice = newMin;
                currentMaxPrice = newMax;
                currentPage = 1;
                loadProducts();
                updateURL();
            });
        }
        
        // Apply manual price range when button is clicked
        if (applyPriceRangeBtn) {
            applyPriceRangeBtn.addEventListener('click', function() {
                applyManualPriceRange();
            });
        }
        
        // Allow Enter key on manual price inputs
        if (manualMinPrice && manualMaxPrice) {
            manualMinPrice.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyManualPriceRange();
                }
            });
            
            manualMaxPrice.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyManualPriceRange();
                }
            });
            
            // Input validation
            manualMinPrice.addEventListener('change', function() {
                // Ensure min is not less than slider minimum
                if (Number(this.value) < minPrice) {
                    this.value = minPrice;
                }
                // Ensure min is not greater than current max
                if (Number(this.value) > Number(manualMaxPrice.value)) {
                    this.value = manualMaxPrice.value;
                }
            });
            
            manualMaxPrice.addEventListener('change', function() {
                // Ensure max is not greater than slider maximum
                if (Number(this.value) > maxPrice) {
                    this.value = maxPrice;
                }
                // Ensure max is not less than current min
                if (Number(this.value) < Number(manualMinPrice.value)) {
                    this.value = manualMinPrice.value;
                }
            });
        }
        
        function applyManualPriceRange() {
            const newMin = Number(manualMinPrice.value);
            const newMax = Number(manualMaxPrice.value);
            
            // Validate input
            if (isNaN(newMin) || isNaN(newMax) || newMin > newMax) {
                alert('Please enter a valid price range');
                return;
            }
            
            // Update slider
            if (priceSlider.noUiSlider) {
                priceSlider.noUiSlider.set([newMin, newMax]);
            }
            
            // Update filtering
            currentMinPrice = newMin;
            currentMaxPrice = newMax;
            currentPage = 1;
            loadProducts();
            updateURL();
        }
        
        // Initialize - load first page of products
        if (urlParams.has('page')) {
            currentPage = parseInt(urlParams.get('page')) || 1;
        }
        
        if (urlParams.has('search')) {
            searchQuery = urlParams.get('search');
            searchInput.value = searchQuery;
        }
        
        // Check for price params in URL
        if (urlParams.has('min_price') && urlParams.has('max_price')) {
            const urlMinPrice = parseInt(urlParams.get('min_price'));
            const urlMaxPrice = parseInt(urlParams.get('max_price'));
            
            if (!isNaN(urlMinPrice) && !isNaN(urlMaxPrice) && urlMinPrice <= urlMaxPrice) {
                currentMinPrice = urlMinPrice;
                currentMaxPrice = urlMaxPrice;
                
                // Update slider
                if (priceSlider.noUiSlider) {
                    priceSlider.noUiSlider.set([urlMinPrice, urlMaxPrice]);
                }
                
                // Update manual inputs
                if (manualMinPrice && manualMaxPrice) {
                    manualMinPrice.value = urlMinPrice;
                    manualMaxPrice.value = urlMaxPrice;
                }
            }
        }
        
        loadProducts();
        
        // Event listeners
        sortSelect.addEventListener('change', function() {
            currentPage = 1;
            loadProducts();
            updateURL();
        });
        
        // Add input event with debounce to search
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                searchQuery = searchInput.value.trim();
                currentPage = 1;
                loadProducts();
                updateURL();
            }, 500);
        });
        
        // Custom radio button functionality
        document.querySelectorAll('input[type="radio"].stock-filter').forEach(radio => {
            radio.addEventListener('change', function() {
                // First reset all radio containers
                document.querySelectorAll('.radio-container').forEach(container => {
                    const dot = container.querySelector('.radio-dot');
                    if (dot) dot.classList.add('hidden');
                });
                
                // Then show the selected one
                if (this.checked) {
                    const container = this.nextElementSibling;
                    const dot = container.querySelector('.radio-dot');
                    if (dot) dot.classList.remove('hidden');
                }
                
                // Execute the existing filter functionality
                stockFilter = this.value;
                currentPage = 1;
                loadProducts();
                updateURL();
            });
        });
        
        resetFiltersBtn.addEventListener('click', function() {
            resetFilters();
            currentPage = 1;
            loadProducts();
            updateURL();
        });

        // Add "future feature" class to elements without real functionality
        document.querySelectorAll('a[href="#"]').forEach(link => {
            if (!link.classList.contains('page-link')) {
                link.classList.add('future-feature');
            }
        });
        
        // Functions
        function resetFilters() {
            // Reset price slider
            if (priceSlider.noUiSlider) {
                priceSlider.noUiSlider.set([minPrice, maxPrice]);
                currentMinPrice = minPrice;
                currentMaxPrice = maxPrice;
            }
            
            // Reset manual price inputs
            if (manualMinPrice && manualMaxPrice) {
                manualMinPrice.value = minPrice;
                manualMaxPrice.value = maxPrice;
            }
            
            // Reset stock filter
            stockFilters[0].checked = true; // Select "All"
            stockFilter = 'all';
            
            // Reset search
            searchInput.value = '';
            searchQuery = '';
        }
        
        function updateURL() {
            const params = new URLSearchParams();
            
            if (categoryId) {
                params.set('category_id', categoryId);
            }
            
            if (searchQuery) {
                params.set('search', searchQuery);
            }
            
            if (currentPage > 1) {
                params.set('page', currentPage);
            }
            
            // Add price range to URL if not at default values
            if (currentMinPrice > minPrice || currentMaxPrice < maxPrice) {
                params.set('min_price', currentMinPrice);
                params.set('max_price', currentMaxPrice);
            }
            
            const [sortBy, sortDir] = sortSelect.value.split('-');
            if (sortBy !== 'created_at' || sortDir !== 'DESC') {
                params.set('sort_by', sortBy);
                params.set('sort_dir', sortDir);
            }
            
            if (stockFilter !== 'all') {
                params.set('stock', stockFilter);
            }
            
            const newUrl = `${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
            window.history.replaceState({}, '', newUrl);
        }
        
        async function loadProducts() {
            productGrid.innerHTML = getLoadingHTML();
            
            const [sortBy, sortDir] = sortSelect.value.split('-');
            
            // Build API URL
            let apiUrl = `api/catalog.php?page=${currentPage}&sort_by=${sortBy}&sort_dir=${sortDir}`;
            
            if (categoryId) {
                apiUrl += `&category_id=${categoryId}`;
            }
            
            if (searchQuery) {
                apiUrl += `&search=${encodeURIComponent(searchQuery)}`;
            }
            
            // Add price range parameters
            if (currentMinPrice > minPrice) {
                apiUrl += `&min_price=${currentMinPrice}`;
            }
            
            if (currentMaxPrice < maxPrice) {
                apiUrl += `&max_price=${currentMaxPrice}`;
            }
            
            if (stockFilter !== 'all') {
                apiUrl += `&stock=${stockFilter}`;
            }
            
            try {
                // Use the apiGet utility function from ajax.js
                const data = await apiGet(apiUrl);
                
                if (data.status === 'success') {
                    displayProducts(data.data.products);
                    displayPagination(data.data.pagination);
                    updateProductCount(data.data.pagination.total);
                } else {
                    productGrid.innerHTML = '<div class="col-span-3 bg-white p-8 rounded-lg shadow text-center"><p class="text-gray-500">Error loading products</p></div>';
                }
            } catch (error) {
                console.error('Error fetching products:', error);
                productGrid.innerHTML = '<div class="col-span-3 bg-white p-8 rounded-lg shadow text-center"><p class="text-gray-500">Error loading products: ' + error.message + '</p></div>';
            }
        }
        
        function getLoadingHTML() {
            let html = '';
            for (let i = 0; i < 3; i++) {
                html += `
                <div class="bg-white p-5 rounded-lg shadow border border-gray-200 animate-pulse">
                    <div class="bg-gray-300 h-48 rounded-md mb-4"></div>
                    <div class="bg-gray-300 h-4 rounded mb-2"></div>
                    <div class="bg-gray-300 h-4 w-2/3 rounded mb-4"></div>
                    <div class="bg-gray-300 h-8 w-1/3 rounded"></div>
                </div>`;
            }
            return html;
        }
        
        function displayProducts(products) {
            if (!products.length) {
                productGrid.innerHTML = `
                    <div class="col-span-3 bg-white p-8 rounded-lg shadow text-center">
                        <div class="flex flex-col items-center gap-4">
                            <i class="fas fa-search text-gray-300 text-5xl"></i>
                            <p class="text-gray-500">No products found matching your criteria.</p>
                            <button onclick="document.getElementById('resetFilters').click()" class="mt-2 text-blue-600 hover:underline">Clear all filters</button>
                        </div>
                    </div>
                `;
                return;
            }
            
            let html = '';
            
            products.forEach(product => {
                // Format price
                const formattedPrice = new Intl.NumberFormat('en-IN', {
                    style: 'currency',
                    currency: 'BDT',
                    currencyDisplay: 'symbol'
                }).format(product.selling_price);
                
                // Create size badges HTML
                let sizesHtml = '';
                if (product.sizes && product.sizes.length > 0) {
                    sizesHtml = '<div class="flex flex-wrap gap-1 mt-3 mb-3">';
                    product.sizes.forEach(size => {
                        const inStock = size.stock > 0;
                        sizesHtml += `
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md border-2 border-gray-200 text-xs font-medium ${inStock ? 'bg-white' : 'bg-gray-100 text-gray-400'}">
                                <i class="fas fa-tag text-gray-500 mr-1"></i>${size.size_name}${inStock ? ` (${size.stock})` : ''}
                            </span>`;
                    });
                    sizesHtml += '</div>';
                }
                
                // Create HTML for the product card
                html += `
                <div class="product-card bg-white rounded-lg shadow overflow-hidden border border-gray-200 relative">
                    ${product.is_new ? '<div class="badge badge-new"><i class="fas fa-star mr-1"></i> New</div>' : ''}
                    ${product.is_low_stock ? '<div class="badge badge-low"><i class="fas fa-exclamation-circle mr-1"></i> Low Stock</div>' : ''}
                    
                    <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                        ${product.image 
                            ? `<img src="${product.image}" alt="${product.name}" class="w-full h-full object-cover">`
                            : `<div class="flex items-center justify-center h-full w-full bg-gray-200"><i class="fas fa-image text-gray-400 text-4xl"></i></div>`
                        }
                    </div>
                    
                    <div class="p-5">
                        <h3 class="font-medium text-gray-900 text-lg leading-tight mb-1">${product.name}</h3>
                        <p class="text-gray-500 text-sm line-clamp-2 h-10">${product.description || 'No description available'}</p>
                        
                        ${sizesHtml}
                        
                        <div class="flex justify-between items-center mt-4">
                            <span class="font-bold text-lg">${formattedPrice}</span>
                            <button class="future-feature bg-gray-700 text-white px-3 py-2 rounded text-sm hover:bg-gray-600 transition-colors flex items-center" data-message="Shopping cart will be implemented in the future">
                                <i class="fas fa-info-circle mr-1"></i> Details
                            </button>
                        </div>
                    </div>
                </div>`;
            });
            
            productGrid.innerHTML = html;
        }
        
        function displayPagination(paginationData) {
            const { current_page, last_page, total } = paginationData;
            
            if (last_page <= 1) {
                pagination.innerHTML = '';
                return;
            }
            
            let html = '<div class="flex items-center justify-center space-x-1">';
            
            // Previous button
            if (current_page > 1) {
                html += `
                <a href="#" class="page-link flex items-center justify-center h-10 px-4 rounded-md border-2 border-gray-300 text-gray-700 hover:bg-gray-100" data-page="${current_page - 1}" aria-label="Previous">
                    <i class="fas fa-chevron-left mr-1"></i> Prev
                </a>`;
            } else {
                html += `
                <span class="flex items-center justify-center h-10 px-4 rounded-md border-2 border-gray-200 text-gray-400 cursor-not-allowed">
                    <i class="fas fa-chevron-left mr-1"></i> Prev
                </span>`;
            }
            
            // Page numbers
            const pageNumbers = getPageNumbers(current_page, last_page);
            
            pageNumbers.forEach(pageNum => {
                if (pageNum === '...') {
                    html += `
                    <span class="flex items-center justify-center h-10 w-10 text-gray-500">
                        ...
                    </span>`;
                } else {
                    const isActive = pageNum === current_page;
                    html += `
                    <a href="#" class="page-link flex items-center justify-center h-10 w-10 rounded-md 
                        ${isActive 
                            ? 'bg-gray-700 text-white border-2 border-gray-700' 
                            : 'text-gray-700 border-2 border-gray-300 hover:bg-gray-100'}" 
                        data-page="${pageNum}">
                        ${pageNum}
                    </a>`;
                }
            });
            
            // Next button
            if (current_page < last_page) {
                html += `
                <a href="#" class="page-link flex items-center justify-center h-10 px-4 rounded-md border-2 border-gray-300 text-gray-700 hover:bg-gray-100" data-page="${current_page + 1}" aria-label="Next">
                    Next <i class="fas fa-chevron-right ml-1"></i>
                </a>`;
            } else {
                html += `
                <span class="flex items-center justify-center h-10 px-4 rounded-md border-2 border-gray-200 text-gray-400 cursor-not-allowed">
                    Next <i class="fas fa-chevron-right ml-1"></i>
                </span>`;
            }
            
            html += '</div>';
            pagination.innerHTML = html;
            
            // Add event listeners to pagination links
            document.querySelectorAll('.page-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    currentPage = parseInt(this.dataset.page);
                    loadProducts();
                    updateURL();
                    window.scrollTo(0, 0);
                });
            });
        }
        
        function getPageNumbers(currentPage, lastPage) {
            const pageNumbers = [];
            
            // Always include page 1
            pageNumbers.push(1);
            
            // Add dots after page 1 if needed
            if (currentPage > 3) {
                pageNumbers.push('...');
            }
            
            // Add pages around current page
            for (let i = Math.max(2, currentPage - 1); i <= Math.min(lastPage - 1, currentPage + 1); i++) {
                pageNumbers.push(i);
            }
            
            // Add dots before last page if needed
            if (currentPage < lastPage - 2) {
                pageNumbers.push('...');
            }
            
            // Always include last page if there is more than one page
            if (lastPage > 1) {
                pageNumbers.push(lastPage);
            }
            
            return pageNumbers;
        }
        
        function updateProductCount(total) {
            productCount.innerHTML = `
                <i class="fas fa-cubes text-gray-400"></i>
                <span>${total} product${total === 1 ? '' : 's'} found</span>
            `;
        }
    });
    </script>
</body>
</html> 