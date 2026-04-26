# RJSStore

Platform e-commerce digital berbasis PHP (MVC) untuk penjualan produk digital dengan integrasi payment gateway Midtrans.

## Tech Stack

- **Backend**: PHP 8.1 (MVC Architecture — custom framework)
- **Database**: MySQL 8.0 (PDO prepared statements)
- **Frontend**: Tailwind CSS (CDN), Chart.js, Vanilla JavaScript
- **Payment**: Midtrans Snap (Sandbox/Production)
- **Server**: Laragon (Apache + mod_rewrite)

## Arsitektur

Proyek ini menggunakan arsitektur **MVC (Model-View-Controller)** dengan custom micro-framework:

- **Front Controller** — Semua request masuk melalui `public/index.php` via `.htaccess` rewrite
- **Router** — Mendukung explicit routes (API, webhook) dan convention-based routing (URL slug → Controller class)
- **BaseController** — `view()`, `json()`, `redirect()`, `requireAuth()`, `requirePost()`, `csrfValidate()`
- **BaseModel** — CRUD generik: `all()`, `find()`, `create()`, `update()`, `delete()`, `where()`, `count()`
- **Database** — PDO singleton dengan transaction support (`beginTransaction`, `commit`, `rollback`)
- **Auth** — Session-based authentication (`login`, `logout`, `check`, `user`, `isAdmin`, `isCustomer`)
- **Layout System** — 4 layout templates: `main`, `admin`, `customer`, `checkout`
- **Environment Config** — `.env` file dengan `env()` helper (tidak ada hardcoded credentials)

### Routing

**Convention-based** (otomatis):
```
URL: /admin-produk/index  →  AdminProdukController::index()
URL: /auth/login          →  AuthController::login()
URL: /                    →  HomeController::index()
```

**Explicit routes** (didaftarkan di `public/index.php`):
```
GET  api/cart/get            →  CartController::apiGet()
POST api/cart/add            →  CartController::apiAdd()
POST api/cart/remove         →  CartController::apiRemove()
POST api/cart/clear          →  CartController::apiClear()
POST webhook/midtrans        →  WebhookController::handle()
GET  api/product-detail/:id  →  HomeController::productDetail()
GET  customer/checkout       →  CheckoutController::index()
POST customer/checkout       →  CheckoutController::index()
GET  customer/bayar          →  CustomerBayarController::index()
GET  customer/rating/:id     →  CustomerRatingController::index()
POST customer/rating/:id     →  CustomerRatingController::index()
```

## Fitur

### Public Storefront
- Katalog produk digital tanpa perlu login
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

### Keamanan
- PDO prepared statements di semua query (zero SQL injection)
- CSRF protection di semua form
- Bcrypt password hashing (dengan fallback auto-upgrade dari MD5)
- Environment-based configuration (credentials tidak di-hardcode)
- Input validation terpusat (`helpers/validation.php`)

## Struktur Direktori

```
ecommerce/
├── app/
│   ├── controllers/                # 14 controllers
│   │   ├── AdminDashboardController.php
│   │   ├── AdminLaporanController.php
│   │   ├── AdminProdukController.php
│   │   ├── AdminProfileController.php
│   │   ├── AdminTransaksiController.php
│   │   ├── AdminUserController.php
│   │   ├── AuthController.php
│   │   ├── CartController.php
│   │   ├── CheckoutController.php
│   │   ├── CustomerBayarController.php
│   │   ├── CustomerController.php
│   │   ├── CustomerRatingController.php
│   │   ├── HomeController.php
│   │   └── WebhookController.php
│   ├── core/                       # Framework core
│   │   ├── Auth.php                # Session-based authentication
│   │   ├── BaseController.php      # Base controller (view, json, redirect, auth guards)
│   │   ├── BaseModel.php           # Base model (CRUD generik)
│   │   ├── Database.php            # PDO singleton + transactions
│   │   └── Router.php              # Explicit + convention-based routing
│   ├── models/                     # 4 models
│   │   ├── Keranjang.php
│   │   ├── Produk.php
│   │   ├── Transaksi.php
│   │   └── User.php
│   └── views/
│       ├── admin/                  # dashboard, profile, produk/, transaksi/, laporan/, users/
│       ├── auth/                   # login, register
│       ├── checkout/               # index
│       ├── customer/               # bayar, dashboard, download, keranjang, pembelian, produk, profile, rating
│       ├── home/                   # index
│       ├── layouts/                # admin, checkout, customer, main
│       └── partials/               # cart_dropdown, pagination, toast
├── config/
│   ├── app.php                     # App name, base URL, Midtrans keys (dari env())
│   └── database.php                # Database config (dari env())
├── database/
│   ├── ecommerce.sql               # Database schema
│   ├── keranjang.sql               # Schema tabel keranjang
│   ├── migrate_keranjang.php       # Migrasi: buat tabel keranjang
│   ├── migrate_order_ref.php       # Migrasi: tambah kolom order_ref
│   ├── migrate_tipe_produk.php     # Migrasi: tambah kolom tipe_produk
│   └── seed_dummy_data.php         # Seeder data dummy
├── helpers/
│   ├── functions.php               # Helper functions (env, url, csrf, flash, format, pagination)
│   └── validation.php              # Validasi input (required, email, min_length, numeric, file, dll.)
├── public/
│   ├── .htaccess                   # URL rewrite ke front controller
│   ├── index.php                   # Front controller (entry point)
│   ├── assets/                     # CSS, JS, images
│   └── uploads/                    # Storage file produk digital
├── .env.example                    # Template environment variables
└── README.md
```

## Instalasi

### Prasyarat
- [Laragon](https://laragon.org/) (atau XAMPP/WAMP)
- PHP >= 8.0 dengan ekstensi `pdo_mysql` dan `curl`
- MySQL >= 8.0

### Langkah

1. Clone atau copy project ke folder web server:
   ```
   laragon/www/ecommerce/
   ```

2. Copy file environment dan sesuaikan:
   ```bash
   cp .env.example .env
   ```
   Edit `.env` dengan konfigurasi database dan Midtrans key:
   ```env
   DB_HOST=localhost
   DB_USER=root
   DB_PASS=
   DB_NAME=ecommerce

   MIDTRANS_SERVER_KEY=your-server-key
   MIDTRANS_CLIENT_KEY=your-client-key
   MIDTRANS_IS_PRODUCTION=false
   ```

3. Buat database dan import schema:
   ```sql
   CREATE DATABASE ecommerce;
   USE ecommerce;
   SOURCE database/ecommerce.sql;
   ```

4. Jalankan migrasi database (buka di browser):
   ```
   http://ecommerce.test/database/migrate_keranjang.php
   http://ecommerce.test/database/migrate_order_ref.php
   http://ecommerce.test/database/migrate_tipe_produk.php
   ```

5. (Opsional) Jalankan seeder untuk data dummy:
   ```
   http://ecommerce.test/database/seed_dummy_data.php
   ```

6. Pastikan document root mengarah ke folder `public/`:
   - **Laragon**: Buat virtual host yang mengarah ke `ecommerce/public/`
   - Atau akses langsung: `http://localhost/ecommerce/public/`

7. Akses aplikasi:
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
Guest mengunjungi halaman utama
  → Klik "+ Keranjang" → AJAX request → item disimpan di $_SESSION['cart']
  → Badge keranjang update tanpa reload
  → Klik icon keranjang → dropdown muncul (list item, subtotal)
  → Klik "Checkout" → redirect ke login (jika belum login)
  → Login berhasil → guest cart otomatis merge ke database
  → Redirect ke checkout → pembayaran via Midtrans Snap
  → Webhook update status transaksi
```

## Konfigurasi Midtrans

Semua key Midtrans dikonfigurasi melalui file `.env`:

```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxx
MIDTRANS_IS_PRODUCTION=false
```

Key diakses via `config/app.php` menggunakan `env()` helper. Untuk production, ganti dengan key production dari [Midtrans Dashboard](https://dashboard.midtrans.com/) dan set `MIDTRANS_IS_PRODUCTION=true`.

## Helper Functions (`helpers/functions.php`)

| Function | Deskripsi |
|----------|-----------|
| `env_load()` | Load file `.env` ke environment |
| `env($key, $default)` | Ambil nilai environment variable |
| `config($key)` | Ambil nilai konfigurasi dari `config/` |
| `url($path)` | Generate URL lengkap |
| `base_path($path)` | Path absolut dari root project |
| `public_path($path)` | Path absolut dari folder public |
| `csrf_token()` / `csrf_field()` / `csrf_validate()` | CSRF protection |
| `flash($type, $msg)` / `flash_render()` | Flash messages (toast notification) |
| `flash_get($type)` | Get dan hapus satu flash message |
| `current_user_id()` | Get ID user yang sedang login |
| `current_user_name()` | Get nama user yang sedang login |
| `tipe_produk_list()` | Daftar semua tipe produk |
| `tipe_produk_config($tipe)` | Konfigurasi tampilan tipe produk |
| `tipe_produk_badge($tipe)` | Render badge HTML tipe produk |
| `paginate($sql, $params, $per_page)` | Pagination calculator |
| `pagination_render($paging)` | Render pagination HTML |
| `rupiah($amount)` | Format Rupiah (Rp 150.000) |
| `e($value)` | HTML escape |
| `format_tanggal($date)` | Format tanggal Indonesia |
| `json_response($data, $code)` | JSON response untuk API |

## Validasi (`helpers/validation.php`)

| Function | Deskripsi |
|----------|-----------|
| `validate_required($value)` | Cek field tidak kosong |
| `validate_email($value)` | Validasi format email |
| `validate_min_length($value, $min)` | Cek panjang minimum |
| `validate_numeric($value)` | Cek nilai numerik |
| `validate_in($value, $list)` | Cek nilai ada dalam daftar |
| `validate_file_type($file, $types)` | Validasi tipe file upload |
| `validate_file_size($file, $max)` | Validasi ukuran file upload |
| `validate_confirm($value, $confirm)` | Cek konfirmasi password |

## Contributors

| Kontributor | Peran | GitHub |
|-------------|-------|--------|
| **rizky-create** | Pengembang awal — membangun fondasi dan struktur dasar proyek | [github.com/rizky-create](https://github.com/rizky-create) |
| **Kyuzan0** | Enhance & optimize — refactor ke MVC, menyempurnakan arsitektur dan fitur | [github.com/Kyuzan0](https://github.com/Kyuzan0) |

## Lisensi

Proyek ini dibuat untuk keperluan pembelajaran.
