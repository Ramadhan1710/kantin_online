<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- Notifikasi Pesanan (untuk Kasir/Admin) -->
<?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['kasir', 'admin'])): ?>
<script src="assets/js/notifications.js"></script>
<?php endif; ?>
