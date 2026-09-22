<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? e($title) : 'Recipe Management System' ?></title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25/dist/sweetalert2.all.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Tailwind Config -->
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#2b6cee", // Blue - Professional and vibrant
                        "primary-dark": "#1e4fc7",
                        "background-light": "#F8F9FA", // Soft light gray
                        "background-dark": "#F0F2F5", // Off-white
                        "surface-dark": "#FFFFFF", // White surface
                        "surface-border": "#DEE2E6", // Light border
                        "text-primary": "#343A40", // Dark charcoal
                        "text-secondary": "#6C757D", // Secondary gray
                        "success": "#28A745", // Success green
                        "danger": "#DC3545", // Danger red
                        "warning": "#FFC107", // Warning yellow
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    
    <style>
        body {
            font-family: "Inter", sans-serif;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        /* Fix select box styling for light theme */
        select {
            color: #343A40 !important;
            background-color: #FFFFFF !important;
        }
        select option {
            background-color: #FFFFFF !important;
            color: #343A40 !important;
        }
        /* Ensure input text is visible */
        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="email"],
        textarea {
            color: #343A40 !important;
            background-color: #FFFFFF !important;
        }
        input::placeholder,
        textarea::placeholder {
            color: #6C757D !important;
        }
        /* Fix Choices.js dropdown styling */
        .choices__inner {
            background-color: #FFFFFF !important;
            border-color: #DEE2E6 !important;
            color: #343A40 !important;
        }
        .choices__list--dropdown {
            background-color: #FFFFFF !important;
            border-color: #DEE2E6 !important;
        }
        .choices__item {
            color: #343A40 !important;
        }
        .choices__item--selectable.is-highlighted {
            background-color: #F8F9FA !important;
            color: #343A40 !important;
        }
        .choices__input {
            background-color: #FFFFFF !important;
            color: #343A40 !important;
        }
        .choices__input::placeholder {
            color: #6C757D !important;
        }
        /* Fix Choices.js width inside ingredient rows (prevent letter-by-letter wrapping) */
        .ingredient-item .choices {
            flex: 1 1 0%;
            min-width: 0;
        }
        .ingredient-item .choices__inner {
            min-height: 2.625rem;
            border-radius: 0.5rem;
            padding: 0.625rem 1rem;
        }
        .ingredient-item .choices__list--dropdown .choices__item {
            white-space: normal;
            word-break: break-word;
        }
        /* Toaster-Ui Notifications */
        .toaster-ui-lib {
            border-radius: 8px;
            font-family: "Inter", sans-serif;
        }
        .toaster-ui-lib-success {
            background-color: #28A745 !important;
        }
        .toaster-ui-lib-error {
            background-color: #DC3545 !important;
        }
        .toaster-ui-lib-warning {
            background-color: #FFC107 !important;
            color: #343A40 !important;
        }
        .toaster-ui-lib-info {
            background-color: #2b6cee !important;
        }
        /* Sidebar Collapse Styles */
        #sidebar.collapsed {
            width: 5rem;
        }
        #sidebar.collapsed .sidebar-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        #sidebar.collapsed .px-6 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        #sidebar.collapsed .px-4 {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        #sidebar.collapsed .px-3 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        #sidebar.collapsed a {
            justify-content: center;
        }
        .sidebar-text {
            transition: opacity 0.2s, width 0.2s;
            overflow: hidden;
        }
    </style>
    
    <?= isset($styles) ? $styles : '' ?>
</head>
<body class="bg-background-light text-text-primary font-display antialiased min-h-screen flex">
    <!-- Sidebar Navigation -->
    <aside id="sidebar" class="hidden md:flex w-64 flex-shrink-0 border-r border-surface-border bg-white flex-col h-screen sticky top-0 shadow-sm transition-all duration-300">
        <!-- Logo Area -->
        <div class="flex items-center gap-3 px-6 py-5 border-b border-surface-border">
            <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary flex-shrink-0">
                <span class="material-symbols-outlined text-2xl">restaurant_menu</span>
            </div>
            <h1 class="text-lg font-bold text-text-primary tracking-tight sidebar-text">RecipeSys</h1>
            <button id="sidebarToggle" class="ml-auto p-1.5 rounded-md hover:bg-background-light text-text-secondary hover:text-text-primary transition-colors" title="Toggle Sidebar">
                <span class="material-symbols-outlined text-xl">menu</span>
            </button>
        </div>
        
        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <a href="<?= url('') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:bg-background-light hover:text-text-primary transition-colors group <?= isset($currentPage) && $currentPage === 'home' ? 'bg-primary/10 text-primary border border-primary/20' : '' ?>" title="Home">
                <span class="material-symbols-outlined text-xl group-hover:text-primary transition-colors flex-shrink-0">home</span>
                <span class="text-sm font-medium sidebar-text whitespace-nowrap">Home</span>
            </a>
            
            <a href="<?= url('dashboard') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:bg-background-light hover:text-text-primary transition-colors group <?= isset($currentPage) && $currentPage === 'dashboard' ? 'bg-primary/10 text-primary border border-primary/20' : '' ?>" title="Dashboard">
                <span class="material-symbols-outlined text-xl group-hover:text-primary transition-colors flex-shrink-0">dashboard</span>
                <span class="text-sm font-medium sidebar-text whitespace-nowrap">Dashboard</span>
            </a>
            
            <a href="<?= url('items') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:bg-background-light hover:text-text-primary transition-colors group <?= isset($currentPage) && $currentPage === 'items' ? 'bg-primary/10 text-primary border border-primary/20' : '' ?>" title="Items">
                <span class="material-symbols-outlined text-xl group-hover:text-primary transition-colors flex-shrink-0">inventory_2</span>
                <span class="text-sm font-medium sidebar-text whitespace-nowrap">Items</span>
            </a>
            
            <a href="<?= url('recipes') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:bg-background-light hover:text-text-primary transition-colors group <?= isset($currentPage) && $currentPage === 'recipes' ? 'bg-primary/10 text-primary border border-primary/20' : '' ?>" title="Recipes">
                <span class="material-symbols-outlined text-xl group-hover:text-primary transition-colors flex-shrink-0">calendar_month</span>
                <span class="text-sm font-medium sidebar-text whitespace-nowrap">Recipes</span>
            </a>
            
            <a href="<?= url('ingredients') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:bg-background-light hover:text-text-primary transition-colors group <?= isset($currentPage) && $currentPage === 'ingredients' ? 'bg-primary/10 text-primary border border-primary/20' : '' ?>" title="Ingredients">
                <span class="material-symbols-outlined text-xl group-hover:text-primary transition-colors flex-shrink-0">local_offer</span>
                <span class="text-sm font-medium sidebar-text whitespace-nowrap">Ingredients</span>
            </a>
            
            <a href="<?= url('transactions') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:bg-background-light hover:text-text-primary transition-colors group <?= isset($currentPage) && $currentPage === 'transactions' ? 'bg-primary/10 text-primary border border-primary/20' : '' ?>" title="Transactions">
                <span class="material-symbols-outlined text-xl group-hover:text-primary transition-colors flex-shrink-0">receipt_long</span>
                <span class="text-sm font-medium sidebar-text whitespace-nowrap">Transactions</span>
            </a>
        </nav>
    </aside>
    
    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen">
        <!-- Top Header Bar (Mobile Menu Toggle) -->
        <header class="md:hidden flex items-center justify-between px-4 py-3 border-b border-surface-border bg-white shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex size-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <span class="material-symbols-outlined text-xl">restaurant_menu</span>
                </div>
                <h2 class="text-lg font-bold text-text-primary">RecipeSys</h2>
            </div>
            <button id="mobileMenuToggle" class="text-text-primary p-2">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </header>
        
        <!-- Main Content -->
        <main class="flex-1 bg-background-light">
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8">
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>
    
    <!-- Mobile Sidebar Overlay -->
    <div id="mobileSidebar" class="md:hidden fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('mobileSidebar').classList.add('hidden')"></div>
        <aside class="absolute left-0 top-0 bottom-0 w-64 bg-white border-r border-surface-border flex flex-col shadow-lg">
            <!-- Mobile Sidebar Content (same as desktop) -->
            <div class="flex items-center gap-3 px-6 py-5 border-b border-surface-border">
                <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <span class="material-symbols-outlined text-2xl">restaurant_menu</span>
                </div>
                <h1 class="text-lg font-bold text-text-primary tracking-tight">RecipeSys</h1>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <a href="<?= url('') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:bg-background-light hover:text-text-primary transition-colors" onclick="document.getElementById('mobileSidebar').classList.add('hidden')">Home</a>
                <a href="<?= url('dashboard') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:bg-background-light hover:text-text-primary transition-colors" onclick="document.getElementById('mobileSidebar').classList.add('hidden')">Dashboard</a>
                <a href="<?= url('items') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:bg-background-light hover:text-text-primary transition-colors" onclick="document.getElementById('mobileSidebar').classList.add('hidden')">Items</a>
                <a href="<?= url('recipes') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:bg-background-light hover:text-text-primary transition-colors" onclick="document.getElementById('mobileSidebar').classList.add('hidden')">Recipes</a>
                <a href="<?= url('ingredients') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:bg-background-light hover:text-text-primary transition-colors" onclick="document.getElementById('mobileSidebar').classList.add('hidden')">Ingredients</a>
                <a href="<?= url('transactions') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-text-secondary hover:bg-background-light hover:text-text-primary transition-colors" onclick="document.getElementById('mobileSidebar').classList.add('hidden')">Transactions</a>
            </nav>
        </aside>
    </div>
    
    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuToggle')?.addEventListener('click', function() {
            document.getElementById('mobileSidebar').classList.toggle('hidden');
        });
        
        // Sidebar collapse/expand toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            // Check localStorage for saved state
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed && sidebar) {
                sidebar.classList.add('collapsed');
            }
            
            // Toggle sidebar on button click
            sidebarToggle?.addEventListener('click', function(e) {
                e.stopPropagation();
                if (sidebar) {
                    sidebar.classList.toggle('collapsed');
                    // Save state to localStorage
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/toaster-ui@1.1.5/dist/main.js"></script>
    <script src="<?= asset('js/api.js') ?>"></script>
    <script src="<?= asset('js/notifications.js') ?>?v=<?= filemtime(BASE.'public/js/notifications.js') ?>"></script>
    <script src="<?= asset('js/confirm-dialog.js') ?>?v=<?= filemtime(BASE.'public/js/confirm-dialog.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <?= isset($scripts) ? $scripts : '' ?>

</body>
</html>
