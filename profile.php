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
    
    // Handle profile update
    if ($action === 'update_profile') {
        // Get form data
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        // Validate inputs
        if (empty($username) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Name and email are required']);
            exit;
        }
        
        try {
            // Check if email is already used by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'Email already in use by another user']);
                exit;
            }
            
            // Update user information
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ? WHERE id = ?");
            $result = $stmt->execute([$username, $email, $phone, $userId]);
            
            if ($result) {
                // Update session data
                $_SESSION['user_username'] = $username;
                $_SESSION['user_email'] = $email;
                
                echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
                exit;
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit;
        }
    }
    // Handle profile picture update
    else if ($action === 'update_profile_picture') {
        // Check if file was uploaded
        if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
            exit;
        }
        
        $file = $_FILES['profile_picture'];
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF and WEBP are allowed']);
            exit;
        }
        
        // Check file size
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'File is too large. Maximum size is 2MB']);
            exit;
        }
        
        try {
            // Create uploads directory if it doesn't exist
            $uploadDir = 'uploads/profile/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate a unique filename
            $filename = $userId . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $destination = $uploadDir . $filename;
            
            // Move the uploaded file
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Get the old profile picture
                $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $oldPicture = $stmt->fetchColumn();
                
                // Delete old profile picture if it exists
                if ($oldPicture && file_exists('uploads/profile/' . $oldPicture)) {
                    unlink('uploads/profile/' . $oldPicture);
                }
                
                // Update the database
                $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $result = $stmt->execute([$filename, $userId]);
                
                if ($result) {
                    // Update the session
                    $_SESSION['user_profile_picture'] = $filename;
                    
                    echo json_encode(['success' => true, 'message' => 'Profile picture updated successfully']);
                    exit;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update profile picture in database']);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
                exit;
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }
    
    // If we reach here, it's an invalid action
    if (!empty($action)) {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }
}

// Get user data for display
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Include header and sidebar
include 'header.php';
include 'sidebar.php';
?>

<main class="lg:ml-64 min-h-screen p-6 bg-gray-100">
  <!-- Toast Notification -->
  <div id="toast" class="fixed bottom-4 right-4 bg-gray-700 text-white px-4 py-2 rounded-lg shadow-lg hidden z-50"></div>

  <!-- Header -->
  <div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
      <div>
        <h2 class="text-3xl font-bold text-gray-900">My Profile</h2>
        <p class="text-gray-600 mt-1">Manage your account settings and information</p>
      </div>
      <div class="flex gap-3">
        <a href="password.php" class="flex items-center gap-2 px-4 py-2.5 bg-gray-700 text-white font-medium rounded-md hover:bg-gray-600 transition-colors">
          <i class="fas fa-key"></i>
          <span>Change Password</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Profile Section -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Profile Picture -->
    <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
      <div class="p-6 text-center">
        <div class="mb-4 relative mx-auto w-48 h-48">
          <?php if (!empty($user['profile_picture']) && file_exists('uploads/profile/' . $user['profile_picture'])): ?>
            <img src="uploads/profile/<?= htmlspecialchars($user['profile_picture']) ?>" 
                alt="Profile Picture" 
                class="w-full h-full rounded-full object-cover border-4 border-gray-200" />
          <?php else: ?>
            <div class="w-full h-full rounded-full bg-gray-300 flex items-center justify-center">
              <i class="fas fa-user text-6xl text-gray-500"></i>
            </div>
          <?php endif; ?>
          
          <button id="change-picture-btn" type="button" 
                  class="absolute bottom-0 right-0 bg-gray-700 text-white p-2 rounded-full hover:bg-gray-600">
            <i class="fas fa-camera"></i>
          </button>
        </div>
        
        <h3 class="text-xl font-semibold"><?= htmlspecialchars($user['username']) ?></h3>
        <p class="text-gray-500"><?= htmlspecialchars($user['role']) ?></p>
        
        <form id="profilePictureForm" class="hidden">
          <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="hidden" />
        </form>
      </div>
    </div>
    
    <!-- Right Column - User Information -->
    <div class="lg:col-span-2">
      <!-- Personal Information -->
      <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200 mb-6">
        <div class="p-6 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Personal Information</h3>
            <button id="edit-profile-btn" class="text-gray-700 hover:text-gray-900">
              <i class="fas fa-edit"></i> Edit
            </button>
          </div>
        </div>
        
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <p class="text-sm text-gray-500">Username</p>
              <p class="font-medium"><?= htmlspecialchars($user['username']) ?></p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Email Address</p>
              <p class="font-medium"><?= htmlspecialchars($user['email']) ?></p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Phone Number</p>
              <p class="font-medium"><?= htmlspecialchars($user['phone'] ?? 'Not set') ?></p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Role</p>
              <p class="font-medium"><?= htmlspecialchars($user['role']) ?></p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Account Information -->
      <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800">Account Information</h3>
        </div>
        
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <p class="text-sm text-gray-500">Date Joined</p>
              <p class="font-medium">
                <?= isset($user['created_at']) && $user['created_at'] ? date('F j, Y', strtotime($user['created_at'])) : 'N/A' ?>
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Last Login</p>
              <p class="font-medium">
                <?= isset($user['last_login']) && $user['last_login'] ? date('F j, Y g:i A', strtotime($user['last_login'])) : 'N/A' ?>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Edit Profile Modal -->
<div id="edit-profile-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div class="bg-white rounded-lg max-w-md w-full mx-4 p-6">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-semibold">Edit Profile</h3>
      <button id="close-modal-btn" class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <form id="updateProfileForm">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1 text-gray-700">Username</label>
          <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>"
                class="w-full border-2 border-gray-300 rounded-md py-2 px-4 focus:border-gray-700 focus:ring-1 focus:ring-gray-700" required>
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1 text-gray-700">Email Address</label>
          <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                class="w-full border-2 border-gray-300 rounded-md py-2 px-4 focus:border-gray-700 focus:ring-1 focus:ring-gray-700" required>
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1 text-gray-700">Phone Number</label>
          <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                class="w-full border-2 border-gray-300 rounded-md py-2 px-4 focus:border-gray-700 focus:ring-1 focus:ring-gray-700">
        </div>
      </div>
      
      <div class="mt-6 flex justify-end gap-3">
        <button type="button" id="cancel-edit-btn"
                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
          Cancel
        </button>
        <button type="submit"
                class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-600">
          Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

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

// Modal Control
const editModal = document.getElementById('edit-profile-modal');
const editBtn = document.getElementById('edit-profile-btn');
const closeBtn = document.getElementById('close-modal-btn');
const cancelBtn = document.getElementById('cancel-edit-btn');

editBtn.addEventListener('click', () => {
  editModal.classList.remove('hidden');
});

function closeModal() {
  editModal.classList.add('hidden');
}

closeBtn.addEventListener('click', closeModal);
cancelBtn.addEventListener('click', closeModal);

// Handle profile update
document.getElementById('updateProfileForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  formData.append('action', 'update_profile');
  
  try {
    const response = await fetch('profile.php', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      showToast('Profile updated successfully');
      closeModal();
      // Refresh the page to show updated info
      setTimeout(() => {
        location.reload();
      }, 1500);
    } else {
      showToast(result.message || 'Failed to update profile', false);
    }
  } catch (error) {
    console.error('Error updating profile:', error);
    showToast('Error updating profile', false);
  }
});

// Handle profile picture change
const pictureBtn = document.getElementById('change-picture-btn');
const pictureInput = document.getElementById('profile_picture');

pictureBtn.addEventListener('click', () => {
  pictureInput.click();
});

pictureInput.addEventListener('change', async function() {
  if (this.files.length === 0) return;
  
  const formData = new FormData();
  formData.append('action', 'update_profile_picture');
  formData.append('profile_picture', this.files[0]);
  
  try {
    const response = await fetch('profile.php', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      showToast('Profile picture updated successfully');
      // Refresh the page to show the new picture
      setTimeout(() => {
        location.reload();
      }, 1500);
    } else {
      showToast(result.message || 'Failed to update profile picture', false);
    }
  } catch (error) {
    console.error('Error updating profile picture:', error);
    showToast('Error updating profile picture', false);
  }
});
</script> 