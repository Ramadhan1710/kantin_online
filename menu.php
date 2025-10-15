<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

check_role(['admin']);
$user = get_user($_SESSION['user_id']);

// Proses hapus menu
if (isset($_GET['hapus'])) {
    $id = clean_input($_GET['hapus']);
    
    // Get gambar lama untuk dihapus
    $query_get = "SELECT gambar FROM menu WHERE id = '$id'";
    $result_get = mysqli_query($conn, $query_get);
    $menu = mysqli_fetch_assoc($result_get);
    
    // Hapus dari database
    $query = "DELETE FROM menu WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        // Hapus file gambar jika ada
        if (!empty($menu['gambar']) && file_exists('assets/uploads/menu/' . $menu['gambar'])) {
            unlink('assets/uploads/menu/' . $menu['gambar']);
        }
        $success = "Menu berhasil dihapus!";
    } else {
        $error = "Gagal menghapus menu!";
    }
}

// Filter
$kategori_filter = isset($_GET['kategori']) ? clean_input($_GET['kategori']) : '';
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Query menu dengan filter
$query = "SELECT m.*, k.nama_kategori FROM menu m 
          LEFT JOIN kategori k ON m.kategori_id = k.id 
          WHERE 1=1";

if (!empty($kategori_filter)) {
    $query .= " AND m.kategori_id = '$kategori_filter'";
}
if (!empty($status_filter)) {
    $query .= " AND m.status = '$status_filter'";
}
if (!empty($search)) {
    $query .= " AND m.nama_menu LIKE '%$search%'";
}

$query .= " ORDER BY m.nama_menu ASC";
$result = mysqli_query($conn, $query);

// Get kategori untuk filter
$query_kategori = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($conn, $query_kategori);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Menu - Kantin Online</title>
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
                        <h1 class="m-0"><i class="fas fa-utensils"></i> Data Menu</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Data Menu</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-check"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-ban"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Menu</h3>
                        <div class="card-tools">
                            <a href="menu_tambah.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Tambah Menu
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filter -->
                        <form method="GET" class="mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control" placeholder="Cari menu..." value="<?php echo $search; ?>">
                                </div>
                                <div class="col-md-3">
                                    <select name="kategori" class="form-control">
                                        <option value="">Semua Kategori</option>
                                        <?php 
                                        mysqli_data_seek($result_kategori, 0);
                                        while ($kat = mysqli_fetch_assoc($result_kategori)): 
                                        ?>
                                            <option value="<?php echo $kat['id']; ?>" <?php echo $kategori_filter == $kat['id'] ? 'selected' : ''; ?>>
                                                <?php echo $kat['nama_kategori']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="status" class="form-control">
                                        <option value="">Semua Status</option>
                                        <option value="tersedia" <?php echo $status_filter == 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                                        <option value="habis" <?php echo $status_filter == 'habis' ? 'selected' : ''; ?>>Habis</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="menu.php" class="btn btn-secondary">
                                        <i class="fas fa-redo"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="50">No</th>
                                        <th width="80">Gambar</th>
                                        <th>Nama Menu</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Status</th>
                                        <th width="150">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result) > 0): ?>
                                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td>
                                                    <?php if (!empty($row['gambar']) && file_exists('assets/uploads/menu/' . $row['gambar'])): ?>
                                                        <img src="assets/uploads/menu/<?php echo $row['gambar']; ?>" class="img-thumbnail" style="max-width: 60px;">
                                                    <?php else: ?>
                                                        <img src="https://via.placeholder.com/60?text=No+Image" class="img-thumbnail">
                                                    <?php endif; ?>
                                                </td>
                                                <td><b><?php echo $row['nama_menu']; ?></b><br>
                                                    <small class="text-muted"><?php echo $row['deskripsi']; ?></small>
                                                </td>
                                                <td><?php echo $row['nama_kategori'] ?: '-'; ?></td>
                                                <td><b><?php echo format_rupiah($row['harga']); ?></b></td>
                                                <td><?php echo $row['stok']; ?></td>
                                                <td>
                                                    <?php if ($row['status'] == 'tersedia'): ?>
                                                        <span class="badge badge-success">Tersedia</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Habis</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="menu_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?hapus=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" 
                                                       onclick="return confirm('Yakin ingin menghapus menu ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Belum ada data menu</td>
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
</body>
</html>
