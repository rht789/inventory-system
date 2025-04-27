<?php
include 'authcheck.php'; // Adjust path as needed
requireLogin();           // Ensures the user is logged in
requireRole('admin');

// Get current admin's user ID for access control
$currentAdminId = $_SESSION['user_id'];
?>

<?php include 'header.php'; include 'sidebar.php'; ?>

<main class="min-h-screen p-6 bg-gray-50">
  <!-- Page Header -->
  <div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-gray-800">User Management</h2>
        <p class="mt-1 text-sm text-gray-600">Manage system users, roles and permissions</p>
      </div>
      <button onclick="openAddUserModal()" class="bg-gray-700 text-white px-4 py-2.5 rounded-lg flex items-center gap-2 hover:bg-gray-800 transition shadow-sm self-start md:self-auto">
        <i class="fa fa-user-plus"></i>
        <span>Add New User</span>
      </button>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
      <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
        <div class="flex items-center gap-3">
          <div class="bg-gray-100 text-gray-700 p-3 rounded-lg">
            <i class="fas fa-users text-xl"></i>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500">Total Users</h3>
            <p class="text-2xl font-bold text-gray-800" id="total-users-count">-</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
        <div class="flex items-center gap-3">
          <div class="bg-red-100 text-red-600 p-3 rounded-lg">
            <i class="fas fa-user-shield text-xl"></i>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500">Admin Users</h3>
            <p class="text-2xl font-bold text-gray-800" id="admin-users-count">-</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
        <div class="flex items-center gap-3">
          <div class="bg-green-100 text-green-600 p-3 rounded-lg">
            <i class="fas fa-user-tie text-xl"></i>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500">Staff Users</h3>
            <p class="text-2xl font-bold text-gray-800" id="staff-users-count">-</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Search and Filter -->
  <div class="bg-white rounded-lg shadow-sm p-4 mb-6 border border-gray-200">
    <div class="flex flex-col md:flex-row md:items-center gap-4">
      <div class="relative flex-1">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <i class="fas fa-search text-gray-400"></i>
        </div>
        <input type="text" id="userSearch" placeholder="Search users..." 
               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full focus:ring-gray-500 focus:border-gray-500">
      </div>
      
      <div class="md:w-48">
        <select id="roleFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-gray-500 focus:border-gray-500">
          <option value="all">All Roles</option>
          <option value="admin">Admin Only</option>
          <option value="staff">Staff Only</option>
        </select>
      </div>
    </div>
  </div>
  
  <!-- Admin Section -->
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
    <div class="p-4 border-b bg-gray-700 flex items-center justify-between">
      <h3 class="text-lg font-semibold text-white">System Administrators</h3>
      <span class="bg-red-700 text-white text-xs font-medium px-3 py-1 rounded-full border border-red-600 shadow-sm">Admin Access</span>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody id="admin-users" class="bg-white divide-y divide-gray-200">
          <!-- Filled by JS -->
          <tr>
            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Loading administrators...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Staff Section -->
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-4 border-b bg-gray-700 flex items-center justify-between">
      <h3 class="text-lg font-semibold text-white">System Staff</h3>
      <span class="bg-gray-500 text-white text-xs font-medium px-3 py-1 rounded-full border border-gray-400 shadow-sm">Standard Access</span>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody id="staff-users" class="bg-white divide-y divide-gray-200">
          <!-- Filled by JS -->
          <tr>
            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Loading staff members...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- No Results Message -->
  <div id="no-results" class="hidden mt-6 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-md">
    <div class="flex items-center gap-3">
      <i class="fas fa-exclamation-circle text-yellow-500"></i>
      <p>No users match your search criteria. Please try a different search term or filter.</p>
    </div>
  </div>

  <!-- Add User Modal -->
  <div id="addUserModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex justify-center items-center">
    <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-0 relative">
      <div class="bg-gray-50 p-4 border-b border-gray-200 rounded-t-lg">
        <h2 class="text-xl font-bold text-gray-800">Add New User</h2>
        <p class="text-sm text-gray-500 mt-1">Create a new user account with specific role permissions</p>
      </div>
      
      <form id="addUserForm" class="p-6">
        <div class="space-y-4">
          <div>
            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-user text-gray-400"></i>
              </div>
              <input id="username" name="username" type="text" required 
                     class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full focus:ring-gray-500 focus:border-gray-500">
            </div>
          </div>
          
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-envelope text-gray-400"></i>
              </div>
              <input id="email" name="email" type="email" required 
                     class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full focus:ring-gray-500 focus:border-gray-500">
            </div>
          </div>
          
          <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number (Optional)</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-phone text-gray-400"></i>
              </div>
              <input id="phone" name="phone" type="text" 
                     class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full focus:ring-gray-500 focus:border-gray-500">
            </div>
          </div>
          
          <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
            <div class="flex items-start gap-2">
              <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
              <div class="text-sm text-blue-700">
                <p><strong>Note:</strong> A secure password will be auto-generated and sent to the user's email address.</p>
              </div>
            </div>
          </div>
          
          <div>
            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">User Role</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-user-tag text-gray-400"></i>
              </div>
              <select id="role" name="role" 
                      class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full focus:ring-gray-500 focus:border-gray-500">
                <option value="staff">Staff</option>
                <option value="admin">Admin</option>
              </select>
            </div>
          </div>
        </div>
        
        <div class="mt-6 flex items-center justify-end gap-3">
          <button type="button" onclick="closeAddUserModal()" 
                  class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
            Cancel
          </button>
          <button type="submit" 
                  class="px-4 py-2 border border-transparent rounded-lg text-white bg-gray-700 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
            Add User
          </button>
        </div>
      </form>
      
      <button onclick="closeAddUserModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-500 transition">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </div>

  <!-- Toast Notification -->
  <div id="toast" class="fixed bottom-4 right-4 bg-gray-900 text-white px-5 py-3 rounded-lg shadow-xl hidden z-50 transition-opacity duration-300 max-w-md">
    <div class="flex items-start gap-3">
      <i id="toast-icon" class="fas fa-check-circle text-green-400 mt-0.5 flex-shrink-0"></i>
      <span id="toast-message" class="text-sm leading-tight"></span>
    </div>
  </div>
</main>

<script>
function openAddUserModal() {
  document.getElementById('addUserModal').classList.remove('hidden');
  document.getElementById('addUserForm').reset();
}

function closeAddUserModal() {
  document.getElementById('addUserModal').classList.add('hidden');
}

function showToast(message, isSuccess = true, duration = 3000) {
  const toast = document.getElementById('toast');
  const icon = document.getElementById('toast-icon');
  const messageEl = document.getElementById('toast-message');
  
  icon.className = isSuccess ? 'fas fa-check-circle text-green-400' : 'fas fa-exclamation-circle text-red-400';
  messageEl.textContent = message;
  
  toast.classList.remove('hidden');
  
  setTimeout(() => {
    toast.classList.add('hidden');
  }, duration);
}

function fetchUsers() {
  // Get the current admin ID from PHP
  const currentAdminId = <?php echo $currentAdminId; ?>;
  
  fetch('api/users.php')
    .then(res => res.json())
    .then(data => {
      const adminTbody = document.getElementById('admin-users');
      const staffTbody = document.getElementById('staff-users');
      
      // Update counters
      document.getElementById('total-users-count').textContent = data.admin.length + data.staff.length;
      document.getElementById('admin-users-count').textContent = data.admin.length;
      document.getElementById('staff-users-count').textContent = data.staff.length;

      const renderRow = (u) => {
        // Determine if edit/delete should be disabled for this user
        const isCurrentAdmin = u.id == currentAdminId;
        const isAdmin = u.role === 'admin';
        const canModify = !isAdmin && !isCurrentAdmin;
        
        // Get profile picture or generate avatar
        let profileImage;
        
        // Check if user has a profile picture
        const hasProfilePicture = u.profile_picture && u.profile_picture.trim() !== '';
        
        if (hasProfilePicture) {
          // User has a profile picture
          profileImage = `<img src="uploads/profile/${u.profile_picture}" alt="${u.username}" class="h-10 w-10 object-cover" onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(u.username)}&background=random&color=fff'; this.onerror=null;">`;
          console.log(`User ${u.username} profile picture path: uploads/profile/${u.profile_picture}`);
        } else {
          // Generate avatar with first letter of username
          const initial = u.username.charAt(0).toUpperCase();
          const bgColor = isAdmin ? 'bg-red-500' : 'bg-blue-500';
          profileImage = `
            <div class="${bgColor} text-white w-full h-full flex items-center justify-center">
              <span class="text-lg font-semibold">${initial}</span>
            </div>
          `;
        }
        
        // Buttons with conditionally disabled state
        const actionButtons = canModify ? 
          `<button onclick="editUser(${u.id})" class="text-gray-600 hover:text-gray-800 bg-gray-100 p-1.5 rounded-md transition">
             <i class="fas fa-edit"></i>
           </button>
           <button onclick="deleteUser(${u.id}, '${u.username}')" class="text-red-600 hover:text-red-900 bg-red-100 p-1.5 rounded-md transition">
             <i class="fas fa-trash-alt"></i>
           </button>` :
          `<button disabled class="text-gray-400 bg-gray-100 p-1.5 rounded-md cursor-not-allowed opacity-50" title="${isCurrentAdmin ? 'Cannot modify your own account here' : 'Cannot modify admin accounts'}">
             <i class="fas fa-edit"></i>
           </button>
           <button disabled class="text-gray-400 bg-gray-100 p-1.5 rounded-md cursor-not-allowed opacity-50" title="${isCurrentAdmin ? 'Cannot modify your own account here' : 'Cannot modify admin accounts'}">
             <i class="fas fa-trash-alt"></i>
           </button>`;
            
        return `
        <tr class="hover:bg-gray-50 transition ${isCurrentAdmin ? 'bg-gray-50' : ''}">
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center gap-3">
              <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                ${profileImage}
              </div>
              <div>
                <div class="text-sm font-medium text-gray-900">${u.username} ${isCurrentAdmin ? '<span class="text-xs text-gray-500">(You)</span>' : ''}</div>
                <div class="text-sm text-gray-500">ID: ${u.id}</div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">${u.email}</div>
            <div class="text-sm text-gray-500">${u.phone || 'No phone number'}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
              Active
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ${
              u.role === 'admin'
                ? 'bg-red-100 text-red-800'
                : 'bg-blue-100 text-blue-800'
            }">
              <i class="fas ${u.role === 'admin' ? 'fa-user-shield' : 'fa-user'} mr-1.5"></i>
              ${u.role.charAt(0).toUpperCase() + u.role.slice(1)}
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <div class="flex items-center justify-end gap-2">
              ${actionButtons}
            </div>
          </td>
        </tr>
      `};

      if (data.admin.length > 0) {
        adminTbody.innerHTML = data.admin.map(renderRow).join('');
      } else {
        adminTbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No administrators found</td></tr>';
      }

      if (data.staff.length > 0) {
        staffTbody.innerHTML = data.staff.map(renderRow).join('');
      } else {
        staffTbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No staff members found</td></tr>';
      }

      // Handle search and filtering
      setupSearch();
    })
    .catch(error => {
      console.error('Error fetching users:', error);
      showToast('Failed to load users. Please try again.', false);
    });
}

function setupSearch() {
  const searchInput = document.getElementById('userSearch');
  const roleFilter = document.getElementById('roleFilter');
  
  function filterUsers() {
    const searchTerm = searchInput.value.toLowerCase();
    const roleValue = roleFilter.value;
    
    const allRows = [...document.querySelectorAll('#admin-users tr, #staff-users tr')];
    let visibleCount = 0;
    
    allRows.forEach(row => {
      if (row.querySelector('td[colspan]')) return; // Skip message rows
      
      const username = row.querySelector('td:first-child .text-gray-900')?.textContent.toLowerCase() || '';
      const email = row.querySelector('td:nth-child(2) .text-gray-900')?.textContent.toLowerCase() || '';
      const role = row.querySelector('td:nth-child(4) span')?.textContent.trim().toLowerCase() || '';
      
      const matchesSearch = username.includes(searchTerm) || email.includes(searchTerm);
      const matchesRole = roleValue === 'all' || role.includes(roleValue);
      
      if (matchesSearch && matchesRole) {
        row.classList.remove('hidden');
        visibleCount++;
      } else {
        row.classList.add('hidden');
      }
    });
    
    // Show/hide no results message
    document.getElementById('no-results').classList.toggle('hidden', visibleCount > 0);
  }
  
  searchInput.addEventListener('input', filterUsers);
  roleFilter.addEventListener('change', filterUsers);
}

document.getElementById('addUserForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(this);
  const userEmail = document.getElementById('email').value;
  const userName = document.getElementById('username').value;

  // Show loading state
  const submitBtn = this.querySelector('button[type="submit"]');
  const originalBtnText = submitBtn.innerHTML;
  submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
  submitBtn.disabled = true;

  fetch('api/users.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      // Restore button state
      submitBtn.innerHTML = originalBtnText;
      submitBtn.disabled = false;
      
      if (data.success) {
        // Check if it's specifically about email delivery
        const isEmailSuccess = data.success.includes('email');
        
        // Use a more detailed toast for email notifications
        if (isEmailSuccess) {
          showToast(`User "${userName}" created successfully. Password has been sent to ${userEmail}`, true, 5000);
        } else {
          // Check if we have a note about the password (when email fails)
          if (data.note && data.note.includes('Password:')) {
            const password = data.note.split('Password: ')[1];
            showToast(`User "${userName}" created, but email failed. Temporary password: ${password}`, true, 10000);
          } else {
            showToast(data.success, true);
          }
        }
        
        this.reset();
        closeAddUserModal();
        fetchUsers();
      } else {
        showToast(data.error || 'Failed to add user', false);
      }
    })
    .catch(error => {
      // Restore button state
      submitBtn.innerHTML = originalBtnText;
      submitBtn.disabled = false;
      
      console.error('Error adding user:', error);
      showToast('An error occurred. Please try again.', false);
    });
});

function deleteUser(id, username) {
  // Add an extra check to prevent deleting admins
  fetch('api/users.php?id=' + id)
    .then(res => res.json())
    .then(data => {
      if (data.user && data.user.role === 'admin') {
        showToast('Cannot delete administrator accounts', false);
        return;
      }
      
      if (!confirm(`Are you sure you want to delete user "${username}"?`)) return;
    
      const formData = new FormData();
      formData.append('id', id);
      formData.append('_method', 'DELETE');
    
      fetch('api/users.php', {
        method: 'POST',
        body: formData
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            showToast(`User "${username}" deleted successfully`, true);
            fetchUsers();
          } else {
            showToast(data.error || 'Failed to delete user', false);
          }
        })
        .catch(error => {
          console.error('Error deleting user:', error);
          showToast('An error occurred. Please try again.', false);
        });
    })
    .catch(error => {
      console.error('Error checking user:', error);
      showToast('An error occurred. Please try again.', false);
    });
}

function editUser(id) {
  fetch('api/users.php?id=' + id)
    .then(res => res.json())
    .then(data => {
      if (data.user && data.user.role === 'admin') {
        showToast('Cannot edit administrator accounts', false);
        return;
      }
      
      // This is the placeholder for edit functionality
      showToast(`Edit functionality for user ID: ${id} will be implemented soon`, true);
    })
    .catch(error => {
      console.error('Error checking user:', error);
      showToast('An error occurred. Please try again.', false);
    });
}

window.addEventListener('DOMContentLoaded', fetchUsers);
</script>