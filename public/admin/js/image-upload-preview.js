/**
 * Admin image fields: upload new or pick from existing media, with client-side
 * resize when over 700 KB (skipped when already under the limit). Shows the
 * final upload size before submit.
 */
(function () {
    var MAX_BYTES = 700 * 1024;
    var MIN_TARGET_BYTES = 400 * 1024;
    var libraryUrl = '';
    var activeInput = null;
    var selectedLibrary = [];

    function formatBytes(bytes) {
        if (!bytes || bytes <= 0) return '0 B';
        var units = ['B', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(1024));
        var value = bytes / Math.pow(1024, i);
        return value.toFixed(i === 0 ? 0 : 1) + ' ' + units[i];
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function resolveLibraryUrl() {
        if (libraryUrl) return libraryUrl;
        var meta = document.querySelector('meta[name="admin-media-library-url"]');
        if (meta && meta.content) {
            libraryUrl = meta.content;
            return libraryUrl;
        }
        var base = document.querySelector('meta[name="app-base-url"]');
        libraryUrl = (base && base.content ? base.content.replace(/\/$/, '') : '') + '/content-management/media-library';
        return libraryUrl;
    }

    function loadImage(file) {
        return new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function () {
                URL.revokeObjectURL(url);
                resolve(img);
            };
            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('Unable to read image'));
            };
            img.src = url;
        });
    }

    function canvasToBlob(canvas, quality) {
        return new Promise(function (resolve) {
            canvas.toBlob(function (blob) {
                resolve(blob);
            }, 'image/jpeg', quality);
        });
    }

    function resizeCanvas(img, scale) {
        var w = Math.max(1, Math.round(img.naturalWidth * scale));
        var h = Math.max(1, Math.round(img.naturalHeight * scale));
        var canvas = document.createElement('canvas');
        canvas.width = w;
        canvas.height = h;
        var ctx = canvas.getContext('2d');
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, w, h);
        ctx.drawImage(img, 0, 0, w, h);
        return canvas;
    }

    async function optimizeFile(file) {
        if (!file || !String(file.type || '').match(/^image\/(jpeg|jpg|png|webp|gif)$/i)) {
            return { file: file, originalSize: file.size, finalSize: file.size, resized: false };
        }

        if (file.size <= MAX_BYTES) {
            return { file: file, originalSize: file.size, finalSize: file.size, resized: false };
        }

        try {
            var img = await loadImage(file);
            var bestUnderMax = null;
            var bestUnderMaxSize = Infinity;
            var fallback = null;
            var fallbackSize = Infinity;
            var scales = [1, 0.85, 0.7, 0.55, 0.45, 0.35];

            for (var s = 0; s < scales.length; s++) {
                var canvas = resizeCanvas(img, scales[s]);
                for (var q = 0.9; q >= 0.5; q -= 0.05) {
                    var blob = await canvasToBlob(canvas, q);
                    if (!blob) continue;
                    var size = blob.size;
                    if (size < fallbackSize) {
                        fallback = blob;
                        fallbackSize = size;
                    }
                    if (size > MAX_BYTES) continue;
                    if (size >= MIN_TARGET_BYTES) {
                        var readyName = file.name.replace(/\.[^.]+$/, '') + '.jpg';
                        var readyFile = new File([blob], readyName, { type: 'image/jpeg', lastModified: Date.now() });
                        return {
                            file: readyFile,
                            originalSize: file.size,
                            finalSize: readyFile.size,
                            resized: true,
                        };
                    }
                    if (size < bestUnderMaxSize) {
                        bestUnderMax = blob;
                        bestUnderMaxSize = size;
                    }
                }
            }

            var chosen = bestUnderMax || fallback;
            if (!chosen) {
                return { file: file, originalSize: file.size, finalSize: file.size, resized: false };
            }
            var outName = file.name.replace(/\.[^.]+$/, '') + '.jpg';
            var outFile = new File([chosen], outName, { type: 'image/jpeg', lastModified: Date.now() });
            return {
                file: outFile,
                originalSize: file.size,
                finalSize: outFile.size,
                resized: true,
            };
        } catch (err) {
            return { file: file, originalSize: file.size, finalSize: file.size, resized: false, error: String(err) };
        }
    }

    function buildMetaHtml(results) {
        if (!results || !results.length) return '';
        var parts = [];
        results.forEach(function (r) {
            var line = '<strong>' + escapeHtml(r.file.name) + '</strong> — original ' + formatBytes(r.originalSize);
            if (r.resized) {
                line += ' → upload <strong>' + formatBytes(r.finalSize) + '</strong>';
                if (r.finalSize <= MAX_BYTES) {
                    line += ' <span class="text-success">(resized under 700 KB)</span>';
                } else {
                    line += ' <span class="text-warning">(still large; server will optimize further)</span>';
                }
            } else if (r.finalSize <= MAX_BYTES) {
                line += ' <span class="text-success">(under 700 KB — no resize needed)</span>';
            }
            parts.push(line);
        });
        if (results.length > 1) {
            var total = results.reduce(function (sum, r) { return sum + r.finalSize; }, 0);
            parts.push('<em>Total upload size: ' + formatBytes(total) + ' (' + results.length + ' files)</em>');
        }
        return parts.join('<br>');
    }

    function setInputFiles(input, files) {
        var dt = new DataTransfer();
        files.forEach(function (f) { dt.items.add(f); });
        input.files = dt.files;
    }

    function ensureShell(input) {
        if (input.closest('.admin-image-field')) {
            return input.closest('.admin-image-field');
        }

        var shell = document.createElement('div');
        shell.className = 'admin-image-field';
        input.parentNode.insertBefore(shell, input);
        shell.appendChild(input);

        var toolbar = document.createElement('div');
        toolbar.className = 'admin-image-field__toolbar';
        toolbar.innerHTML =
            '<button type="button" class="btn btn-sm btn-outline-secondary admin-image-field__browse">Upload new</button>' +
            '<button type="button" class="btn btn-sm btn-outline-primary admin-image-field__library">Choose existing</button>' +
            '<button type="button" class="btn btn-sm btn-link text-danger admin-image-field__clear d-none">Clear</button>';
        shell.insertBefore(toolbar, input);

        input.classList.add('admin-image-field__input');
        input.style.position = 'absolute';
        input.style.width = '1px';
        input.style.height = '1px';
        input.style.opacity = '0';
        input.style.pointerEvents = 'none';

        var preview = document.createElement('div');
        preview.className = 'admin-image-field__preview';
        shell.appendChild(preview);

        var meta = document.createElement('div');
        meta.className = 'admin-image-field__meta js-admin-image-upload-meta small mt-1';
        shell.appendChild(meta);

        toolbar.querySelector('.admin-image-field__browse').addEventListener('click', function () {
            input.click();
        });
        toolbar.querySelector('.admin-image-field__library').addEventListener('click', function () {
            openLibrary(input);
        });
        toolbar.querySelector('.admin-image-field__clear').addEventListener('click', function () {
            setInputFiles(input, []);
            preview.innerHTML = '';
            meta.innerHTML = '';
            toolbar.querySelector('.admin-image-field__clear').classList.add('d-none');
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });

        return shell;
    }

    function renderPreview(shell, files) {
        var preview = shell.querySelector('.admin-image-field__preview');
        var clearBtn = shell.querySelector('.admin-image-field__clear');
        preview.innerHTML = '';
        if (!files || !files.length) {
            clearBtn.classList.add('d-none');
            return;
        }
        clearBtn.classList.remove('d-none');
        Array.prototype.forEach.call(files, function (file) {
            var fig = document.createElement('figure');
            fig.className = 'admin-image-field__thumb';
            var img = document.createElement('img');
            img.alt = file.name;
            img.src = URL.createObjectURL(file);
            img.onload = function () { URL.revokeObjectURL(img.src); };
            var cap = document.createElement('figcaption');
            cap.textContent = file.name;
            fig.appendChild(img);
            fig.appendChild(cap);
            preview.appendChild(fig);
        });
    }

    async function processInputFiles(input) {
        var shell = ensureShell(input);
        var meta = shell.querySelector('.admin-image-field__meta');
        var files = input.files ? Array.prototype.slice.call(input.files) : [];
        if (!files.length) {
            meta.innerHTML = '';
            renderPreview(shell, []);
            return;
        }

        meta.innerHTML = '<span class="text-muted">Preparing images…</span>';
        var results = [];
        for (var i = 0; i < files.length; i++) {
            results.push(await optimizeFile(files[i]));
        }
        setInputFiles(input, results.map(function (r) { return r.file; }));
        meta.innerHTML = buildMetaHtml(results);
        renderPreview(shell, results.map(function (r) { return r.file; }));
    }

    function ensureLibraryModal() {
        var existing = document.getElementById('adminMediaLibraryModal');
        if (existing) return existing;

        var modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'adminMediaLibraryModal';
        modal.tabIndex = -1;
        modal.innerHTML =
            '<div class="modal-dialog modal-xl modal-dialog-scrollable">' +
            '  <div class="modal-content">' +
            '    <div class="modal-header">' +
            '      <h5 class="modal-title">Choose existing image</h5>' +
            '      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
            '    </div>' +
            '    <div class="modal-body">' +
            '      <div class="d-flex flex-wrap gap-2 align-items-center mb-3">' +
            '        <input type="search" class="form-control form-control-sm" id="adminMediaLibrarySearch" placeholder="Search by folder or filename…" style="max-width:280px">' +
            '        <button type="button" class="btn btn-sm btn-outline-secondary" id="adminMediaLibraryRefresh">Refresh</button>' +
            '        <span class="small text-muted" id="adminMediaLibraryStatus"></span>' +
            '      </div>' +
            '      <div id="adminMediaLibraryGrid" class="admin-media-library-grid"></div>' +
            '      <div id="adminMediaLibraryConfirm" class="admin-media-library-confirm d-none mt-3"></div>' +
            '    </div>' +
            '    <div class="modal-footer">' +
            '      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>' +
            '      <button type="button" class="btn btn-primary" id="adminMediaLibraryUse" disabled>Use selected</button>' +
            '    </div>' +
            '  </div>' +
            '</div>';
        document.body.appendChild(modal);

        document.getElementById('adminMediaLibraryRefresh').addEventListener('click', function () {
            loadLibraryItems();
        });
        var searchTimer = null;
        document.getElementById('adminMediaLibrarySearch').addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(loadLibraryItems, 250);
        });
        document.getElementById('adminMediaLibraryUse').addEventListener('click', applyLibrarySelection);

        return modal;
    }

    function showModal(el) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).show();
            return;
        }
        if (typeof jQuery !== 'undefined' && jQuery(el).modal) {
            jQuery(el).modal('show');
        }
    }

    function hideModal(el) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var inst = bootstrap.Modal.getInstance(el);
            if (inst) inst.hide();
            return;
        }
        if (typeof jQuery !== 'undefined' && jQuery(el).modal) {
            jQuery(el).modal('hide');
        }
    }

    function openLibrary(input) {
        activeInput = input;
        selectedLibrary = [];
        ensureLibraryModal();
        document.getElementById('adminMediaLibraryUse').disabled = true;
        document.getElementById('adminMediaLibraryConfirm').classList.add('d-none');
        document.getElementById('adminMediaLibraryConfirm').innerHTML = '';
        document.getElementById('adminMediaLibrarySearch').value = '';
        showModal(document.getElementById('adminMediaLibraryModal'));
        loadLibraryItems();
    }

    function loadLibraryItems() {
        var grid = document.getElementById('adminMediaLibraryGrid');
        var status = document.getElementById('adminMediaLibraryStatus');
        var q = (document.getElementById('adminMediaLibrarySearch').value || '').trim();
        status.textContent = 'Loading…';
        grid.innerHTML = '<p class="text-muted small mb-0">Loading media library…</p>';

        var url = resolveLibraryUrl() + (q ? ('?q=' + encodeURIComponent(q)) : '');
        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (payload) {
                var items = payload.data || [];
                status.textContent = items.length + ' image' + (items.length === 1 ? '' : 's');
                if (!items.length) {
                    grid.innerHTML = '<p class="text-muted small mb-0">No images found in storage yet.</p>';
                    return;
                }
                grid.innerHTML = '';
                items.forEach(function (item) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'admin-media-library-item';
                    btn.dataset.path = item.path;
                    btn.innerHTML =
                        '<img src="' + escapeHtml(item.url) + '" alt="" loading="lazy">' +
                        '<span class="admin-media-library-item__meta">' +
                        '<strong>' + escapeHtml(item.name) + '</strong>' +
                        '<small>' + escapeHtml(item.folder) + ' · ' + escapeHtml(item.size_label) + '</small>' +
                        '</span>';
                    btn.addEventListener('click', function () {
                        toggleLibraryItem(item, btn);
                    });
                    grid.appendChild(btn);
                });
            })
            .catch(function () {
                status.textContent = 'Failed to load';
                grid.innerHTML = '<p class="text-danger small mb-0">Could not load media library.</p>';
            });
    }

    function toggleLibraryItem(item, btn) {
        var multiple = !!(activeInput && activeInput.multiple);
        var idx = selectedLibrary.findIndex(function (x) { return x.path === item.path; });
        if (idx >= 0) {
            selectedLibrary.splice(idx, 1);
            btn.classList.remove('is-selected');
        } else {
            if (!multiple) {
                selectedLibrary = [];
                document.querySelectorAll('.admin-media-library-item.is-selected').forEach(function (el) {
                    el.classList.remove('is-selected');
                });
            }
            selectedLibrary.push(item);
            btn.classList.add('is-selected');
        }
        updateLibraryConfirm();
    }

    function updateLibraryConfirm() {
        var box = document.getElementById('adminMediaLibraryConfirm');
        var useBtn = document.getElementById('adminMediaLibraryUse');
        if (!selectedLibrary.length) {
            box.classList.add('d-none');
            box.innerHTML = '';
            useBtn.disabled = true;
            return;
        }
        useBtn.disabled = false;
        box.classList.remove('d-none');
        box.innerHTML =
            '<div class="alert alert-info mb-0 py-2">' +
            '<strong>Selected (' + selectedLibrary.length + '):</strong> ' +
            selectedLibrary.map(function (x) {
                return escapeHtml(x.name) + ' <span class="text-muted">(' + escapeHtml(x.size_label) + ')</span>';
            }).join(', ') +
            '<div class="small mt-1 mb-0">Confirm to attach ' + (selectedLibrary.length === 1 ? 'this image' : 'these images') + ' to the form field.</div>' +
            '</div>';
    }

    async function applyLibrarySelection() {
        if (!activeInput || !selectedLibrary.length) return;
        var useBtn = document.getElementById('adminMediaLibraryUse');
        useBtn.disabled = true;
        useBtn.textContent = 'Attaching…';

        try {
            var files = [];
            for (var i = 0; i < selectedLibrary.length; i++) {
                var item = selectedLibrary[i];
                var res = await fetch(item.url, { credentials: 'same-origin' });
                var blob = await res.blob();
                var type = blob.type || 'image/jpeg';
                files.push(new File([blob], item.name, { type: type, lastModified: Date.now() }));
            }

            if (activeInput.multiple) {
                var existing = activeInput.files ? Array.prototype.slice.call(activeInput.files) : [];
                setInputFiles(activeInput, existing.concat(files));
            } else {
                setInputFiles(activeInput, files.slice(0, 1));
            }

            await processInputFiles(activeInput);
            hideModal(document.getElementById('adminMediaLibraryModal'));
        } catch (err) {
            alert('Could not attach the selected image. Please try again or upload a new file.');
        } finally {
            useBtn.disabled = false;
            useBtn.textContent = 'Use selected';
        }
    }

    function shouldEnhance(input) {
        if (!input || !input.matches('input[type="file"]')) return false;
        if (input.dataset.adminImageSkip === '1') return false;
        var accept = (input.getAttribute('accept') || '').toLowerCase();
        if (accept && accept.indexOf('image') === -1 && accept.indexOf('*/*') === -1) return false;
        // Heuristic: no accept often still used for images in this admin — enhance when name/id suggests image
        if (!accept) {
            var hint = ((input.name || '') + ' ' + (input.id || '')).toLowerCase();
            if (!/(image|cover|photo|thumbnail|gallery|og_|logo|banner|slide)/.test(hint)) {
                return false;
            }
        }
        return true;
    }

    function bindInput(input) {
        if (!shouldEnhance(input) || input.dataset.imageUploadBound === '1') return;
        input.dataset.imageUploadBound = '1';
        ensureShell(input);
        input.addEventListener('change', function () {
            processInputFiles(input);
        });
    }

    function bindAll(root) {
        (root || document).querySelectorAll('input[type="file"]').forEach(bindInput);
    }

    window.AdminImageUpload = {
        MAX_BYTES: MAX_BYTES,
        MIN_TARGET_BYTES: MIN_TARGET_BYTES,
        formatBytes: formatBytes,
        bindAll: bindAll,
        processInputFiles: processInputFiles,
    };

    document.addEventListener('DOMContentLoaded', function () {
        bindAll(document);
    });

    document.addEventListener('shown.bs.modal', function () {
        bindAll(document);
    });

    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('shown.bs.modal', function () {
            bindAll(document);
        });
    }
})();
