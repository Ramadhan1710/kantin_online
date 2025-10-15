<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

check_role(['kasir', 'admin']);
$current_user = get_user($_SESSION['user_id']);

$pesanan = null;
$error = '';
$success = '';

// Handle scan barcode
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['scan'])) {
    $kode_pesanan = clean_input($_POST['kode_pesanan']);
    
    // Cari pesanan berdasarkan kode
    $query = "SELECT p.*, u.nama as nama_kasir 
              FROM pesanan p 
              LEFT JOIN users u ON p.user_id = u.id 
              WHERE p.kode_pesanan = '$kode_pesanan'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $pesanan = mysqli_fetch_assoc($result);
        
        // Get detail pesanan
        $query_detail = "SELECT dp.*, m.nama_menu 
                         FROM detail_pesanan dp 
                         LEFT JOIN menu m ON dp.menu_id = m.id 
                         WHERE dp.pesanan_id = '{$pesanan['id']}'";
        $result_detail = mysqli_query($conn, $query_detail);
        $detail_items = [];
        while($row = mysqli_fetch_assoc($result_detail)) {
            $detail_items[] = $row;
        }
    } else {
        $error = "Pesanan tidak ditemukan!";
    }
}

// Handle konfirmasi pembayaran
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['konfirmasi'])) {
    $pesanan_id = clean_input($_POST['pesanan_id']);
    
    // Update status pembayaran menjadi lunas
    $query_update = "UPDATE pesanan SET status_pembayaran = 'lunas', status_pesanan = 'diproses' WHERE id = '$pesanan_id'";
    
    if (mysqli_query($conn, $query_update)) {
        $success = "Pembayaran berhasil dikonfirmasi!";
        
        // Ambil data pesanan lagi
        $query = "SELECT p.*, u.nama as nama_kasir 
                  FROM pesanan p 
                  LEFT JOIN users u ON p.user_id = u.id 
                  WHERE p.id = '$pesanan_id'";
        $result = mysqli_query($conn, $query);
        $pesanan = mysqli_fetch_assoc($result);
        
        // Get detail pesanan
        $query_detail = "SELECT dp.*, m.nama_menu 
                         FROM detail_pesanan dp 
                         LEFT JOIN menu m ON dp.menu_id = m.id 
                         WHERE dp.pesanan_id = '$pesanan_id'";
        $result_detail = mysqli_query($conn, $query_detail);
        $detail_items = [];
        while($row = mysqli_fetch_assoc($result_detail)) {
            $detail_items[] = $row;
        }
    } else {
        $error = "Gagal konfirmasi pembayaran!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scan Barcode - Kantin Online</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        #scanner-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }
        #scanner-result {
            font-size: 1.2em;
            margin-top: 20px;
        }
    </style>
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
                        <h1 class="m-0"><i class="fas fa-qrcode"></i> Scan Barcode Pembayaran</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Scan Barcode</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-ban"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-check"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Scan Input -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary">
                                <h3 class="card-title">Scan QR Code / Input Kode Manual</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="form-group">
                                        <label>Kode Pesanan</label>
                                        <input type="text" name="kode_pesanan" id="kode_pesanan" class="form-control form-control-lg" 
                                               placeholder="Masukkan atau scan kode pesanan" autofocus required>
                                        <small class="text-muted">Ketik manual atau scan QR Code untuk input otomatis</small>
                                    </div>
                                    <button type="submit" name="scan" class="btn btn-primary btn-lg btn-block">
                                        <i class="fas fa-search"></i> Cari Pesanan
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Info QR Scanner -->
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Petunjuk</h3>
                            </div>
                            <div class="card-body">
                                <ol>
                                    <li>Scan QR Code dari struk pesanan pelanggan</li>
                                    <li>Atau ketik manual kode pesanan di form di atas</li>
                                    <li>Klik tombol "Cari Pesanan"</li>
                                    <li>Verifikasi detail pesanan</li>
                                    <li>Klik "Konfirmasi Pembayaran" untuk melunasi</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- Result -->
                    <div class="col-md-6">
                        <?php if ($pesanan): ?>
                            <div class="card">
                                <div class="card-header <?php echo $pesanan['status_pembayaran'] == 'lunas' ? 'bg-success' : 'bg-warning'; ?>">
                                    <h3 class="card-title">Detail Pesanan</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <tr>
                                            <th width="150">Kode Pesanan</th>
                                            <td><strong><?php echo $pesanan['kode_pesanan']; ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal</th>
                                            <td><?php echo date('d/m/Y H:i', strtotime($pesanan['created_at'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Pelanggan</th>
                                            <td><?php echo $pesanan['nama_pelanggan']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Total</th>
                                            <td><h4 class="text-success mb-0"><?php echo format_rupiah($pesanan['total_harga']); ?></h4></td>
                                        </tr>
                                        <tr>
                                            <th>Metode</th>
                                            <td><?php echo strtoupper($pesanan['metode_pembayaran']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                <span class="badge badge-<?php echo $pesanan['status_pembayaran'] == 'lunas' ? 'success' : 'warning'; ?> badge-lg">
                                                    <?php echo strtoupper($pesanan['status_pembayaran']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    </table>

                                    <hr>

                                    <h5>Item Pesanan:</h5>
                                    <table class="table table-sm">
                                        <?php foreach($detail_items as $item): ?>
                                            <tr>
                                                <td><?php echo $item['nama_menu']; ?></td>
                                                <td class="text-right"><?php echo $item['qty']; ?>x</td>
                                                <td class="text-right"><?php echo format_rupiah($item['subtotal']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>

                                    <?php if ($pesanan['status_pembayaran'] == 'belum_bayar'): ?>
                                        <form method="POST" onsubmit="return confirm('Konfirmasi pembayaran pesanan ini?')">
                                            <input type="hidden" name="pesanan_id" value="<?php echo $pesanan['id']; ?>">
                                            <button type="submit" name="konfirmasi" class="btn btn-success btn-lg btn-block mt-3">
                                                <i class="fas fa-check-circle"></i> Konfirmasi Pembayaran
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <div class="alert alert-success mt-3">
                                            <i class="fas fa-check-circle"></i> Pembayaran sudah lunas!
                                        </div>
                                    <?php endif; ?>

                                    <a href="pesanan_detail.php?id=<?php echo $pesanan['id']; ?>" class="btn btn-info btn-block mt-2">
                                        <i class="fas fa-eye"></i> Lihat Detail Lengkap
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-qrcode fa-5x text-muted mb-3"></i>
                                    <p class="text-muted">Scan atau input kode pesanan untuk melihat detail</p>
                                </div>
                            </div>
                        <?php endif; ?>
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
<script>
// Auto focus untuk scanner barcode
document.getElementById('kode_pesanan').focus();

// Auto submit setelah scan (barcode scanner biasanya menambah Enter)
document.getElementById('kode_pesanan').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        this.form.submit();
    }
});
</script>
</body>
</html>
