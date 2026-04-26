<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/Produk.php';

class AdminProdukController extends BaseController
{
    private $produkModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('admin');
        $this->produkModel = new Produk();
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

        $paging = paginate($this->db, "SELECT COUNT(*) as c FROM produk" . $where, $params, 15);
        $products = $this->db->fetchAll("SELECT * FROM produk" . $where . " ORDER BY id DESC LIMIT ? OFFSET ?", array_merge($params, [$paging['limit'], $paging['offset']]));

        // Get rating for each product
        foreach ($products as &$product) {
            $rating_data = $this->db->fetchOne(
                "SELECT ROUND(AVG(rating),1) as avg_rating, COUNT(rating) as total_rating FROM transaksi WHERE produk_id = ? AND rating IS NOT NULL",
                [$product['id']]
            );
            $product['avg_rating'] = $rating_data['avg_rating'] ?? '0.0';
            $product['total_rating'] = $rating_data['total_rating'] ?? 0;
        }

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
            'extra_css' => $extra_css
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
        }

        $this->redirect('/admin-produk');
    }

    private function handleTambah()
    {
        $nama = $_POST['nama_produk'] ?? '';
        $harga = (int) ($_POST['harga'] ?? 0);
        $deskripsi = $_POST['deskripsi'] ?? '';
        $tipe = $_POST['tipe_produk'] ?? 'Lainnya';
        if (!array_key_exists($tipe, tipe_produk_list())) $tipe = 'Lainnya';

        $file_name = $_FILES['file_upload']['name'] ?? '';
        $file_tmp = $_FILES['file_upload']['tmp_name'] ?? '';

        if ($file_name && $file_tmp) {
            $nama_file_db = time() . "_" . basename($file_name);
            $file_dest = BASE_PATH . '/public/uploads/' . $nama_file_db;
            if (move_uploaded_file($file_tmp, $file_dest)) {
                $this->produkModel->create([
                    'nama_produk' => $nama,
                    'harga' => $harga,
                    'deskripsi' => $deskripsi,
                    'tipe_produk' => $tipe,
                    'file_upload' => $nama_file_db
                ]);
                flash('success', 'Produk berhasil ditambahkan!');
            } else {
                flash('error', 'Gagal mengupload file.');
            }
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

        $file_name = $_FILES['file_upload']['name'] ?? '';
        $file_tmp = $_FILES['file_upload']['tmp_name'] ?? '';

        if ($file_name != "") {
            $d_lama = $this->db->fetchOne("SELECT file_upload FROM produk WHERE id = ?", [$id]);
            if ($d_lama && file_exists(BASE_PATH . '/public/uploads/' . $d_lama['file_upload'])) {
                unlink(BASE_PATH . '/public/uploads/' . $d_lama['file_upload']);
            }
            $nama_file_db = time() . "_" . basename($file_name);
            move_uploaded_file($file_tmp, BASE_PATH . '/public/uploads/' . $nama_file_db);
            $this->produkModel->update($id, [
                'nama_produk' => $nama,
                'harga' => $harga,
                'deskripsi' => $deskripsi,
                'tipe_produk' => $tipe,
                'file_upload' => $nama_file_db
            ]);
        } else {
            $this->produkModel->update($id, [
                'nama_produk' => $nama,
                'harga' => $harga,
                'deskripsi' => $deskripsi,
                'tipe_produk' => $tipe
            ]);
        }
        flash('success', 'Produk berhasil diupdate!');
    }

    private function handleHapus()
    {
        $id = (int) ($_POST['produk_id'] ?? 0);
        $data_file = $this->db->fetchOne("SELECT file_upload FROM produk WHERE id = ?", [$id]);
        if ($data_file && file_exists(BASE_PATH . '/public/uploads/' . $data_file['file_upload'])) {
            unlink(BASE_PATH . '/public/uploads/' . $data_file['file_upload']);
        }
        $this->produkModel->delete($id);
        flash('success', 'Produk berhasil dihapus.');
    }
}
