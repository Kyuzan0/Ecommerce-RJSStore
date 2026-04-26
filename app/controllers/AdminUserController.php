<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/User.php';

class AdminUserController extends BaseController
{
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('admin');
        $this->userModel = new User();
    }

    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
            return;
        }

        $search = $_GET['q'] ?? '';
        $filter_role = $_GET['role'] ?? '';
        $where_clauses = [];
        $params = [];

        if ($filter_role !== '' && in_array($filter_role, ['admin', 'customer'])) {
            $where_clauses[] = "u.role = ?";
            $params[] = $filter_role;
        }
        if ($search !== '') {
            $where_clauses[] = "(u.name LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $where = "";
        if (!empty($where_clauses)) {
            $where = " WHERE " . implode(" AND ", $where_clauses);
        }

        $paging = paginate($this->db, "SELECT COUNT(*) as c FROM users u" . $where, $params, 10);
        $users = $this->db->fetchAll(
            "SELECT u.*, (SELECT COUNT(*) FROM transaksi WHERE user_id = u.id) as jumlah_transaksi FROM users u" . $where . " ORDER BY u.role ASC, u.name ASC LIMIT ? OFFSET ?",
            array_merge($params, [$paging['limit'], $paging['offset']])
        );

        // Get role counts
        $role_counts = [];
        $rows_role = $this->db->fetchAll("SELECT role, COUNT(*) as total FROM users GROUP BY role", []);
        foreach ($rows_role as $rr) {
            $role_counts[$rr['role']] = (int) $rr['total'];
        }
        $total_users = array_sum($role_counts);

        $extra_css = 'input[type=text],input[type=email],input[type=password],input[type=number],textarea,input[type=file],select { width:100%; padding:10px 14px; border:1px solid #e5e7eb; border-radius:10px; font-size:14px; outline:none; transition:border 0.15s; } input:focus,textarea:focus,select:focus { border-color:#42B549; box-shadow:0 0 0 3px rgba(66,181,73,0.12); }';

        $this->view('admin/users/index', [
            'users' => $users,
            'paging' => $paging,
            'role_counts' => $role_counts,
            'total_users' => $total_users,
            'current_role' => $filter_role,
            'search' => $search,
            'active_page' => 'user',
            'page_title' => 'Kelola User',
            'extra_css' => $extra_css
        ], 'admin');
    }

    private function handlePost()
    {
        $this->csrfValidate();
        $action = $_POST['action'] ?? '';

        if ($action === 'tambah_user') {
            $this->handleTambah();
        } elseif ($action === 'update_user') {
            $this->handleUpdate();
        } elseif ($action === 'hapus_user') {
            $this->handleHapus();
        }

        $this->redirect('/admin-user');
    }

    private function handleTambah()
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'customer';
        if (!in_array($role, ['admin', 'customer'])) $role = 'customer';

        $exists = $this->userModel->findByEmail($email);
        if ($exists) {
            flash('error', 'Email sudah terdaftar.');
        } else {
            $this->userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role
            ]);
            flash('success', 'Pengguna berhasil ditambahkan!');
        }
    }

    private function handleUpdate()
    {
        $id = (int) ($_POST['user_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'customer';
        if (!in_array($role, ['admin', 'customer'])) $role = 'customer';

        // Self-edit protection: force role='admin'
        if ($id == $this->auth->id()) $role = 'admin';

        $exists = $this->db->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $id]);
        if ($exists) {
            flash('error', 'Email sudah digunakan pengguna lain.');
        } else {
            $data = [
                'name' => $name,
                'email' => $email,
                'role' => $role
            ];
            if ($password !== '') {
                $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $this->userModel->update($id, $data);
            flash('success', 'Pengguna berhasil diupdate!');
        }
    }

    private function handleHapus()
    {
        $id = (int) ($_POST['user_id'] ?? 0);
        if ($id != $this->auth->id()) {
            $this->userModel->delete($id);
            flash('success', 'Pengguna berhasil dihapus.');
        } else {
            flash('error', 'Tidak bisa menghapus akun sendiri.');
        }
    }
}
