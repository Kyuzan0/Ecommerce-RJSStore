<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/AkunPreset.php';

class AdminPresetController extends BaseController
{
    private AkunPreset $presetModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('admin');
        $this->presetModel = new AkunPreset();
    }

    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
            return;
        }

        $presets = $this->presetModel->getAll();

        $extra_css = 'input[type=text],textarea { width:100%; padding:10px 14px; border:1px solid #e5e7eb; border-radius:10px; font-size:14px; outline:none; transition:border 0.15s; } input:focus,textarea:focus { border-color:#42B549; box-shadow:0 0 0 3px rgba(66,181,73,0.12); }';

        $this->view('admin/preset/index', [
            'presets'     => $presets,
            'active_page' => 'preset',
            'page_title'  => 'Kelola Preset Layanan',
            'extra_css'   => $extra_css,
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

        $this->redirect('/admin-preset');
    }

    private function handleTambah()
    {
        $nama = trim($_POST['nama_layanan'] ?? '');
        $durasi = $this->parseList($_POST['durasi'] ?? '');
        $paket = $this->parseList($_POST['paket'] ?? '');

        if ($nama === '' || empty($durasi)) {
            flash('error', 'Nama layanan dan minimal satu durasi wajib diisi.');
            return;
        }

        $this->presetModel->createPreset($nama, $durasi, $paket);
        ActivityLog::log('tambah_preset', 'akun_preset', null, $nama);
        flash('success', 'Preset "' . $nama . '" berhasil ditambahkan!');
    }

    private function handleUpdate()
    {
        $id = (int) ($_POST['preset_id'] ?? 0);
        $nama = trim($_POST['nama_layanan'] ?? '');
        $durasi = $this->parseList($_POST['durasi'] ?? '');
        $paket = $this->parseList($_POST['paket'] ?? '');

        if ($id <= 0 || $nama === '' || empty($durasi)) {
            flash('error', 'Data tidak valid.');
            return;
        }

        $this->presetModel->updatePreset($id, $nama, $durasi, $paket);
        flash('success', 'Preset "' . $nama . '" berhasil diupdate!');
    }

    private function handleHapus()
    {
        $id = (int) ($_POST['preset_id'] ?? 0);
        if ($id > 0) {
            $this->presetModel->delete($id);
            flash('success', 'Preset berhasil dihapus.');
        }
    }

    /**
     * Parse comma-separated or newline-separated text into a clean array.
     */
    private function parseList(string $input): array
    {
        $items = preg_split('/[,\n]+/', $input);
        $result = [];
        foreach ($items as $item) {
            $item = trim($item);
            if ($item !== '') {
                $result[] = $item;
            }
        }
        return $result;
    }
}
