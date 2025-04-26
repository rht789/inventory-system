<?php
require_once 'authcheck.php';
requireLogin(); // Ensure the user is logged in
?>

<?php
include 'header.php';
include 'sidebar.php';
?>

<div class="p-6 sm:ml-64 mt-14">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Password Settings</h1>
    </div>

    <!-- Main Content -->
    <div class="grid gap-6">
        <!-- Password Change Card -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6">
                <div class="max-w-3xl">
                    <!-- Section Header -->
                    <div class="mb-6">
                        <h2 class="text-lg font-medium text-gray-900">Change Password</h2>
                        <p class="mt-1 text-sm text-gray-500">Update your password to keep your account secure.</p>
                    </div>

                    <!-- Form Section -->
                    <form id="changePasswordForm" method="POST" action="api/update_password.php">
                        <div class="grid gap-6 mb-6">
                            <!-- Current Password -->
                            <div>
                                <label for="old_password" class="block text-sm font-medium text-gray-700 mb-1">
                                    Current Password
                                </label>
                                <div class="relative mt-1">
                                    <input type="password" id="old_password" name="old_password" required
                                        class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        placeholder="Enter your current password">
                                    <button type="button" class="absolute inset-y-0 right-0 flex items-center pr-3 toggle-password">
                                        <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- New Password -->
                            <div>
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">
                                    New Password
                                </label>
                                <div class="relative mt-1">
                                    <input type="password" id="new_password" name="new_password" required
                                        class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        placeholder="Enter your new password">
                                    <button type="button" class="absolute inset-y-0 right-0 flex items-center pr-3 toggle-password">
                                        <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Confirm New Password -->
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">
                                    Confirm New Password
                                </label>
                                <div class="relative mt-1">
                                    <input type="password" id="confirm_password" name="confirm_password" required
                                        class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        placeholder="Confirm your new password">
                                    <button type="button" class="absolute inset-y-0 right-0 flex items-center pr-3 toggle-password">
                                        <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Password Requirements -->
                            <div class="bg-gray-50 rounded-md p-4">
                                <h3 class="text-sm font-medium text-gray-700 mb-3">Password Requirements:</h3>
                                <ul class="space-y-2 text-sm text-gray-600" id="requirements-list">
                                    <li class="flex items-center" data-requirement="length">
                                        <span class="w-4 h-4 mr-2 flex items-center justify-center">
                                            <i class="fas fa-circle text-xs text-gray-300"></i>
                                        </span>
                                        At least 8 characters long
                                    </li>
                                    <li class="flex items-center" data-requirement="uppercase">
                                        <span class="w-4 h-4 mr-2 flex items-center justify-center">
                                            <i class="fas fa-circle text-xs text-gray-300"></i>
                                        </span>
                                        Contains uppercase letter
                                    </li>
                                    <li class="flex items-center" data-requirement="lowercase">
                                        <span class="w-4 h-4 mr-2 flex items-center justify-center">
                                            <i class="fas fa-circle text-xs text-gray-300"></i>
                                        </span>
                                        Contains lowercase letter
                                    </li>
                                    <li class="flex items-center" data-requirement="number">
                                        <span class="w-4 h-4 mr-2 flex items-center justify-center">
                                            <i class="fas fa-circle text-xs text-gray-300"></i>
                                        </span>
                                        Contains number
                                    </li>
                                    <li class="flex items-center" data-requirement="special">
                                        <span class="w-4 h-4 mr-2 flex items-center justify-center">
                                            <i class="fas fa-circle text-xs text-gray-300"></i>
                                        </span>
                                        Contains special character
                                    </li>
                                    <li class="flex items-center" data-requirement="match">
                                        <span class="w-4 h-4 mr-2 flex items-center justify-center">
                                            <i class="fas fa-circle text-xs text-gray-300"></i>
                                        </span>
                                        Passwords match
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="submit" id="submit-button"
                                class="px-4 py-2 bg-[#1e2936] text-white text-sm font-medium rounded-md hover:bg-[#2c3a47] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1e2936] transition-colors duration-200">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Password Security Tips -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-2">Password Security Tips</h2>
                <p class="text-sm text-gray-500 mb-4">Best practices for keeping your account secure</p>
                
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mt-1">
                            <i class="fas fa-shield-alt text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-900">Use a unique password</h3>
                            <p class="text-sm text-gray-500">Don't reuse passwords across multiple sites to prevent credential stuffing attacks.</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0 mt-1">
                            <i class="fas fa-key text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-900">Create strong passwords</h3>
                            <p class="text-sm text-gray-500">Combine uppercase, lowercase, numbers, and special characters to create a strong password.</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0 mt-1">
                            <i class="fas fa-sync text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-900">Change passwords regularly</h3>
                            <p class="text-sm text-gray-500">Regularly update your password, especially for accounts with sensitive information.</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0 mt-1">
                            <i class="fas fa-user-secret text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-900">Keep passwords private</h3>
                            <p class="text-sm text-gray-500">Never share your password with others or store it in plain text.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });

    // Password validation
    // To disable validation, comment out from here...
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const requirementsList = document.getElementById('requirements-list');
    const submitButton = document.getElementById('submit-button');

    function updateRequirement(requirement, isValid) {
        const listItem = requirementsList.querySelector(`[data-requirement="${requirement}"]`);
        const icon = listItem.querySelector('i');
        icon.className = isValid 
            ? 'fas fa-check-circle text-green-500' 
            : 'fas fa-circle text-xs text-gray-300';
    }

    function validatePassword() {
        const password = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        // Length check
        updateRequirement('length', password.length >= 8);

        // Uppercase letter check
        updateRequirement('uppercase', /[A-Z]/.test(password));

        // Lowercase letter check
        updateRequirement('lowercase', /[a-z]/.test(password));

        // Number check
        updateRequirement('number', /\d/.test(password));

        // Special character check
        updateRequirement('special', /[!@#$%^&*(),.?":{}|<>]/.test(password));

        // Passwords match check
        updateRequirement('match', password === confirmPassword && password !== '');

        // Check if all requirements are met
        const allRequirementsMet = Array.from(requirementsList.querySelectorAll('i'))
            .every(icon => icon.classList.contains('fa-check-circle'));

        submitButton.disabled = !allRequirementsMet;
        submitButton.classList.toggle('opacity-50', !allRequirementsMet);
        submitButton.classList.toggle('cursor-not-allowed', !allRequirementsMet);
    }

    newPasswordInput.addEventListener('input', validatePassword);
    confirmPasswordInput.addEventListener('input', validatePassword);
    // ...to here to disable validation

    // Form submission
    document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const form = this;
        const formData = new FormData(form);

        try {
            const response = await fetch('api/updatepassword.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            // Create notification function
            const showNotification = (message, type) => {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white px-6 py-3 rounded-md shadow-lg z-50 transform transition-all duration-300 ease-in-out`;
                notification.textContent = message;
                document.body.appendChild(notification);
                
                // Fade out and remove
                setTimeout(() => {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateY(-100%)';
                    setTimeout(() => notification.remove(), 300);
                }, 2700);
            };

            if (response.ok && result.success) {
                showNotification(result.success, 'success');
                form.reset();
                // Reset requirements after successful submission
                requirementsList.querySelectorAll('i').forEach(icon => {
                    icon.className = 'fas fa-circle text-xs text-gray-300';
                });
            } else {
                showNotification(result.error || 'Something went wrong.', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('An unexpected error occurred.', 'error');
        }
    });
});
</script>





