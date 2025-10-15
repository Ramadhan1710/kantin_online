<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Cek login
check_role();

// Get data user
$user = get_user($_SESSION['user_id']);

// Get statistik
$total_pesanan_hari_ini = count_pesanan_hari_ini();
$total_pendapatan_hari_ini = count_pendapatan_hari_ini();
$total_menu = count_menu();
$total_pelanggan = count_pelanggan();

// Get pesanan terbaru (5 terakhir)
$query_pesanan = "SELECT p.*, u.nama FROM pesanan p 
                  JOIN users u ON p.user_id = u.id 
                  ORDER BY p.created_at DESC LIMIT 5";
$result_pesanan = mysqli_query($conn, $query_pesanan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Kantin Online</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome (CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Theme style (CDN) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <?php include 'includes/navbar.php'; ?>
    <!-- /.navbar -->

    <?php include 'includes/sidebar.php'; ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Dashboard</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Info boxes -->
                <div class="row">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-shopping-cart"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pesanan Hari Ini</span>
                                <span class="info-box-number"><?php echo $total_pesanan_hari_ini; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-money-bill-wave"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pendapatan Hari Ini</span>
                                <span class="info-box-number"><?php echo format_rupiah($total_pendapatan_hari_ini); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-utensils"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Menu</span>
                                <span class="info-box-number"><?php echo $total_menu; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3">
                            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Pelanggan</span>
                                <span class="info-box-number"><?php echo $total_pelanggan; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.row -->

                <!-- Pesanan Terbaru -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-list"></i> Pesanan Terbaru</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Kode Pesanan</th>
                                            <th>Pelanggan</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result_pesanan) > 0): ?>
                                            <?php while ($pesanan = mysqli_fetch_assoc($result_pesanan)): ?>
                                                <tr>
                                                    <td><?php echo $pesanan['kode_pesanan']; ?></td>
                                                    <td><?php echo $pesanan['nama']; ?></td>
                                                    <td><?php echo format_rupiah($pesanan['total_harga']); ?></td>
                                                    <td>
                                                        <?php
                                                        $badge_class = '';
                                                        switch ($pesanan['status_pesanan']) {
                                                            case 'menunggu': $badge_class = 'warning'; break;
                                                            case 'diproses': $badge_class = 'info'; break;
                                                            case 'selesai': $badge_class = 'success'; break;
                                                            case 'dibatalkan': $badge_class = 'danger'; break;
                                                        }
                                                        ?>
                                                        <span class="badge badge-<?php echo $badge_class; ?>">
                                                            <?php echo ucfirst($pesanan['status_pesanan']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($pesanan['created_at'])); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Belum ada pesanan</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.row -->

                <!-- Informasi Selamat Datang -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-info-circle"></i> Selamat Datang</h3>
                            </div>
                            <div class="card-body">
                                <h5>Halo, <?php echo $user['nama']; ?>!</h5>
                                <p>Selamat datang di Sistem Pemesanan Menu Kantin Online.</p>
                                
                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <p>Sebagai <b>Admin</b>, Anda dapat:</p>
                                    <ul>
                                        <li>Mengelola data kategori dan menu</li>
                                        <li>Mengelola data user (Admin, Kasir, Pelanggan)</li>
                                        <li>Melihat semua pesanan</li>
                                        <li>Melihat laporan penjualan</li>
                                    </ul>
                                <?php elseif ($_SESSION['role'] == 'kasir'): ?>
                                    <p>Sebagai <b>Kasir</b>, Anda dapat:</p>
                                    <ul>
                                        <li>Membuat pesanan baru untuk pelanggan</li>
                                        <li>Memproses pembayaran</li>
                                        <li>Mengupdate status pesanan</li>
                                    </ul>
                                <?php else: ?>
                                    <p>Sebagai <b>Pelanggan</b>, Anda dapat:</p>
                                    <ul>
                                        <li>Melihat daftar menu yang tersedia</li>
                                        <li>Melakukan pemesanan menu</li>
                                        <li>Melihat status pesanan Anda</li>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <?php include 'includes/footer.php'; ?>
</div>
<!-- ./wrapper -->

<!-- jQuery (CDN) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- Notifikasi Pesanan -->
<?php if (in_array($_SESSION['role'], ['kasir', 'admin'])): ?>
<script src="assets/js/notifications.js"></script>
<?php endif; ?>
</body>
</html>
