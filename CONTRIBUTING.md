# Contributing to Kantin Online

Terima kasih atas ketertarikan Anda untuk berkontribusi pada project **Kantin Online**! 🎉

## 📋 Cara Berkontribusi

### 1. Fork Repository
- Klik tombol "Fork" di pojok kanan atas halaman repository
- Clone fork Anda ke komputer lokal:
  ```bash
  git clone https://github.com/USERNAME_ANDA/kantin_online.git
  cd kantin_online
  ```

### 2. Buat Branch Baru
```bash
git checkout -b feature/nama-fitur-anda
```

Naming convention untuk branch:
- `feature/nama-fitur` - Fitur baru
- `fix/nama-bug` - Perbaikan bug
- `docs/update` - Update dokumentasi
- `refactor/nama` - Refactoring code

### 3. Buat Perubahan
- Tulis kode yang clean dan readable
- Ikuti coding standards yang sudah ada
- Tambahkan komentar jika diperlukan
- Test perubahan Anda secara menyeluruh

### 4. Commit Perubahan
```bash
git add .
git commit -m "feat: deskripsi singkat fitur"
```

Commit message format:
- `feat:` - Fitur baru
- `fix:` - Perbaikan bug
- `docs:` - Perubahan dokumentasi
- `style:` - Formatting, missing semicolons, etc
- `refactor:` - Refactoring code
- `test:` - Menambah test
- `chore:` - Update dependencies, dll

### 5. Push ke GitHub
```bash
git push origin feature/nama-fitur-anda
```

### 6. Buat Pull Request
- Buka repository Anda di GitHub
- Klik "Compare & pull request"
- Jelaskan perubahan yang Anda buat
- Submit pull request

## 🎯 Apa yang Bisa Dikontribusikan?

### Fitur Baru
- [ ] Integrasi payment gateway (Midtrans, Xendit)
- [ ] Export laporan ke PDF/Excel
- [ ] Multi-language support
- [ ] Dark mode
- [ ] Progressive Web App (PWA)
- [ ] Email notification
- [ ] WhatsApp notification
- [ ] Loyalty/Points system
- [ ] Reviews & ratings

### Perbaikan
- [ ] Optimasi performance
- [ ] Security improvements
- [ ] UI/UX improvements
- [ ] Mobile responsiveness
- [ ] Accessibility (a11y)

### Dokumentasi
- [ ] Tutorial video
- [ ] API documentation
- [ ] Code comments
- [ ] Translation (English, etc)

## 💻 Coding Standards

### PHP
```php
// Gunakan camelCase untuk function
function getUserById($id) {
    // Code here
}

// Gunakan PascalCase untuk class
class UserController {
    // Code here
}

// Gunakan UPPERCASE untuk constants
define('MAX_LOGIN_ATTEMPTS', 3);

// Always use prepared statements untuk query
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
```

### JavaScript
```javascript
// Gunakan camelCase untuk variables dan functions
const userName = 'John';

function getUserData() {
    // Code here
}

// Gunakan const/let, hindari var
const API_URL = '/api/get_notifications.php';
let counter = 0;
```

### SQL
```sql
-- Gunakan UPPERCASE untuk keywords
SELECT * FROM users WHERE status = 'aktif';

-- Gunakan snake_case untuk nama tabel dan kolom
CREATE TABLE order_items (
    order_id INT,
    item_name VARCHAR(100)
);
```

### CSS
```css
/* Gunakan kebab-case untuk class names */
.login-container {
    padding: 20px;
}

/* Gunakan BEM notation untuk komponen kompleks */
.card__header--primary {
    background: #667eea;
}
```

## 🧪 Testing

Sebelum submit PR, pastikan:
- [ ] Semua fitur berjalan dengan baik
- [ ] Tidak ada error di console browser (F12)
- [ ] Tidak ada PHP errors/warnings
- [ ] Test di multiple browsers (Chrome, Firefox, Safari)
- [ ] Test responsive di mobile devices
- [ ] Test semua user roles (Admin, Kasir, Pelanggan)

## 📝 Pull Request Checklist

- [ ] Code mengikuti style guidelines
- [ ] Self-review code Anda sendiri
- [ ] Tambahkan comments untuk bagian yang kompleks
- [ ] Update dokumentasi jika diperlukan
- [ ] Tidak ada konflik dengan branch main
- [ ] Test secara menyeluruh
- [ ] Screenshot/GIF jika UI changes

## 🐛 Report Bug

Gunakan [GitHub Issues](https://github.com/Ramadhan1710/kantin_online/issues) untuk report bug.

Template bug report:
```markdown
**Describe the bug**
Deskripsi singkat bug

**To Reproduce**
Steps untuk reproduce:
1. Go to '...'
2. Click on '....'
3. See error

**Expected behavior**
Yang seharusnya terjadi

**Screenshots**
Jika ada, tambahkan screenshot

**Environment:**
 - OS: [e.g. Windows 10]
 - Browser: [e.g. Chrome 120]
 - PHP Version: [e.g. 8.1]
```

## 💡 Request Fitur

Template feature request:
```markdown
**Deskripsi Fitur**
Jelaskan fitur yang diinginkan

**Motivasi**
Kenapa fitur ini diperlukan?

**Solusi Alternatif**
Alternatif lain yang sudah Anda pertimbangkan

**Context Tambahan**
Informasi tambahan yang relevan
```

## ❓ Pertanyaan?

Jika ada pertanyaan, silakan:
- Buat [GitHub Discussion](https://github.com/Ramadhan1710/kantin_online/discussions)
- Atau hubungi via GitHub Issues

## 🙏 Terima Kasih!

Setiap kontribusi, sekecil apapun, sangat berarti! 

---

Happy Coding! 🚀
