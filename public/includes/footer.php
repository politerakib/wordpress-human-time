    </main>
    <?php
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
    $basePath = $scriptDir === '' || $scriptDir === '.' ? '' : $scriptDir;
    $homeUrl = $basePath === '' ? '/' : $basePath . '/';
    ?>
    <footer class="mt-16 border-t border-slate-200 bg-white/70 py-10 text-sm dark:border-slate-800 dark:bg-slate-900/70">
        <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 md:flex-row md:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">FileShare24</h3>
                <p class="mt-2 max-w-sm text-slate-600 dark:text-slate-300">Secure, temporary file sharing for teams, creators, and businesses. Upload now and let your file live for 24 hours.</p>
            </div>
            <div class="flex gap-12">
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Product</h4>
                    <ul class="mt-3 space-y-2 text-slate-600 dark:text-slate-300">
                        <li><a href="<?= htmlspecialchars($homeUrl . '#features', ENT_QUOTES) ?>" class="transition hover:text-brand">Features</a></li>
                        <li><a href="<?= htmlspecialchars($homeUrl . '#why-us', ENT_QUOTES) ?>" class="transition hover:text-brand">Why Us</a></li>
                        <li><a href="<?= htmlspecialchars($homeUrl . '#faq', ENT_QUOTES) ?>" class="transition hover:text-brand">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Legal</h4>
                    <ul class="mt-3 space-y-2 text-slate-600 dark:text-slate-300">
                        <li><a href="mailto:legal@fileshare24.com" class="transition hover:text-brand">Privacy inquiries</a></li>
                        <li><a href="mailto:support@fileshare24.com" class="transition hover:text-brand">Terms &amp; compliance</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="mt-10 border-t border-slate-200 pt-6 text-center text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">&copy; <?= date('Y') ?> FileShare24. All rights reserved.</div>
    </footer>
    <script src="assets/js/app.js" defer></script>
    <?php if (!empty($pageScripts)): ?>
        <?= $pageScripts ?>
    <?php endif; ?>
</body>
</html>
