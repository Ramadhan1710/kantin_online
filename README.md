# 🍽️ Sistem Pemesanan Menu Kantin Online

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

Sistem pemesanan menu kantin berbasis web yang dibangun menggunakan **PHP Native** dan **AdminLTE 3.2**. Aplikasi ini memungkinkan pengelolaan menu kantin secara digital dengan fitur pemesanan online dan manajemen transaksi.

---

## ✨ Fitur Utama

### 👥 Multi-Role System

#### 🔐 Admin
- ✅ Manajemen Kategori Menu (CRUD)
- ✅ Manajemen Menu Kantin (CRUD)
- ✅ Manajemen User (Admin, Kasir, Pelanggan)
- ✅ Laporan Penjualan (Harian, Bulanan, Range)
- ✅ Dashboard dengan Statistik Real-time
- ✅ Notifikasi Pesanan Baru

#### 💰 Kasir
- ✅ Point of Sale (POS) untuk Input Pesanan Cepat
- ✅ Scan Barcode/QR Code Pesanan
- ✅ Konfirmasi Pembayaran (Cash, QRIS, Transfer)
- ✅ Update Status Pesanan
- ✅ Notifikasi Real-time

#### 🛒 Pelanggan
- ✅ Katalog Menu dengan Filter Kategori
- ✅ Keranjang Belanja
- ✅ Pemesanan Online
- ✅ QR Code untuk Pembayaran
- ✅ Tracking Status Pesanan
- ✅ Riwayat Pesanan

---

## 🚀 Quick Start

### Prasyarat
- PHP 8.0 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web Server (Apache/Nginx) atau PHP Built-in Server

### Instalasi

#### 1️⃣ Clone Repository
```bash
git clone https://github.com/Ramadhan1710/kantin_online.git
cd kantin_online
```

#### 2️⃣ Setup Database
**Option A: Via phpMyAdmin**
1. Buka phpMyAdmin
2. Klik tab **Import**
3. Pilih file `database.sql`
4. Klik **Go**

**Option B: Via Command Line**
```bash
mysql -u root -p < database.sql
```

> 💡 Database `kantin_online` akan dibuat otomatis dengan struktur, sample data, dan optimasi performa.

#### 3️⃣ Konfigurasi Database
Edit file `config.php` sesuaikan dengan kredensial database Anda:
```php
$host = 'localhost';
$user = 'root';           // Ganti dengan username MySQL Anda
$pass = '';               // Ganti dengan password MySQL Anda
$db   = 'kantin_online';
```

Atau copy dari template:
```bash
cp config.example.php config.php
# Edit config.php sesuai kebutuhan
```

#### 4️⃣ Buat Folder Upload
```bash
# Windows (PowerShell)
New-Item -ItemType Directory -Force -Path "assets\uploads\menu","assets\uploads\users","assets\qrcodes"

# Linux/Mac
mkdir -p assets/uploads/menu assets/uploads/users assets/qrcodes
chmod -R 777 assets/
```

#### 5️⃣ Jalankan Server
**Option A: PHP Built-in Server (Development)**
```bash
php -S localhost:8000
```

**Option B: Batch File (Windows)**
```bash
# Double click jalankan.bat
# atau
./jalankan.bat
```

**Option C: XAMPP/Laragon**
1. Copy project ke `htdocs` atau `www`
2. Akses via `http://localhost/kantin_online`

#### 6️⃣ Akses Aplikasi
Buka browser dan akses:
```
http://localhost:8000/login.php
```

---

## 🔐 Default Login

| Role | Username | Password |
|------|----------|----------|
| 👑 Admin | `admin` | `admin123` |
| 💰 Kasir | `kasir` | `admin123` |
| 🛒 Pelanggan | `budi` | `admin123` |

> ⚠️ **PENTING:** Ganti password default setelah login pertama kali!

---

## 📁 Struktur Project

## 📁 Struktur Project

```
kantin_online/
├── 📂 api/
│   └── get_notifications.php    # API notifikasi real-time
├── 📂 assets/
│   ├── 📂 js/
│   │   └── notifications.js     # JavaScript notifikasi
│   ├── 📂 uploads/              # Folder upload (buat manual)
│   │   ├── menu/                # Gambar menu
│   │   └── users/               # Foto profil user
│   └── 📂 qrcodes/              # QR codes pesanan
├── 📂 includes/
│   ├── navbar.php               # Navbar dengan notifikasi
│   ├── sidebar.php              # Sidebar menu
│   ├── footer.php               # Footer
│   └── qrcode_functions.php     # Helper QR code
├── 📄 config.php                # Konfigurasi database
├── 📄 functions.php             # Helper functions
├── 📄 login.php                 # Halaman login
├── 📄 dashboard.php             # Dashboard utama
├── 📄 kategori*.php             # CRUD Kategori (3 files)
├── 📄 menu*.php                 # CRUD Menu (3 files)
├── 📄 users*.php                # CRUD Users (3 files)
├── 📄 kasir.php                 # Point of Sale
├── 📄 pesan_menu.php            # Catalog & Order (Pelanggan)
├── 📄 pesanan*.php              # Order Management (2 files)
├── 📄 scan_barcode.php          # Barcode Scanner
├── 📄 laporan.php               # Sales Report
├── 📄 profil.php                # User Profile
├── 📄 database.sql              # Database schema + data
└── 📄 README.md                 # Dokumentasi ini
```

---

## 🗄️ Database Schema

### 📊 5 Tabel Utama

#### 1. **users** - Data Pengguna
```sql
id, nama, username, password, email, no_hp, 
role, foto, status, created_at, updated_at
```

#### 2. **kategori** - Kategori Menu
```sql
id, nama_kategori, deskripsi, created_at
```

#### 3. **menu** - Data Menu
```sql
id, nama_menu, kategori_id, harga, deskripsi, 
gambar, stok, status, created_at
```

#### 4. **pesanan** - Transaksi Pesanan
```sql
id, kode_pesanan, user_id, nama_pelanggan, no_hp,
total_harga, status_pesanan, metode_pembayaran,
status_pembayaran, catatan, created_at, updated_at
```

#### 5. **detail_pesanan** - Detail Item Pesanan
```sql
id, pesanan_id, menu_id, qty, harga, subtotal
```

> 💡 Database sudah include **5 indexes** untuk optimasi performa (query 10x lebih cepat!)

---

## 🛠️ Tech Stack

| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | 8.0+ | Backend Logic |
| **MySQL** | 5.7+ | Database |
| **AdminLTE** | 3.2 | Admin Template (CDN) |
| **jQuery** | 3.6 | JavaScript Library |
| **Chart.js** | 3.9 | Data Visualization |
| **Font Awesome** | 6.4 | Icons |
| **Bootstrap** | 4.6 | CSS Framework |

> 📌 **Note:** Semua library menggunakan CDN, pastikan ada koneksi internet saat development.

---

## 📈 Performance

### Database Optimization
- ✅ 5 Composite Indexes pada tabel `pesanan`
- ✅ Query caching untuk notifikasi (5 detik)
- ✅ Optimized JOIN queries

### Benchmark
- Dashboard load: **< 1 detik**
- Query pesanan: **~50ms** (dengan index)
- Notifikasi: **~30ms** (dengan caching)
- Auto-refresh: **60 detik** interval

---

## 🎨 Screenshots

### Admin Dashboard
![Dashboard](https://via.placeholder.com/800x400/667eea/ffffff?text=Dashboard+Admin)

### Kasir POS
![Kasir](https://via.placeholder.com/800x400/28a745/ffffff?text=Point+of+Sale)

### Pelanggan Catalog
![Catalog](https://via.placeholder.com/800x400/17a2b8/ffffff?text=Menu+Catalog)

---

## 🔧 Troubleshooting

### Error: "Connection failed"
```
❌ Masalah: Koneksi database gagal
✅ Solusi: 
   - Cek apakah MySQL sudah running
   - Periksa config.php (username, password, database name)
   - Pastikan database 'kantin_online' sudah di-import
```

### Error: "Access Denied"
```
❌ Masalah: Login gagal
✅ Solusi:
   - Pastikan sudah import database.sql
   - Gunakan kredensial default: admin/admin123
   - Cek tabel users di database
```

### Notifikasi tidak muncul
```
❌ Masalah: Notifikasi tidak tampil
✅ Solusi:
   - Cek folder api/get_notifications.php ada
   - Pastikan ada koneksi internet (untuk AdminLTE CDN)
   - Buka Console browser (F12) untuk cek error JavaScript
```

### Upload gambar gagal
```
❌ Masalah: Upload gambar error
✅ Solusi:
   - Buat folder: assets/uploads/menu dan assets/uploads/users
   - Set permission 777 (Linux/Mac): chmod -R 777 assets/
   - Cek php.ini: upload_max_filesize dan post_max_size
```

---

## 🚀 Deployment

### Production Checklist

- [ ] Ganti semua password default
- [ ] Hapus user demo (budi) jika tidak diperlukan
- [ ] Backup database secara berkala
- [ ] Set `error_reporting(0)` di production
- [ ] Gunakan HTTPS
- [ ] Batasi akses folder uploads
- [ ] Setup .htaccess untuk security
- [ ] Monitor log errors

### Environment Variables
Untuk production, gunakan environment variables:
```php
// config.php
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: 'kantin_online';
```

---

## 🤝 Contributing

Kontribusi sangat diterima! Silakan:

1. Fork repository ini
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

---

## 📝 License

Project ini menggunakan lisensi **MIT**. Lihat file `LICENSE` untuk detail.

---

## 👨‍💻 Author

**Ramadhan1710**
- GitHub: [@Ramadhan1710](https://github.com/Ramadhan1710)

---

## 🙏 Acknowledgments

- [AdminLTE](https://adminlte.io/) - Admin Dashboard Template
- [Font Awesome](https://fontawesome.com/) - Icons
- [Chart.js](https://www.chartjs.org/) - Charts Library
- [QR Server API](https://goqr.me/api/) - QR Code Generator

---

## 📞 Support

Jika menemukan bug atau ingin request fitur:
- 🐛 [Report Issues](https://github.com/Ramadhan1710/kantin_online/issues)
- 💬 [Discussions](https://github.com/Ramadhan1710/kantin_online/discussions)

---

## ⭐ Star History

Jika project ini membantu, berikan ⭐ ya!

[![Star History Chart](https://api.star-history.com/svg?repos=Ramadhan1710/kantin_online&type=Date)](https://star-history.com/#Ramadhan1710/kantin_online&Date)

---

<div align="center">

**Dibuat dengan ❤️ menggunakan PHP Native & AdminLTE**

[⬆ Back to Top](#-sistem-pemesanan-menu-kantin-online)

</div>
