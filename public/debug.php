<?php
// Step 1 - basic output
echo "STEP1:OK\n";
flush();

// Step 2 - PHP version
echo "STEP2:PHP=" . PHP_VERSION . "\n";
flush();

// Step 3 - BASE_PATH
define('BASE_PATH', dirname(__DIR__));
echo "STEP3:BASE=" . BASE_PATH . "\n";
flush();

// Step 4 - check files exist
echo "STEP4:ENV=" . (file_exists(BASE_PATH . '/.env') ? 'YES' : 'NO') . "\n";
echo "STEP4:HELPERS=" . (file_exists(BASE_PATH . '/helpers/functions.php') ? 'YES' : 'NO') . "\n";
echo "STEP4:VALIDATION=" . (file_exists(BASE_PATH . '/helpers/validation.php') ? 'YES' : 'NO') . "\n";
echo "STEP4:DATABASE=" . (file_exists(BASE_PATH . '/app/core/Database.php') ? 'YES' : 'NO') . "\n";
echo "STEP4:AUTH=" . (file_exists(BASE_PATH . '/app/core/Auth.php') ? 'YES' : 'NO') . "\n";
echo "STEP4:ROUTER=" . (file_exists(BASE_PATH . '/app/core/Router.php') ? 'YES' : 'NO') . "\n";
flush();

// Step 5 - load helpers
try {
    require_once BASE_PATH . '/helpers/functions.php';
    echo "STEP5:HELPERS=OK\n";
} catch (\Throwable $e) {
    echo "STEP5:HELPERS=FAIL:" . $e->getMessage() . "\n";
}
flush();

// Step 6 - load validation
try {
    require_once BASE_PATH . '/helpers/validation.php';
    echo "STEP6:VALIDATION=OK\n";
} catch (\Throwable $e) {
    echo "STEP6:VALIDATION=FAIL:" . $e->getMessage() . "\n";
}
flush();

// Step 7 - env
try {
    $env = env_load();
    echo "STEP7:ENV_KEYS=" . implode(',', array_keys($env)) . "\n";
    echo "STEP7:DB_HOST=" . ($env['DB_HOST'] ?? 'NOT_SET') . "\n";
} catch (\Throwable $e) {
    echo "STEP7:ENV=FAIL:" . $e->getMessage() . "\n";
}
flush();

// Step 8 - DB connect
try {
    require_once BASE_PATH . '/app/core/Database.php';
    $db = Database::getInstance();
    echo "STEP8:DB=CONNECTED\n";
} catch (\Throwable $e) {
    echo "STEP8:DB=FAIL:" . $e->getMessage() . "\n";
}
flush();

echo "DONE\n";
