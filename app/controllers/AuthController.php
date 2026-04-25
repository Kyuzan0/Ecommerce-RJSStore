<?php

class AuthController extends BaseController
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
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
                $userId      = (int) $user['id'];
                $mergedCount = 0;

                foreach ($guestCart as $cartItem) {
                    $produkId = (int) $cartItem['produk_id'];

                    // Skip if already in DB cart
                    $existing = $this->db->fetchOne(
                        "SELECT id FROM keranjang WHERE user_id = ? AND produk_id = ?",
                        [$userId, $produkId]
                    );
                    if ($existing) continue;

                    // Skip if already purchased
                    $purchased = $this->db->fetchOne(
                        "SELECT id FROM transaksi WHERE user_id = ? AND produk_id = ? AND status IN ('pending', 'success')",
                        [$userId, $produkId]
                    );
                    if ($purchased) continue;

                    // Verify product exists
                    $produk = $this->db->fetchOne("SELECT id FROM produk WHERE id = ?", [$produkId]);
                    if (!$produk) continue;

                    $this->db->execute(
                        "INSERT INTO keranjang (user_id, produk_id) VALUES (?, ?)",
                        [$userId, $produkId]
                    );
                    $mergedCount++;
                }

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
                $this->redirect('/admin-dashboard');
            } else {
                $this->redirect('/customer/dashboard');
            }
            return;
        }

        // Authentication failed
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
