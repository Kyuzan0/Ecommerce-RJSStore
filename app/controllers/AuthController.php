<?php

class AuthController extends BaseController
{
    private User $userModel;
    private Keranjang $keranjangModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->keranjangModel = new Keranjang();
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
            return;
        }

        $next = $_GET['next'] ?? '';
        if ($next === 'checkout') {
            flash('info', 'Silakan login untuk melanjutkan pembayaran.');
        }

        $this->view('auth/login', [
            'next'       => $next,
            'page_title' => 'Masuk - RJSStore',
        ]);
    }

    private function handleLogin(): void
    {
        $this->csrfValidate();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = $this->userModel->findByEmail($email);

        if ($user && $this->userModel->verifyPassword($password, $user)) {
            // Save guest cart before session regeneration
            $guestCart          = $_SESSION['cart'] ?? [];
            $redirectAfterLogin = $_SESSION['redirect_after_login'] ?? null;
            $redirectNext       = $_POST['redirect_next'] ?? $_GET['next'] ?? null;

            $this->auth->login($user);

            // Merge guest cart into DB for customers
            if ($user['role'] === 'customer' && !empty($guestCart)) {
                $mergedCount = $this->keranjangModel->mergeGuestCart((int) $user['id'], $guestCart);
                if ($mergedCount > 0) {
                    flash('success', $mergedCount . ' produk dari keranjang tamu berhasil ditambahkan ke akun kamu.');
                }
            }

            // Redirect priority
            if ($redirectNext === 'checkout') {
                $this->redirect('/customer/checkout');
            } elseif ($redirectAfterLogin) {
                $this->redirect('/' . ltrim($redirectAfterLogin, '/'));
            } elseif ($user['role'] === 'admin') {
                AuditLog::log('login', 'auth', (int) $user['id'], 'Admin login successful');
                $this->redirect('/admin-dashboard');
            } else {
                $this->redirect('/customer/dashboard');
            }
            return;
        }

        // Authentication failed
        // Log failed attempt (use admin_id=0 since no one is logged in)
        $ip = !empty($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP']
            : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0])
            : (!empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP']
            : ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0')));
        $this->db->execute(
            "INSERT INTO audit_log (admin_id, action, target_type, detail, ip_address) VALUES (0, 'login_failed', 'auth', ?, ?)",
            ['email: ' . $email, $ip]
        );
        $next      = $_POST['redirect_next'] ?? '';
        $nextParam = $next ? '?next=' . urlencode($next) : '';
        flash('error', 'Email atau password salah!');
        $this->redirect('/auth/login' . $nextParam);
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleRegister();
            return;
        }

        $this->view('auth/register', [
            'page_title' => 'Daftar - RJSStore',
        ]);
    }

    private function handleRegister(): void
    {
        $this->csrfValidate();

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if ($name === '' || $email === '') {
            flash('error', 'Nama dan email harus diisi!');
            $this->redirect('/auth/register');
            return;
        }

        if (!validate_email($email)) {
            flash('error', 'Format email tidak valid!');
            $this->redirect('/auth/register');
            return;
        }

        if ($password !== $confirm) {
            flash('error', 'Password tidak sama!');
            $this->redirect('/auth/register');
            return;
        }

        if (strlen($password) < 8) {
            flash('error', 'Password minimal 8 karakter!');
            $this->redirect('/auth/register');
            return;
        }

        if ($this->userModel->emailExists($email)) {
            flash('error', 'Email sudah terdaftar!');
            $this->redirect('/auth/register');
            return;
        }

        $id = $this->userModel->createUser($name, $email, $password);

        if ($id) {
            flash('success', 'Registrasi berhasil! Silakan login.');
            $this->redirect('/auth/login');
        } else {
            flash('error', 'Terjadi kesalahan!');
            $this->redirect('/auth/register');
        }
    }

    public function logout(): void
    {
        $this->auth->logout();
        $this->redirect('/auth/login');
    }
}
