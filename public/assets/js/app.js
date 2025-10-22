(function () {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
    const html = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');

    function applyTheme(theme) {
        if (theme === 'dark' || (theme === 'system' && prefersDark.matches)) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }

        if (themeToggle) {
            const sun = themeToggle.querySelector('.sun');
            const moon = themeToggle.querySelector('.moon');
            if (sun && moon) {
                sun.classList.toggle('hidden', html.classList.contains('dark'));
                moon.classList.toggle('hidden', !html.classList.contains('dark'));
            }
        }
    }

    const storedTheme = localStorage.getItem('fs24-theme') || html.dataset.themePreference || 'system';
    applyTheme(storedTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const current = localStorage.getItem('fs24-theme') || 'system';
            let next;
            if (current === 'dark') {
                next = 'light';
            } else if (current === 'light') {
                next = 'system';
            } else {
                next = 'dark';
            }
            localStorage.setItem('fs24-theme', next);
            applyTheme(next);
        });
    }

    prefersDark.addEventListener('change', () => {
        const preference = localStorage.getItem('fs24-theme') || 'system';
        if (preference === 'system') {
            applyTheme('system');
        }
    });

    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const uploadForm = document.getElementById('uploadForm');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    const uploadResult = document.getElementById('uploadResult');

    function setDropZoneActive(active) {
        if (!dropZone) return;
        dropZone.classList.toggle('border-brand', active);
        dropZone.classList.toggle('bg-brand/10', active);
    }

    if (dropZone && fileInput) {
        dropZone.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('dragover', (event) => {
            event.preventDefault();
            setDropZoneActive(true);
        });

        dropZone.addEventListener('dragleave', () => setDropZoneActive(false));

        dropZone.addEventListener('drop', (event) => {
            event.preventDefault();
            setDropZoneActive(false);
            if (event.dataTransfer?.files?.length) {
                fileInput.files = event.dataTransfer.files;
            }
        });
    }

    function showToast(message, type = 'success') {
        if (!uploadResult) return;
        const colorClasses = type === 'success'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-200'
            : 'border-red-200 bg-red-50 text-red-700 dark:border-red-500/40 dark:bg-red-500/10 dark:text-red-200';
        const toast = document.createElement('div');
        toast.className = `rounded-2xl border p-4 text-sm transition ${colorClasses}`;
        toast.textContent = message;
        uploadResult.appendChild(toast);
    }

    let countdownInterval;

    function renderResult(data) {
        if (!uploadResult) return;
        uploadResult.innerHTML = '';

        const card = document.createElement('div');
        card.className = 'space-y-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-md dark:border-slate-800 dark:bg-slate-900';
        card.innerHTML = `
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm uppercase text-slate-500 dark:text-slate-400">File ready</p>
                    <p class="text-lg font-semibold text-slate-900 dark:text-white">${data.fileName}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">${data.fileSize} • expires soon</p>
                </div>
                <button id="copyLink" class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 transition hover:border-brand hover:text-brand dark:border-slate-700 dark:text-slate-200 dark:hover:border-brand dark:hover:text-brand">
                    <i class="fa-solid fa-link"></i>
                    Copy Link
                </button>
            </div>
            <div class="rounded-2xl border border-dashed border-brand/40 bg-brand/5 p-4 text-sm text-slate-600 dark:border-brand/20 dark:bg-brand/10 dark:text-slate-200">
                Share this link:
                <span class="mt-2 block break-all text-sm font-mono text-brand dark:text-brand/90">${data.downloadUrl}</span>
                <span class="mt-2 block text-xs text-slate-500 dark:text-slate-400">Time remaining: <span id="expiryCountdown"></span></span>
            </div>
            <a href="${data.downloadUrl}" class="flex items-center justify-center gap-2 rounded-full bg-brand px-6 py-3 text-sm font-semibold uppercase tracking-wide text-white shadow-lg shadow-brand/30 transition hover:bg-brand-dark">
                <i class="fa-solid fa-paper-plane"></i>
                Open download page
            </a>
        `;

        uploadResult.appendChild(card);

        const copyButton = card.querySelector('#copyLink');
        if (copyButton) {
            copyButton.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(data.downloadUrl);
                    showToast('Link copied to clipboard!');
                } catch (error) {
                    showToast('Unable to copy link automatically. Copy it manually.', 'error');
                }
            });
        }

        if (countdownInterval) {
            clearInterval(countdownInterval);
        }

        const expiryEl = card.querySelector('#expiryCountdown');
        if (expiryEl) {
            const expiryTime = new Date(data.expiresAt).getTime();
            function updateCountdown() {
                const remaining = expiryTime - Date.now();
                if (remaining <= 0) {
                    expiryEl.textContent = 'Expired';
                    clearInterval(countdownInterval);
                    return;
                }
                const totalSeconds = Math.floor(remaining / 1000);
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;
                expiryEl.textContent = `${hours}h ${minutes}m ${seconds}s`;
            }
            updateCountdown();
            countdownInterval = setInterval(updateCountdown, 1000);
        }
    }

    if (uploadForm) {
        uploadForm.addEventListener('submit', (event) => {
            event.preventDefault();
            if (!fileInput || !fileInput.files || !fileInput.files.length) {
                showToast('Please choose a file to upload.', 'error');
                return;
            }

            const formData = new FormData(uploadForm);
            formData.append('csrf_token', window.__CSRF_TOKEN || '');

            const request = new XMLHttpRequest();
            request.open('POST', 'upload.php');

            request.upload.addEventListener('progress', (e) => {
                if (!progressContainer || !progressBar || !progressPercent) return;
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = `${percent}%`;
                    progressPercent.textContent = `${percent}%`;
                }
            });

            request.addEventListener('loadstart', () => {
                if (progressContainer && progressBar && progressPercent) {
                    progressContainer.classList.remove('hidden');
                    progressBar.style.width = '0%';
                    progressPercent.textContent = '0%';
                }
                if (uploadResult) {
                    uploadResult.innerHTML = '';
                }
            });

            request.addEventListener('load', () => {
                if (progressContainer) {
                    progressContainer.classList.add('hidden');
                }

                try {
                    const response = JSON.parse(request.responseText);
                    if (response.success) {
                        renderResult(response.data);
                    } else {
                        showToast(response.message || 'Upload failed.', 'error');
                    }
                } catch (error) {
                    showToast('Unexpected response from server.', 'error');
                }
            });

            request.addEventListener('error', () => {
                if (progressContainer) {
                    progressContainer.classList.add('hidden');
                }
                showToast('Network error occurred. Please retry.', 'error');
            });

            request.send(formData);
        });
    }
})();
