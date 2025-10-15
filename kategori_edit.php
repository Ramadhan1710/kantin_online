<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

check_role(['admin']);
$user = get_user($_SESSION['user_id']);

// Get ID
$id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
if (empty($id)) {
    header("Location: kategori.php");
    exit();
}

// Get data kategori
$query = "SELECT * FROM kategori WHERE id = '$id'";
$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) == 0) {
    header("Location: kategori.php");
    exit();
}
$kategori = mysqli_fetch_assoc($result);

// Proses form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kategori = clean_input($_POST['nama_kategori']);
    $deskripsi = clean_input($_POST['deskripsi']);
    
    if (empty($nama_kategori)) {
        $error = "Nama kategori harus diisi!";
    } else {
        $query = "UPDATE kategori SET nama_kategori = '$nama_kategori', deskripsi = '$deskripsi' WHERE id = '$id'";
        if (mysqli_query($conn, $query)) {
            header("Location: kategori.php");
            exit();
        } else {
            $error = "Gagal mengupdate kategori!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Kategori - Kantin Online</title>
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
                        <h1 class="m-0"><i class="fas fa-edit"></i> Edit Kategori</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="kategori.php">Kategori</a></li>
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
                        <h3 class="card-title">Form Edit Kategori</h3>
                    </div>
                    <form method="POST">
                        <div class="card-body">
                            <div class="form-group">
                                <label>Nama Kategori <span class="text-danger">*</span></label>
                                <input type="text" name="nama_kategori" class="form-control" value="<?php echo $kategori['nama_kategori']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="3"><?php echo $kategori['deskripsi']; ?></textarea>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update
                            </button>
                            <a href="kategori.php" class="btn btn-secondary">
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
