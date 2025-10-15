<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

check_role(['admin', 'kasir', 'pelanggan']);
$current_user = get_user($_SESSION['user_id']);

// Filter
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$status = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$tanggal = isset($_GET['tanggal']) ? clean_input($_GET['tanggal']) : '';

$where = "WHERE 1=1";

// Jika pelanggan, hanya tampilkan pesanan sendiri
if ($_SESSION['role'] == 'pelanggan') {
    $where .= " AND p.user_id = '{$_SESSION['user_id']}'";
}

if (!empty($search)) {
    $where .= " AND (p.kode_pesanan LIKE '%$search%' OR p.nama_pelanggan LIKE '%$search%')";
}

if (!empty($status)) {
    $where .= " AND p.status_pesanan = '$status'";
}

if (!empty($tanggal)) {
    $where .= " AND DATE(p.created_at) = '$tanggal'";
}

$query = "SELECT p.*, u.nama as nama_kasir 
          FROM pesanan p 
          LEFT JOIN users u ON p.user_id = u.id 
          $where 
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Pesanan - Kantin Online</title>
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
                        <h1 class="m-0"><i class="fas fa-receipt"></i> Daftar Pesanan</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Pesanan</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Filter Pesanan</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET">
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control" placeholder="Cari kode/nama..." value="<?php echo $search; ?>">
                                </div>
                                <div class="col-md-3">
                                    <select name="status" class="form-control">
                                        <option value="">Semua Status</option>
                                        <option value="menunggu" <?php echo $status == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                        <option value="diproses" <?php echo $status == 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                                        <option value="selesai" <?php echo $status == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                        <option value="dibatalkan" <?php echo $status == 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="date" name="tanggal" class="form-control" value="<?php echo $tanggal; ?>">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    <a href="pesanan.php" class="btn btn-secondary">
                                        <i class="fas fa-redo"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data Pesanan</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Pesanan</th>
                                        <th>Tanggal</th>
                                        <th>Pelanggan</th>
                                        <?php if ($_SESSION['role'] != 'pelanggan'): ?>
                                            <th>Kasir</th>
                                        <?php endif; ?>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Pembayaran</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result) > 0): ?>
                                        <?php $no = 1; while($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><strong><?php echo $row['kode_pesanan']; ?></strong></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                                <td>
                                                    <?php echo $row['nama_pelanggan']; ?>
                                                    <?php if (!empty($row['no_hp'])): ?>
                                                        <br><small class="text-muted"><?php echo $row['no_hp']; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <?php if ($_SESSION['role'] != 'pelanggan'): ?>
                                                    <td><?php echo $row['nama_kasir']; ?></td>
                                                <?php endif; ?>
                                                <td class="font-weight-bold"><?php echo format_rupiah($row['total_harga']); ?></td>
                                                <td>
                                                    <?php
                                                    $badge = '';
                                                    switch($row['status_pesanan']) {
                                                        case 'menunggu':
                                                            $badge = 'warning';
                                                            break;
                                                        case 'diproses':
                                                            $badge = 'info';
                                                            break;
                                                        case 'selesai':
                                                            $badge = 'success';
                                                            break;
                                                        case 'dibatalkan':
                                                            $badge = 'danger';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge badge-<?php echo $badge; ?>">
                                                        <?php echo ucfirst($row['status_pesanan']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $badge_bayar = $row['status_pembayaran'] == 'lunas' ? 'success' : 'warning';
                                                    ?>
                                                    <span class="badge badge-<?php echo $badge_bayar; ?>">
                                                        <?php echo ucfirst($row['status_pembayaran']); ?>
                                                    </span>
                                                    <br><small><?php echo ucfirst($row['metode_pembayaran']); ?></small>
                                                </td>
                                                <td>
                                                    <a href="pesanan_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="<?php echo $_SESSION['role'] != 'pelanggan' ? '9' : '8'; ?>" class="text-center">
                                                <div class="py-3">
                                                    <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                                    <p class="text-muted mb-0">Tidak ada data pesanan</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
<!-- Notifikasi Pesanan -->
<?php if (in_array($_SESSION['role'], ['kasir', 'admin'])): ?>
<script src="assets/js/notifications.js"></script>
<?php endif; ?>
</body>
</html>
