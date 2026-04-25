<?php

class HomeController extends BaseController
{
    private Produk $produkModel;
    private Transaksi $transaksiModel;

    public function __construct()
    {
        parent::__construct();
        $this->produkModel    = new Produk();
        $this->transaksiModel = new Transaksi();
    }

    /**
     * Public storefront — product listing with search, pagination, reviews.
     */
    public function index(): void
    {
        // Admin goes to admin dashboard
        if ($this->auth->isAdmin()) {
            $this->redirect('/admin-dashboard');
            return;
        }

        // Initialize session cart for guests
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Cart count
        $initial_cart_count = 0;
        if ($this->auth->isCustomer()) {
            $keranjang = new Keranjang();
            $initial_cart_count = $keranjang->getCartCount($this->auth->id());
        } else {
            $initial_cart_count = count($_SESSION['cart']);
        }

        // Build sets of cart/purchased product IDs for button state
        $cart_produk_ids      = [];
        $purchased_produk_ids = [];
        if ($this->auth->isCustomer()) {
            $userId   = $this->auth->id();
            $cartRows = $this->db->fetchAll("SELECT produk_id FROM keranjang WHERE user_id = ?", [$userId]);
            foreach ($cartRows as $cr) $cart_produk_ids[] = (int) $cr['produk_id'];
            $purchRows = $this->db->fetchAll("SELECT produk_id FROM transaksi WHERE user_id = ? AND status IN ('pending', 'success')", [$userId]);
            foreach ($purchRows as $pr) $purchased_produk_ids[] = (int) $pr['produk_id'];
        } else {
            foreach ($_SESSION['cart'] as $ci) $cart_produk_ids[] = (int) $ci['produk_id'];
        }

        // Search & pagination
        $search  = $_GET['search'] ?? '';
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 12;
        $total   = $this->produkModel->countSearch($search);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page    = min($page, $totalPages);
        $offset  = ($page - 1) * $perPage;

        $products = $this->produkModel->search($search, $perPage, $offset);

        $paging = [
            'page'        => $page,
            'per_page'    => $perPage,
            'total'       => $total,
            'total_pages' => $totalPages,
            'offset'      => $offset,
            'limit'       => $perPage,
        ];

        // Latest reviews for homepage
        $ulasan_terbaru = [];
        if (empty($search) && $page === 1) {
            $ulasan_terbaru = $this->produkModel->getLatestReviews(6);
        }

        $this->view('home/index', [
            'page_title'           => 'RJSStore - Toko Produk Digital',
            'initial_cart_count'   => $initial_cart_count,
            'cart_produk_ids'      => $cart_produk_ids,
            'purchased_produk_ids' => $purchased_produk_ids,
            'search'               => $search,
            'products'             => $products,
            'paging'               => $paging,
            'ulasan_terbaru'       => $ulasan_terbaru,
        ]);
    }

    /**
     * Product detail API for modal (JSON).
     */
    public function productDetail(string $id): void
    {
        $produkId = (int) $id;
        if ($produkId <= 0) {
            $this->json(['success' => false, 'message' => 'Produk tidak valid.']);
            return;
        }

        $produk = $this->produkModel->findWithRating($produkId);
        if (!$produk) {
            $this->json(['success' => false, 'message' => 'Produk tidak ditemukan.']);
            return;
        }

        $reviews  = $this->produkModel->getReviews($produkId);
        $tipe_cfg = tipe_produk_config($produk['tipe_produk'] ?? 'Lainnya');

        $reviewList = [];
        foreach ($reviews as $r) {
            $reviewList[] = [
                'nama_user' => $r['nama_user'],
                'initial'   => strtoupper(mb_substr($r['nama_user'], 0, 1)),
                'rating'    => (int) $r['rating'],
                'ulasan'    => $r['ulasan'],
                'tanggal'   => format_tanggal($r['tanggal']),
            ];
        }

        $this->json([
            'success' => true,
            'product' => [
                'id'            => (int) $produk['id'],
                'nama_produk'   => $produk['nama_produk'],
                'deskripsi'     => $produk['deskripsi'],
                'harga'         => (int) $produk['harga'],
                'harga_formatted' => rupiah($produk['harga']),
                'tipe_produk'   => $produk['tipe_produk'] ?? 'Lainnya',
                'tipe_label'    => $tipe_cfg['label'],
                'tipe_color'    => $tipe_cfg['color'],
                'tipe_bg'       => $tipe_cfg['bg'],
                'avg_rating'    => round((float) $produk['avg_rating'], 1),
                'total_reviews' => (int) $produk['total_reviews'],
            ],
            'reviews' => $reviewList,
        ]);
    }
}
