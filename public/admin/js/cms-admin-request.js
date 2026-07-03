/**
 * Shared CMS admin helpers: same-origin fetch URLs, JSON forms, Bootstrap modals.
 * Loaded once from layouts/adminBase.blade.php (before page-specific scripts).
 */
(function (window) {
    'use strict';

    if (window.CmsAdmin) {
        return;
    }

    var nativeFetch = window.fetch.bind(window);

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function appBaseUrl() {
        var meta = document.querySelector('meta[name="app-base-url"]');
        return meta ? String(meta.getAttribute('content') || '').replace(/\/$/, '') : window.location.origin;
    }

    function appUrl(path) {
        if (!path) {
            return window.location.href;
        }

        try {
            var base = appBaseUrl();

            if (/^https?:\/\//i.test(path)) {
                var parsed = new URL(path);
                return base + parsed.pathname + parsed.search;
            }

            return base + (path.charAt(0) === '/' ? path : '/' + path);
        } catch (error) {
            return path;
        }
    }

    function templateUrl(template, id, placeholder) {
        placeholder = placeholder || '__ID__';
        return appUrl(String(template).replace(placeholder, String(id)));
    }

    function resolveFetchInput(input) {
        if (typeof input === 'string') {
            return appUrl(input);
        }

        if (typeof Request !== 'undefined' && input instanceof Request) {
            return new Request(appUrl(input.url), input);
        }

        return input;
    }

    function parseJsonResponse(response) {
        return response.json().catch(function () {
            return {};
        }).then(function (data) {
            return {
                ok: response.ok,
                status: response.status,
                data: data,
                response: response,
            };
        });
    }

    function fetchJson(url, options) {
        options = options || {};
        options.headers = Object.assign({}, options.headers || {});

        if (!options.headers.Accept) {
            options.headers.Accept = 'application/json';
        }

        if (!options.headers['X-Requested-With']) {
            options.headers['X-Requested-With'] = 'XMLHttpRequest';
        }

        var method = String(options.method || 'GET').toUpperCase();
        if (method !== 'GET' && method !== 'HEAD' && !options.headers['X-CSRF-TOKEN']) {
            options.headers['X-CSRF-TOKEN'] = csrfToken();
        }

        return nativeFetch(appUrl(url), options).then(parseJsonResponse);
    }

    function modalElement(target) {
        if (!target) {
            return null;
        }

        return typeof target === 'string' ? document.getElementById(target) : target;
    }

    function getModalInstance(el) {
        if (!el) {
            return null;
        }

        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var Modal = bootstrap.Modal;
            if (typeof Modal.getOrCreateInstance === 'function') {
                return Modal.getOrCreateInstance(el);
            }

            return Modal.getInstance(el) || new Modal(el);
        }

        return null;
    }

    function showModal(target) {
        var el = modalElement(target);
        if (!el) {
            return;
        }

        if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
            jQuery(el).modal('show');
            return;
        }

        var instance = getModalInstance(el);
        if (instance && typeof instance.show === 'function') {
            instance.show();
        }
    }

    function hideModal(target) {
        var el = modalElement(target);
        if (!el) {
            return;
        }

        if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
            jQuery(el).modal('hide');
            return;
        }

        var instance = getModalInstance(el);
        if (instance && typeof instance.hide === 'function') {
            instance.hide();
            return;
        }

        el.classList.remove('show');
        el.style.display = 'none';
        el.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
        document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
            backdrop.remove();
        });
    }

    function showErrors(container, message, errors) {
        var el = typeof container === 'string' ? document.getElementById(container) : container;
        if (!el) {
            window.alert(message);
            return;
        }

        var html = '<strong>' + message + '</strong>';
        if (errors && typeof errors === 'object') {
            html += '<ul class="mb-0 mt-2">';
            Object.keys(errors).forEach(function (field) {
                var messages = errors[field];
                if (Array.isArray(messages)) {
                    messages.forEach(function (item) {
                        html += '<li>' + item + '</li>';
                    });
                }
            });
            html += '</ul>';
        }

        el.innerHTML = html;
        el.classList.remove('d-none');
        el.style.display = 'block';
    }

    function clearErrors(container) {
        var el = typeof container === 'string' ? document.getElementById(container) : container;
        if (!el) {
            return;
        }

        el.innerHTML = '';
        el.classList.add('d-none');
        el.style.display = 'none';
    }

    function handleFormResult(result, options) {
        options = options || {};

        if (result.ok && result.data && result.data.success) {
            if (options.modalId) {
                hideModal(options.modalId);
            }

            if (typeof options.onSuccess === 'function') {
                options.onSuccess(result);
            } else if (options.reload !== false) {
                window.setTimeout(function () {
                    window.location.reload();
                }, options.reloadDelay == null ? 200 : options.reloadDelay);
            }

            return true;
        }

        var data = result.data || {};
        var message = data.message || options.defaultError || 'Could not save. Please check the form and try again.';

        if (options.errorsEl) {
            showErrors(options.errorsEl, message, data.errors);
        } else {
            window.alert(message);
        }

        return false;
    }

    function submitFormData(url, formData, options) {
        return fetchJson(url, {
            method: 'POST',
            body: formData,
        }).then(function (result) {
            handleFormResult(result, options || {});
            return result;
        });
    }

    window.fetch = function (input, init) {
        return nativeFetch(resolveFetchInput(input), init);
    };

    window.CmsAdmin = {
        appUrl: appUrl,
        templateUrl: templateUrl,
        csrfToken: csrfToken,
        fetchJson: fetchJson,
        parseJsonResponse: parseJsonResponse,
        showModal: showModal,
        hideModal: hideModal,
        showErrors: showErrors,
        clearErrors: clearErrors,
        handleFormResult: handleFormResult,
        submitFormData: submitFormData,
    };
})(window);
