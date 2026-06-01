<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/Produk.php';
require_once __DIR__ . '/../models/ProdukVarian.php';
require_once __DIR__ . '/../models/AkunPreset.php';
require_once __DIR__ . '/../models/AkunStok.php';

class AdminProdukController extends BaseController
{
    private $produkModel;
    private $varianModel;
    private $presetModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('admin');
        $this->produkModel = new Produk();
        $this->varianModel = new ProdukVarian();
        $this->presetModel = new AkunPreset();
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
            'akun_presets' => $this->presetModel->getForForm(),
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
                'thumbnail' => $this->handleThumbnail(),
            ]);
            $this->varianModel->replaceForProduk((int) $newId, $variants);
            flash('success', 'Produk akun berhasil ditambahkan!');
            AuditLog::log('tambah_produk', 'produk', (int) $newId, $nama . ' (Akun)');
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
                'file_upload' => $nama_file_db,
                'thumbnail' => $this->handleThumbnail(),
            ]);
            flash('success', 'Produk berhasil ditambahkan!');
            AuditLog::log('tambah_produk', 'produk', null, $nama);
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
            $updateData = [
                'nama_produk' => $nama,
                'harga' => $minHarga,
                'deskripsi' => $deskripsi,
                'tipe_produk' => $tipe,
                'account_info' => null,
            ];
            $thumb = $this->handleThumbnail();
            if ($thumb) $updateData['thumbnail'] = $thumb;
            $this->produkModel->update($id, $updateData);
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

        if (!is_array($durasiArr)) {
            return [];
        }

        $variants = [];
        foreach ($durasiArr as $i => $durasi) {
            $durasi = trim((string) $durasi);
            $paket = trim((string) ($paketArr[$i] ?? ''));
            $harga = (int) preg_replace('/\D/', '', (string) ($hargaArr[$i] ?? '0'));

            // Ignore the "type manually" placeholder value if it slips through
            if ($durasi === '__custom__') $durasi = '';
            if ($paket === '__custom__') $paket = '';

            // Skip rows without durasi
            if ($durasi === '') {
                continue;
            }

            $variants[] = [
                'durasi'       => $durasi,
                'paket'        => $paket !== '' ? $paket : null,
                'harga'        => $harga,
                'account_info' => null,
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

    /**
     * Handle optional thumbnail upload. Returns filename or null.
     */
    private function handleThumbnail(): ?string
    {
        $file = $_FILES['thumbnail'] ?? null;
        if (!$file || empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Validate: images only, max 2MB
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!validate_file_type($file, $allowedMimes)) {
            return null;
        }
        if (!validate_file_size($file, 2 * 1024 * 1024)) {
            return null;
        }

        $uploadDir = BASE_PATH . '/public/uploads/thumbnails/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $storedName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

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
        AuditLog::log('hapus_produk', 'produk', $id);
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

    /**
     * API: Get stock list for a variant (JSON).
     */
    public function apiStok($id)
    {
        $varianId = (int) $id;
        $stokModel = new AkunStok();
        $stocks = $stokModel->getByVarian($varianId);
        $available = $stokModel->countAvailable($varianId);
        $this->json([
            'success'   => true,
            'stocks'    => $stocks,
            'available' => $available,
            'total'     => count($stocks),
        ]);
    }

    /**
     * API: Add stock (single or bulk) — JSON response.
     */
    public function apiStokAdd()
    {
        $this->requirePost();
        $varianId = (int) ($_POST['varian_id'] ?? 0);
        $mode = $_POST['mode'] ?? 'single';

        if ($varianId <= 0) {
            $this->json(['success' => false, 'message' => 'Varian tidak valid.']);
            return;
        }

        $stokModel = new AkunStok();

        if ($mode === 'bulk') {
            $bulkText = trim($_POST['bulk_data'] ?? '');
            $items = $this->parseBulkStok($bulkText);
            if (empty($items)) {
                $this->json(['success' => false, 'message' => 'Format tidak valid. Gunakan email:password per baris.']);
                return;
            }
            $count = $stokModel->addBulk($varianId, $items);
            $this->json(['success' => true, 'message' => $count . ' stok berhasil ditambahkan.']);
        } else {
            $email = trim($_POST['account_email'] ?? '');
            $pass = trim($_POST['account_password'] ?? '');
            if ($email === '' || $pass === '') {
                $this->json(['success' => false, 'message' => 'Email dan password wajib diisi.']);
                return;
            }
            $stokModel->addStock($varianId, $email, $pass);
            $this->json(['success' => true, 'message' => '1 stok berhasil ditambahkan.']);
        }
    }

    /**
     * API: Delete a stock item — JSON response.
     */
    public function apiStokDelete()
    {
        $this->requirePost();
        $stokId = (int) ($_POST['stok_id'] ?? 0);
        if ($stokId <= 0) {
            $this->json(['success' => false, 'message' => 'ID tidak valid.']);
            return;
        }
        $stokModel = new AkunStok();
        $stokModel->deleteIfAvailable($stokId);
        $this->json(['success' => true, 'message' => 'Stok berhasil dihapus.']);
    }

    /**
     * Manage stock for a product variant (admin page).
     * GET: show stock list + add form
     * POST: add stock (single or bulk)
     */
    public function stok($id)
    {
        $varian_id = (int) $id;
        $varian = $this->db->fetchOne(
            "SELECT v.*, p.nama_produk FROM produk_varian v JOIN produk p ON v.produk_id = p.id WHERE v.id = ?",
            [$varian_id]
        );

        if (!$varian) {
            flash('error', 'Varian tidak ditemukan.');
            $this->redirect('/admin-produk');
            return;
        }

        $stokModel = new AkunStok();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->csrfValidate();
            $action = $_POST['action'] ?? '';

            if ($action === 'tambah_single') {
                $email = trim($_POST['account_email'] ?? '');
                $pass = trim($_POST['account_password'] ?? '');
                if ($email !== '' && $pass !== '') {
                    $stokModel->addStock($varian_id, $email, $pass);
                    flash('success', '1 stok berhasil ditambahkan.');
                } else {
                    flash('error', 'Email dan password wajib diisi.');
                }
            } elseif ($action === 'tambah_bulk') {
                $bulkText = trim($_POST['bulk_data'] ?? '');
                $items = $this->parseBulkStok($bulkText);
                if (!empty($items)) {
                    $count = $stokModel->addBulk($varian_id, $items);
                    flash('success', $count . ' stok berhasil ditambahkan.');
                } else {
                    flash('error', 'Format bulk tidak valid. Gunakan format: email:password (satu per baris).');
                }
            } elseif ($action === 'hapus_stok') {
                $stok_id = (int) ($_POST['stok_id'] ?? 0);
                if ($stok_id > 0) {
                    $stokModel->deleteIfAvailable($stok_id);
                    flash('success', 'Stok berhasil dihapus.');
                }
            }

            $this->redirect('/admin-produk/stok/' . $varian_id);
            return;
        }

        $stocks = $stokModel->getByVarian($varian_id);
        $available = $stokModel->countAvailable($varian_id);

        $label = $varian['durasi'];
        if (!empty($varian['paket'])) $label .= ' - ' . $varian['paket'];

        // Get all variants of the same product for the switcher dropdown
        $allVariants = $this->varianModel->getByProduk((int) $varian['produk_id']);
        foreach ($allVariants as &$av) {
            $av['label'] = $av['durasi'] . (!empty($av['paket']) ? ' - ' . $av['paket'] : '');
            $av['stok_count'] = $stokModel->countAvailable((int) $av['id']);
        }
        unset($av);

        $extra_css = 'input[type=text],textarea { width:100%; padding:10px 14px; border:1px solid #e5e7eb; border-radius:10px; font-size:14px; outline:none; transition:border 0.15s; } input:focus,textarea:focus { border-color:#42B549; box-shadow:0 0 0 3px rgba(66,181,73,0.12); }';

        $this->view('admin/produk/stok', [
            'varian'       => $varian,
            'varian_label' => $label,
            'all_variants' => $allVariants,
            'stocks'       => $stocks,
            'available'    => $available,
            'active_page'  => 'produk',
            'page_title'   => 'Kelola Stok - ' . $varian['nama_produk'],
            'extra_css'    => $extra_css,
        ], 'admin');
    }

    /**
     * Parse bulk paste text into stock items.
     * Supported formats:
     *   email:password (one per line)
     *   email|password
     *   email password (tab or space separated)
     */
    private function parseBulkStok(string $text): array
    {
        $lines = preg_split('/\r?\n/', $text);
        $items = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // Try common separators: colon, pipe, tab
            $parts = null;
            if (strpos($line, ':') !== false) {
                $parts = explode(':', $line, 2);
            } elseif (strpos($line, '|') !== false) {
                $parts = explode('|', $line, 2);
            } elseif (strpos($line, "\t") !== false) {
                $parts = explode("\t", $line, 2);
            }

            if ($parts && count($parts) === 2) {
                $email = trim($parts[0]);
                $pass = trim($parts[1]);
                if ($email !== '' && $pass !== '') {
                    $items[] = ['email' => $email, 'password' => $pass];
                }
            }
        }
        return $items;
    }
}
