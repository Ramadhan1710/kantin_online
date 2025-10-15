<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

check_role(['admin', 'kasir', 'pelanggan']);
$current_user = get_user($_SESSION['user_id']);

// Get ID
$id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
if (empty($id)) {
    header("Location: pesanan.php");
    exit();
}

// Get data pesanan
$query = "SELECT p.*, u.nama as nama_kasir 
          FROM pesanan p 
          LEFT JOIN users u ON p.user_id = u.id 
          WHERE p.id = '$id'";

// Jika pelanggan, hanya bisa lihat pesanan sendiri
if ($_SESSION['role'] == 'pelanggan') {
    $query .= " AND p.user_id = '{$_SESSION['user_id']}'";
}

$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) == 0) {
    header("Location: pesanan.php");
    exit();
}
$pesanan = mysqli_fetch_assoc($result);

// Get detail pesanan
$query_detail = "SELECT dp.*, m.nama_menu, m.gambar 
                 FROM detail_pesanan dp 
                 LEFT JOIN menu m ON dp.menu_id = m.id 
                 WHERE dp.pesanan_id = '$id'";
$result_detail = mysqli_query($conn, $query_detail);

// Handle update status pesanan (Admin & Kasir)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status']) && in_array($_SESSION['role'], ['admin', 'kasir'])) {
    $status_pesanan = clean_input($_POST['status_pesanan']);
    
    $query_update = "UPDATE pesanan SET status_pesanan = '$status_pesanan' WHERE id = '$id'";
    if (mysqli_query($conn, $query_update)) {
        header("Location: pesanan_detail.php?id=$id");
        exit();
    }
}

// Handle update status pembayaran (Admin & Kasir)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_pembayaran']) && in_array($_SESSION['role'], ['admin', 'kasir'])) {
    $status_pembayaran = clean_input($_POST['status_pembayaran']);
    
    $query_update = "UPDATE pesanan SET status_pembayaran = '$status_pembayaran' WHERE id = '$id'";
    if (mysqli_query($conn, $query_update)) {
        header("Location: pesanan_detail.php?id=$id");
        exit();
    }
}

// Handle batal pesanan (Pelanggan, hanya jika status masih menunggu)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['batal_pesanan']) && $_SESSION['role'] == 'pelanggan') {
    if ($pesanan['status_pesanan'] == 'menunggu') {
        $query_update = "UPDATE pesanan SET status_pesanan = 'dibatalkan' WHERE id = '$id'";
        if (mysqli_query($conn, $query_update)) {
            header("Location: pesanan_detail.php?id=$id");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Pesanan - Kantin Online</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-receipt"></i> Detail Pesanan</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="pesanan.php">Pesanan</a></li>
                            <li class="breadcrumb-item active">Detail</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <!-- Info Pesanan -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-primary">
                                <h3 class="card-title">Informasi Pesanan</h3>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <tr>
                                        <th width="200">Kode Pesanan</th>
                                        <td><strong class="text-primary"><?php echo $pesanan['kode_pesanan']; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal</th>
                                        <td><?php echo date('d F Y, H:i', strtotime($pesanan['created_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Nama Pelanggan</th>
                                        <td><?php echo $pesanan['nama_pelanggan']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>No HP</th>
                                        <td><?php echo $pesanan['no_hp'] ?: '-'; ?></td>
                                    </tr>
                                    <?php if ($_SESSION['role'] != 'pelanggan'): ?>
                                        <tr>
                                            <th>Kasir</th>
                                            <td><?php echo $pesanan['nama_kasir']; ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($pesanan['catatan'])): ?>
                                        <tr>
                                            <th>Catatan</th>
                                            <td><?php echo $pesanan['catatan']; ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <th>Status Pesanan</th>
                                        <td>
                                            <?php
                                            $badge = '';
                                            switch($pesanan['status_pesanan']) {
                                                case 'menunggu': $badge = 'warning'; break;
                                                case 'diproses': $badge = 'info'; break;
                                                case 'selesai': $badge = 'success'; break;
                                                case 'dibatalkan': $badge = 'danger'; break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $badge; ?> badge-lg">
                                                <?php echo strtoupper($pesanan['status_pesanan']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Metode Pembayaran</th>
                                        <td><?php echo strtoupper($pesanan['metode_pembayaran']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status Pembayaran</th>
                                        <td>
                                            <span class="badge badge-<?php echo $pesanan['status_pembayaran'] == 'lunas' ? 'success' : 'warning'; ?> badge-lg">
                                                <?php echo strtoupper($pesanan['status_pembayaran']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Detail Item -->
                        <div class="card">
                            <div class="card-header bg-info">
                                <h3 class="card-title">Detail Item Pesanan</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Menu</th>
                                                <th>Harga</th>
                                                <th>Qty</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $no = 1;
                                            while($detail = mysqli_fetch_assoc($result_detail)): 
                                            ?>
                                                <tr>
                                                    <td><?php echo $no++; ?></td>
                                                    <td><?php echo $detail['nama_menu']; ?></td>
                                                    <td><?php echo format_rupiah($detail['harga']); ?></td>
                                                    <td><?php echo $detail['qty']; ?></td>
                                                    <td class="font-weight-bold"><?php echo format_rupiah($detail['subtotal']); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="4" class="text-right">TOTAL:</th>
                                                <th class="text-success" style="font-size: 1.2em;">
                                                    <?php echo format_rupiah($pesanan['total_harga']); ?>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Panel -->
                    <div class="col-md-4">
                        <?php if (in_array($_SESSION['role'], ['admin', 'kasir'])): ?>
                            <!-- Update Status Pesanan -->
                            <div class="card">
                                <div class="card-header bg-warning">
                                    <h3 class="card-title">Update Status Pesanan</h3>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="form-group">
                                            <label>Status Pesanan</label>
                                            <select name="status_pesanan" class="form-control" required>
                                                <option value="menunggu" <?php echo $pesanan['status_pesanan'] == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                                <option value="diproses" <?php echo $pesanan['status_pesanan'] == 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                                                <option value="selesai" <?php echo $pesanan['status_pesanan'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                                <option value="dibatalkan" <?php echo $pesanan['status_pesanan'] == 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                            </select>
                                        </div>
                                        <button type="submit" name="update_status" class="btn btn-warning btn-block">
                                            <i class="fas fa-sync"></i> Update Status
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Update Status Pembayaran -->
                            <div class="card">
                                <div class="card-header bg-success">
                                    <h3 class="card-title">Update Pembayaran</h3>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="form-group">
                                            <label>Status Pembayaran</label>
                                            <select name="status_pembayaran" class="form-control" required>
                                                <option value="belum_bayar" <?php echo $pesanan['status_pembayaran'] == 'belum_bayar' ? 'selected' : ''; ?>>Belum Bayar</option>
                                                <option value="lunas" <?php echo $pesanan['status_pembayaran'] == 'lunas' ? 'selected' : ''; ?>>Lunas</option>
                                            </select>
                                        </div>
                                        <button type="submit" name="update_pembayaran" class="btn btn-success btn-block">
                                            <i class="fas fa-money-bill"></i> Update Pembayaran
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($_SESSION['role'] == 'pelanggan' && $pesanan['status_pesanan'] == 'menunggu'): ?>
                            <!-- Batalkan Pesanan (Pelanggan) -->
                            <div class="card">
                                <div class="card-header bg-danger">
                                    <h3 class="card-title">Batalkan Pesanan</h3>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Anda dapat membatalkan pesanan jika status masih <strong>Menunggu</strong></p>
                                    <form method="POST" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                                        <button type="submit" name="batal_pesanan" class="btn btn-danger btn-block">
                                            <i class="fas fa-times"></i> Batalkan Pesanan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- QR Code Payment -->
                        <div class="card">
                            <div class="card-header bg-info">
                                <h3 class="card-title">QR Code Pembayaran</h3>
                            </div>
                            <div class="card-body text-center">
                                <?php if ($pesanan['status_pembayaran'] == 'belum_bayar'): ?>
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($pesanan['kode_pesanan']); ?>" alt="QR Code">
                                    <p class="mt-2 text-muted">Scan QR Code untuk pembayaran</p>
                                    <p class="font-weight-bold"><?php echo $pesanan['kode_pesanan']; ?></p>
                                <?php else: ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle fa-3x mb-2"></i>
                                        <p class="mb-0">Pembayaran Lunas</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Print Struk -->
                        <div class="card">
                            <div class="card-body">
                                <button onclick="window.print()" class="btn btn-secondary btn-block">
                                    <i class="fas fa-print"></i> Cetak Struk
                                </button>
                                <a href="pesanan.php" class="btn btn-primary btn-block">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
