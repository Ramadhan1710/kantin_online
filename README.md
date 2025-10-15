# Sistem Pemesanan Menu Kantin Online

Sistem pemesanan menu kantin berbasis web menggunakan PHP Native dan AdminLTE.

## 📋 Fitur Sistem

### 3 Tipe User:
1. **Admin** - Mengelola menu, kategori, user, dan laporan
2. **Kasir** - Memproses pesanan dan pembayaran
3. **Pelanggan** - Memesan menu dan melihat status pesanan

## 🚀 Cara Install

**LOKASI PROJECT ANDA:** `D:\PROJECT\php\kantin_online`

### CARA TERCEPAT (Menggunakan PHP Built-in Server):

### Step 1: Setup Database
1. Buka **Laragon**, klik **Start All**
2. Klik kanan icon Laragon → **MySQL** → **phpMyAdmin**
3. Import file `database.sql` (database otomatis dibuat)
   - Pilih tab **Import**
   - Pilih file `database.sql`
   - Klik **Go**
   - ✅ Database `kantin_online` otomatis dibuat dengan struktur + data + optimasi

### Step 2: Jalankan Server
1. Buka **PowerShell** atau **Terminal**
2. Masuk ke folder project:
   ```bash
   cd D:\PROJECT\php\kantin_online
   ```
3. Jalankan PHP server:
   ```bash
   php -S localhost:8000
   ```

### Step 3: Akses Aplikasi
1. Buka browser, akses: `http://localhost:8000/login.php`
2. Login dengan akun demo:
   - **Admin**: username: `admin` / password: `admin123`
   - **Kasir**: username: `kasir` / password: `admin123`
   - **Pelanggan**: username: `budi` / password: `admin123`

### ATAU: Double Klik File Batch (Lebih Mudah!)
1. Double klik file `jalankan.bat`
2. Browser otomatis terbuka
3. Login dengan akun di atas

**CATATAN:** AdminLTE sudah menggunakan CDN (dari internet), jadi:
- ✅ Tidak perlu download AdminLTE
- ✅ Tidak perlu setup plugins
- ✅ Pastikan ada koneksi internet saat development
- ✅ Dashboard akan tampil sempurna!

## 📁 Struktur Database

### Tabel Users
- Menyimpan data admin, kasir, dan pelanggan
- Field: id, nama, username, password, email, no_hp, role, foto, status

### Tabel Kategori
- Menyimpan kategori menu (Makanan Berat, Snack, Minuman, Dessert)
- Field: id, nama_kategori, deskripsi

### Tabel Menu
- Menyimpan data menu yang tersedia
- Field: id, nama_menu, kategori_id, harga, deskripsi, gambar, stok, status

### Tabel Pesanan
- Menyimpan data pesanan pelanggan
- Field: id, kode_pesanan, user_id, total_harga, status, metode_pembayaran, catatan

### Tabel Detail Pesanan
- Menyimpan detail item pesanan
- Field: id, pesanan_id, menu_id, jumlah, harga, subtotal

## 🎯 Fitur yang Sudah Dibuat

✅ Login System dengan role-based access
✅ Dashboard dengan statistik
✅ Fungsi-fungsi helper (format rupiah, tanggal, dll)
✅ Database lengkap dengan sample data

## 📝 Fitur yang Perlu Dikembangkan Selanjutnya

- [ ] CRUD Kategori Menu (untuk Admin)
- [ ] CRUD Menu (untuk Admin)
- [ ] CRUD User (untuk Admin)
- [ ] Halaman Kasir (untuk Kasir)
- [ ] Halaman Pesan Menu (untuk Pelanggan)
- [ ] Manajemen Pesanan
- [ ] Laporan Penjualan
- [ ] Update Profil

## 🔐 Default Login

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |
| Kasir | kasir | admin123 |
| Pelanggan | budi | admin123 |

## 💡 Tips Pengembangan

1. **Untuk Admin**: Mulai dengan membuat halaman CRUD Menu dan Kategori
2. **Untuk Kasir**: Buat halaman kasir untuk input pesanan
3. **Untuk Pelanggan**: Buat halaman katalog menu dan keranjang belanja

## 📞 Support

Jika ada pertanyaan atau masalah, silakan hubungi developer.

---
**Dibuat dengan ❤️ menggunakan PHP Native & AdminLTE**
