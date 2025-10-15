<?php
// File: functions.php
// Fungsi-fungsi umum untuk sistem kantin online

// Fungsi untuk membersihkan input dari user
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Fungsi untuk cek login
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk cek role user
function check_role($allowed_roles = []) {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
    
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        echo "<script>alert('Anda tidak memiliki akses ke halaman ini!'); window.location='dashboard.php';</script>";
        exit();
    }
}

// Fungsi untuk generate kode pesanan
function generate_kode_pesanan() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// Fungsi untuk format rupiah
function format_rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Fungsi untuk format tanggal Indonesia
function format_tanggal($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $pecah = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $pecah[2] . ' ' . $bulan[(int)$pecah[1]] . ' ' . $pecah[0];
}

// Fungsi untuk upload gambar
function upload_gambar($file, $folder = 'menu') {
    $target_dir = "assets/uploads/" . $folder . "/";
    
    // Buat folder jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = time() . '_' . basename($file["name"]);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Cek apakah file adalah gambar
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return array('status' => false, 'message' => 'File bukan gambar!');
    }
    
    // Cek ukuran file (max 2MB)
    if ($file["size"] > 2000000) {
        return array('status' => false, 'message' => 'Ukuran file terlalu besar! Max 2MB');
    }
    
    // Hanya izinkan format tertentu
    if (!in_array($imageFileType, array("jpg", "jpeg", "png", "gif"))) {
        return array('status' => false, 'message' => 'Hanya file JPG, JPEG, PNG & GIF yang diizinkan!');
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return array('status' => true, 'file_name' => $file_name);
    } else {
        return array('status' => false, 'message' => 'Gagal upload file!');
    }
}

// Fungsi untuk get data user berdasarkan ID
function get_user($user_id) {
    global $conn;
    $query = "SELECT * FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Fungsi untuk hitung total pesanan hari ini
function count_pesanan_hari_ini() {
    global $conn;
    $today = date('Y-m-d');
    $query = "SELECT COUNT(*) as total FROM pesanan WHERE DATE(created_at) = '$today'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Fungsi untuk hitung total pendapatan hari ini
function count_pendapatan_hari_ini() {
    global $conn;
    $today = date('Y-m-d');
    $query = "SELECT SUM(total_harga) as total FROM pesanan 
              WHERE DATE(created_at) = '$today' AND status_pesanan IN ('selesai', 'diproses') AND status_pembayaran = 'lunas'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ? $row['total'] : 0;
}

// Fungsi untuk hitung total menu
function count_menu() {
    global $conn;
    $query = "SELECT COUNT(*) as total FROM menu WHERE status = 'tersedia'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Fungsi untuk hitung total pelanggan
function count_pelanggan() {
    global $conn;
    $query = "SELECT COUNT(*) as total FROM users WHERE role = 'pelanggan' AND status = 'aktif'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}
?>
