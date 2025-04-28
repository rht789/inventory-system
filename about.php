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
            <h2 class="text-2xl font-bold text-gray-800 mb-8 text-center">Our Team</h2>
            <div class="relative overflow-hidden">
                <div class="team-scroll">
                    <div class="team-track flex gap-6">
                        <!-- Team Member 1 -->
                        <div class="flex-none w-80">
                            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden h-[400px]">
                                <div class="relative h-72 overflow-hidden">
                                    <img src="uploads/images/team/rezaul.jpg" 
                                         alt="Md. Rezaul Karim" 
                                         class="w-full h-full object-cover scale-105 hover:scale-108 transition-transform duration-500"
                                         onerror="this.onerror=null; this.src='uploads/images/default-user.jpg';">
                                </div>
                                <div class="p-4 bg-white">
                                    <h3 class="text-xl font-bold text-gray-800 mb-1">Md. Rezaul Karim</h3>
                                    <p class="text-gray-600 mb-2">Founder & CEO</p>
                                    <p class="text-gray-600 text-sm">Leading innovation in inventory management systems with over 15 years of experience.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Team Member 2 -->
                        <div class="flex-none w-80">
                            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden h-[400px]">
                                <div class="relative h-72 overflow-hidden">
                                    <img src="uploads/images/team/zahidul.jpg" 
                                         alt="Zahidul Hasan Sajjad" 
                                         class="w-full h-full object-cover scale-105 hover:scale-108 transition-transform duration-500"
                                         onerror="this.onerror=null; this.src='uploads/images/default-user.jpg';">
                                </div>
                                <div class="p-4 bg-white">
                                    <h3 class="text-xl font-bold text-gray-800 mb-1">Zahidul Hasan Sajjad</h3>
                                    <p class="text-gray-600 mb-2">CTO</p>
                                    <p class="text-gray-600 text-sm">Expert in cloud infrastructure and innovative technology solutions.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Team Member 3 -->
                        <div class="flex-none w-80">
                            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden h-[400px]">
                                <div class="relative h-72 overflow-hidden">
                                    <img src="uploads/images/team/abdullah.jpg" 
                                         alt="Abdullah AL Shahariya Hasnat" 
                                         class="w-full h-full object-cover scale-105 hover:scale-108 transition-transform duration-500"
                                         onerror="this.onerror=null; this.src='uploads/images/default-user.jpg';">
                                </div>
                                <div class="p-4 bg-white">
                                    <h3 class="text-xl font-bold text-gray-800 mb-1">Abdullah AL Shahariya Hasnat</h3>
                                    <p class="text-gray-600 mb-2">Head of Customer Success</p>
                                    <p class="text-gray-600 text-sm">Dedicated to ensuring exceptional customer experience and satisfaction.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Team Member 4 -->
                        <div class="flex-none w-80">
                            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden h-[400px]">
                                <div class="relative h-72 overflow-hidden">
                                    <img src="uploads/images/team/sunny.jpg" 
                                         alt="Md Siddiqur Rahman Sunny" 
                                         class="w-full h-full object-cover scale-105 hover:scale-108 transition-transform duration-500"
                                         onerror="this.onerror=null; this.src='uploads/images/default-user.jpg';">
                                </div>
                                <div class="p-4 bg-white">
                                    <h3 class="text-xl font-bold text-gray-800 mb-1">Md Siddiqur Rahman Sunny</h3>
                                    <p class="text-gray-600 mb-2">Lead Developer</p>
                                    <p class="text-gray-600 text-sm">Driving technical excellence and innovation in our development team.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Team Member 5 -->
                        <div class="flex-none w-80">
                            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden h-[400px]">
                                <div class="relative h-72 overflow-hidden">
                                    <img src="uploads/images/team/mizanur.jpg" 
                                         alt="Md. Mizanu Rahman" 
                                         class="w-full h-full object-cover scale-105 hover:scale-108 transition-transform duration-500"
                                         onerror="this.onerror=null; this.src='uploads/images/default-user.jpg';">
                                </div>
                                <div class="p-4 bg-white">
                                    <h3 class="text-xl font-bold text-gray-800 mb-1">Md. Mizanu Rahman</h3>
                                    <p class="text-gray-600 mb-2">Senior System Architect</p>
                                    <p class="text-gray-600 text-sm">Architecting robust and scalable solutions for complex business needs.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Section -->
        <div class="bg-white p-8 rounded-lg shadow border border-gray-200 mt-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Visit Us</h2>
            <div class="flex flex-col md:flex-row items-center gap-8">
                <div class="w-full md:w-1/2 text-center md:text-left">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <i class="fas fa-building text-gray-400 text-4xl mb-4"></i>
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Our Office</h3>
                        <div class="space-y-2 text-gray-600">
                            <p>123 Business Avenue</p>
                            <p>Dhaka, Bangladesh</p>
                            <div class="flex items-center justify-center md:justify-start gap-4 mt-4">
                                <a href="tel:+8801711123456" class="text-gray-600 hover:text-gray-900">
                                    <i class="fas fa-phone mr-2"></i>+880 1711 123456
                                </a>
                                <span class="text-gray-300">|</span>
                                <a href="mailto:info@smartinventory.com" class="text-gray-600 hover:text-gray-900">
                                    <i class="fas fa-envelope mr-2"></i>Email Us
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-full md:w-1/2 text-center md:text-left">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <i class="fas fa-clock text-gray-400 text-4xl mb-4"></i>
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Business Hours</h3>
                        <div class="space-y-2 text-gray-600">
                            <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                            <p>Saturday: 10:00 AM - 4:00 PM</p>
                            <p>Sunday: Closed</p>
                            <div class="mt-4">
                                <a href="https://www.google.com/maps/dir//Dhaka" 
                                   target="_blank"
                                   class="inline-flex items-center gap-2 bg-gray-700 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors">
                                    <i class="fas fa-directions"></i>
                                    Get Directions
                                </a>
                            </div>
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

    <style>
        .team-scroll {
            overflow: hidden;
            position: relative;
            width: 100%;
        }

        .team-track {
            animation: scroll 25s linear infinite;
            width: calc(320px * 10 + 1.5rem * 10);
        }

        @keyframes scroll {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(calc(-320px * 5 - 1.5rem * 5));
            }
        }

        .team-track:hover {
            animation-play-state: paused;
        }

        @media (prefers-reduced-motion: reduce) {
            .team-track {
                animation-play-state: paused;
            }
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const track = document.querySelector('.team-track');
            const originalContent = track.innerHTML;
            track.innerHTML = originalContent + originalContent;
        });
    </script>
</body>
</html> 