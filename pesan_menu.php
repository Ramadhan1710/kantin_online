<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

check_role(['pelanggan', 'admin']);
$current_user = get_user($_SESSION['user_id']);

// Inisialisasi keranjang pelanggan
if (!isset($_SESSION['cart_pelanggan'])) {
    $_SESSION['cart_pelanggan'] = [];
}

// Handle Ajax Request untuk tambah ke keranjang
if (isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {
    $menu_id = clean_input($_POST['menu_id']);
    $qty = (int)$_POST['qty'];
    
    // Get menu data
    $query = "SELECT * FROM menu WHERE id = '$menu_id' AND status = 'tersedia'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $menu = mysqli_fetch_assoc($result);
        
        // Cek jika sudah ada di keranjang
        if (isset($_SESSION['cart_pelanggan'][$menu_id])) {
            $_SESSION['cart_pelanggan'][$menu_id]['qty'] += $qty;
        } else {
            $_SESSION['cart_pelanggan'][$menu_id] = [
                'id' => $menu['id'],
                'nama' => $menu['nama_menu'],
                'harga' => $menu['harga'],
                'qty' => $qty,
                'gambar' => $menu['gambar']
            ];
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Menu ditambahkan ke keranjang']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Menu tidak ditemukan']);
    }
    exit();
}

// Handle update qty
if (isset($_POST['action']) && $_POST['action'] == 'update_qty') {
    $menu_id = clean_input($_POST['menu_id']);
    $qty = (int)$_POST['qty'];
    
    if ($qty <= 0) {
        unset($_SESSION['cart_pelanggan'][$menu_id]);
    } else {
        $_SESSION['cart_pelanggan'][$menu_id]['qty'] = $qty;
    }
    
    echo json_encode(['status' => 'success']);
    exit();
}

// Handle remove item
if (isset($_POST['action']) && $_POST['action'] == 'remove_item') {
    $menu_id = clean_input($_POST['menu_id']);
    unset($_SESSION['cart_pelanggan'][$menu_id]);
    
    echo json_encode(['status' => 'success']);
    exit();
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    if (empty($_SESSION['cart_pelanggan'])) {
        $error = "Keranjang masih kosong!";
    } else {
        $catatan = clean_input($_POST['catatan']);
        $metode_pembayaran = clean_input($_POST['metode_pembayaran']);
        
        // Hitung total
        $total = 0;
        foreach ($_SESSION['cart_pelanggan'] as $item) {
            $total += $item['harga'] * $item['qty'];
        }
        
        // Generate kode pesanan
        $kode_pesanan = generate_kode_pesanan();
        
        // Insert pesanan
        $query = "INSERT INTO pesanan (kode_pesanan, user_id, nama_pelanggan, no_hp, total_harga, status_pesanan, metode_pembayaran, status_pembayaran, catatan) 
                 VALUES ('$kode_pesanan', '{$_SESSION['user_id']}', '{$current_user['nama']}', '{$current_user['no_hp']}', '$total', 'menunggu', '$metode_pembayaran', 'belum_lunas', '$catatan')";
        
        if (mysqli_query($conn, $query)) {
            $pesanan_id = mysqli_insert_id($conn);
            
            // Insert detail pesanan
            foreach ($_SESSION['cart_pelanggan'] as $item) {
                $menu_id = $item['id'];
                $qty = $item['qty'];
                $harga = $item['harga'];
                $subtotal = $harga * $qty;
                
                $query_detail = "INSERT INTO detail_pesanan (pesanan_id, menu_id, qty, harga, subtotal) 
                                VALUES ('$pesanan_id', '$menu_id', '$qty', '$harga', '$subtotal')";
                mysqli_query($conn, $query_detail);
            }
            
            // Clear cart
            $_SESSION['cart_pelanggan'] = [];
            
            // Redirect ke detail pesanan
            header("Location: pesanan_detail.php?id=$pesanan_id");
            exit();
        } else {
            $error = "Gagal membuat pesanan!";
        }
    }
}

// Get menu untuk ditampilkan
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? clean_input($_GET['kategori']) : '';

$where = "WHERE m.status = 'tersedia'";
if (!empty($search)) {
    $where .= " AND m.nama_menu LIKE '%$search%'";
}
if (!empty($kategori)) {
    $where .= " AND m.kategori_id = '$kategori'";
}

$query = "SELECT m.*, k.nama_kategori 
          FROM menu m 
          LEFT JOIN kategori k ON m.kategori_id = k.id 
          $where 
          ORDER BY m.nama_menu ASC";
$result_menu = mysqli_query($conn, $query);

// Get kategori untuk filter
$query_kategori = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($conn, $query_kategori);

// Hitung total keranjang
$total = 0;
$total_items = 0;
foreach ($_SESSION['cart_pelanggan'] as $item) {
    $total += $item['harga'] * $item['qty'];
    $total_items += $item['qty'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesan Menu - Kantin Online</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        .menu-card {
            transition: all 0.3s;
            height: 100%;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .menu-img {
            height: 200px;
            object-fit: cover;
        }
        .cart-badge {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        .cart-float {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            font-size: 24px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
    </style>
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
                        <h1 class="m-0"><i class="fas fa-utensils"></i> Pesan Menu</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Pesan Menu</li>
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

                <!-- Filter -->
                <div class="card">
                    <div class="card-body">
                        <form method="GET">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Cari menu..." value="<?php echo $search; ?>">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <select name="kategori" class="form-control" onchange="this.form.submit()">
                                        <option value="">Semua Kategori</option>
                                        <?php 
                                        mysqli_data_seek($result_kategori, 0);
                                        while($kat = mysqli_fetch_assoc($result_kategori)): 
                                        ?>
                                            <option value="<?php echo $kat['id']; ?>" <?php echo $kategori == $kat['id'] ? 'selected' : ''; ?>>
                                                <?php echo $kat['nama_kategori']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 text-right">
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalCart">
                                        <i class="fas fa-shopping-cart"></i> Keranjang 
                                        <span class="badge badge-light"><?php echo $total_items; ?></span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Menu Grid -->
                <div class="row">
                    <?php if (mysqli_num_rows($result_menu) > 0): ?>
                        <?php while($menu = mysqli_fetch_assoc($result_menu)): ?>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                <div class="card menu-card">
                                    <?php if (!empty($menu['gambar']) && file_exists(UPLOAD_PATH . 'menu/' . $menu['gambar'])): ?>
                                        <img src="<?php echo BASE_URL; ?>assets/uploads/menu/<?php echo $menu['gambar']; ?>" class="card-img-top menu-img" alt="<?php echo $menu['nama_menu']; ?>">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/300x200?text=<?php echo urlencode($menu['nama_menu']); ?>" class="card-img-top menu-img" alt="No Image">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $menu['nama_menu']; ?></h5>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <i class="fas fa-tag"></i> <?php echo $menu['nama_kategori']; ?>
                                            </small>
                                        </p>
                                        <?php if (!empty($menu['deskripsi'])): ?>
                                            <p class="card-text text-sm"><?php echo substr($menu['deskripsi'], 0, 60); ?>...</p>
                                        <?php endif; ?>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="text-primary mb-0"><?php echo format_rupiah($menu['harga']); ?></h5>
                                            <button class="btn btn-success btn-sm" onclick="addToCart(<?php echo $menu['id']; ?>)">
                                                <i class="fas fa-cart-plus"></i> Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <p class="mb-0">Menu tidak ditemukan</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>

<!-- Floating Cart Button -->
<?php if ($total_items > 0): ?>
<div class="cart-badge">
    <button class="btn btn-success cart-float" data-toggle="modal" data-target="#modalCart">
        <i class="fas fa-shopping-cart"></i>
        <span class="badge badge-danger" style="position: absolute; top: 5px; right: 5px;">
            <?php echo $total_items; ?>
        </span>
    </button>
</div>
<?php endif; ?>

<!-- Modal Cart -->
<div class="modal fade" id="modalCart">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title"><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <?php if (empty($_SESSION['cart_pelanggan'])): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                        <p class="text-muted">Keranjang belanja kosong</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Menu</th>
                                    <th>Harga</th>
                                    <th width="150">Qty</th>
                                    <th>Subtotal</th>
                                    <th width="50">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="cart-items">
                                <?php foreach ($_SESSION['cart_pelanggan'] as $item): ?>
                                    <tr>
                                        <td><?php echo $item['nama']; ?></td>
                                        <td><?php echo format_rupiah($item['harga']); ?></td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <button class="btn btn-secondary" onclick="updateQty(<?php echo $item['id']; ?>, <?php echo $item['qty'] - 1; ?>)">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </div>
                                                <input type="number" class="form-control text-center" value="<?php echo $item['qty']; ?>" 
                                                       onchange="updateQty(<?php echo $item['id']; ?>, this.value)" min="1">
                                                <div class="input-group-append">
                                                    <button class="btn btn-secondary" onclick="updateQty(<?php echo $item['id']; ?>, <?php echo $item['qty'] + 1; ?>)">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="font-weight-bold"><?php echo format_rupiah($item['harga'] * $item['qty']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-danger" onclick="removeItem(<?php echo $item['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                    <td colspan="2">
                                        <h5 class="text-success mb-0"><?php echo format_rupiah($total); ?></h5>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <label>Catatan (Opsional)</label>
                            <textarea name="catatan" class="form-control" rows="2" placeholder="Contoh: Tidak pakai cabe"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Metode Pembayaran <span class="text-danger">*</span></label>
                            <select name="metode_pembayaran" class="form-control" required>
                                <option value="cash">Cash</option>
                                <option value="qris">QRIS</option>
                                <option value="transfer">Transfer Bank</option>
                            </select>
                        </div>
                        <div class="text-right">
                            <button type="submit" name="checkout" class="btn btn-success btn-lg">
                                <i class="fas fa-check"></i> Checkout (<?php echo format_rupiah($total); ?>)
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
function addToCart(id) {
    $.post('pesan_menu.php', {
        action: 'add_to_cart',
        menu_id: id,
        qty: 1
    }, function(response) {
        let data = JSON.parse(response);
        if (data.status == 'success') {
            location.reload();
        } else {
            alert(data.message);
        }
    });
}

function updateQty(id, qty) {
    if (qty < 1) {
        removeItem(id);
        return;
    }
    
    $.post('pesan_menu.php', {
        action: 'update_qty',
        menu_id: id,
        qty: qty
    }, function() {
        location.reload();
    });
}

function removeItem(id) {
    if (confirm('Hapus item dari keranjang?')) {
        $.post('pesan_menu.php', {
            action: 'remove_item',
            menu_id: id
        }, function() {
            location.reload();
        });
    }
}
</script>
</body>
</html>
