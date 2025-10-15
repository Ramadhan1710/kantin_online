<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="dashboard.php" class="brand-link">
        <i class="fas fa-utensils brand-image ml-3"></i>
        <span class="brand-text font-weight-light">Kantin Online</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <?php
                $foto_path = !empty($_SESSION['foto']) && file_exists('assets/uploads/users/' . $_SESSION['foto']) 
                    ? 'assets/uploads/users/' . $_SESSION['foto'] 
                    : 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['nama']) . '&background=random&color=fff&size=160';
                ?>
                <img src="<?php echo $foto_path; ?>" class="img-circle elevation-2" alt="User">
            </div>
            <div class="info">
                <a href="profil.php" class="d-block"><?php echo $_SESSION['nama']; ?></a>
                <small class="text-white-50">
                    <i class="fas fa-circle text-success" style="font-size: 8px;"></i> 
                    <?php echo ucfirst($_SESSION['role']); ?>
                </small>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <!-- Menu Admin -->
                    <li class="nav-header">MASTER DATA</li>
                    <li class="nav-item">
                        <a href="kategori.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kategori.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-list"></i>
                            <p>Kategori Menu</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="menu.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'menu.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-utensils"></i>
                            <p>Data Menu</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Data User</p>
                        </a>
                    </li>
                    
                    <li class="nav-header">TRANSAKSI</li>
                    <li class="nav-item">
                        <a href="pesanan.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pesanan.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Data Pesanan</p>
                        </a>
                    </li>
                    
                    <li class="nav-header">LAPORAN</li>
                    <li class="nav-item">
                        <a href="laporan.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Laporan Penjualan</p>
                        </a>
                    </li>

                <?php elseif ($_SESSION['role'] == 'kasir'): ?>
                    <!-- Menu Kasir -->
                    <li class="nav-header">TRANSAKSI</li>
                    <li class="nav-item">
                        <a href="kasir.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kasir.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-cash-register"></i>
                            <p>Kasir / POS</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="pesanan.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pesanan.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Data Pesanan</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="scan_barcode.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'scan_barcode.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-qrcode"></i>
                            <p>Scan Barcode</p>
                        </a>
                    </li>

                <?php else: ?>
                    <!-- Menu Pelanggan -->
                    <li class="nav-header">MENU</li>
                    <li class="nav-item">
                        <a href="pesan_menu.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pesan_menu.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-shopping-bag"></i>
                            <p>Pesan Menu</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="pesanan.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pesanan.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-receipt"></i>
                            <p>Pesanan Saya</p>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Profil -->
                <li class="nav-header">AKUN</li>
                <li class="nav-item">
                    <a href="profil.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-user"></i>
                        <p>Profil Saya</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
