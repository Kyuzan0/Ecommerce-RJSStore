# RJSStore

Platform e-commerce digital berbasis PHP untuk penjualan produk digital dengan integrasi payment gateway Midtrans.

## Tech Stack

- **Backend**: PHP 8.1 (Prosedural)
- **Database**: MySQL 8.0 (mysqli prepared statements)
- **Frontend**: Tailwind CSS (CDN), Chart.js, Vanilla JavaScript
- **Payment**: Midtrans Snap (Sandbox)
- **Server**: Laragon

## Fitur

### Public Storefront
- Katalog produk digital tanpa perlu login (`index.php`)
- Pencarian produk dengan pagination
- Keranjang belanja untuk guest (session-based) — tambah produk tanpa login
- AJAX add-to-cart (tanpa reload halaman)
- Dropdown keranjang interaktif di header (scrollable, subtotal, checkout)
- Auth gate: checkout memerlukan login, redirect otomatis setelah login

### Customer
- Registrasi & login (bcrypt password hashing)
- Katalog produk digital dengan AJAX add-to-cart
- Keranjang belanja (database-based) dengan dropdown interaktif
- Checkout multi-item dengan Midtrans Snap
- Riwayat pembelian dengan filter status & grouping per order
- Pembayaran ulang untuk pesanan pending
- Download produk setelah pembayaran berhasil
- Rating & ulasan produk
- Profil & ubah password

### Admin
- Dashboard statistik (total produk, user, transaksi)
- CRUD produk (upload file digital)
- Tipe produk (Akun, Ebook, Game, Software, Template, Lainnya)
- Manajemen transaksi dengan pagination
- Manajemen user dengan pagination
- Laporan pendapatan (grafik harian & bulanan via Chart.js)
- Profil & ubah password

### Keamanan & Arsitektur
- Prepared statements di semua query (zero SQL injection)
- CSRF protection di semua form
- Bcrypt password hashing (dengan fallback auto-upgrade dari MD5)
- Flash messages dengan toast notification system dan PRG (Post-Redirect-Get) pattern
- Template system (shared header/sidebar/footer via includes)
- Helper functions terpusat (`config/helpers.php`)
- Session-based guest cart yang otomatis merge ke DB saat login

## Struktur Direktori

```
ecommerce/
├── config/
│   ├── koneksi.php              # Koneksi database
│   └── helpers.php              # Helper functions (DB, CSRF, flash, pagination, format)
├── includes/
│   ├── customer_header.php      # Template header customer
│   ├── customer_sidebar.php     # Template sidebar customer
│   ├── customer_footer.php      # Template footer customer
│   ├── admin_header.php         # Template header admin
│   ├── admin_header_html.php    # Template HTML header admin (DOCTYPE, head, navbar)
│   ├── admin_sidebar.php        # Template sidebar admin
│   ├── admin_footer.php         # Template footer admin
│   ├── cart_dropdown.php        # Dropdown keranjang interaktif (shared partial + JS)
│   └── toast.php                # Toast notification system (animated, auto-dismiss)
├── auth/
│   ├── login.php                # Halaman login (support ?next= redirect)
│   ├── proses_login.php         # Handler login + merge guest cart
│   ├── register.php             # Halaman registrasi
│   ├── proses_register.php      # Handler registrasi
│   └── logout.php               # Logout
├── admin/
│   ├── dashboard.php            # Dashboard admin
│   ├── produk.php               # CRUD produk
│   ├── transaksi.php            # Daftar transaksi (paginated)
│   ├── user.php                 # Manajemen user (paginated)
│   ├── laporan.php              # Laporan pendapatan + grafik
│   └── profile.php              # Profil admin
├── customer/
│   ├── dashboard.php            # Halaman utama customer
│   ├── produk.php               # Katalog produk (AJAX cart, paginated)
│   ├── keranjang.php            # Halaman keranjang belanja (full page)
│   ├── checkout.php             # Checkout + Midtrans payment
│   ├── pembelian.php            # Riwayat pembelian (grouped by order_ref)
│   ├── bayar.php                # Pembayaran ulang (single/multi-item)
│   ├── download.php             # Download produk (paginated)
│   ├── beri_nilai.php           # Rating & ulasan
│   └── profile.php              # Profil customer
├── api/
│   ├── cart_action.php          # AJAX endpoint keranjang (add/remove/get/clear)
│   └── midtrans_webhook.php     # Webhook notifikasi Midtrans
├── database/
│   ├── ecommerce.sql            # Database schema awal
│   ├── keranjang.sql            # SQL schema tabel keranjang
│   ├── migrate_keranjang.php    # Migrasi: buat tabel keranjang
│   ├── migrate_order_ref.php    # Migrasi: tambah kolom order_ref di transaksi
│   └── migrate_tipe_produk.php  # Migrasi: tambah kolom tipe_produk di produk
├── uploads/                     # Storage file produk digital
└── index.php                    # Public storefront (guest-friendly)
```

## Instalasi

### Prasyarat
- [Laragon](https://laragon.org/) (atau XAMPP/WAMP)
- PHP >= 8.0 dengan ekstensi `mysqli` dan `curl`
- MySQL >= 8.0

### Langkah

1. Clone atau copy project ke folder web server:
   ```
   laragon/www/ecommerce/
   ```

2. Buat database dan import schema:
   ```sql
   CREATE DATABASE ecommerce;
   USE ecommerce;
   SOURCE database/ecommerce.sql;
   ```

3. Jalankan migrasi database (buka di browser):
   ```
   http://ecommerce.test/database/migrate_keranjang.php
   http://ecommerce.test/database/migrate_order_ref.php
   http://ecommerce.test/database/migrate_tipe_produk.php
   ```

4. Sesuaikan konfigurasi database di `config/koneksi.php`:
   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db   = "ecommerce";
   ```

5. Akses aplikasi:
   ```
   http://ecommerce.test/
   ```

## Akun Default

| Role     | Email              | Password |
|----------|--------------------|----------|
| Admin    | admin@gmail.com    | admin    |
| Customer | user@gmail.com     | customer |

> Password lama (MD5) akan otomatis di-upgrade ke bcrypt saat login pertama.

## Alur Keranjang & Checkout

```
Guest mengunjungi index.php
  → Klik "+ Keranjang" → AJAX request → item disimpan di $_SESSION['cart']
  → Badge keranjang update tanpa reload
  → Klik icon keranjang → dropdown muncul (list item, subtotal)
  → Klik "Checkout" → redirect ke login.php?next=checkout
  → Login berhasil → guest cart otomatis merge ke database
  → Redirect ke checkout.php → pembayaran via Midtrans Snap
  → Webhook update status transaksi
```

## Konfigurasi Midtrans

Aplikasi menggunakan Midtrans Sandbox. Key dikonfigurasi di:
- `customer/checkout.php` (Server Key)
- `customer/bayar.php` (Server Key)
- `includes/cart_dropdown.php` → `customer/checkout.php` (Client Key via Snap.js)
- `api/midtrans_webhook.php`

Untuk production, ganti dengan key production dari [Midtrans Dashboard](https://dashboard.midtrans.com/).

## Helper Functions (`config/helpers.php`)

| Function | Deskripsi |
|----------|-----------|
| `db_query($conn, $sql, $params)` | SELECT query, return array of rows |
| `db_query_one($conn, $sql, $params)` | SELECT single row |
| `db_execute($conn, $sql, $params)` | INSERT/UPDATE/DELETE |
| `db_insert($conn, $sql, $params)` | INSERT, return auto-increment ID |
| `db_count($conn, $sql, $params)` | COUNT query |
| `csrf_token()` / `csrf_field()` / `csrf_validate()` | CSRF protection |
| `flash($type, $msg)` / `flash_render()` | Flash messages (toast notification system) |
| `flash_get($type)` | Get dan hapus satu flash message |
| `current_user_id()` | Get ID user yang sedang login |
| `current_user_name()` | Get nama user yang sedang login |
| `tipe_produk_list()` | Daftar semua tipe produk dengan konfigurasi tampilan |
| `tipe_produk_config($tipe)` | Get konfigurasi tampilan untuk satu tipe produk |
| `tipe_produk_badge($tipe)` | Render badge HTML tipe produk |
| `paginate($conn, $sql, $params, $per_page)` | Pagination calculator |
| `pagination_render($paging)` | Pagination HTML |
| `rupiah($amount)` | Format Rupiah (Rp 150.000) |
| `e($value)` | HTML escape |
| `format_tanggal($date)` | Format tanggal Indonesia |
| `require_role($role)` | Auth guard |

## Contributors

| Kontributor | Peran | GitHub |
|-------------|-------|--------|
| **rizky-create** | Pengembang awal — membangun fondasi dan struktur dasar proyek | [github.com/rizky-create](https://github.com/rizky-create) |
| **Kyuzan0** | Enhance & optimize — memperbaiki, menyempurnakan, dan memaksimalkan keseluruhan proyek | [github.com/Kyuzan0](https://github.com/Kyuzan0) |

## Lisensi

Proyek ini dibuat untuk keperluan pembelajaran.
