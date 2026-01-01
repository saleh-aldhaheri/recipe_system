<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? e($title) : 'Recipe Management System' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <?= isset($styles) ? $styles : '' ?>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <ul>
            <li><a href="<?= url('') ?>" <?= isset($currentPage) && $currentPage === 'home' ? 'class="active"' : '' ?>>Home</a></li>
            <li><a href="<?= url('dashboard') ?>" <?= isset($currentPage) && $currentPage === 'dashboard' ? 'class="active"' : '' ?>>Dashboard</a></li>
            <li><a href="<?= url('items') ?>" <?= isset($currentPage) && $currentPage === 'items' ? 'class="active"' : '' ?>>Manage Items</a></li>
            <li><a href="<?= url('recipes') ?>" <?= isset($currentPage) && $currentPage === 'recipes' ? 'class="active"' : '' ?>>Recipes Calendar</a></li>
            <li><a href="<?= url('ingredients') ?>" <?= isset($currentPage) && $currentPage === 'ingredients' ? 'class="active"' : '' ?>>Manage Ingredients</a></li>
            <li><a href="<?= url('transactions') ?>" <?= isset($currentPage) && $currentPage === 'transactions' ? 'class="active"' : '' ?>>Transactions</a></li>
        </ul>
    </nav>

    <div class="container">
        <?= $content ?? '' ?>
    </div>

    <!-- JavaScript -->
    <script src="<?= asset('js/api.js') ?>"></script>
    <script src="<?= asset('js/notifications.js') ?>"></script>
    <script src="<?= asset('js/confirm-dialog.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <?= isset($scripts) ? $scripts : '' ?>
</body>
</html>
