<?php
/**
 * RJSStore MVC — Front Controller
 */

// Define base path (project root)
define('BASE_PATH', dirname(__DIR__));

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

// Customer checkout uses dedicated CheckoutController
$router->get('customer/checkout', 'CheckoutController', 'index');
$router->post('customer/checkout', 'CheckoutController', 'index');

// Customer routes (bayar and rating use separate controllers)
$router->get('customer/bayar', 'CustomerBayarController', 'index');
$router->get('customer/rating/:id', 'CustomerRatingController', 'index');
$router->post('customer/rating/:id', 'CustomerRatingController', 'index');

// Dispatch request
$uri    = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
$router->dispatch($uri, $method);
