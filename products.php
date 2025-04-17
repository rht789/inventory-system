<?php include 'sidebar.php'; ?>

<div class="lg:ml-64 min-h-screen flex flex-col bg-gray-50">
  <?php include 'header.php'; ?>

  <main class="flex-grow p-6">
    <!-- Page content -->
    <h2 class="text-xl font-semibold">Products</h2>
  </main>

  <?php include 'footer.php'; ?>
</div>

<script>
  document.getElementById('sidebarToggle').addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('-translate-x-full');
  });
</script>
