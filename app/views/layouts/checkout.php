<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Checkout - RJSStore') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php if (!empty($snap_url)): ?>
    <script src="<?= e($snap_url) ?>" data-client-key="<?= e($client_key ?? '') ?>"></script>
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center p-6">
    <?= $content ?>
<?php include BASE_PATH . '/app/views/partials/toast.php'; ?>
</body>
</html>
