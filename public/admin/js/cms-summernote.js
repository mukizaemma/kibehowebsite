/**
 * Shared Summernote helpers for content-management pages.
 * Must load after jQuery and summernote-lite (see layouts/adminBase.blade.php).
 */
(function (window) {
    'use strict';

    var $ = window.jQuery;
    if (!$ || !$.fn.summernote) {
        return;
    }

    var defaultToolbar = [
        ['style', ['style']],
        ['font', ['bold', 'italic', 'underline', 'clear']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['insert', ['link', 'picture']],
        ['view', ['fullscreen', 'codeview']]
    ];

    var defaultOptions = {
        height: 200,
        tabsize: 2,
        disableResizeEditor: true,
        dialogsInBody: true,
        toolbar: defaultToolbar
    };

    function isInitialized(selector) {
        var $el = $(selector);
        return $el.length > 0 && $el.next('.note-editor').length > 0;
    }

    function init(selector, options) {
        var $el = $(selector);
        if (!$el.length || isInitialized(selector)) {
            return $el;
        }

        var opts = $.extend({}, defaultOptions, options || {});
        var initialHtml = opts.initialHtml;
        delete opts.initialHtml;

        $el.summernote(opts);

        if (initialHtml != null && initialHtml !== '') {
            $el.summernote('code', initialHtml);
        }

        return $el;
    }

    function setCode(selector, html) {
        var $el = $(selector);
        if (!$el.length) {
            return;
        }
        if (isInitialized(selector)) {
            $el.summernote('code', html || '');
        } else {
            $el.val(html || '');
        }
    }

    function getCode(selector) {
        var $el = $(selector);
        if (!$el.length) {
            return '';
        }
        if (isInitialized(selector)) {
            return $el.summernote('code');
        }
        return $el.val();
    }

    function syncTextarea(selector) {
        var $el = $(selector);
        if ($el.length && isInitialized(selector)) {
            $el.val($el.summernote('code'));
        }
    }

    function syncFormData(formData, selector, fieldName) {
        var $el = $(selector);
        if (!$el.length) {
            return formData;
        }
        var name = fieldName || $el.attr('name');
        if (!name) {
            return formData;
        }
        formData.set(name, getCode(selector));
        return formData;
    }

    function initInModal(modalSelector, textareaSelector, options) {
        $(modalSelector).on('shown.bs.modal', function () {
            init(textareaSelector, options);
        });
    }

    function initOnReady(selector, options) {
        $(function () {
            init(selector, options);
        });
    }

    window.CmsSummernote = {
        defaultOptions: defaultOptions,
        init: init,
        setCode: setCode,
        getCode: getCode,
        syncTextarea: syncTextarea,
        syncFormData: syncFormData,
        initInModal: initInModal,
        initOnReady: initOnReady,
        isInitialized: isInitialized
    };
})(window);
