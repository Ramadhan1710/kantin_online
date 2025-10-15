<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

check_role(['admin']);
$current_user = get_user($_SESSION['user_id']);

// Get ID
$id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
if (empty($id)) {
    header("Location: users.php");
    exit();
}

// Get data user
$query = "SELECT * FROM users WHERE id = '$id'";
$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) == 0) {
    header("Location: users.php");
    exit();
}
$user = mysqli_fetch_assoc($result);

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
    if (empty($nama) || empty($username) || empty($role)) {
        $error = "Nama, username, dan role harus diisi!";
    } else {
        // Cek username sudah ada (kecuali username sendiri)
        $check = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username' AND id != '$id'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Update password jika diisi
            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $password_query = ", password = '$password_hash'";
            } else {
                $password_query = "";
            }
            
            $query = "UPDATE users SET 
                      nama = '$nama',
                      username = '$username'
                      $password_query,
                      email = '$email',
                      no_hp = '$no_hp',
                      role = '$role',
                      status = '$status'
                      WHERE id = '$id'";
            
            if (mysqli_query($conn, $query)) {
                header("Location: users.php");
                exit();
            } else {
                $error = "Gagal mengupdate user!";
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
    <title>Edit User - Kantin Online</title>
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
                        <h1 class="m-0"><i class="fas fa-user-edit"></i> Edit User</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="users.php">User</a></li>
                            <li class="breadcrumb-item active">Edit</li>
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
                        <h3 class="card-title">Form Edit User</h3>
                    </div>
                    <form method="POST">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" name="nama" class="form-control" value="<?php echo $user['nama']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Username <span class="text-danger">*</span></label>
                                        <input type="text" name="username" class="form-control" value="<?php echo $user['username']; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah">
                                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>No HP</label>
                                        <input type="text" name="no_hp" class="form-control" value="<?php echo $user['no_hp']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Role <span class="text-danger">*</span></label>
                                        <select name="role" class="form-control" required>
                                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            <option value="kasir" <?php echo $user['role'] == 'kasir' ? 'selected' : ''; ?>>Kasir</option>
                                            <option value="pelanggan" <?php echo $user['role'] == 'pelanggan' ? 'selected' : ''; ?>>Pelanggan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-control" required>
                                            <option value="aktif" <?php echo $user['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="nonaktif" <?php echo $user['status'] == 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update
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
