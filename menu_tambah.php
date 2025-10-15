<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

check_role(['admin']);
$user = get_user($_SESSION['user_id']);

// Proses form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_menu = clean_input($_POST['nama_menu']);
    $kategori_id = clean_input($_POST['kategori_id']);
    $harga = clean_input($_POST['harga']);
    $deskripsi = clean_input($_POST['deskripsi']);
    $stok = clean_input($_POST['stok']);
    $status = clean_input($_POST['status']);
    
    $gambar = '';
    
    // Validasi
    if (empty($nama_menu) || empty($harga) || empty($stok)) {
        $error = "Nama menu, harga, dan stok harus diisi!";
    } else {
        // Upload gambar jika ada
        if (!empty($_FILES['gambar']['name'])) {
            $upload = upload_gambar($_FILES['gambar'], 'menu');
            if ($upload['status']) {
                $gambar = $upload['file_name'];
            } else {
                $error = $upload['message'];
            }
        }
        
        if (!isset($error)) {
            $query = "INSERT INTO menu (nama_menu, kategori_id, harga, deskripsi, gambar, stok, status) 
                      VALUES ('$nama_menu', " . ($kategori_id ? "'$kategori_id'" : "NULL") . ", '$harga', '$deskripsi', '$gambar', '$stok', '$status')";
            
            if (mysqli_query($conn, $query)) {
                header("Location: menu.php");
                exit();
            } else {
                $error = "Gagal menambah menu!";
            }
        }
    }
}

// Get kategori
$query_kategori = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($conn, $query_kategori);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Menu - Kantin Online</title>
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
                        <h1 class="m-0"><i class="fas fa-plus"></i> Tambah Menu</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="menu.php">Menu</a></li>
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
                        <h3 class="card-title">Form Tambah Menu</h3>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Menu <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_menu" class="form-control" placeholder="Contoh: Nasi Goreng" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kategori</label>
                                        <select name="kategori_id" class="form-control">
                                            <option value="">Pilih Kategori</option>
                                            <?php while ($kat = mysqli_fetch_assoc($result_kategori)): ?>
                                                <option value="<?php echo $kat['id']; ?>"><?php echo $kat['nama_kategori']; ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Harga <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="number" name="harga" class="form-control" placeholder="15000" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Stok <span class="text-danger">*</span></label>
                                        <input type="number" name="stok" class="form-control" value="0" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-control" required>
                                            <option value="tersedia">Tersedia</option>
                                            <option value="habis">Habis</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi menu..."></textarea>
                            </div>

                            <div class="form-group">
                                <label>Gambar Menu</label>
                                <div class="custom-file">
                                    <input type="file" name="gambar" class="custom-file-input" id="gambar" accept="image/*" onchange="previewImage(event)">
                                    <label class="custom-file-label" for="gambar">Pilih gambar...</label>
                                </div>
                                <small class="text-muted">Format: JPG, JPEG, PNG, GIF. Maksimal 2MB.</small>
                                <div class="mt-2">
                                    <img id="preview" src="" style="max-width: 200px; display: none;" class="img-thumbnail">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <a href="menu.php" class="btn btn-secondary">
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
<script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input@1.3.4/dist/bs-custom-file-input.min.js"></script>
<script>
$(function () {
    bsCustomFileInput.init();
});

function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function(){
        var output = document.getElementById('preview');
        output.src = reader.result;
        output.style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
</body>
</html>
