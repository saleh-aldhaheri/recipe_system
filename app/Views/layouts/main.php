<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? e($title) : 'Recipe Management System' ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <?= isset($styles) ? $styles : '' ?>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <ul>
            <li><a href="<?= url('index.php') ?>" <?= isset($currentPage) && $currentPage === 'home' ? 'class="active"' : '' ?>>الرئيسية</a></li>
            <li><a href="<?= url('items.php') ?>" <?= isset($currentPage) && $currentPage === 'items' ? 'class="active"' : '' ?>>إدارة العناصر</a></li>
            <li><a href="<?= url('recipes.php') ?>" <?= isset($currentPage) && $currentPage === 'recipes' ? 'class="active"' : '' ?>>تقويم الوصفات</a></li>
            <li><a href="<?= url('ingredients.php') ?>" <?= isset($currentPage) && $currentPage === 'ingredients' ? 'class="active"' : '' ?>>إدارة المكونات</a></li>
        </ul>
    </nav>

    <div class="container">
        <?= $content ?? '' ?>
    </div>

    <!-- JavaScript -->
    <script src="<?= asset('js/api.js') ?>"></script>
    <?= isset($scripts) ? $scripts : '' ?>
</body>
</html>

