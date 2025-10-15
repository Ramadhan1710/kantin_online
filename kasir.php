<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

check_role(['kasir', 'admin']);
$current_user = get_user($_SESSION['user_id']);

// Inisialisasi keranjang
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
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
        if (isset($_SESSION['cart'][$menu_id])) {
            $_SESSION['cart'][$menu_id]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$menu_id] = [
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
        unset($_SESSION['cart'][$menu_id]);
    } else {
        $_SESSION['cart'][$menu_id]['qty'] = $qty;
    }
    
    echo json_encode(['status' => 'success']);
    exit();
}

// Handle remove item
if (isset($_POST['action']) && $_POST['action'] == 'remove_item') {
    $menu_id = clean_input($_POST['menu_id']);
    unset($_SESSION['cart'][$menu_id]);
    
    echo json_encode(['status' => 'success']);
    exit();
}

// Handle clear cart
if (isset($_POST['action']) && $_POST['action'] == 'clear_cart') {
    $_SESSION['cart'] = [];
    echo json_encode(['status' => 'success']);
    exit();
}

// Handle proses pesanan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proses_pesanan'])) {
    if (empty($_SESSION['cart'])) {
        $error = "Keranjang masih kosong!";
    } else {
        $nama_pelanggan = clean_input($_POST['nama_pelanggan']);
        $no_hp = clean_input($_POST['no_hp']);
        $metode_pembayaran = clean_input($_POST['metode_pembayaran']);
        
        if (empty($nama_pelanggan)) {
            $error = "Nama pelanggan harus diisi!";
        } else {
            // Hitung total
            $total = 0;
            foreach ($_SESSION['cart'] as $item) {
                $total += $item['harga'] * $item['qty'];
            }
            
            // Generate kode pesanan
            $kode_pesanan = generate_kode_pesanan();
            
            // Tentukan status pembayaran berdasarkan metode
            $status_pembayaran = ($metode_pembayaran == 'cash') ? 'lunas' : 'belum_lunas';
            
            // Insert pesanan
            $query = "INSERT INTO pesanan (kode_pesanan, user_id, nama_pelanggan, no_hp, total_harga, status_pesanan, metode_pembayaran, status_pembayaran) 
                     VALUES ('$kode_pesanan', '{$_SESSION['user_id']}', '$nama_pelanggan', '$no_hp', '$total', 'diproses', '$metode_pembayaran', '$status_pembayaran')";
            
            if (mysqli_query($conn, $query)) {
                $pesanan_id = mysqli_insert_id($conn);
                
                // Insert detail pesanan
                foreach ($_SESSION['cart'] as $item) {
                    $menu_id = $item['id'];
                    $qty = $item['qty'];
                    $harga = $item['harga'];
                    $subtotal = $harga * $qty;
                    
                    $query_detail = "INSERT INTO detail_pesanan (pesanan_id, menu_id, qty, harga, subtotal) 
                                    VALUES ('$pesanan_id', '$menu_id', '$qty', '$harga', '$subtotal')";
                    mysqli_query($conn, $query_detail);
                }
                
                // Clear cart
                $_SESSION['cart'] = [];
                
                // Redirect ke detail pesanan
                header("Location: pesanan_detail.php?id=$pesanan_id");
                exit();
            } else {
                $error = "Gagal membuat pesanan!";
            }
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kasir/POS - Kantin Online</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        .menu-item {
            cursor: pointer;
            transition: all 0.3s;
        }
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .menu-img {
            height: 150px;
            object-fit: cover;
        }
        .cart-fixed {
            position: sticky;
            top: 70px;
        }
        .qty-control {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .qty-control input {
            width: 60px;
            text-align: center;
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
                        <h1 class="m-0"><i class="fas fa-cash-register"></i> Kasir / POS</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Kasir</li>
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

                <div class="row">
                    <!-- Menu List -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Daftar Menu</h3>
                            </div>
                            <div class="card-body">
                                <!-- Filter -->
                                <form method="GET" class="mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="text" name="search" class="form-control" placeholder="Cari menu..." value="<?php echo $search; ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <select name="kategori" class="form-control">
                                                <option value="">Semua Kategori</option>
                                                <?php while($kat = mysqli_fetch_assoc($result_kategori)): ?>
                                                    <option value="<?php echo $kat['id']; ?>" <?php echo $kategori == $kat['id'] ? 'selected' : ''; ?>>
                                                        <?php echo $kat['nama_kategori']; ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-search"></i> Cari
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <!-- Menu Grid -->
                                <div class="row">
                                    <?php if (mysqli_num_rows($result_menu) > 0): ?>
                                        <?php while($menu = mysqli_fetch_assoc($result_menu)): ?>
                                            <div class="col-md-4 col-sm-6 mb-3">
                                                <div class="card menu-item" onclick="addToCart(<?php echo $menu['id']; ?>, '<?php echo addslashes($menu['nama_menu']); ?>', <?php echo $menu['harga']; ?>)">
                                                    <?php if (!empty($menu['gambar']) && file_exists(UPLOAD_PATH . 'menu/' . $menu['gambar'])): ?>
                                                        <img src="<?php echo BASE_URL; ?>assets/uploads/menu/<?php echo $menu['gambar']; ?>" class="card-img-top menu-img" alt="<?php echo $menu['nama_menu']; ?>">
                                                    <?php else: ?>
                                                        <img src="https://via.placeholder.com/300x150?text=No+Image" class="card-img-top menu-img" alt="No Image">
                                                    <?php endif; ?>
                                                    <div class="card-body p-2">
                                                        <h6 class="card-title mb-1"><?php echo $menu['nama_menu']; ?></h6>
                                                        <p class="card-text mb-1">
                                                            <small class="text-muted"><?php echo $menu['nama_kategori']; ?></small>
                                                        </p>
                                                        <p class="card-text mb-0">
                                                            <strong class="text-primary"><?php echo format_rupiah($menu['harga']); ?></strong>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> Tidak ada menu tersedia
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cart -->
                    <div class="col-md-4">
                        <div class="card cart-fixed">
                            <div class="card-header bg-primary">
                                <h3 class="card-title">Keranjang</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-sm btn-danger" onclick="clearCart()">
                                        <i class="fas fa-trash"></i> Clear
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-2" style="max-height: 400px; overflow-y: auto;">
                                <div id="cart-items">
                                    <?php if (empty($_SESSION['cart'])): ?>
                                        <div class="text-center text-muted py-5">
                                            <i class="fas fa-shopping-cart fa-3x mb-2"></i>
                                            <p>Keranjang kosong</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($_SESSION['cart'] as $item): ?>
                                            <div class="cart-item mb-2 p-2 border-bottom">
                                                <div class="d-flex justify-content-between">
                                                    <strong><?php echo $item['nama']; ?></strong>
                                                    <button class="btn btn-sm btn-danger" onclick="removeItem(<?php echo $item['id']; ?>)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-1">
                                                    <div class="qty-control">
                                                        <button class="btn btn-sm btn-secondary" onclick="updateQty(<?php echo $item['id']; ?>, <?php echo $item['qty'] - 1; ?>)">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <input type="number" class="form-control form-control-sm" value="<?php echo $item['qty']; ?>" 
                                                               onchange="updateQty(<?php echo $item['id']; ?>, this.value)" min="1">
                                                        <button class="btn btn-sm btn-secondary" onclick="updateQty(<?php echo $item['id']; ?>, <?php echo $item['qty'] + 1; ?>)">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                    <span class="text-primary font-weight-bold">
                                                        <?php echo format_rupiah($item['harga'] * $item['qty']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer">
                                <?php 
                                $total = 0;
                                foreach ($_SESSION['cart'] as $item) {
                                    $total += $item['harga'] * $item['qty'];
                                }
                                ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>Total:</strong>
                                    <strong class="text-success" style="font-size: 1.2em;" id="total-harga">
                                        <?php echo format_rupiah($total); ?>
                                    </strong>
                                </div>
                                <button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#modalCheckout" 
                                        <?php echo empty($_SESSION['cart']) ? 'disabled' : ''; ?>>
                                    <i class="fas fa-shopping-bag"></i> Proses Pesanan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>

<!-- Modal Checkout -->
<div class="modal fade" id="modalCheckout">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-success">
                    <h4 class="modal-title">Proses Pesanan</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Pelanggan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_pelanggan" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>No HP</label>
                        <input type="text" name="no_hp" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="metode_pembayaran" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="qris">QRIS</option>
                            <option value="transfer">Transfer Bank</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <strong>Total: <?php echo format_rupiah($total); ?></strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="proses_pesanan" class="btn btn-success">
                        <i class="fas fa-check"></i> Proses
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
function addToCart(id, nama, harga) {
    $.post('kasir.php', {
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
    
    $.post('kasir.php', {
        action: 'update_qty',
        menu_id: id,
        qty: qty
    }, function() {
        location.reload();
    });
}

function removeItem(id) {
    if (confirm('Hapus item dari keranjang?')) {
        $.post('kasir.php', {
            action: 'remove_item',
            menu_id: id
        }, function() {
            location.reload();
        });
    }
}

function clearCart() {
    if (confirm('Kosongkan semua keranjang?')) {
        $.post('kasir.php', {
            action: 'clear_cart'
        }, function() {
            location.reload();
        });
    }
}
</script>
<!-- Notifikasi Pesanan -->
<script src="assets/js/notifications.js"></script>
</body>
</html>
