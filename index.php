<?php
require_once 'db.php';
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Inventory - Complete Inventory Management Solution</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(90deg, #374151, #4B5563);
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-boxes text-gray-700 text-3xl mr-2"></i>
                        <span class="text-xl font-bold text-gray-800">Smart Inventory</span>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="#features" class="text-gray-600 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">Features</a>
                    <a href="#benefits" class="text-gray-600 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">Benefits</a>
                    <a href="#faq" class="text-gray-600 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">FAQ</a>
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="ml-4 bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-medium">Dashboard</a>
                    <?php else: ?>
                        <a href="login.php" class="ml-4 bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-medium">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="gradient-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold text-white leading-tight">
                        Streamline Your Inventory Management
                    </h1>
                    <p class="mt-4 text-xl text-gray-100">
                        Powerful, intuitive, and comprehensive inventory solution for businesses of all sizes.
                    </p>
                    <div class="mt-8">
                        <a href="login.php" class="bg-white text-gray-700 hover:bg-gray-100 px-6 py-3 rounded-md text-lg font-medium shadow-md">
                            Get Started
                        </a>
                        <a href="#demo" class="ml-4 text-white border border-white hover:bg-gray-600 px-6 py-3 rounded-md text-lg font-medium">
                            Watch Demo
                        </a>
                    </div>
                </div>
                <div class="hidden md:block">
                    <img src="https://via.placeholder.com/600x400?text=Inventory+Dashboard" alt="Inventory Dashboard" class="rounded-lg shadow-xl">
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="bg-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="p-4">
                    <p class="text-4xl font-bold text-gray-700">99.9%</p>
                    <p class="text-gray-600 mt-2">Uptime</p>
                </div>
                <div class="p-4">
                    <p class="text-4xl font-bold text-gray-700">500+</p>
                    <p class="text-gray-600 mt-2">Businesses</p>
                </div>
                <div class="p-4">
                    <p class="text-4xl font-bold text-gray-700">42%</p>
                    <p class="text-gray-600 mt-2">Cost Reduction</p>
                </div>
                <div class="p-4">
                    <p class="text-4xl font-bold text-gray-700">24/7</p>
                    <p class="text-gray-600 mt-2">Support</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <section id="features" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800">Powerful Features</h2>
                <p class="mt-4 text-xl text-gray-600">Everything you need to manage your inventory efficiently</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 feature-card">
                    <div class="bg-gray-100 rounded-full p-3 w-12 h-12 flex items-center justify-center mb-4">
                        <i class="fas fa-box text-gray-700 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Inventory Tracking</h3>
                    <p class="mt-2 text-gray-600">Real-time tracking of all your products across multiple locations.</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 feature-card">
                    <div class="bg-gray-100 rounded-full p-3 w-12 h-12 flex items-center justify-center mb-4">
                        <i class="fas fa-chart-line text-gray-700 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Sales Analytics</h3>
                    <p class="mt-2 text-gray-600">Comprehensive insights into your sales performance and trends.</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 feature-card">
                    <div class="bg-gray-100 rounded-full p-3 w-12 h-12 flex items-center justify-center mb-4">
                        <i class="fas fa-users text-gray-700 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Customer Management</h3>
                    <p class="mt-2 text-gray-600">Keep track of your customers and their purchase history.</p>
                </div>
                
                <!-- Feature 4 -->
                <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 feature-card">
                    <div class="bg-gray-100 rounded-full p-3 w-12 h-12 flex items-center justify-center mb-4">
                        <i class="fas fa-barcode text-gray-700 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Barcode Integration</h3>
                    <p class="mt-2 text-gray-600">Generate and scan barcodes for efficient inventory management.</p>
                </div>
                
                <!-- Feature 5 -->
                <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 feature-card">
                    <div class="bg-gray-100 rounded-full p-3 w-12 h-12 flex items-center justify-center mb-4">
                        <i class="fas fa-bell text-gray-700 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Low Stock Alerts</h3>
                    <p class="mt-2 text-gray-600">Automatic notifications when inventory levels are running low.</p>
                </div>
                
                <!-- Feature 6 -->
                <div class="bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 feature-card">
                    <div class="bg-gray-100 rounded-full p-3 w-12 h-12 flex items-center justify-center mb-4">
                        <i class="fas fa-file-alt text-gray-700 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Reports Generation</h3>
                    <p class="mt-2 text-gray-600">Generate detailed reports for business insights and decision making.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800">Why Choose Smart Inventory?</h2>
                <p class="mt-4 text-xl text-gray-600">Benefits that drive success for your business</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div>
                    <img src="https://via.placeholder.com/600x400?text=Happy+Business+Owner" alt="Benefits" class="rounded-lg shadow-xl">
                </div>
                <div>
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="bg-gray-100 rounded-full p-2 mr-4 mt-1">
                                <i class="fas fa-check text-gray-700"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-800">Increased Efficiency</h3>
                                <p class="mt-2 text-gray-600">Streamline your inventory processes and reduce manual work by up to 75%.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-gray-100 rounded-full p-2 mr-4 mt-1">
                                <i class="fas fa-check text-gray-700"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-800">Cost Reduction</h3>
                                <p class="mt-2 text-gray-600">Lower operational costs by preventing overstocking and stockouts.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-gray-100 rounded-full p-2 mr-4 mt-1">
                                <i class="fas fa-check text-gray-700"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-800">Data-Driven Decisions</h3>
                                <p class="mt-2 text-gray-600">Make informed business decisions based on accurate inventory data and analytics.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-gray-100 rounded-full p-2 mr-4 mt-1">
                                <i class="fas fa-check text-gray-700"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-800">Scalability</h3>
                                <p class="mt-2 text-gray-600">Easily scale your inventory management as your business grows.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800">How It Works</h2>
                <p class="mt-4 text-xl text-gray-600">Simple steps to transform your inventory management</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-gray-100 rounded-full h-20 w-20 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-gray-700">1</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Set Up Your Inventory</h3>
                    <p class="mt-2 text-gray-600">Quickly import your existing inventory or add products one by one.</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-gray-100 rounded-full h-20 w-20 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-gray-700">2</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Manage Daily Operations</h3>
                    <p class="mt-2 text-gray-600">Track sales, purchases, and movements with an intuitive interface.</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-gray-100 rounded-full h-20 w-20 flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-gray-700">3</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Analyze and Optimize</h3>
                    <p class="mt-2 text-gray-600">Get insights from reports and analytics to optimize your inventory.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800">Frequently Asked Questions</h2>
                <p class="mt-4 text-xl text-gray-600">Find answers to common questions about Smart Inventory</p>
            </div>
            
            <div class="max-w-3xl mx-auto space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <button class="flex justify-between items-center w-full text-left focus:outline-none">
                        <h3 class="text-lg font-semibold text-gray-800">How easy is it to migrate from my current system?</h3>
                        <i class="fas fa-chevron-down text-gray-400"></i>
                    </button>
                    <div class="mt-2">
                        <p class="text-gray-600">Our migration specialists make it easy to import your existing inventory data. We support imports from Excel, CSV, and most popular inventory systems. The typical migration takes just a few hours.</p>
                    </div>
                </div>
                
                <div class="border-b border-gray-200 pb-4">
                    <button class="flex justify-between items-center w-full text-left focus:outline-none">
                        <h3 class="text-lg font-semibold text-gray-800">Is there a mobile app available?</h3>
                        <i class="fas fa-chevron-down text-gray-400"></i>
                    </button>
                    <div class="mt-2">
                        <p class="text-gray-600">Yes, Smart Inventory is available on both iOS and Android platforms. The mobile app allows you to scan barcodes, check inventory levels, and process sales on the go.</p>
                    </div>
                </div>
                
                <div class="border-b border-gray-200 pb-4">
                    <button class="flex justify-between items-center w-full text-left focus:outline-none">
                        <h3 class="text-lg font-semibold text-gray-800">Can I integrate with my accounting software?</h3>
                        <i class="fas fa-chevron-down text-gray-400"></i>
                    </button>
                    <div class="mt-2">
                        <p class="text-gray-600">Smart Inventory integrates seamlessly with popular accounting software like QuickBooks, Xero, and Sage. This ensures your financial data stays synchronized with your inventory management.</p>
                    </div>
                </div>
                
                <div class="border-b border-gray-200 pb-4">
                    <button class="flex justify-between items-center w-full text-left focus:outline-none">
                        <h3 class="text-lg font-semibold text-gray-800">How secure is my data?</h3>
                        <i class="fas fa-chevron-down text-gray-400"></i>
                    </button>
                    <div class="mt-2">
                        <p class="text-gray-600">We take security seriously. All data is encrypted both in transit and at rest. We perform regular security audits and backups to ensure your valuable business information is protected at all times.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 gradient-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white">Ready to Transform Your Inventory Management?</h2>
            <p class="mt-4 text-xl text-gray-100 max-w-2xl mx-auto">Join thousands of businesses already using Smart Inventory to streamline operations and boost profitability.</p>
            <div class="mt-8">
                <a href="login.php" class="bg-white text-gray-700 hover:bg-gray-100 px-6 py-3 rounded-md text-lg font-medium shadow-md">Get Started Today</a>
                <a href="#demo" class="ml-4 text-white border border-white hover:bg-gray-600 px-6 py-3 rounded-md text-lg font-medium">Request Demo</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-boxes text-gray-400 text-2xl mr-2"></i>
                        <span class="text-xl font-bold">Smart Inventory</span>
                    </div>
                    <p class="text-gray-400">Complete inventory management solution for businesses of all sizes.</p>
                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Product</h3>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-gray-400 hover:text-white">Features</a></li>
                        <li><a href="#benefits" class="text-gray-400 hover:text-white">Benefits</a></li>
                        <li><a href="#faq" class="text-gray-400 hover:text-white">FAQ</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Integrations</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Support</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Documentation</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">API Status</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-8 pt-8 border-t border-gray-700 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> Smart Inventory. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Simple JavaScript for FAQ toggle -->
    <script>
        // Toggle FAQ answers
        document.querySelectorAll('#faq button').forEach(button => {
            button.addEventListener('click', () => {
                const content = button.nextElementSibling;
                const icon = button.querySelector('i');
                
                content.classList.toggle('hidden');
                icon.classList.toggle('fa-chevron-down');
                icon.classList.toggle('fa-chevron-up');
            });
        });
    </script>

<?php include 'footer.php'; ?>
</body>
</html>