<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

check_role(['admin']);
$user = get_user($_SESSION['user_id']);

// Proses form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = clean_input($_POST['nama']);
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    $email = clean_input($_POST['email']);
    $no_hp = clean_input($_POST['no_hp']);
    $role = clean_input($_POST['role']);
    $status = clean_input($_POST['status']);
    
    // Validasi
    if (empty($nama) || empty($username) || empty($password) || empty($role)) {
        $error = "Nama, username, password, dan role harus diisi!";
    } else {
        // Cek username sudah ada
        $check = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (nama, username, password, email, no_hp, role, status) 
                      VALUES ('$nama', '$username', '$password_hash', '$email', '$no_hp', '$role', '$status')";
            
            if (mysqli_query($conn, $query)) {
                header("Location: users.php");
                exit();
            } else {
                $error = "Gagal menambah user!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah User - Kantin Online</title>
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
                        <h1 class="m-0"><i class="fas fa-user-plus"></i> Tambah User</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="users.php">User</a></li>
                            <li class="breadcrumb-item active">Tambah</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-ban"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Form Tambah User</h3>
                    </div>
                    <form method="POST">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" name="nama" class="form-control" placeholder="Nama lengkap" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Username <span class="text-danger">*</span></label>
                                        <input type="text" name="username" class="form-control" placeholder="Username untuk login" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                                        <small class="text-muted">Minimal 6 karakter</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" placeholder="email@example.com">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>No HP</label>
                                        <input type="text" name="no_hp" class="form-control" placeholder="08123456789">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Role <span class="text-danger">*</span></label>
                                        <select name="role" class="form-control" required>
                                            <option value="">Pilih Role</option>
                                            <option value="admin">Admin</option>
                                            <option value="kasir">Kasir</option>
                                            <option value="pelanggan">Pelanggan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-control" required>
                                            <option value="aktif">Aktif</option>
                                            <option value="nonaktif">Nonaktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <a href="users.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
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
