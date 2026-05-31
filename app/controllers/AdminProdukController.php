<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/Produk.php';
require_once __DIR__ . '/../models/ProdukVarian.php';

class AdminProdukController extends BaseController
{
    private $produkModel;
    private $varianModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('admin');
        $this->produkModel = new Produk();
        $this->varianModel = new ProdukVarian();
    }

    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
            return;
        }

        $search = $_GET['q'] ?? '';
        $filter_tipe = $_GET['tipe'] ?? '';
        $where_clauses = [];
        $params = [];

        if ($filter_tipe !== '' && array_key_exists($filter_tipe, tipe_produk_list())) {
            $where_clauses[] = "tipe_produk = ?";
            $params[] = $filter_tipe;
        }
        if ($search !== '') {
            $where_clauses[] = "(nama_produk LIKE ? OR deskripsi LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $where = "";
        if (!empty($where_clauses)) {
            $where = " WHERE " . implode(" AND ", $where_clauses);
        }

        $paging = paginate($this->db, "SELECT COUNT(*) as c FROM produk" . $where, $params, 10);
        $products = $this->db->fetchAll("SELECT * FROM produk" . $where . " ORDER BY id DESC LIMIT ? OFFSET ?", array_merge($params, [$paging['limit'], $paging['offset']]));

        // Get rating and variants for each product
        foreach ($products as &$product) {
            $rating_data = $this->db->fetchOne(
                "SELECT ROUND(AVG(rating),1) as avg_rating, COUNT(rating) as total_rating FROM transaksi WHERE produk_id = ? AND rating IS NOT NULL",
                [$product['id']]
            );
            $product['avg_rating'] = $rating_data['avg_rating'] ?? '0.0';
            $product['total_rating'] = $rating_data['total_rating'] ?? 0;
            $product['varian'] = $this->varianModel->getByProduk((int) $product['id']);
        }
        unset($product);

        // Get tipe counts
        $tipe_counts = [];
        $rows_tipe = $this->db->fetchAll("SELECT tipe_produk, COUNT(*) as total FROM produk GROUP BY tipe_produk", []);
        foreach ($rows_tipe as $rt) {
            $tipe_counts[$rt['tipe_produk']] = (int) $rt['total'];
        }
        $total_produk = array_sum($tipe_counts);

        $extra_css = 'input[type=text],input[type=number],textarea,input[type=file],select { width:100%; padding:10px 14px; border:1px solid #e5e7eb; border-radius:10px; font-size:14px; outline:none; transition:border 0.15s; } input:focus,textarea:focus,select:focus { border-color:#42B549; box-shadow:0 0 0 3px rgba(66,181,73,0.12); }';

        $this->view('admin/produk/index', [
            'products' => $products,
            'paging' => $paging,
            'tipe_counts' => $tipe_counts,
            'total_produk' => $total_produk,
            'current_tipe' => $filter_tipe,
            'search' => $search,
            'active_page' => 'produk',
            'page_title' => 'Kelola Produk',
            'extra_css' => $extra_css,
            'akun_presets' => akun_presets(),
        ], 'admin');
    }

    private function handlePost()
    {
        $this->csrfValidate();
        $action = $_POST['action'] ?? '';

        if ($action === 'tambah') {
            $this->handleTambah();
        } elseif ($action === 'update') {
            $this->handleUpdate();
        } elseif ($action === 'hapus') {
            $this->handleHapus();
        } elseif ($action === 'hapus_bulk') {
            $this->handleHapusBulk();
        }

        $this->redirect('/admin-produk');
    }

    /**
     * Serve a product file for admin preview/download (admin-only).
     */
    public function file($id)
    {
        $produk_id = (int) $id;
        $row = $this->db->fetchOne("SELECT file_upload, nama_produk FROM produk WHERE id = ?", [$produk_id]);

        if (!$row || empty($row['file_upload'])) {
            http_response_code(404);
            echo 'File tidak ditemukan.';
            return;
        }

        $fileName = basename($row['file_upload']);
        $filePath = BASE_PATH . '/storage/uploads/' . $fileName;

        if (!is_file($filePath)) {
            http_response_code(404);
            echo 'File tidak ditemukan.';
            return;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('X-Content-Type-Options: nosniff');

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        readfile($filePath);
        exit;
    }

    private function handleTambah()
    {
        $nama = $_POST['nama_produk'] ?? '';
        $harga = (int) ($_POST['harga'] ?? 0);
        $deskripsi = $_POST['deskripsi'] ?? '';
        $tipe = $_POST['tipe_produk'] ?? 'Lainnya';
        if (!array_key_exists($tipe, tipe_produk_list())) $tipe = 'Lainnya';

        // Account-type products store variants (durasi + paket + harga + credentials)
        if ($tipe === 'Akun') {
            $variants = $this->parseVariants();
            if (empty($variants)) {
                flash('error', 'Minimal satu varian akun (durasi, harga, email, password) wajib diisi.');
                return;
            }
            // Product-level price = cheapest variant (for catalog display)
            $minHarga = min(array_column($variants, 'harga'));
            $newId = $this->produkModel->create([
                'nama_produk' => $nama,
                'harga' => $minHarga,
                'deskripsi' => $deskripsi,
                'tipe_produk' => $tipe,
                'file_upload' => null,
                'account_info' => null,
            ]);
            $this->varianModel->replaceForProduk((int) $newId, $variants);
            flash('success', 'Produk akun berhasil ditambahkan!');
            return;
        }

        $file_name = $_FILES['file_upload']['name'] ?? '';
        $file_tmp = $_FILES['file_upload']['tmp_name'] ?? '';

        if ($file_name && $file_tmp) {
            $error = $this->validateUpload($_FILES['file_upload']);
            if ($error !== null) {
                flash('error', $error);
                return;
            }

            $nama_file_db = $this->storeUpload($_FILES['file_upload']);
            if ($nama_file_db === null) {
                flash('error', 'Gagal mengupload file.');
                return;
            }

            $this->produkModel->create([
                'nama_produk' => $nama,
                'harga' => $harga,
                'deskripsi' => $deskripsi,
                'tipe_produk' => $tipe,
                'file_upload' => $nama_file_db
            ]);
            flash('success', 'Produk berhasil ditambahkan!');
        } else {
            flash('error', 'File produk wajib diupload.');
        }
    }

    private function handleUpdate()
    {
        $id = (int) ($_POST['id_produk'] ?? 0);
        $nama = $_POST['nama_produk'] ?? '';
        $harga = (int) ($_POST['harga'] ?? 0);
        $deskripsi = $_POST['deskripsi'] ?? '';
        $tipe = $_POST['tipe_produk'] ?? 'Lainnya';
        if (!array_key_exists($tipe, tipe_produk_list())) $tipe = 'Lainnya';

        // Account-type products: update variants, no file required
        if ($tipe === 'Akun') {
            $variants = $this->parseVariants();
            if (empty($variants)) {
                flash('error', 'Minimal satu varian akun (durasi, harga, email, password) wajib diisi.');
                return;
            }
            $minHarga = min(array_column($variants, 'harga'));
            $this->produkModel->update($id, [
                'nama_produk' => $nama,
                'harga' => $minHarga,
                'deskripsi' => $deskripsi,
                'tipe_produk' => $tipe,
                'account_info' => null,
            ]);
            $this->varianModel->replaceForProduk($id, $variants);
            flash('success', 'Produk akun berhasil diupdate!');
            return;
        }

        // Non-account product: clear any leftover variants
        $this->varianModel->replaceForProduk($id, []);

        $file_name = $_FILES['file_upload']['name'] ?? '';

        if ($file_name != "") {
            $error = $this->validateUpload($_FILES['file_upload']);
            if ($error !== null) {
                flash('error', $error);
                return;
            }

            $nama_file_db = $this->storeUpload($_FILES['file_upload']);
            if ($nama_file_db === null) {
                flash('error', 'Gagal mengupload file.');
                return;
            }

            // Delete old file after the new one is stored successfully
            $d_lama = $this->db->fetchOne("SELECT file_upload FROM produk WHERE id = ?", [$id]);
            if ($d_lama && $d_lama['file_upload'] && file_exists(BASE_PATH . '/storage/uploads/' . $d_lama['file_upload'])) {
                unlink(BASE_PATH . '/storage/uploads/' . $d_lama['file_upload']);
            }

            $this->produkModel->update($id, [
                'nama_produk' => $nama,
                'harga' => $harga,
                'deskripsi' => $deskripsi,
                'tipe_produk' => $tipe,
                'file_upload' => $nama_file_db,
                'account_info' => null,
            ]);
        } else {
            $this->produkModel->update($id, [
                'nama_produk' => $nama,
                'harga' => $harga,
                'deskripsi' => $deskripsi,
                'tipe_produk' => $tipe,
                'account_info' => null,
            ]);
        }
        flash('success', 'Produk berhasil diupdate!');
    }

    /**
     * Parse variant rows from POST into a clean array.
     * Expects parallel arrays: durasi[], paket[], harga[], account_email[], account_password[].
     * Returns rows with non-empty durasi + email + password.
     */
    private function parseVariants(): array
    {
        $durasiArr = $_POST['varian_durasi'] ?? [];
        $paketArr = $_POST['varian_paket'] ?? [];
        $hargaArr = $_POST['varian_harga'] ?? [];
        $emailArr = $_POST['varian_email'] ?? [];
        $passArr = $_POST['varian_password'] ?? [];

        if (!is_array($durasiArr)) {
            return [];
        }

        $variants = [];
        foreach ($durasiArr as $i => $durasi) {
            $durasi = trim((string) $durasi);
            $paket = trim((string) ($paketArr[$i] ?? ''));
            $harga = (int) preg_replace('/\D/', '', (string) ($hargaArr[$i] ?? '0'));
            $email = trim((string) ($emailArr[$i] ?? ''));
            $pass = trim((string) ($passArr[$i] ?? ''));

            // Ignore the "type manually" placeholder value if it slips through
            if ($durasi === '__custom__') $durasi = '';
            if ($paket === '__custom__') $paket = '';

            // Skip incomplete rows
            if ($durasi === '' || $email === '' || $pass === '') {
                continue;
            }

            $variants[] = [
                'durasi'       => $durasi,
                'paket'        => $paket !== '' ? $paket : null,
                'harga'        => $harga,
                'account_info' => "Email: {$email}\nPassword: {$pass}",
            ];
        }

        return $variants;
    }

    /**
     * Validate an uploaded product file. Returns an error message, or null if valid.
     */
    private function validateUpload(array $file): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return 'Terjadi kesalahan saat mengupload file.';
        }

        // Max 50MB
        if (!validate_file_size($file, 50 * 1024 * 1024)) {
            return 'Ukuran file maksimal 50MB.';
        }

        // Whitelist allowed digital-product file types
        $allowedMimes = [
            'application/pdf',
            'application/zip',
            'application/x-zip-compressed',
            'application/x-rar-compressed',
            'application/vnd.rar',
            'application/octet-stream',
            'text/plain',
            'image/jpeg',
            'image/png',
        ];
        if (!validate_file_type($file, $allowedMimes)) {
            return 'Tipe file tidak diizinkan. Gunakan PDF, ZIP, RAR, TXT, atau gambar.';
        }

        // Block dangerous extensions regardless of MIME
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $blockedExt = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'pht', 'cgi', 'pl', 'exe', 'sh', 'htaccess'];
        if (in_array($ext, $blockedExt, true)) {
            return 'Ekstensi file tidak diizinkan.';
        }

        return null;
    }

    /**
     * Store an uploaded file in the private storage dir. Returns stored filename or null.
     */
    private function storeUpload(array $file): ?string
    {
        $uploadDir = BASE_PATH . '/storage/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Sanitize and randomize filename
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $storedName = time() . '_' . bin2hex(random_bytes(4)) . '_' . substr($safeBase, 0, 40) . ($ext ? '.' . $ext : '');

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $storedName)) {
            return $storedName;
        }
        return null;
    }

    private function handleHapus()
    {
        $id = (int) ($_POST['produk_id'] ?? 0);
        $data_file = $this->db->fetchOne("SELECT file_upload FROM produk WHERE id = ?", [$id]);
        if ($data_file && $data_file['file_upload'] && file_exists(BASE_PATH . '/storage/uploads/' . $data_file['file_upload'])) {
            unlink(BASE_PATH . '/storage/uploads/' . $data_file['file_upload']);
        }
        $this->produkModel->delete($id);
        flash('success', 'Produk berhasil dihapus.');
    }

    private function handleHapusBulk()
    {
        $ids = $_POST['produk_ids'] ?? [];
        if (empty($ids)) {
            flash('error', 'Tidak ada produk yang dipilih.');
            return;
        }

        $deleted = 0;
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id <= 0) continue;

            $data_file = $this->db->fetchOne("SELECT file_upload FROM produk WHERE id = ?", [$id]);
            if ($data_file && $data_file['file_upload'] && file_exists(BASE_PATH . '/storage/uploads/' . $data_file['file_upload'])) {
                unlink(BASE_PATH . '/storage/uploads/' . $data_file['file_upload']);
            }
            $this->produkModel->delete($id);
            $deleted++;
        }

        flash('success', $deleted . ' produk berhasil dihapus.');
    }
}
