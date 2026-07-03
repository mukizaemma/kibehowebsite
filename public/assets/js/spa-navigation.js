/**
 * Public site SPA navigation — intercept same-origin links and use Livewire.navigate().
 * Mirrors content-management adminBase behaviour so not every link needs wire:navigate.
 */
(function () {
    function shouldSpaNavigate(link) {
        if (!link || link.target === '_blank' || link.hasAttribute('download')) {
            return false;
        }
        if (link.closest('[data-no-spa-navigate]')) {
            return false;
        }
        if (link.hasAttribute('data-bs-toggle') || link.hasAttribute('data-bs-target')) {
            return false;
        }
        if (link.getAttribute('role') === 'button' && link.classList.contains('gallery-image-trigger')) {
            return false;
        }

        var href = link.getAttribute('href');
        if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:')) {
            return false;
        }
        if (href.startsWith('mailto:') || href.startsWith('tel:')) {
            return false;
        }

        try {
            var url = new URL(link.href, window.location.origin);
            if (url.origin !== window.location.origin) {
                return false;
            }
            if (url.pathname.startsWith('/livewire/')) {
                return false;
            }
        } catch (err) {
            return false;
        }

        return typeof Livewire !== 'undefined' && typeof Livewire.navigate === 'function';
    }

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
            return;
        }

        var link = e.target.closest('a[href]');
        if (!shouldSpaNavigate(link)) {
            return;
        }

        e.preventDefault();
        Livewire.navigate(link.href);
    });

    document.addEventListener('livewire:navigate', function () {
        var offcanvas = document.getElementById('offcanvasRight');
        if (offcanvas && typeof bootstrap !== 'undefined') {
            var instance = bootstrap.Offcanvas.getInstance(offcanvas);
            if (instance) {
                instance.hide();
            }
        }
        window.scrollTo(0, 0);
    });

    document.addEventListener('livewire:navigated', function () {
        document.body.classList.add('loaded');
        var preloader = document.getElementById('site-preloader');
        if (preloader) {
            preloader.style.display = 'none';
        }
    });
})();
