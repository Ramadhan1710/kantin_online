<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Cek login dan role admin
check_role(['admin']);

// Get data user
$user = get_user($_SESSION['user_id']);

// Proses hapus kategori
if (isset($_GET['hapus'])) {
    $id = clean_input($_GET['hapus']);
    
    // Cek apakah kategori masih dipakai di menu
    $check = mysqli_query($conn, "SELECT COUNT(*) as total FROM menu WHERE kategori_id = '$id'");
    $row = mysqli_fetch_assoc($check);
    
    if ($row['total'] > 0) {
        $error = "Kategori tidak bisa dihapus karena masih digunakan di menu!";
    } else {
        $query = "DELETE FROM kategori WHERE id = '$id'";
        if (mysqli_query($conn, $query)) {
            $success = "Kategori berhasil dihapus!";
        } else {
            $error = "Gagal menghapus kategori!";
        }
    }
}

// Get semua kategori
$query = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kategori Menu - Kantin Online</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-list"></i> Kategori Menu</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Kategori Menu</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
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
                        <h3 class="card-title">Daftar Kategori Menu</h3>
                        <div class="card-tools">
                            <a href="kategori_tambah.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Tambah Kategori
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Nama Kategori</th>
                                    <th>Deskripsi</th>
                                    <th>Tanggal Dibuat</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result) > 0): ?>
                                    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><b><?php echo $row['nama_kategori']; ?></b></td>
                                            <td><?php echo $row['deskripsi'] ?: '-'; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <a href="kategori_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="?hapus=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada data kategori</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
