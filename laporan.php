<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

check_role(['admin']);
$current_user = get_user($_SESSION['user_id']);

// Filter
$periode = isset($_GET['periode']) ? clean_input($_GET['periode']) : 'hari_ini';
$tanggal_dari = isset($_GET['tanggal_dari']) ? clean_input($_GET['tanggal_dari']) : '';
$tanggal_sampai = isset($_GET['tanggal_sampai']) ? clean_input($_GET['tanggal_sampai']) : '';

// Query berdasarkan periode
$where = "WHERE p.status_pembayaran = 'lunas'";

switch($periode) {
    case 'hari_ini':
        $where .= " AND DATE(p.created_at) = CURDATE()";
        break;
    case 'minggu_ini':
        $where .= " AND YEARWEEK(p.created_at) = YEARWEEK(CURDATE())";
        break;
    case 'bulan_ini':
        $where .= " AND MONTH(p.created_at) = MONTH(CURDATE()) AND YEAR(p.created_at) = YEAR(CURDATE())";
        break;
    case 'tahun_ini':
        $where .= " AND YEAR(p.created_at) = YEAR(CURDATE())";
        break;
    case 'custom':
        if (!empty($tanggal_dari) && !empty($tanggal_sampai)) {
            $where .= " AND DATE(p.created_at) BETWEEN '$tanggal_dari' AND '$tanggal_sampai'";
        }
        break;
}

// Total Penjualan
$query_total = "SELECT 
                COUNT(*) as total_pesanan,
                SUM(p.total_harga) as total_pendapatan
                FROM pesanan p
                $where";
$result_total = mysqli_query($conn, $query_total);
$data_total = mysqli_fetch_assoc($result_total);

// Penjualan per Menu
$query_menu = "SELECT 
               m.nama_menu,
               SUM(dp.qty) as total_qty,
               SUM(dp.subtotal) as total_pendapatan
               FROM detail_pesanan dp
               JOIN menu m ON dp.menu_id = m.id
               JOIN pesanan p ON dp.pesanan_id = p.id
               $where
               GROUP BY dp.menu_id
               ORDER BY total_qty DESC
               LIMIT 10";
$result_menu = mysqli_query($conn, $query_menu);

// Penjualan per Hari (7 hari terakhir)
$query_harian = "SELECT 
                 DATE(p.created_at) as tanggal,
                 COUNT(*) as total_pesanan,
                 SUM(p.total_harga) as total_pendapatan
                 FROM pesanan p
                 WHERE p.status_pembayaran = 'lunas'
                 AND DATE(p.created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                 GROUP BY DATE(p.created_at)
                 ORDER BY tanggal ASC";
$result_harian = mysqli_query($conn, $query_harian);

$labels_harian = [];
$data_harian = [];
while($row = mysqli_fetch_assoc($result_harian)) {
    $labels_harian[] = date('d M', strtotime($row['tanggal']));
    $data_harian[] = $row['total_pendapatan'];
}

// Penjualan per Kategori
$query_kategori = "SELECT 
                   k.nama_kategori,
                   SUM(dp.subtotal) as total_pendapatan
                   FROM detail_pesanan dp
                   JOIN menu m ON dp.menu_id = m.id
                   JOIN kategori k ON m.kategori_id = k.id
                   JOIN pesanan p ON dp.pesanan_id = p.id
                   $where
                   GROUP BY k.id
                   ORDER BY total_pendapatan DESC";
$result_kategori = mysqli_query($conn, $query_kategori);

$labels_kategori = [];
$data_kategori = [];
while($row = mysqli_fetch_assoc($result_kategori)) {
    $labels_kategori[] = $row['nama_kategori'];
    $data_kategori[] = $row['total_pendapatan'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Penjualan - Kantin Online</title>
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
                        <h1 class="m-0"><i class="fas fa-chart-line"></i> Laporan Penjualan</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Laporan</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Filter -->
                <div class="card">
                    <div class="card-header bg-primary">
                        <h3 class="card-title">Filter Periode</h3>
                        <div class="card-tools">
                            <button class="btn btn-sm btn-light" onclick="window.print()">
                                <i class="fas fa-print"></i> Cetak
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" id="filterForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <select name="periode" class="form-control" id="periode" onchange="toggleCustomDate()">
                                        <option value="hari_ini" <?php echo $periode == 'hari_ini' ? 'selected' : ''; ?>>Hari Ini</option>
                                        <option value="minggu_ini" <?php echo $periode == 'minggu_ini' ? 'selected' : ''; ?>>Minggu Ini</option>
                                        <option value="bulan_ini" <?php echo $periode == 'bulan_ini' ? 'selected' : ''; ?>>Bulan Ini</option>
                                        <option value="tahun_ini" <?php echo $periode == 'tahun_ini' ? 'selected' : ''; ?>>Tahun Ini</option>
                                        <option value="custom" <?php echo $periode == 'custom' ? 'selected' : ''; ?>>Custom</option>
                                    </select>
                                </div>
                                <div class="col-md-3" id="customDateDiv" style="display: <?php echo $periode == 'custom' ? 'block' : 'none'; ?>;">
                                    <input type="date" name="tanggal_dari" class="form-control" value="<?php echo $tanggal_dari; ?>" placeholder="Dari">
                                </div>
                                <div class="col-md-3" id="customDateDiv2" style="display: <?php echo $periode == 'custom' ? 'block' : 'none'; ?>;">
                                    <input type="date" name="tanggal_sampai" class="form-control" value="<?php echo $tanggal_sampai; ?>" placeholder="Sampai">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row">
                    <div class="col-lg-6 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo $data_total['total_pesanan'] ?: 0; ?></h3>
                                <p>Total Pesanan</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php echo format_rupiah($data_total['total_pendapatan'] ?: 0); ?></h3>
                                <p>Total Pendapatan</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row">
                    <!-- Grafik Harian -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-info">
                                <h3 class="card-title">Grafik Penjualan 7 Hari Terakhir</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="chartHarian" height="80"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Grafik Kategori -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-warning">
                                <h3 class="card-title">Penjualan per Kategori</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="chartKategori"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Menu -->
                <div class="card">
                    <div class="card-header bg-success">
                        <h3 class="card-title">Top 10 Menu Terlaris</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Menu</th>
                                        <th>Total Terjual</th>
                                        <th>Total Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if (mysqli_num_rows($result_menu) > 0):
                                        $no = 1;
                                        mysqli_data_seek($result_menu, 0);
                                        while($menu = mysqli_fetch_assoc($result_menu)): 
                                    ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $menu['nama_menu']; ?></td>
                                            <td><span class="badge badge-primary"><?php echo $menu['total_qty']; ?> porsi</span></td>
                                            <td class="font-weight-bold text-success"><?php echo format_rupiah($menu['total_pendapatan']); ?></td>
                                        </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Tidak ada data</td>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
function toggleCustomDate() {
    var periode = document.getElementById('periode').value;
    document.getElementById('customDateDiv').style.display = periode == 'custom' ? 'block' : 'none';
    document.getElementById('customDateDiv2').style.display = periode == 'custom' ? 'block' : 'none';
}

// Chart Harian
var ctxHarian = document.getElementById('chartHarian').getContext('2d');
var chartHarian = new Chart(ctxHarian, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels_harian); ?>,
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: <?php echo json_encode($data_harian); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 2,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});

// Chart Kategori
var ctxKategori = document.getElementById('chartKategori').getContext('2d');
var chartKategori = new Chart(ctxKategori, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($labels_kategori); ?>,
        datasets: [{
            data: <?php echo json_encode($data_kategori); ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(153, 102, 255, 0.7)',
                'rgba(255, 159, 64, 0.7)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': Rp ' + context.parsed.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});
</script>
</body>
</html>
