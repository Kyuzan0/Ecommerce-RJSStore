<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Checkout - RJSStore') ?></title>
    <?php $ga_id = env('GA_MEASUREMENT_ID', ''); if ($ga_id): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($ga_id) ?>"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($ga_id) ?>', { 'debug_mode': true });</script>
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={darkMode:'class'}</script>
    <?php if (!empty($snap_url)): ?>
    <script src="<?= e($snap_url) ?>" data-client-key="<?= e($client_key ?? '') ?>"></script>
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('/assets/css/dark-mode.css') ?>?v=<?= @filemtime(BASE_PATH . '/public/assets/css/dark-mode.css') ?>">
    <script>
        (function(){
            var theme = localStorage.getItem('theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4 md:p-6">
    <?= $content ?>
<?php include BASE_PATH . '/app/views/partials/toast.php'; ?>
</body>
</html>
