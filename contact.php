<?php
require_once 'db.php';

// Fetch all categories for the footer
$categoriesStmt = $pdo->prepare("SELECT id, name FROM categories ORDER BY name");
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll();

// Page title
$pageTitle = "Contact Us";
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
                <a href="about.php" class="text-gray-700 hover:text-gray-900">About Us</a>
                <a href="contact.php" class="text-gray-700 hover:text-gray-900 font-medium">Contact Us</a>
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
            <span class="text-gray-700 font-medium">Contact Us</span>
        </div>
    
        <!-- Page Title -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Contact Us</h1>
            <p class="text-gray-600">Have questions? We're here to help. Reach out to us using any of the methods below.</p>
        </div>
        
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Contact Information -->
            <div class="w-full lg:w-1/3 space-y-6">
                <!-- Address Card -->
                <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                    <div class="flex items-start">
                        <div class="bg-gray-100 rounded-full p-3 mr-4">
                            <i class="fas fa-map-marker-alt text-gray-700"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 mb-1">Visit Us</h3>
                            <p class="text-gray-700">
                                SmartInventory Inc.<br>
                                123 Business Avenue<br>
                                Dhaka, Bangladesh
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Email Card -->
                <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                    <div class="flex items-start">
                        <div class="bg-gray-100 rounded-full p-3 mr-4">
                            <i class="fas fa-envelope text-gray-700"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 mb-1">Email Us</h3>
                            <p class="text-gray-700 mb-1">
                                <span class="font-medium">General Inquiries:</span><br>
                                <a href="mailto:info@smartinventory.com" class="text-blue-600 hover:underline">info@smartinventory.com</a>
                            </p>
                            <p class="text-gray-700 mb-1">
                                <span class="font-medium">Support:</span><br>
                                <a href="mailto:support@smartinventory.com" class="text-blue-600 hover:underline">support@smartinventory.com</a>
                            </p>
                            <p class="text-gray-700">
                                <span class="font-medium">Sales:</span><br>
                                <a href="mailto:sales@smartinventory.com" class="text-blue-600 hover:underline">sales@smartinventory.com</a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Phone Card -->
                <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                    <div class="flex items-start">
                        <div class="bg-gray-100 rounded-full p-3 mr-4">
                            <i class="fas fa-phone-alt text-gray-700"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 mb-1">Call Us</h3>
                            <p class="text-gray-700 mb-1">
                                <span class="font-medium">Main Office:</span><br>
                                <a href="tel:+8801711123456" class="text-blue-600 hover:underline">+880 1711 123456</a>
                            </p>
                            <p class="text-gray-700">
                                <span class="font-medium">Customer Support:</span><br>
                                <a href="tel:+8801711654321" class="text-blue-600 hover:underline">+880 1711 654321</a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Business Hours Card -->
                <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                    <div class="flex items-start">
                        <div class="bg-gray-100 rounded-full p-3 mr-4">
                            <i class="fas fa-clock text-gray-700"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 mb-1">Business Hours</h3>
                            <div class="text-gray-700">
                                <div class="flex justify-between mb-1">
                                    <span>Monday - Friday:</span>
                                    <span>9:00 AM - 6:00 PM</span>
                                </div>
                                <div class="flex justify-between mb-1">
                                    <span>Saturday:</span>
                                    <span>10:00 AM - 4:00 PM</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Sunday:</span>
                                    <span>Closed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="w-full lg:w-2/3">
                <div class="bg-white p-8 rounded-lg shadow border border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Send Us a Message</h2>
                    
                    <form action="#" method="post" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
                                <input type="text" id="name" name="name" class="w-full border-2 border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:border-gray-500 focus:ring-1 focus:ring-gray-500" required>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" id="email" name="email" class="w-full border-2 border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:border-gray-500 focus:ring-1 focus:ring-gray-500" required>
                            </div>
                        </div>
                        
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <input type="text" id="subject" name="subject" class="w-full border-2 border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:border-gray-500 focus:ring-1 focus:ring-gray-500" required>
                        </div>
                        
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Your Message</label>
                            <textarea id="message" name="message" rows="6" class="w-full border-2 border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:border-gray-500 focus:ring-1 focus:ring-gray-500" required></textarea>
                        </div>
                        
                        <div class="flex items-start">
                            <input type="checkbox" id="privacy" name="privacy" class="mt-1 mr-2" required>
                            <label for="privacy" class="text-sm text-gray-700">
                                I agree to the <a href="#" class="text-blue-600 hover:underline">privacy policy</a> and consent to being contacted by SmartInventory.
                            </label>
                        </div>
                        
                        <div>
                            <button type="submit" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-md transition-colors font-medium">
                                <i class="fas fa-paper-plane mr-2"></i> Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Location Info -->
        <div class="mt-8 bg-white p-8 rounded-lg shadow border border-gray-200">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Our Location</h2>
            <div class="flex flex-col md:flex-row items-center justify-center gap-8 text-center md:text-left">
                <div class="w-full md:w-1/2">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <i class="fas fa-building text-gray-400 text-4xl mb-4"></i>
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Main Office</h3>
                        <div class="space-y-2 text-gray-600">
                            <p>123 Business Avenue</p>
                            <p>Dhaka, Bangladesh</p>
                            <p class="pt-2">
                                <a href="https://www.google.com/maps/dir//Dhaka" 
                                   target="_blank"
                                   class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700">
                                    <i class="fas fa-directions"></i>
                                    Get Directions
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="w-full md:w-1/2">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <i class="fas fa-clock text-gray-400 text-4xl mb-4"></i>
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Business Hours</h3>
                        <div class="space-y-2 text-gray-600">
                            <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                            <p>Saturday: 10:00 AM - 4:00 PM</p>
                            <p>Sunday: Closed</p>
                        </div>
                    </div>
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