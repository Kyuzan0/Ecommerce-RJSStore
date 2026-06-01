<?php
/**
 * RJSStore MVC — Front Controller
 */

// Define base path (project root)
// __DIR__ always resolves to this file's physical directory (public/),
// so dirname(__DIR__) is always the project root — even when loaded
// via require from a bridge index.php in the document root.
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Load helpers
require_once BASE_PATH . '/helpers/functions.php';
require_once BASE_PATH . '/helpers/validation.php';

// Load .env
env_load();

// Load core classes
require_once BASE_PATH . '/app/core/Database.php';
require_once BASE_PATH . '/app/core/Auth.php';
require_once BASE_PATH . '/app/core/BaseController.php';
require_once BASE_PATH . '/app/core/BaseModel.php';
require_once BASE_PATH . '/app/core/Router.php';
require_once BASE_PATH . '/app/core/MidtransService.php';

// Simple autoloader for models
spl_autoload_register(function (string $class) {
    $file = BASE_PATH . '/app/models/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize router
$router = new Router();

// Register explicit routes (API + webhook)
$router->get('api/cart/get', 'CartController', 'apiGet');
$router->post('api/cart/add', 'CartController', 'apiAdd');
$router->post('api/cart/remove', 'CartController', 'apiRemove');
$router->post('api/cart/clear', 'CartController', 'apiClear');
$router->post('webhook/midtrans', 'WebhookController', 'handle');
$router->get('api/product-detail/:id', 'HomeController', 'productDetail');
$router->get('api/notifikasi', 'NotifikasiController', 'apiGet');
$router->post('api/notifikasi/read', 'NotifikasiController', 'apiRead');
$router->post('api/notifikasi/read-all', 'NotifikasiController', 'apiReadAll');

// Customer checkout uses dedicated CheckoutController
$router->get('customer/checkout', 'CheckoutController', 'index');
$router->post('customer/checkout', 'CheckoutController', 'index');
$router->get('customer/checkout/callback', 'CheckoutController', 'callback');

// Customer routes (bayar and rating use separate controllers)
$router->get('customer/bayar', 'CustomerBayarController', 'index');
$router->get('customer/rating/:id', 'CustomerRatingController', 'index');
$router->post('customer/rating/:id', 'CustomerRatingController', 'index');

// Secure file download (verifies purchase before serving)
$router->get('customer/download-file/:id', 'CustomerController', 'downloadFile');
$router->get('admin-produk/file/:id', 'AdminProdukController', 'file');
$router->get('admin-produk/stok/:id', 'AdminProdukController', 'stok');
$router->post('admin-produk/stok/:id', 'AdminProdukController', 'stok');
$router->get('api/admin/stok/:id', 'AdminProdukController', 'apiStok');
$router->post('api/admin/stok/add', 'AdminProdukController', 'apiStokAdd');
$router->post('api/admin/stok/delete', 'AdminProdukController', 'apiStokDelete');

// Chat routes
$router->get('customer/chat', 'ChatController', 'index');
$router->post('api/chat/send', 'ChatController', 'apiSend');
$router->get('api/chat/poll', 'ChatController', 'apiPoll');
$router->get('admin-chat/view/:id', 'AdminChatController', 'view_conv');

// Dispatch request
$uri    = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
$router->dispatch($uri, $method);
