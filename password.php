<?php
include 'authcheck.php'; // Adjust path as needed
requireLogin();           // Ensures the user is logged in

// Include database connection
include 'db.php';

// Process AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user ID from session
    $userId = $_SESSION['user_id'];
    
    // Check action type
    $action = $_POST['action'] ?? '';
    
    // Handle password change
    if ($action === 'change_password') {
        // Get form data
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate inputs
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }
        
        // Check if passwords match
        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
            exit;
        }
        
        // Validate password strength
        $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!.#%*?&]{8,}$/';
        if (!preg_match($passwordRegex, $newPassword)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Password must be at least 8 characters with uppercase, lowercase, number, and special character'
            ]);
            exit;
        }
        
        try {
            // Get current password hash
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $currentPasswordHash = $stmt->fetchColumn();
            
            // Verify current password
            if (!password_verify($currentPassword, $currentPasswordHash)) {
                echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
                exit;
            }
            
            // Hash the new password
            $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);
            
            // Update the password
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $result = $stmt->execute([$newPasswordHash, $userId]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update password']);
                exit;
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit;
        }
    }
    
    // Invalid action
    if (!empty($action)) {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }
}

include 'header.php';
include 'sidebar.php';
?>

<main class="min-h-screen p-6 bg-gray-100">
  <!-- Toast Notification -->
  <div id="toast" class="fixed bottom-4 right-4 bg-gray-700 text-white px-4 py-2 rounded-lg shadow-lg hidden z-50"></div>

  <!-- Header -->
  <div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-bold text-gray-900">Change Password</h2>
        <p class="text-gray-600 mt-1">Update your account password</p>
      </div>
      <div class="flex gap-3">
        <a href="profile.php" class="flex items-center gap-2 px-4 py-2.5 border-2 border-gray-700 text-gray-700 font-medium rounded-md hover:bg-gray-700 hover:text-white transition-colors">
          <i class="fas fa-arrow-left"></i>
          <span>Back to Profile</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Two-column Layout -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Password Change Card - Takes 2/3 of space on large screens -->
    <div class="lg:col-span-2">
      <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800 mb-1">Password Settings</h3>
          <p class="text-sm text-gray-500">Create a strong password to protect your account</p>
        </div>
        <div class="p-6">
          <form id="changePasswordForm" class="space-y-6">
            <div>
              <label class="block text-sm font-medium mb-2 text-gray-700">Current Password</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                  <i class="fas fa-lock text-gray-400"></i>
                </div>
                <input type="password" name="current_password" id="current-password"
                      class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-gray-700 focus:ring-1 focus:ring-gray-700 transition-all" required />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-2 text-gray-700">New Password</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                  <i class="fas fa-key text-gray-400"></i>
                </div>
                <input type="password" name="new_password" id="new-password"
                      class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-gray-700 focus:ring-1 focus:ring-gray-700 transition-all" required />
              </div>
              <p class="text-xs text-gray-500 mt-1">
                Must be at least 8 characters with uppercase, lowercase, number and special character.
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium mb-2 text-gray-700">Confirm New Password</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                  <i class="fas fa-key text-gray-400"></i>
                </div>
                <input type="password" name="confirm_password" id="confirm-password"
                      class="w-full border-2 border-gray-300 rounded-md pl-10 py-2.5 px-4 focus:border-gray-700 focus:ring-1 focus:ring-gray-700 transition-all" required />
              </div>
            </div>

            <div>
              <h4 class="font-medium text-gray-700 mb-3">Password Requirements:</h4>
              <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-center" id="length-check">
                  <i class="fas fa-circle text-xs mr-2 text-gray-300"></i>
                  At least 8 characters long
                </li>
                <li class="flex items-center" id="uppercase-check">
                  <i class="fas fa-circle text-xs mr-2 text-gray-300"></i>
                  Contains uppercase letter
                </li>
                <li class="flex items-center" id="lowercase-check">
                  <i class="fas fa-circle text-xs mr-2 text-gray-300"></i>
                  Contains lowercase letter
                </li>
                <li class="flex items-center" id="number-check">
                  <i class="fas fa-circle text-xs mr-2 text-gray-300"></i>
                  Contains number
                </li>
                <li class="flex items-center" id="special-check">
                  <i class="fas fa-circle text-xs mr-2 text-gray-300"></i>
                  Contains special character
                </li>
                <li class="flex items-center" id="match-check">
                  <i class="fas fa-circle text-xs mr-2 text-gray-300"></i>
                  Passwords match
                </li>
              </ul>
            </div>

            <div class="pt-2">
              <button type="submit" 
                      class="w-full md:w-auto px-6 py-3 bg-gray-700 text-white font-medium rounded-md hover:bg-gray-600 transition-colors">
                Update Password
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Password Security Tips Card - Takes 1/3 of space on large screens -->
    <div class="lg:col-span-1">
      <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200 h-full">
        <div class="p-4 border-b border-gray-200">
          <h3 class="text-md font-semibold text-gray-800">Security Tips</h3>
        </div>
        <div class="p-4">
          <ul class="space-y-3">
            <li class="flex items-start">
              <i class="fas fa-shield-alt text-green-600 mt-1 mr-2 text-sm"></i>
              <div>
                <h4 class="font-medium text-sm text-gray-800">Use a unique password</h4>
                <p class="text-xs text-gray-600">Don't reuse passwords across multiple sites.</p>
              </div>
            </li>
            <li class="flex items-start">
              <i class="fas fa-key text-green-600 mt-1 mr-2 text-sm"></i>
              <div>
                <h4 class="font-medium text-sm text-gray-800">Create strong passwords</h4>
                <p class="text-xs text-gray-600">Mix uppercase, lowercase, numbers, and special characters.</p>
              </div>
            </li>
            <li class="flex items-start">
              <i class="fas fa-sync-alt text-green-600 mt-1 mr-2 text-sm"></i>
              <div>
                <h4 class="font-medium text-sm text-gray-800">Change regularly</h4>
                <p class="text-xs text-gray-600">Update passwords for sensitive accounts periodically.</p>
              </div>
            </li>
            <li class="flex items-start">
              <i class="fas fa-user-secret text-green-600 mt-1 mr-2 text-sm"></i>
              <div>
                <h4 class="font-medium text-sm text-gray-800">Keep passwords private</h4>
                <p class="text-xs text-gray-600">Never share passwords or store them in plain text.</p>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
// Toast notification system
function showToast(message, success = true) {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.classList.remove('bg-green-500', 'bg-red-500', 'hidden');
  toast.classList.add(success ? 'bg-green-500' : 'bg-red-500');
  
  setTimeout(() => {
    toast.classList.add('hidden');
  }, 3000);
}

// DOM Elements
const changePasswordForm = document.getElementById('changePasswordForm');
const currentPasswordInput = document.getElementById('current-password');
const newPasswordInput = document.getElementById('new-password');
const confirmPasswordInput = document.getElementById('confirm-password');

// Password requirement check elements
const lengthCheck = document.getElementById('length-check');
const uppercaseCheck = document.getElementById('uppercase-check');
const lowercaseCheck = document.getElementById('lowercase-check');
const numberCheck = document.getElementById('number-check');
const specialCheck = document.getElementById('special-check');
const matchCheck = document.getElementById('match-check');

// Function to check password criteria
function checkPasswordCriteria() {
  const password = newPasswordInput.value;
  const confirmPassword = confirmPasswordInput.value;
  
  // Check length
  if (password.length >= 8) {
    lengthCheck.classList.add('text-green-600');
    lengthCheck.querySelector('i').classList.remove('text-gray-300');
    lengthCheck.querySelector('i').classList.add('text-green-600');
  } else {
    lengthCheck.classList.remove('text-green-600');
    lengthCheck.querySelector('i').classList.remove('text-green-600');
    lengthCheck.querySelector('i').classList.add('text-gray-300');
  }
  
  // Check uppercase
  if (/[A-Z]/.test(password)) {
    uppercaseCheck.classList.add('text-green-600');
    uppercaseCheck.querySelector('i').classList.remove('text-gray-300');
    uppercaseCheck.querySelector('i').classList.add('text-green-600');
  } else {
    uppercaseCheck.classList.remove('text-green-600');
    uppercaseCheck.querySelector('i').classList.remove('text-green-600');
    uppercaseCheck.querySelector('i').classList.add('text-gray-300');
  }
  
  // Check lowercase
  if (/[a-z]/.test(password)) {
    lowercaseCheck.classList.add('text-green-600');
    lowercaseCheck.querySelector('i').classList.remove('text-gray-300');
    lowercaseCheck.querySelector('i').classList.add('text-green-600');
  } else {
    lowercaseCheck.classList.remove('text-green-600');
    lowercaseCheck.querySelector('i').classList.remove('text-green-600');
    lowercaseCheck.querySelector('i').classList.add('text-gray-300');
  }
  
  // Check number
  if (/\d/.test(password)) {
    numberCheck.classList.add('text-green-600');
    numberCheck.querySelector('i').classList.remove('text-gray-300');
    numberCheck.querySelector('i').classList.add('text-green-600');
  } else {
    numberCheck.classList.remove('text-green-600');
    numberCheck.querySelector('i').classList.remove('text-green-600');
    numberCheck.querySelector('i').classList.add('text-gray-300');
  }
  
  // Check special character
  if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
    specialCheck.classList.add('text-green-600');
    specialCheck.querySelector('i').classList.remove('text-gray-300');
    specialCheck.querySelector('i').classList.add('text-green-600');
  } else {
    specialCheck.classList.remove('text-green-600');
    specialCheck.querySelector('i').classList.remove('text-green-600');
    specialCheck.querySelector('i').classList.add('text-gray-300');
  }
  
  // Check if passwords match
  if (password && confirmPassword && password === confirmPassword) {
    matchCheck.classList.add('text-green-600');
    matchCheck.querySelector('i').classList.remove('text-gray-300');
    matchCheck.querySelector('i').classList.add('text-green-600');
  } else {
    matchCheck.classList.remove('text-green-600');
    matchCheck.querySelector('i').classList.remove('text-green-600');
    matchCheck.querySelector('i').classList.add('text-gray-300');
  }
}

// Add event listeners for real-time validation
newPasswordInput.addEventListener('input', checkPasswordCriteria);
confirmPasswordInput.addEventListener('input', checkPasswordCriteria);

// Form submission
changePasswordForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const currentPassword = currentPasswordInput.value;
  const newPassword = newPasswordInput.value;
  const confirmPassword = confirmPasswordInput.value;
  
  // Validate inputs
  if (!currentPassword || !newPassword || !confirmPassword) {
    showToast('All password fields are required', false);
    return;
  }
  
  if (newPassword !== confirmPassword) {
    showToast('New passwords do not match', false);
    return;
  }
  
  // Validate password complexity
  const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
  if (!passwordRegex.test(newPassword)) {
    showToast('Password does not meet all requirements', false);
    return;
  }
  
  try {
    const formData = new FormData(changePasswordForm);
    formData.append('action', 'change_password');
    
    const response = await fetch('password.php', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      showToast('Password changed successfully');
      changePasswordForm.reset();
      checkPasswordCriteria(); // Reset the checks
    } else {
      showToast(result.message || 'Failed to change password', false);
    }
  } catch (error) {
    console.error('Error changing password:', error);
    showToast('Error changing password', false);
  }
});
</script>

<?php include 'footer.php'; ?> 