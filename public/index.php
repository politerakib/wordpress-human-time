<?php
require_once __DIR__ . '/includes/functions.php';
start_session();

$csrfToken = get_csrf_token();

$pageTitle = 'FileShare24 – Upload. Share. Done.';

ob_start();
?>
<section class="grid gap-12 md:grid-cols-[1.1fr_0.9fr]" id="hero">
    <div class="flex flex-col justify-center gap-6">
        <span class="inline-flex w-fit items-center gap-2 rounded-full bg-brand/10 px-4 py-1 text-sm font-medium text-brand">
            <i class="fa-solid fa-bolt"></i> Upload. Share. Done.
        </span>
        <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white sm:text-5xl">Share files that auto-expire in 24 hours.</h1>
        <p class="text-lg text-slate-600 dark:text-slate-300">Effortless uploads, beautiful download pages, and smart clean-up so you never worry about lingering files again.</p>
        <div class="flex flex-wrap gap-3">
            <a href="#upload" class="rounded-full bg-brand px-6 py-3 text-sm font-semibold uppercase tracking-wide text-white shadow-lg shadow-brand/30 transition hover:bg-brand-dark">Start Uploading</a>
            <a href="#features" class="rounded-full border border-slate-300 px-6 py-3 text-sm font-semibold uppercase tracking-wide text-slate-700 transition hover:border-brand hover:text-brand dark:border-slate-700 dark:text-slate-200 dark:hover:border-brand dark:hover:text-brand">See Features</a>
        </div>
        <dl class="mt-8 grid gap-6 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <dt class="text-sm uppercase text-slate-500">Retention</dt>
                <dd class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">24 Hours</dd>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <dt class="text-sm uppercase text-slate-500">Limit</dt>
                <dd class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">100MB</dd>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <dt class="text-sm uppercase text-slate-500">Links</dt>
                <dd class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">Instant</dd>
            </div>
        </dl>
    </div>
    <div class="relative flex items-center justify-center">
        <div class="absolute inset-x-16 -top-10 h-32 rounded-full bg-brand/20 blur-3xl"></div>
        <div class="relative w-full rounded-3xl border border-slate-200 bg-white p-8 shadow-xl shadow-brand/10 dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <p class="text-sm uppercase text-slate-500">Secure Uploads</p>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Encrypted in transit</h2>
                </div>
                <i class="fa-solid fa-shield-halved text-3xl text-brand"></i>
            </div>
            <ul class="space-y-4 text-sm text-slate-600 dark:text-slate-300">
                <li class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand/10 text-brand"><i class="fa-solid fa-link"></i></span> Unique, private download links</li>
                <li class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand/10 text-brand"><i class="fa-solid fa-clock"></i></span> Automatic deletion after 24 hours</li>
                <li class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand/10 text-brand"><i class="fa-solid fa-lock"></i></span> Secure delivery via PHP streaming</li>
            </ul>
        </div>
    </div>
</section>

<section class="mt-20" id="upload">
    <div class="grid gap-10 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="rounded-3xl border border-slate-200 bg-white/95 p-8 shadow-xl shadow-brand/10 backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/95">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Deliver your file in seconds</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">Choose any document, media file, or archive up to 100MB. We screen uploads for dangerous types and stream them securely to your recipients.</p>
            <div class="mt-6">
                <form id="uploadForm" class="space-y-6" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
                    <div id="dropZone" class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-gradient-to-b from-white via-slate-50 to-white px-6 py-16 text-center transition hover:border-brand hover:bg-brand/10 dark:border-slate-700 dark:bg-slate-800 dark:from-slate-900 dark:via-slate-900 dark:to-slate-900">
                        <i class="fa-solid fa-cloud-arrow-up text-4xl text-brand"></i>
                        <p class="mt-4 text-base font-semibold text-slate-700 dark:text-slate-200">Drag and drop to start your upload</p>
                        <p id="dropZoneHint" class="text-sm text-slate-500 dark:text-slate-400">Prefer to browse? Click to choose a file from your device.</p>
                        <p id="dropZoneSelection" class="mt-3 hidden text-sm font-medium text-brand dark:text-brand/80"></p>
                        <input type="file" name="file" id="fileInput" class="hidden" required>
                    </div>
                    <div id="filePreview" class="hidden rounded-2xl border border-slate-200 bg-white/80 p-4 shadow-sm transition dark:border-slate-800 dark:bg-slate-900/70">
                        <div class="flex items-center gap-4">
                            <div id="filePreviewThumb" class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand/10 text-2xl text-brand"></div>
                            <div class="min-w-0 flex-1">
                                <p id="filePreviewName" class="truncate text-sm font-semibold text-slate-900 dark:text-white"></p>
                                <p id="filePreviewMeta" class="text-xs text-slate-500 dark:text-slate-400"></p>
                            </div>
                            <button type="button" id="fileClear" class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 transition hover:border-brand hover:text-brand dark:border-slate-700 dark:text-slate-200 dark:hover:border-brand dark:hover:text-brand">
                                <i class="fa-solid fa-rotate"></i>
                                Change
                            </button>
                        </div>
                        <div id="filePreviewImage" class="mt-4 hidden overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800">
                            <img src="" alt="Selected file preview" class="h-40 w-full object-cover" loading="lazy">
                        </div>
                    </div>
                    <div>
                        <label for="title" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Title (optional)</label>
                        <input type="text" id="title" name="title" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-brand focus:ring-brand dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100" placeholder="Add a descriptive title for the download page" autocomplete="off" maxlength="255">
                    </div>
                    <button type="submit" class="w-full rounded-full bg-brand px-6 py-3 text-sm font-semibold uppercase tracking-wide text-white transition hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-brand/50">Start upload</button>
                </form>
                <div id="progressContainer" class="mt-6 hidden">
                    <div class="flex items-center justify-between text-sm text-slate-600 dark:text-slate-300">
                        <span>Uploading...</span>
                        <span id="progressPercent">0%</span>
                    </div>
                    <div class="mt-2 h-2 w-full rounded-full bg-slate-200 dark:bg-slate-700">
                        <div id="progressBar" class="h-2 rounded-full bg-brand" style="width: 0%;"></div>
                    </div>
                </div>
                <div id="uploadResult" class="mt-6 space-y-4"></div>
            </div>
        </div>
        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-md dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Real-time progress</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Track every percent with live progress indicators so you always know when your link is ready.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-md dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Copy & share instantly</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Send the branded link straight from the dashboard or share the direct download URL for power users.</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-md dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Auto clean-up</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">We delete files and metadata automatically after 24 hours so nothing lingers in storage.</p>
            </div>
        </div>
    </div>
</section>

<section class="mt-24" id="features">
    <div class="text-center">
        <h2 class="text-3xl font-bold text-slate-900 dark:text-white">What we offer</h2>
        <p class="mt-3 text-slate-600 dark:text-slate-300">Everything you need for lightning-fast, secure file handoffs.</p>
    </div>
    <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-md transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand/10 text-brand"><i class="fa-solid fa-lock"></i></div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Private delivery</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Files stream through PHP without exposing paths or directories.</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-md transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand/10 text-brand"><i class="fa-solid fa-gauge-high"></i></div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Optimized performance</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Snappy, responsive UI that adapts beautifully on every device.</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-md transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand/10 text-brand"><i class="fa-solid fa-circle-check"></i></div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Validated uploads</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Smart validation keeps dangerous files out while supporting common types.</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-md transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand/10 text-brand"><i class="fa-solid fa-hourglass-half"></i></div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Expiry countdown</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">View the remaining life of every file with real-time timers.</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-md transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand/10 text-brand"><i class="fa-solid fa-code"></i></div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Reusable components</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Modular PHP templates keep everything tidy and production-ready.</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-md transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand/10 text-brand"><i class="fa-solid fa-server"></i></div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Automated clean-up</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">A ready-to-run cron script wipes expired files and database rows.</p>
        </div>
    </div>
</section>

<section class="mt-24" id="why-us">
    <div class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="flex flex-col justify-center gap-6">
            <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Why teams choose FileShare24</h2>
            <ul class="space-y-4 text-sm text-slate-600 dark:text-slate-300">
                <li class="flex gap-4"><span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand/10 text-brand"><i class="fa-solid fa-sparkles"></i></span> Polished experience with dark/light mode, responsive layouts, and delightful micro-interactions.</li>
                <li class="flex gap-4"><span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand/10 text-brand"><i class="fa-solid fa-user-shield"></i></span> Security-first approach: sanitized filenames, MIME validation, and CSRF protection out of the box.</li>
                <li class="flex gap-4"><span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand/10 text-brand"><i class="fa-solid fa-gears"></i></span> Built with modular PHP so you can extend or plug into existing systems with ease.</li>
            </ul>
            <div class="rounded-3xl border border-dashed border-brand/40 bg-brand/5 p-6 text-sm text-slate-600 dark:text-slate-300">
                <p class="font-semibold text-brand">Production ready:</p>
                <p class="mt-2">Deploy with Apache + PHP + MySQL. Tailored for HTTPS environments with clean .htaccess defaults.</p>
            </div>
        </div>
        <div class="relative rounded-3xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-800 dark:bg-slate-900">
            <div class="absolute -top-5 right-6 rounded-full bg-brand px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white">24h expiry</div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Download experience</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Recipients get a clean, branded download page with file metadata, expiry countdown, and a single-click download button.</p>
            <div class="mt-6 space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-6 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300">
                <p class="font-semibold text-slate-900 dark:text-white">Polished download cards include file name, size, and MIME type for complete transparency.</p>
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Automatic deletion countdown keeps recipients informed up to the second.</p>
                <p class="rounded-full bg-white px-4 py-2 text-center text-xs font-semibold uppercase tracking-wide text-brand shadow-sm dark:bg-slate-900">One-click secure download</p>
            </div>
        </div>
    </div>
</section>

<section class="mt-24" id="faq">
    <div class="text-center">
        <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Frequently asked questions</h2>
        <p class="mt-3 text-slate-600 dark:text-slate-300">Quick answers to the most common questions.</p>
    </div>
    <div class="mt-10 space-y-4">
        <details class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-md transition dark:border-slate-800 dark:bg-slate-900">
            <summary class="flex cursor-pointer items-center justify-between text-lg font-semibold text-slate-900 dark:text-white">
                How long do files stay available?
                <span class="transition group-open:rotate-45"><i class="fa-solid fa-plus"></i></span>
            </summary>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">Every upload receives a 24-hour expiry. After that, both the stored file and the database record are purged automatically.</p>
        </details>
        <details class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-md transition dark:border-slate-800 dark:bg-slate-900">
            <summary class="flex cursor-pointer items-center justify-between text-lg font-semibold text-slate-900 dark:text-white">
                Are there download limits?
                <span class="transition group-open:rotate-45"><i class="fa-solid fa-plus"></i></span>
            </summary>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">No limits. Share the link with as many people as you like during the 24-hour window.</p>
        </details>
        <details class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-md transition dark:border-slate-800 dark:bg-slate-900">
            <summary class="flex cursor-pointer items-center justify-between text-lg font-semibold text-slate-900 dark:text-white">
                Which file types are blocked?
                <span class="transition group-open:rotate-45"><i class="fa-solid fa-plus"></i></span>
            </summary>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">We prevent executable formats like .php, .exe, and .sh for security, while allowing documents, images, archives, and media files.</p>
        </details>
    </div>
</section>
<?php
$pageContent = ob_get_clean();

$pageScripts = '<script>window.__CSRF_TOKEN = "' . addslashes($csrfToken) . '";</script>';

include __DIR__ . '/includes/layout.php';
