<?php
include 'authcheck.php'; // Adjust path as needed
requireLogin();           // Ensures the user is logged in
requireRole('admin');
?>

<?php include 'header.php'; include 'sidebar.php'; ?>

<main class="lg:ml-64 min-h-screen p-6 bg-gray-100">
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">User Management</h2>
    <button onclick="openAddUserModal()" class="bg-black text-white px-4 py-2 rounded flex items-center gap-2 hover:bg-gray-800">
      <i class="fa fa-user-plus"></i> Add user
    </button>
  </div>

  <!-- Admin Section -->
  <div class="bg-white rounded-xl shadow p-4 mb-6">
    <h3 class="text-lg font-semibold mb-3">System Admin</h3>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-left">
        <thead class="text-gray-600 border-b">
          <tr>
            <th class="py-2 px-4">ID</th>
            <th class="py-2 px-4">Username</th>
            <th class="py-2 px-4">Email</th>
            <th class="py-2 px-4">Phone</th>
            <th class="py-2 px-4">Role</th>
            <th class="py-2 px-4">Action</th>
          </tr>
        </thead>
        <tbody id="admin-users" class="text-gray-800">
          <!-- Filled by JS -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Staff Section -->
  <div class="bg-white rounded-xl shadow p-4">
    <h3 class="text-lg font-semibold mb-3">System Staff</h3>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-left">
        <thead class="text-gray-600 border-b">
          <tr>
            <th class="py-2 px-4">ID</th>
            <th class="py-2 px-4">Username</th>
            <th class="py-2 px-4">Email</th>
            <th class="py-2 px-4">Phone</th>
            <th class="py-2 px-4">Role</th>
            <th class="py-2 px-4">Action</th>
          </tr>
        </thead>
        <tbody id="staff-users" class="text-gray-800">
          <!-- Filled by JS -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add User Modal -->
  <div id="addUserModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex justify-center items-center">
    <div class="bg-white w-full max-w-md rounded-lg shadow p-6 relative">
      <h2 class="text-xl font-bold mb-2">Add User</h2>
      <p class="text-sm text-gray-500 mb-4">Create a new user account with specific role permissions.</p>

      <form id="addUserForm" class="space-y-4">
        <input name="username" type="text" placeholder="Username" required class="w-full border rounded px-3 py-2">
        <input name="email" type="email" placeholder="Email" required class="w-full border rounded px-3 py-2">
        <input name="phone" type="text" placeholder="Phone (Optional)" class="w-full border rounded px-3 py-2">
        <input name="password" type="password" placeholder="Password" required class="w-full border rounded px-3 py-2">
        <select name="role" class="w-full border rounded px-3 py-2">
          <option value="staff">Staff</option>
          <option value="admin">Admin</option>
        </select>
        <div class="flex justify-end gap-2">
          <button type="button" onclick="closeAddUserModal()" class="border border-gray-400 px-4 py-2 rounded hover:bg-gray-100">Cancel</button>
          <button type="submit" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">Add User</button>
        </div>
      </form>
      <button onclick="closeAddUserModal()" class="absolute top-2 right-2 text-gray-500 hover:text-black">
        <i class="fa fa-times"></i>
      </button>
    </div>
  </div>
</main>

<script>
function openAddUserModal() {
  document.getElementById('addUserModal').classList.remove('hidden');
}
function closeAddUserModal() {
  document.getElementById('addUserModal').classList.add('hidden');
}

function fetchUsers() {
  fetch('api/users.php')
    .then(res => res.json())
    .then(data => {
      const adminTbody = document.getElementById('admin-users');
      const staffTbody = document.getElementById('staff-users');

      const renderRow = (u) => `
        <tr class="border-t hover:bg-gray-50 transition">
          <td class="py-2 px-4 text-gray-700 font-medium">${u.id}</td>
          <td class="py-2 px-4 capitalize">${u.username}</td>
          <td class="py-2 px-4 text-sm">${u.email}</td>
          <td class="py-2 px-4">${u.phone || '-'}</td>
          <td class="py-2 px-4">
            <span class="px-2 py-1 rounded-full text-xs font-medium ${
              u.role === 'admin'
                ? 'bg-red-100 text-red-600'
                : 'bg-gray-100 text-gray-700'
            }">${u.role.charAt(0).toUpperCase() + u.role.slice(1)}</span>
          </td>
          <td class="py-2 px-4 flex gap-2">
            <button onclick="editUser(${u.id})" class="text-gray-600 hover:text-black">
              <i class="fa fa-pencil"></i>
            </button>
            <button onclick="deleteUser(${u.id})" class="text-red-500 hover:text-red-700">
              <i class="fa fa-trash"></i>
            </button>
          </td>
        </tr>
      `;

      adminTbody.innerHTML = data.admin.map(renderRow).join('');
      staffTbody.innerHTML = data.staff.map(renderRow).join('');
    });
}

document.getElementById('addUserForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch('api/users.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert(data.success);
        this.reset();
        closeAddUserModal();
        fetchUsers();
      } else {
        alert(data.error);
      }
    });
});

function deleteUser(id) {
  if (!confirm("Are you sure you want to delete this user?")) return;

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
        fetchUsers();
      } else {
        alert(data.error);
      }
    });
}

function editUser(id) {
  alert("Edit user with ID: " + id);
}

window.addEventListener('DOMContentLoaded', fetchUsers);
</script>