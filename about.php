<?php
require_once 'db.php';

// Fetch all categories for the footer
$categoriesStmt = $pdo->prepare("SELECT id, name FROM categories ORDER BY name");
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll();

// Page title
$pageTitle = "About Us";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - SmartInventory</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #F3F4F6;
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
                <a href="catalog.php" class="text-gray-700 hover:text-gray-900">Home</a>
                <a href="#" class="text-gray-700 hover:text-gray-900">Products</a>
                <a href="about.php" class="text-gray-700 hover:text-gray-900 font-medium">About Us</a>
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
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-700 font-medium">About Us</span>
        </div>
    
        <!-- Page Title -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">About SmartInventory</h1>
            <p class="text-gray-600">Learn more about our mission and the team behind our inventory system</p>
        </div>
        
        <!-- About Us Content -->
        <div class="bg-white p-8 rounded-lg shadow border border-gray-200 mb-8">
            <div class="flex flex-col lg:flex-row gap-8">
                <div class="lg:w-1/2">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Our Story</h2>
                    <p class="text-gray-700 mb-4">
                        SmartInventory was founded in 2020 with a simple mission: to make inventory management accessible and efficient for businesses of all sizes. 
                        We recognized that traditional inventory systems were either too complex or too basic for most businesses, leaving a gap in the market.
                    </p>
                    <p class="text-gray-700 mb-4">
                        Our team of developers and business experts worked together to create a solution that balances powerful features with user-friendly design. 
                        Today, SmartInventory serves thousands of businesses across multiple industries, helping them streamline their operations and grow.
                    </p>
                    <h2 class="text-2xl font-bold text-gray-800 mt-8 mb-4">Our Mission</h2>
                    <p class="text-gray-700 mb-4">
                        We believe that effective inventory management should be accessible to everyone. Our mission is to empower businesses with tools that simplify 
                        complex processes, reduce errors, and provide valuable insights for better decision-making.
                    </p>
                </div>
                <div class="lg:w-1/2">
                    <div class="bg-gray-100 h-64 mb-6 rounded-lg flex items-center justify-center">
                        <i class="fas fa-building text-gray-300 text-6xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Our Values</h2>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                            <div>
                                <strong class="text-gray-900">Simplicity</strong>
                                <p class="text-gray-700">We believe that powerful software doesn't need to be complicated.</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                            <div>
                                <strong class="text-gray-900">Reliability</strong>
                                <p class="text-gray-700">Our customers depend on us for their business operations, and we take that responsibility seriously.</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                            <div>
                                <strong class="text-gray-900">Innovation</strong>
                                <p class="text-gray-700">We're constantly improving and adapting to the evolving needs of our users.</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                            <div>
                                <strong class="text-gray-900">Customer Focus</strong>
                                <p class="text-gray-700">Everything we build is designed with our users in mind.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Team Section -->
        <div class="bg-white p-8 rounded-lg shadow border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Our Team</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Team Member 1 -->
                <div class="text-center">
                    <div class="w-32 h-32 mx-auto bg-gray-300 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-user text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">John Doe</h3>
                    <p class="text-gray-600 mb-2">Founder & CEO</p>
                    <p class="text-gray-700 text-sm">15+ years of experience in software development and business management.</p>
                </div>
                
                <!-- Team Member 2 -->
                <div class="text-center">
                    <div class="w-32 h-32 mx-auto bg-gray-300 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-user text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Jane Smith</h3>
                    <p class="text-gray-600 mb-2">CTO</p>
                    <p class="text-gray-700 text-sm">Expert in cloud infrastructure and database systems with a focus on scalability.</p>
                </div>
                
                <!-- Team Member 3 -->
                <div class="text-center">
                    <div class="w-32 h-32 mx-auto bg-gray-300 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-user text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Mark Johnson</h3>
                    <p class="text-gray-600 mb-2">Head of Customer Success</p>
                    <p class="text-gray-700 text-sm">Dedicated to ensuring our customers get the most out of SmartInventory.</p>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-white py-8 border-t border-gray-200 mt-16">
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
</body>
</html> 