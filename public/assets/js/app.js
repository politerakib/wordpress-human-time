(function () {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
    const html = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');
    const sunIcon = themeToggle ? themeToggle.querySelector('.sun') : null;
    const moonIcon = themeToggle ? themeToggle.querySelector('.moon') : null;

    function applyTheme(theme, persist = false) {
        const isDark = theme === 'dark';
        html.classList.toggle('dark', isDark);

        if (sunIcon && moonIcon) {
            sunIcon.classList.toggle('hidden', !isDark);
            moonIcon.classList.toggle('hidden', isDark);
        }

        if (persist) {
            localStorage.setItem('fs24-theme', theme);
        }
    }

    const storedTheme = localStorage.getItem('fs24-theme');
    if (storedTheme === 'dark' || storedTheme === 'light') {
        applyTheme(storedTheme);
    } else {
        applyTheme(prefersDark.matches ? 'dark' : 'light');
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const isDark = html.classList.contains('dark');
            const next = isDark ? 'light' : 'dark';
            applyTheme(next, true);
        });
    }

    prefersDark.addEventListener('change', (event) => {
        const stored = localStorage.getItem('fs24-theme');
        if (stored !== 'dark' && stored !== 'light') {
            applyTheme(event.matches ? 'dark' : 'light');
        }
    });

    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const uploadForm = document.getElementById('uploadForm');
    const dropZoneHint = document.getElementById('dropZoneHint');
    const dropZoneSelection = document.getElementById('dropZoneSelection');
    const filePreview = document.getElementById('filePreview');
    const filePreviewThumb = document.getElementById('filePreviewThumb');
    const filePreviewName = document.getElementById('filePreviewName');
    const filePreviewMeta = document.getElementById('filePreviewMeta');
    const filePreviewImage = document.getElementById('filePreviewImage');
    const filePreviewImageEl = filePreviewImage ? filePreviewImage.querySelector('img') : null;
    const fileClearButton = document.getElementById('fileClear');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    const uploadResult = document.getElementById('uploadResult');
    const uploadButton = uploadForm ? uploadForm.querySelector('button[type="submit"]') : null;
    const defaultUploadButtonText = uploadButton ? uploadButton.textContent : '';

    let previewImageUrl = null;

    function setDropZoneActive(active) {
        if (!dropZone) return;
        dropZone.classList.toggle('border-brand', active);
        dropZone.classList.toggle('bg-brand/10', active);
    }

    function escapeHtml(value) {
        if (typeof value !== 'string') {
            return '';
        }
        return value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatFileSize(bytes) {
        if (!Number.isFinite(bytes) || bytes <= 0) {
            return '0 B';
        }

        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
        const size = bytes / (1024 ** index);
        return `${size.toFixed(index === 0 ? 0 : 1)} ${units[index]}`;
    }

    function getFileIcon(file) {
        const type = file.type || '';
        const name = file.name || '';
        const ext = name.includes('.') ? name.split('.').pop().toLowerCase() : '';

        if (type.startsWith('image/')) {
            return '<i class="fa-solid fa-image"></i>';
        }
        if (type.startsWith('video/')) {
            return '<i class="fa-solid fa-film"></i>';
        }
        if (type.startsWith('audio/')) {
            return '<i class="fa-solid fa-music"></i>';
        }
        if (type === 'application/pdf' || ext === 'pdf') {
            return '<i class="fa-solid fa-file-pdf"></i>';
        }
        if (type.includes('zip') || ['zip', 'rar', '7z', 'tar', 'gz'].includes(ext)) {
            return '<i class="fa-solid fa-file-zipper"></i>';
        }
        if (type.startsWith('text/') || ['txt', 'md', 'csv', 'json', 'xml'].includes(ext)) {
            return '<i class="fa-solid fa-file-lines"></i>';
        }
        return '<i class="fa-solid fa-file"></i>';
    }

    function resetFilePreview(clearInput = false) {
        if (clearInput && fileInput) {
            fileInput.value = '';
        }
        if (previewImageUrl && filePreviewImageEl) {
            URL.revokeObjectURL(previewImageUrl);
            previewImageUrl = null;
            filePreviewImageEl.src = '';
        }
        if (dropZoneSelection) {
            dropZoneSelection.textContent = '';
            dropZoneSelection.classList.add('hidden');
        }
        if (dropZoneHint) {
            dropZoneHint.classList.remove('hidden');
        }
        if (filePreview) {
            filePreview.classList.add('hidden');
        }
        if (filePreviewThumb) {
            filePreviewThumb.innerHTML = '';
        }
        if (filePreviewName) {
            filePreviewName.textContent = '';
        }
        if (filePreviewMeta) {
            filePreviewMeta.textContent = '';
        }
        if (filePreviewImage) {
            filePreviewImage.classList.add('hidden');
        }
        if (dropZone) {
            dropZone.classList.remove('ring-2', 'ring-brand/40', 'border-brand');
        }
    }

    function setSelectedFile(file) {
        if (!file) {
            resetFilePreview();
            return;
        }

        if (dropZoneSelection) {
            dropZoneSelection.textContent = `${file.name} (${formatFileSize(file.size)})`;
            dropZoneSelection.classList.remove('hidden');
        }
        if (dropZoneHint) {
            dropZoneHint.classList.add('hidden');
        }
        if (dropZone) {
            dropZone.classList.add('ring-2', 'ring-brand/40', 'border-brand');
        }
        if (filePreview) {
            filePreview.classList.remove('hidden');
        }
        if (filePreviewThumb) {
            filePreviewThumb.innerHTML = getFileIcon(file);
        }
        if (filePreviewName) {
            filePreviewName.textContent = file.name;
        }
        if (filePreviewMeta) {
            const type = file.type || 'Unknown file type';
            filePreviewMeta.textContent = `${formatFileSize(file.size)} • ${type}`;
        }
        if (filePreviewImage && filePreviewImageEl) {
            if (previewImageUrl) {
                URL.revokeObjectURL(previewImageUrl);
                previewImageUrl = null;
            }
            if (file.type && file.type.startsWith('image/')) {
                previewImageUrl = URL.createObjectURL(file);
                filePreviewImageEl.src = previewImageUrl;
                filePreviewImage.classList.remove('hidden');
            } else {
                filePreviewImage.classList.add('hidden');
                filePreviewImageEl.src = '';
            }
        }
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
                setSelectedFile(fileInput.files[0]);
            }
        });

        fileInput.addEventListener('change', () => {
            if (!fileInput.files || !fileInput.files.length) {
                resetFilePreview();
                return;
            }
            setSelectedFile(fileInput.files[0]);
        });
    }

    if (fileClearButton) {
        fileClearButton.addEventListener('click', (event) => {
            event.preventDefault();
            resetFilePreview(true);
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
        const downloadCount = Number.isFinite(Number(data.downloadCount)) ? Number(data.downloadCount) : 0;
        const downloadLabel = downloadCount === 1 ? 'time' : 'times';
        const downloadUrl = typeof data.downloadUrl === 'string' ? data.downloadUrl : '';
        const safeDownloadUrl = escapeHtml(downloadUrl);
        card.innerHTML = `
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm uppercase text-slate-500 dark:text-slate-400">File ready</p>
                    ${data.title ? `<p class="text-lg font-semibold text-slate-900 dark:text-white">${escapeHtml(data.title)}</p>` : ''}
                    <p class="text-xs text-slate-500 dark:text-slate-400">${escapeHtml(data.fileName)} • ${escapeHtml(data.fileSize)} • expires soon</p>
                </div>
                <button id="copyLink" class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 transition hover:border-brand hover:text-brand dark:border-slate-700 dark:text-slate-200 dark:hover:border-brand dark:hover:text-brand">
                    <i class="fa-solid fa-link"></i>
                    Copy Link
                </button>
            </div>
            <div class="rounded-2xl border border-dashed border-brand/40 bg-brand/5 p-4 text-sm text-slate-600 dark:border-brand/20 dark:bg-brand/10 dark:text-slate-200">
                Share this link:
                <span class="mt-2 block break-all text-sm font-mono text-brand dark:text-brand/90">${safeDownloadUrl}</span>
                <span class="mt-2 block text-xs text-slate-500 dark:text-slate-400">Time remaining: <span id="expiryCountdown"></span></span>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-medium uppercase tracking-wide text-slate-600 dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-300">
                Downloads tracked: ${downloadCount} ${downloadLabel}
            </div>
            <a href="${safeDownloadUrl}" class="flex items-center justify-center gap-2 rounded-full bg-brand px-6 py-3 text-sm font-semibold uppercase tracking-wide text-white shadow-lg shadow-brand/30 transition hover:bg-brand-dark">
                <i class="fa-solid fa-paper-plane"></i>
                Open download page
            </a>
        `;

        uploadResult.appendChild(card);

        const copyButton = card.querySelector('#copyLink');
        if (copyButton) {
            copyButton.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(downloadUrl);
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
                if (uploadButton) {
                    uploadButton.disabled = true;
                    uploadButton.classList.add('cursor-not-allowed', 'opacity-60');
                    uploadButton.textContent = 'Uploading...';
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
                        resetFilePreview(true);
                        if (uploadForm) {
                            uploadForm.reset();
                        }
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

            request.addEventListener('loadend', () => {
                if (uploadButton) {
                    uploadButton.disabled = false;
                    uploadButton.classList.remove('cursor-not-allowed', 'opacity-60');
                    uploadButton.textContent = defaultUploadButtonText;
                }
            });

            request.send(formData);
        });
    }
})();
