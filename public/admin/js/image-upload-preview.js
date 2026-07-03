/**
 * Admin CMS: show selected image file size before upload; note auto-compression over 700 KB.
 */
(function () {
    var MAX_BYTES = 700 * 1024;
    var MIN_TARGET_BYTES = 400 * 1024;

    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        var units = ['B', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(1024));
        var value = bytes / Math.pow(1024, i);
        return value.toFixed(i === 0 ? 0 : 1) + ' ' + units[i];
    }

    function buildMetaHtml(files) {
        if (!files || !files.length) {
            return '';
        }

        var parts = [];
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var line = '<strong>' + escapeHtml(file.name) + '</strong> — ' + formatBytes(file.size);
            if (file.size > MAX_BYTES) {
                line += ' <span class="text-warning">(will be optimized to 400–700 KB on save)</span>';
            } else {
                line += ' <span class="text-success">(ready to upload)</span>';
            }
            parts.push(line);
        }

        if (files.length > 1) {
            var total = 0;
            for (var j = 0; j < files.length; j++) {
                total += files[j].size;
            }
            parts.push('<em>Total: ' + formatBytes(total) + ' (' + files.length + ' files)</em>');
        }

        return parts.join('<br>');
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function ensureMetaElement(input) {
        var meta = input.nextElementSibling;
        if (!meta || !meta.classList.contains('js-admin-image-upload-meta')) {
            meta = document.createElement('div');
            meta.className = 'js-admin-image-upload-meta small mt-1';
            input.insertAdjacentElement('afterend', meta);
        }
        return meta;
    }

    function bindInput(input) {
        if (!input || input.dataset.imageUploadBound === '1') {
            return;
        }
        if (!input.matches('input[type="file"]')) {
            return;
        }
        var accept = (input.getAttribute('accept') || '').toLowerCase();
        if (accept && accept.indexOf('image') === -1 && accept.indexOf('*') === -1) {
            return;
        }

        input.dataset.imageUploadBound = '1';
        var meta = ensureMetaElement(input);

        input.addEventListener('change', function () {
            if (!input.files || !input.files.length) {
                meta.innerHTML = '';
                return;
            }
            meta.innerHTML = buildMetaHtml(input.files);
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
    };

    document.addEventListener('DOMContentLoaded', function () {
        bindAll(document);
    });

    document.addEventListener('shown.bs.modal', function () {
        bindAll(document);
    });
})();
