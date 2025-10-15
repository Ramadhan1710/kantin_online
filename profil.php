<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

is_logged_in();
$current_user = get_user($_SESSION['user_id']);

$success = '';
$error = '';

// Handle update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])) {
    $nama = clean_input($_POST['nama']);
    $email = clean_input($_POST['email']);
    $no_hp = clean_input($_POST['no_hp']);
    
    if (empty($nama)) {
        $error = "Nama harus diisi!";
    } else {
        $query = "UPDATE users SET 
                  nama = '$nama',
                  email = '$email',
                  no_hp = '$no_hp'
                  WHERE id = '{$_SESSION['user_id']}'";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['nama'] = $nama;
            $success = "Profil berhasil diupdate!";
            $current_user = get_user($_SESSION['user_id']);
        } else {
            $error = "Gagal update profil!";
        }
    }
}

// Handle ganti password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ganti_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $password_konfirmasi = $_POST['password_konfirmasi'];
    
    if (empty($password_lama) || empty($password_baru) || empty($password_konfirmasi)) {
        $error = "Semua field password harus diisi!";
    } elseif ($password_baru !== $password_konfirmasi) {
        $error = "Password baru dan konfirmasi tidak cocok!";
    } elseif (strlen($password_baru) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        // Cek password lama
        if (password_verify($password_lama, $current_user['password'])) {
            $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
            $query = "UPDATE users SET password = '$password_hash' WHERE id = '{$_SESSION['user_id']}'";
            
            if (mysqli_query($conn, $query)) {
                $success = "Password berhasil diubah!";
            } else {
                $error = "Gagal mengubah password!";
            }
        } else {
            $error = "Password lama salah!";
        }
    }
}

// Handle upload foto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_foto'])) {
    if (!empty($_FILES['foto']['name'])) {
        $upload = upload_gambar($_FILES['foto'], 'users');
        if ($upload['status']) {
            // Hapus foto lama jika ada
            if (!empty($current_user['foto']) && file_exists(UPLOAD_PATH . 'users/' . $current_user['foto'])) {
                unlink(UPLOAD_PATH . 'users/' . $current_user['foto']);
            }
            
            $foto = $upload['file_name'];
            $query = "UPDATE users SET foto = '$foto' WHERE id = '{$_SESSION['user_id']}'";
            
            if (mysqli_query($conn, $query)) {
                // Update session foto
                $_SESSION['foto'] = $foto;
                $success = "Foto profil berhasil diupdate!";
                $current_user = get_user($_SESSION['user_id']);
            } else {
                $error = "Gagal update foto profil!";
            }
        } else {
            $error = $upload['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Saya - Kantin Online</title>
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
                        <h1 class="m-0"><i class="fas fa-user"></i> Profil Saya</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Profil</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-check"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-ban"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Foto Profil -->
                    <div class="col-md-4">
                        <div class="card card-primary card-outline">
                            <div class="card-body box-profile">
                                <div class="text-center">
                                    <?php if (!empty($current_user['foto']) && file_exists(UPLOAD_PATH . 'users/' . $current_user['foto'])): ?>
                                        <img src="<?php echo BASE_URL; ?>assets/uploads/users/<?php echo $current_user['foto']; ?>" 
                                             class="profile-user-img img-fluid img-circle" alt="Foto Profil" id="preview-foto">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/200?text=<?php echo substr($current_user['nama'], 0, 1); ?>" 
                                             class="profile-user-img img-fluid img-circle" alt="Foto Profil" id="preview-foto">
                                    <?php endif; ?>
                                </div>

                                <h3 class="profile-username text-center"><?php echo $current_user['nama']; ?></h3>
                                <p class="text-muted text-center">
                                    <?php
                                    $role_badge = '';
                                    switch($current_user['role']) {
                                        case 'admin': $role_badge = 'danger'; break;
                                        case 'kasir': $role_badge = 'warning'; break;
                                        case 'pelanggan': $role_badge = 'success'; break;
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $role_badge; ?>">
                                        <?php echo strtoupper($current_user['role']); ?>
                                    </span>
                                </p>

                                <form method="POST" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label>Upload Foto Baru</label>
                                        <input type="file" name="foto" class="form-control" accept="image/*" onchange="previewImage(this)">
                                        <small class="text-muted">Max 2MB, JPG/PNG</small>
                                    </div>
                                    <button type="submit" name="upload_foto" class="btn btn-primary btn-block">
                                        <i class="fas fa-upload"></i> Upload Foto
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Profil & Password -->
                    <div class="col-md-8">
                        <!-- Edit Profil -->
                        <div class="card">
                            <div class="card-header bg-primary">
                                <h3 class="card-title">Edit Profil</h3>
                            </div>
                            <form method="POST">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" name="nama" class="form-control" value="<?php echo $current_user['nama']; ?>" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Username</label>
                                                <input type="text" class="form-control" value="<?php echo $current_user['username']; ?>" disabled>
                                                <small class="text-muted">Username tidak dapat diubah</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Role</label>
                                                <input type="text" class="form-control" value="<?php echo ucfirst($current_user['role']); ?>" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" name="email" class="form-control" value="<?php echo $current_user['email']; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>No HP</label>
                                                <input type="text" name="no_hp" class="form-control" value="<?php echo $current_user['no_hp']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" name="update_profil" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Ganti Password -->
                        <div class="card">
                            <div class="card-header bg-warning">
                                <h3 class="card-title">Ganti Password</h3>
                            </div>
                            <form method="POST">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Password Lama <span class="text-danger">*</span></label>
                                        <input type="password" name="password_lama" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Password Baru <span class="text-danger">*</span></label>
                                        <input type="password" name="password_baru" class="form-control" required>
                                        <small class="text-muted">Minimal 6 karakter</small>
                                    </div>
                                    <div class="form-group">
                                        <label>Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                        <input type="password" name="password_konfirmasi" class="form-control" required>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" name="ganti_password" class="btn btn-warning">
                                        <i class="fas fa-key"></i> Ganti Password
                                    </button>
                                </div>
                            </form>
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
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-foto').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
