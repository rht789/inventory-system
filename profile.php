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
        
        // Get new fields
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $bio = $_POST['bio'] ?? '';
        $preferredLanguage = $_POST['preferred_language'] ?? '';
        
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
            
            // Update user information with new fields
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ?, first_name = ?, last_name = ?, bio = ?, preferred_language = ? WHERE id = ?");
            $result = $stmt->execute([$username, $email, $phone, $firstName, $lastName, $bio, $preferredLanguage, $userId]);
            
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
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'File is too large. Maximum size is 5MB']);
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

<main class="lg:ml-64 min-h-screen bg-gray-100 p-0">
  <!-- Toast Notification -->
  <div id="toast" class="fixed bottom-4 right-4 bg-gray-700 text-white px-4 py-2 rounded-lg shadow-lg hidden z-50"></div>

  <!-- Hero Banner -->
  <div class="bg-gray-700 text-white">
    <div class="container mx-auto px-6 py-12">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-center gap-5">
          <div class="relative">
            <div class="w-20 h-20 md:w-24 md:h-24 overflow-hidden bg-gray-600 rounded-full border-4 border-gray-500 shadow-lg">
              <?php if (!empty($user['profile_picture']) && file_exists('uploads/profile/' . $user['profile_picture'])): ?>
                <img src="uploads/profile/<?= htmlspecialchars($user['profile_picture']) ?>" 
                     alt="Profile Picture" 
                     class="w-full h-full object-cover" />
              <?php else: ?>
                <div class="w-full h-full">
                  <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=random&color=fff&size=128" 
                       alt="Profile Picture"
                       class="w-full h-full object-cover" />
                </div>
              <?php endif; ?>
            </div>
            <button id="change-picture-btn" class="absolute -bottom-1 -right-1 bg-white text-gray-700 rounded-full p-2 shadow-md hover:bg-gray-200 transition-colors">
              <i class="fas fa-camera"></i>
            </button>
          </div>
          
          <div>
            <h1 class="text-2xl md:text-3xl font-bold">
              <?php if (!empty($user['first_name']) && !empty($user['last_name'])): ?>
                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
              <?php else: ?>
                <?= htmlspecialchars($user['username']) ?>
              <?php endif; ?>
            </h1>
            <div class="flex items-center gap-2 mt-1">
              <span class="bg-purple-600 text-xs font-medium px-2.5 py-0.5 rounded-full">
                <?= htmlspecialchars(ucfirst($user['role'])) ?>
              </span>
              <span class="text-gray-300 text-sm flex items-center gap-1">
                <i class="fas fa-circle text-green-500 text-xs"></i> Active
              </span>
            </div>
            <p class="text-gray-300 text-sm mt-1">
              Member since <?= isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A' ?>
            </p>
          </div>
        </div>
        
        <div class="flex flex-wrap gap-3">
          <button id="edit-profile-btn" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded-lg transition-colors flex items-center gap-2">
            <i class="fas fa-edit"></i>
            <span>Edit Profile</span>
          </button>
          <a href="password.php" class="px-4 py-2 bg-gray-800 hover:bg-gray-900 rounded-lg transition-colors flex items-center gap-2">
            <i class="fas fa-key"></i>
            <span>Change Password</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Content Area -->
  <div class="container mx-auto px-6 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Contact Information Card -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-4">
          <h2 class="text-lg font-semibold text-gray-800">Contact Information</h2>
        </div>
        <div class="p-6">
          <div class="space-y-6">
            <!-- Email -->
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 mt-1">
                <div class="w-10 h-10 rounded-md bg-blue-100 text-blue-600 flex items-center justify-center">
                  <i class="fas fa-envelope"></i>
                </div>
              </div>
              <div>
                <h3 class="text-sm font-medium text-gray-500">Email Address</h3>
                <p class="mt-1 text-gray-900"><?= htmlspecialchars($user['email']) ?></p>
              </div>
            </div>
            
            <!-- Phone -->
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 mt-1">
                <div class="w-10 h-10 rounded-md bg-green-100 text-green-600 flex items-center justify-center">
                  <i class="fas fa-phone"></i>
                </div>
              </div>
              <div>
                <h3 class="text-sm font-medium text-gray-500">Phone Number</h3>
                <p class="mt-1 text-gray-900"><?= htmlspecialchars($user['phone'] ?? 'Not set') ?></p>
              </div>
            </div>
            
            <!-- Social Media Icons -->
            <div class="pt-2">
              <h3 class="text-sm font-medium text-gray-500 mb-3">Social Media</h3>
              <div class="flex items-center gap-4">
                <a href="#" class="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center hover:bg-blue-600 transition-colors">
                  <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="w-10 h-10 rounded-full bg-pink-500 text-white flex items-center justify-center hover:bg-pink-600 transition-colors">
                  <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="w-10 h-10 rounded-full bg-sky-500 text-white flex items-center justify-center hover:bg-gray-800 transition-colors">
                  <i class="fab fa-twitter"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Personal Information Card -->
      <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-4">
          <h2 class="text-lg font-semibold text-gray-800">Account Information</h2>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- First Name -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="flex items-center gap-3 mb-1">
                <i class="fas fa-user text-gray-700"></i>
                <h3 class="text-sm font-medium text-gray-500">First Name</h3>
              </div>
              <p class="font-medium text-gray-900 pl-9"><?= htmlspecialchars($user['first_name'] ?? 'Not set') ?></p>
            </div>
            
            <!-- Last Name -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="flex items-center gap-3 mb-1">
                <i class="fas fa-user text-gray-700"></i>
                <h3 class="text-sm font-medium text-gray-500">Last Name</h3>
              </div>
              <p class="font-medium text-gray-900 pl-9"><?= htmlspecialchars($user['last_name'] ?? 'Not set') ?></p>
            </div>
            
            <!-- Username Info -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="flex items-center gap-3 mb-1">
                <i class="fas fa-id-badge text-gray-700"></i>
                <h3 class="text-sm font-medium text-gray-500">Username</h3>
              </div>
              <p class="font-medium text-gray-900 pl-9"><?= htmlspecialchars($user['username']) ?></p>
            </div>
            
            <!-- Preferred Language -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="flex items-center gap-3 mb-1">
                <i class="fas fa-language text-gray-700"></i>
                <h3 class="text-sm font-medium text-gray-500">Preferred Language</h3>
              </div>
              <p class="font-medium text-gray-900 pl-9"><?= htmlspecialchars($user['preferred_language'] ?? 'Not set') ?></p>
            </div>
            
            <!-- Role Info -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="flex items-center gap-3 mb-1">
                <i class="fas fa-user-shield text-gray-700"></i>
                <h3 class="text-sm font-medium text-gray-500">Account Role</h3>
              </div>
              <p class="font-medium text-gray-900 pl-9 capitalize"><?= htmlspecialchars($user['role']) ?></p>
            </div>
            
            <!-- Last Login -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="flex items-center gap-3 mb-1">
                <i class="fas fa-history text-gray-700"></i>
                <h3 class="text-sm font-medium text-gray-500">Last Login Time</h3>
              </div>
              <p class="font-medium text-gray-900 pl-9">
                <?= isset($user['last_login']) ? date('M d, Y g:i A', strtotime($user['last_login'])) : 'N/A' ?>
              </p>
            </div>
          </div>
          
          <!-- Bio Section -->
          <?php if (!empty($user['bio'])): ?>
          <div class="mt-6 bg-gray-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-gray-500 mb-2 flex items-center gap-2">
              <i class="fas fa-quote-left text-gray-700"></i> Bio
            </h3>
            <p class="text-gray-800 italic"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Hidden file input for profile picture -->
  <form id="profilePictureForm" class="hidden">
    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="hidden" />
  </form>
</main>

<!-- Edit Profile Modal -->
<div id="edit-profile-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div class="bg-white rounded-lg max-w-md w-full mx-4 shadow-xl">
    <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-800">Edit Profile</h3>
      <button id="close-modal-btn" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <div class="p-6">
      <form id="updateProfileForm" class="space-y-5">
        <!-- Personal Information Section -->
        <div class="pb-4 mb-4 border-b border-gray-200">
          <h4 class="font-medium text-gray-700 mb-3">Personal Information</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- First Name -->
            <div>
              <label class="block text-sm font-medium mb-1 text-gray-700">First Name</label>
              <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>"
                   class="w-full border rounded-lg py-2 px-3 focus:border-gray-700 focus:ring-1 focus:ring-gray-700">
            </div>
            
            <!-- Last Name -->
            <div>
              <label class="block text-sm font-medium mb-1 text-gray-700">Last Name</label>
              <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>"
                   class="w-full border rounded-lg py-2 px-3 focus:border-gray-700 focus:ring-1 focus:ring-gray-700">
            </div>
          </div>
        </div>
        
        <!-- Account Information Section -->
        <div class="pb-4 mb-4 border-b border-gray-200">
          <h4 class="font-medium text-gray-700 mb-3">Account Information</h4>
          
          <!-- Username -->
          <div class="mb-4">
            <label class="block text-sm font-medium mb-1 text-gray-700">Username</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                <i class="fas fa-user"></i>
              </div>
              <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>"
                    class="w-full border rounded-lg py-2.5 pl-10 pr-4 focus:border-gray-700 focus:ring-1 focus:ring-gray-700" required>
            </div>
          </div>

          <!-- Email -->
          <div class="mb-4">
            <label class="block text-sm font-medium mb-1 text-gray-700">Email Address</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                <i class="fas fa-envelope"></i>
              </div>
              <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                    class="w-full border rounded-lg py-2.5 pl-10 pr-4 focus:border-gray-700 focus:ring-1 focus:ring-gray-700" required>
            </div>
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-sm font-medium mb-1 text-gray-700">Phone Number</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                <i class="fas fa-phone"></i>
              </div>
              <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                    class="w-full border rounded-lg py-2.5 pl-10 pr-4 focus:border-gray-700 focus:ring-1 focus:ring-gray-700">
            </div>
          </div>
        </div>
        
        <!-- Preferences Section -->
        <div class="pb-4 mb-4 border-b border-gray-200">
          <h4 class="font-medium text-gray-700 mb-3">Preferences</h4>
          
          <!-- Preferred Language -->
          <div>
            <label class="block text-sm font-medium mb-1 text-gray-700">Preferred Language</label>
            <select name="preferred_language" class="w-full border rounded-lg py-2.5 px-3 focus:border-gray-700 focus:ring-1 focus:ring-gray-700">
              <option value="">Select a language</option>
              <option value="English" <?= ($user['preferred_language'] ?? '') === 'English' ? 'selected' : '' ?>>English</option>
              <option value="Spanish" <?= ($user['preferred_language'] ?? '') === 'Spanish' ? 'selected' : '' ?>>Spanish</option>
              <option value="French" <?= ($user['preferred_language'] ?? '') === 'French' ? 'selected' : '' ?>>French</option>
              <option value="German" <?= ($user['preferred_language'] ?? '') === 'German' ? 'selected' : '' ?>>German</option>
              <option value="Chinese" <?= ($user['preferred_language'] ?? '') === 'Chinese' ? 'selected' : '' ?>>Chinese</option>
              <option value="Japanese" <?= ($user['preferred_language'] ?? '') === 'Japanese' ? 'selected' : '' ?>>Japanese</option>
              <option value="Arabic" <?= ($user['preferred_language'] ?? '') === 'Arabic' ? 'selected' : '' ?>>Arabic</option>
            </select>
          </div>
        </div>
        
        <!-- Bio Section -->
        <div>
          <label class="block text-sm font-medium mb-1 text-gray-700">Bio</label>
          <textarea name="bio" rows="4" class="w-full border rounded-lg py-2 px-3 focus:border-gray-700 focus:ring-1 focus:ring-gray-700"
                    placeholder="Tell us about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
        </div>

        <div class="flex gap-3 mt-6">
          <button type="submit"
                  class="flex-1 py-2 bg-gray-700 text-white font-medium rounded-lg hover:bg-gray-600 transition-colors flex items-center justify-center gap-2">
            <i class="fas fa-save"></i>
            <span>Save Changes</span>
          </button>
          
          <button type="button" id="cancel-edit-btn"
                  class="py-2 px-4 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
            Cancel
          </button>
        </div>
      </form>
    </div>
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

function openModal() {
  editModal.classList.remove('hidden');
}
      
function closeModal() {
  editModal.classList.add('hidden');
}

editBtn.addEventListener('click', openModal);
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