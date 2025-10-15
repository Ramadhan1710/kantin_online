<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="dashboard.php" class="nav-link">Home</a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <?php if (in_array($_SESSION['role'], ['kasir', 'admin'])): ?>
        <!-- Notifikasi Pesanan Baru -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" id="notif-bell">
                <i class="far fa-bell"></i>
                <span class="badge badge-danger navbar-badge" id="notif-count" style="display: none;">0</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header" id="notif-header">0 Pesanan Baru</span>
                <div class="dropdown-divider"></div>
                <div id="notif-list">
                    <a href="#" class="dropdown-item text-center text-muted">
                        <small>Tidak ada pesanan baru</small>
                    </a>
                </div>
                <div class="dropdown-divider"></div>
                <a href="pesanan.php" class="dropdown-item dropdown-footer">Lihat Semua Pesanan</a>
            </div>
        </li>
        <?php endif; ?>
        
        <li class="nav-item">
            <span class="nav-link">
                <i class="far fa-user"></i> <?php echo $_SESSION['nama']; ?>
            </span>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="logout.php" onclick="return confirm('Yakin ingin logout?')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</nav>
