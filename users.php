<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

check_role(['admin']);
$user = get_user($_SESSION['user_id']);

// Proses hapus user
if (isset($_GET['hapus'])) {
    $id = clean_input($_GET['hapus']);
    
    // Tidak boleh hapus diri sendiri
    if ($id == $_SESSION['user_id']) {
        $error = "Tidak bisa menghapus akun Anda sendiri!";
    } else {
        $query = "DELETE FROM users WHERE id = '$id'";
        if (mysqli_query($conn, $query)) {
            $success = "User berhasil dihapus!";
        } else {
            $error = "Gagal menghapus user!";
        }
    }
}

// Filter
$role_filter = isset($_GET['role']) ? clean_input($_GET['role']) : '';
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Query users dengan filter
$query = "SELECT * FROM users WHERE 1=1";

if (!empty($role_filter)) {
    $query .= " AND role = '$role_filter'";
}
if (!empty($status_filter)) {
    $query .= " AND status = '$status_filter'";
}
if (!empty($search)) {
    $query .= " AND (nama LIKE '%$search%' OR username LIKE '%$search%' OR email LIKE '%$search%')";
}

$query .= " ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data User - Kantin Online</title>
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
                        <h1 class="m-0"><i class="fas fa-users"></i> Data User</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Data User</li>
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
                        <h3 class="card-title">Daftar User</h3>
                        <div class="card-tools">
                            <a href="user_tambah.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Tambah User
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filter -->
                        <form method="GET" class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" placeholder="Cari nama, username, email..." value="<?php echo $search; ?>">
                                </div>
                                <div class="col-md-3">
                                    <select name="role" class="form-control">
                                        <option value="">Semua Role</option>
                                        <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        <option value="kasir" <?php echo $role_filter == 'kasir' ? 'selected' : ''; ?>>Kasir</option>
                                        <option value="pelanggan" <?php echo $role_filter == 'pelanggan' ? 'selected' : ''; ?>>Pelanggan</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-control">
                                        <option value="">Semua Status</option>
                                        <option value="aktif" <?php echo $status_filter == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="nonaktif" <?php echo $status_filter == 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="users.php" class="btn btn-secondary">
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
                                        <th>Nama</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>No HP</th>
                                        <th>Role</th>
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
                                                    <b><?php echo $row['nama']; ?></b>
                                                    <?php if ($row['id'] == $_SESSION['user_id']): ?>
                                                        <span class="badge badge-info badge-sm">You</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $row['username']; ?></td>
                                                <td><?php echo $row['email'] ?: '-'; ?></td>
                                                <td><?php echo $row['no_hp'] ?: '-'; ?></td>
                                                <td>
                                                    <?php
                                                    $badge = '';
                                                    switch ($row['role']) {
                                                        case 'admin': $badge = 'danger'; break;
                                                        case 'kasir': $badge = 'warning'; break;
                                                        case 'pelanggan': $badge = 'success'; break;
                                                    }
                                                    ?>
                                                    <span class="badge badge-<?php echo $badge; ?>">
                                                        <?php echo ucfirst($row['role']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($row['status'] == 'aktif'): ?>
                                                        <span class="badge badge-success">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Nonaktif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="user_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                                        <a href="?hapus=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" 
                                                           onclick="return confirm('Yakin ingin menghapus user ini?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Belum ada data user</td>
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
