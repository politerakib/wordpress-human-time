<?php
require_once __DIR__ . '/functions.php';
start_session();
$csrfToken = get_csrf_token();
$theme = $_SESSION['theme_preference'] ?? 'system';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth" data-theme-preference="<?= htmlspecialchars($theme, ENT_QUOTES) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'FileShare 24', ENT_QUOTES) ?></title>
    <meta name="description" content="Upload and share files instantly. Your file lives for 24 hours.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-bxyZkqUPVHtV6nRvWmvMRGsiE9zraFMvx6bMpiKFFitvolSF/GpNZgbf+168Q5e0siJmq9hw3rroWAt4E6C0FQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#0ea5e9',
                            dark: '#0284c7'
                        }
                    }
                }
            }
        };
    </script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-slate-100">
<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/80 backdrop-blur dark:border-slate-800 dark:bg-slate-900/80">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
        <a href="index.php" class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">FileShare<span class="text-brand">24</span></a>
        <nav class="hidden gap-6 text-sm font-medium md:flex">
            <a href="#features" class="transition hover:text-brand dark:hover:text-brand">Features</a>
            <a href="#why-us" class="transition hover:text-brand dark:hover:text-brand">Why Us</a>
            <a href="#faq" class="transition hover:text-brand dark:hover:text-brand">FAQ</a>
            <a href="download.php" class="transition hover:text-brand dark:hover:text-brand">Find File</a>
        </nav>
        <div class="flex items-center gap-3">
            <button id="themeToggle" type="button" class="inline-flex items-center justify-center rounded-full border border-slate-200 p-2 text-slate-700 transition hover:border-brand hover:text-brand dark:border-slate-700 dark:text-slate-200 dark:hover:border-brand dark:hover:text-brand" aria-label="Toggle theme">
                <span class="sun hidden text-xl">☀️</span>
                <span class="moon hidden text-xl">🌙</span>
            </button>
            <a href="#upload" class="rounded-full bg-brand px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-brand/30 transition hover:bg-brand-dark">Upload</a>
        </div>
    </div>
</header>
<main class="mx-auto flex w-full max-w-6xl flex-1 flex-col px-4 py-10">
