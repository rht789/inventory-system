<?php
include 'authcheck.php'; // Adjust path as needed
requireLogin();           // Ensures the user is logged in
allowRoles(['admin', 'staff']); // Both roles can access
?>

<?php
// sales.php
include 'header.php';
include 'sidebar.php';
?>

<main class="lg:ml-64 min-h-screen p-6 bg-gray-100">
  <!-- Toast container -->
  <div id="toast"
       class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg hidden">
  </div>

  <!-- Topbar -->
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">Sales Management</h2>
    <div class="flex gap-2">
      <button onclick="openAddOrderModal()"
              class="bg-black text-white px-4 py-2 rounded text-sm">
        <i class="fas fa-plus mr-1"></i> Add Order
      </button>
    </div>
  </div>

  <!-- Filters & Search -->
  <div class="bg-white p-4 rounded-md shadow-sm mb-4">
    <div class="flex flex-col md:flex-row md:items-center gap-4 justify-between">
      <input type="text" id="searchInput"
             placeholder="Search Orders..."
             class="border px-4 py-2 rounded w-full md:w-1/3" />
      <div class="flex gap-2 w-full md:w-auto">
        <select id="timeSelect" class="border rounded px-3 py-2 text-sm">
          <option>All Time</option>
        </select>
        <select id="statusSelect" class="border rounded px-3 py-2 text-sm">
          <option>All Statuses</option>
          <option>Pending</option>
          <option>Confirmed</option>
          <option>Delivered</option>
          <option>Canceled</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Sales Table -->
  <div class="bg-white rounded shadow-sm overflow-hidden">
    <div class="text-sm p-4 font-medium">Sales List</div>
    <table class="w-full text-sm">
      <thead class="bg-gray-100 text-gray-600">
        <tr>
          <th class="px-4 py-3 text-left">Sales ID</th>
          <th class="px-4 py-3 text-left">Customer</th>
          <th class="px-4 py-3 text-left">Product(s)</th>
          <th class="px-4 py-3 text-right">Total</th>
          <th class="px-4 py-3 text-center">Status</th>
          <th class="px-4 py-3 text-center">Date & Time</th>
          <th class="px-4 py-3 text-center">Actions</th>
        </tr>
      </thead>
      <tbody id="sales-list">
        <!-- injected by JS -->
      </tbody>
    </table>
  </div>
</main>

<!-- Add New Order Modal -->
<div id="addOrderModal"
     class="fixed inset-0 hidden bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-full max-w-3xl overflow-auto max-h-screen">
    <div class="flex justify-between items-center mb-2">
      <h3 class="text-lg font-semibold">Add New Order</h3>
      <button onclick="closeAddOrderModal()">
        <i class="fas fa-times text-gray-600"></i>
      </button>
    </div>
    <p class="text-sm text-gray-500 mb-4">Add a new sales order</p>
    <form id="addOrderForm" class="space-y-4">
      
      <!-- Customer Information -->
      <div>
        <h4 class="font-medium mb-2">Customer Information</h4>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm">Customer Name</label>
            <input type="text" name="customer_name" id="customerName"
                   class="w-full border px-3 py-2 rounded" required />
          </div>
          <div>
            <label class="block text-sm">Phone Name</label>
            <input type="text" name="customer_phone" id="customerPhone"
                   class="w-full border px-3 py-2 rounded" />
          </div>
          <div>
            <label class="block text-sm">Email</label>
            <input type="email" name="customer_email" id="customerEmail"
                   class="w-full border px-3 py-2 rounded" />
          </div>
          <div>
            <label class="block text-sm">Address</label>
            <input type="text" name="customer_address" id="customerAddress"
                   class="w-full border px-3 py-2 rounded" />
          </div>
        </div>
      </div>
      
      <!-- Products -->
      <div>
        <div class="flex justify-between items-center mb-2">
          <h4 class="font-medium">Products</h4>
          <button type="button" onclick="addProductRow()" 
                  class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs">
            + Add Product
          </button>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50">
                <th class="px-2 py-2 text-left">Product</th>
                <th class="px-2 py-2 text-left">Size</th>
                <th class="px-2 py-2 text-center w-20">Quantity</th>
                <th class="px-2 py-2 text-right w-24">Price</th>
                <th class="px-2 py-2 text-right w-24">Total</th>
                <th class="w-10"></th>
              </tr>
            </thead>
            <tbody id="productRows">
              <!-- Product rows will be added here -->
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Discount -->
      <div>
        <h4 class="font-medium mb-2">Discount</h4>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm">Discount %</label>
            <input type="number" name="discount_percentage" id="discountPercentage"
                   min="0" max="100" class="w-full border px-3 py-2 rounded" value="0" />
          </div>
          <div>
            <label class="block text-sm">Amount</label>
            <select name="discount_product" id="discountProduct"
                    class="w-full border px-3 py-2 rounded">
              <option value="">Select product</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end mt-2 text-sm">
          <div class="grid grid-cols-2 gap-8">
            <div class="text-right">Subtotal:</div>
            <div class="text-right font-medium" id="subtotalDisplay">0.00</div>
            <div class="text-right">Discount:</div>
            <div class="text-right font-medium" id="discountDisplay">0.00</div>
            <div class="text-right font-bold">Total:</div>
            <div class="text-right font-bold" id="totalDisplay">0.00</div>
          </div>
        </div>
      </div>
      
      <!-- Order Details -->
      <div>
        <h4 class="font-medium mb-2">Order Details</h4>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm">Status</label>
            <select name="status" id="orderStatus"
                    class="w-full border px-3 py-2 rounded">
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="delivered">Delivered</option>
              <option value="canceled">Canceled</option>
            </select>
          </div>
          <div>
            <label class="block text-sm">Date</label>
            <input type="text" disabled
                   value="<?php echo date('F jS, Y'); ?>"
                   class="w-full border px-3 py-2 rounded bg-gray-50" />
          </div>
        </div>
        <div class="mt-2">
          <label class="block text-sm">Note(optional)</label>
          <textarea name="note" id="orderNote"
                    class="w-full border px-3 py-2 rounded h-20"
                    placeholder="please write your text here"></textarea>
        </div>
      </div>
      
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" onclick="closeAddOrderModal()"
                class="px-4 py-2 border rounded">
          Cancel
        </button>
        <button type="submit"
                class="bg-black text-white px-6 py-2 rounded">
          Add
        </button>
      </div>
    </form>
  </div>
</div>

<script src="js/sales.js"></script>

<?php include 'footer.php'; ?>



