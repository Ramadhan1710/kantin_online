<?php
session_start();
require_once '../config.php';
require_once '../functions.php';

// Set header untuk JSON
header('Content-Type: application/json');

// Cek login dan role kasir/admin
if (!is_logged_in() || !in_array($_SESSION['role'], ['kasir', 'admin'])) {
    echo json_encode(['count' => 0, 'notifications' => []]);
    exit();
}

// Cache result untuk 5 detik (menghindari query berulang terlalu cepat)
$cache_key = 'notif_' . $_SESSION['user_id'];
$cache_file = sys_get_temp_dir() . '/' . $cache_key . '.json';
$cache_time = 5; // seconds

// Cek cache
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
    echo file_get_contents($cache_file);
    exit();
}

// Ambil pesanan baru (menunggu konfirmasi) dalam 24 jam terakhir
// Optimasi: tambah index pada kolom yang sering diquery
$query = "SELECT 
          p.id,
          p.kode_pesanan,
          p.nama_pelanggan,
          p.total_harga,
          p.metode_pembayaran,
          p.status_pesanan,
          p.status_pembayaran,
          p.created_at,
          TIMESTAMPDIFF(MINUTE, p.created_at, NOW()) as minutes_ago
          FROM pesanan p
          WHERE p.status_pesanan = 'menunggu'
          AND p.status_pembayaran = 'belum_lunas'
          AND p.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
          ORDER BY p.created_at DESC
          LIMIT 10";

$result = mysqli_query($conn, $query);
$notifications = [];
$count = 0;

if ($result) {
    $count = mysqli_num_rows($result);
    while ($row = mysqli_fetch_assoc($result)) {
        // Format waktu
        $minutes = $row['minutes_ago'];
        if ($minutes < 1) {
            $time_text = 'Baru saja';
        } elseif ($minutes < 60) {
            $time_text = $minutes . ' menit lalu';
        } else {
            $hours = floor($minutes / 60);
            $time_text = $hours . ' jam lalu';
        }
        
        $notifications[] = [
            'id' => $row['id'],
            'kode' => $row['kode_pesanan'],
            'pelanggan' => $row['nama_pelanggan'],
            'total' => number_format($row['total_harga'], 0, ',', '.'),
            'metode' => ucfirst($row['metode_pembayaran']),
            'time' => $time_text,
            'time_raw' => $row['created_at']
        ];
    }
}

$response = json_encode([
    'count' => $count,
    'notifications' => $notifications
]);

// Simpan ke cache
file_put_contents($cache_file, $response);

echo $response;
