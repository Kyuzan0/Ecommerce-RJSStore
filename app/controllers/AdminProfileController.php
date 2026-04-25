<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/User.php';

class AdminProfileController extends BaseController
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

        $id = $this->auth->id();
        $user = $this->userModel->find($id);

        $this->view('admin/profile', [
            'user' => $user,
            'active_page' => 'profile',
            'page_title' => 'Settings Profile'
        ], 'admin');
    }

    private function handlePost()
    {
        $this->csrfValidate();
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') {
            $this->handleUpdateProfile();
        } elseif ($action === 'update_password') {
            $this->handleUpdatePassword();
        }

        $this->redirect('/admin-profile');
    }

    private function handleUpdateProfile()
    {
        $id = $this->auth->id();
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';

        $existing = $this->db->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $id]);
        if ($existing) {
            flash('error', 'Email sudah digunakan oleh akun lain.');
        } else {
            $this->userModel->update($id, [
                'name' => $name,
                'email' => $email
            ]);
            $_SESSION['name'] = $name;
            flash('success', 'Profil berhasil diperbarui.');
        }
    }

    private function handleUpdatePassword()
    {
        $id = $this->auth->id();
        $current_input = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $data_pw = $this->db->fetchOne("SELECT password FROM users WHERE id = ?", [$id]);

        $password_valid = false;
        if ($data_pw) {
            if (password_verify($current_input, $data_pw['password'])) {
                $password_valid = true;
            } elseif ($data_pw['password'] === md5($current_input)) {
                $password_valid = true;
            }
        }

        if (!$password_valid) {
            flash('error', 'Password lama tidak sesuai.');
        } elseif (strlen($new) < 6) {
            flash('error', 'Password baru minimal 6 karakter.');
        } elseif ($new != $confirm) {
            flash('error', 'Konfirmasi password tidak cocok.');
        } else {
            $new_hash = password_hash($new, PASSWORD_DEFAULT);
            $this->userModel->update($id, ['password' => $new_hash]);
            flash('success', 'Password berhasil diperbarui.');
        }
    }
}
