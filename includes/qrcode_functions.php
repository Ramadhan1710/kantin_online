<?php
// Fungsi untuk generate QR Code untuk pembayaran
// Menggunakan library phpqrcode

function generate_qr_code($data, $filename) {
    // Path untuk save QR code
    $path = 'assets/qrcodes/';
    
    // Buat folder jika belum ada
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
    
    // Include library phpqrcode (akan kita download)
    require_once 'phpqrcode/qrlib.php';
    
    // Generate QR Code
    $file = $path . $filename . '.png';
    QRcode::png($data, $file, QR_ECLEVEL_L, 10);
    
    return $filename . '.png';
}

// Fungsi untuk generate barcode sederhana (menggunakan barcode generator API)
function generate_barcode_image($code) {
    // Menggunakan barcode generator API gratis
    $url = "https://bwipjs-api.metafloor.com/?bcid=code128&text=" . urlencode($code) . "&scale=3";
    return $url;
}

// Fungsi untuk membuat data pembayaran dari pesanan
function create_payment_data($pesanan_id) {
    global $conn;
    
    // Get data pesanan
    $query = "SELECT p.*, u.nama, u.no_hp FROM pesanan p 
              JOIN users u ON p.user_id = u.id 
              WHERE p.id = '$pesanan_id'";
    $result = mysqli_query($conn, $query);
    $pesanan = mysqli_fetch_assoc($result);
    
    if (!$pesanan) {
        return false;
    }
    
    // Format data untuk QR Code (bisa disesuaikan dengan kebutuhan)
    $data = array(
        'kode_pesanan' => $pesanan['kode_pesanan'],
        'total' => $pesanan['total_harga'],
        'nama' => $pesanan['nama'],
        'timestamp' => time()
    );
    
    return json_encode($data);
}
?>
