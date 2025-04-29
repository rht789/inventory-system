<!-- footer.php -->
<footer class="bg-white border-t border-gray-200 py-6 w-full z-40">
    <div class="container mx-auto px-6">
        <!-- Footer content -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <!-- Quick links -->
            <div class="flex flex-wrap justify-center gap-6">
                <a href="index.php" class="text-gray-600 hover:text-gray-900 text-sm transition-colors">Home</a>
                <a href="about.php" class="text-gray-600 hover:text-gray-900 text-sm transition-colors">About</a>
                <a href="contact.php" class="text-gray-600 hover:text-gray-900 text-sm transition-colors">Contact</a>
                <a href="catalog.php" class="text-gray-600 hover:text-gray-900 text-sm transition-colors">Products</a>
            </div>
            
            <!-- Social media -->
            <div class="flex items-center space-x-4">
                <a href="#" class="text-gray-400 hover:text-gray-700 transition-colors">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="#" class="text-gray-400 hover:text-gray-700 transition-colors">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="text-gray-400 hover:text-gray-700 transition-colors">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="text-gray-400 hover:text-gray-700 transition-colors">
                    <i class="fab fa-linkedin"></i>
                </a>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="text-center text-gray-500 text-sm mt-6 pt-4 border-t border-gray-100">
            <p>&copy; <?php echo date('Y'); ?> SmartInventory. All rights reserved.</p>
            <p class="mt-1">Developed by <span class="font-medium text-gray-700">Team Alpha</span></p>
        </div>
    </div>
</footer>
