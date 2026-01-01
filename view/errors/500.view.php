<!-- Error 500 Page -->
<div class="min-h-[60vh] flex items-center justify-center py-12 px-4">
    <div class="max-w-2xl w-full text-center">
        <!-- Error Icon -->
        <div class="mb-8 flex justify-center">
            <div class="relative">
                <div class="absolute inset-0 bg-danger/20 rounded-full blur-3xl animate-pulse"></div>
                <div class="relative size-32 flex items-center justify-center rounded-full bg-danger/10 border-4 border-danger/20">
                    <span class="material-symbols-outlined text-6xl text-danger">error</span>
                </div>
            </div>
        </div>

        <!-- Error Title -->
        <h1 class="text-5xl md:text-6xl font-black text-text-primary mb-4 tracking-tight">
            500
        </h1>
        
        <h2 class="text-2xl md:text-3xl font-bold text-text-primary mb-3">
            Internal Server Error
        </h2>
        
        <p class="text-lg text-text-secondary mb-8 max-w-md mx-auto">
            We're sorry, but something went wrong on our end. Our team has been notified and is working to fix the issue.
        </p>

        <!-- Error Details (only show in development) -->
        <?php if (isset($error) && $error instanceof Throwable) { ?>
            <div class="bg-white border border-surface-border rounded-xl p-6 mb-8 text-left shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-danger">bug_report</span>
                    <h3 class="text-lg font-bold text-text-primary">Error Details</h3>
                </div>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1">Message</p>
                        <p class="text-sm text-text-primary font-mono bg-background-light p-3 rounded-lg border border-surface-border break-words">
                            <?= e($error->getMessage()) ?>
                        </p>
                    </div>
                    <?php if ($error->getFile()) { ?>
                        <div>
                            <p class="text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1">File</p>
                            <p class="text-sm text-text-primary font-mono bg-background-light p-3 rounded-lg border border-surface-border break-all">
                                <?= e($error->getFile()) ?>
                            </p>
                        </div>
                    <?php } ?>
                    <?php if ($error->getLine()) { ?>
                        <div>
                            <p class="text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1">Line</p>
                            <p class="text-sm text-text-primary font-mono bg-background-light p-3 rounded-lg border border-surface-border">
                                <?= e($error->getLine()) ?>
                            </p>
                        </div>
                    <?php } ?>
                    <?php if ($error->getTraceAsString()) { ?>
                        <details class="mt-4">
                            <summary class="text-sm font-medium text-text-primary cursor-pointer hover:text-primary transition-colors mb-2">
                                Stack Trace
                            </summary>
                            <pre class="text-xs text-text-primary font-mono bg-background-light p-4 rounded-lg border border-surface-border overflow-x-auto max-h-96 overflow-y-auto"><?= e($error->getTraceAsString()) ?></pre>
                        </details>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="<?= url('') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-primary-dark transition-colors shadow-sm hover:shadow-md">
                <span class="material-symbols-outlined text-xl">home</span>
                <span>Go to Home</span>
            </a>
            
            <button onclick="window.location.reload()" class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-surface-border text-text-primary rounded-lg font-medium hover:bg-background-light transition-colors shadow-sm hover:shadow-md">
                <span class="material-symbols-outlined text-xl">refresh</span>
                <span>Try Again</span>
            </button>
            
            <a href="<?= url('dashboard') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-surface-border text-text-primary rounded-lg font-medium hover:bg-background-light transition-colors shadow-sm hover:shadow-md">
                <span class="material-symbols-outlined text-xl">dashboard</span>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- Help Text -->
        <div class="mt-12 pt-8 border-t border-surface-border">
            <p class="text-sm text-text-secondary">
                If this problem persists, please contact support or check back later.
            </p>
        </div>
    </div>
</div>
