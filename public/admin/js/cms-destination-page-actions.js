/**
 * Kibeho, Nyaruguru, and Gikongoro Diocese CMS sub-items (activities / statistics).
 * Delegated events — loaded once from adminBase (after jQuery + Bootstrap).
 */
(function () {
    'use strict';

    if (window.__cmsDestinationPageActionsInitialized) {
        return;
    }
    window.__cmsDestinationPageActionsInitialized = true;

    var Cms = window.CmsAdmin;
    if (!Cms) {
        return;
    }

    var state = {};

    function cfg(scope) {
        return document.querySelector('.cms-subresource-config[data-scope="' + scope + '"]');
    }

    function formatDate(value) {
        if (!value) {
            return '';
        }
        if (typeof value === 'string') {
            return value.substring(0, 10);
        }
        return '';
    }

    function resetItemForm(scope) {
        var config = cfg(scope);
        if (!config) {
            return;
        }

        state[scope] = null;

        var form = document.getElementById(config.dataset.itemForm);
        if (form) {
            form.reset();
        }

        if (config.dataset.itemActive) {
            var active = document.getElementById(config.dataset.itemActive);
            if (active) {
                active.checked = true;
            }
        }

        if (config.dataset.itemTitle) {
            var titleEl = document.getElementById(config.dataset.itemTitle);
            if (titleEl) {
                titleEl.textContent = config.dataset.addLabel || 'Add item';
            }
        }

        if (config.dataset.itemImageWrap) {
            var wrap = document.getElementById(config.dataset.itemImageWrap);
            if (wrap) {
                wrap.style.display = 'none';
            }
        }

        if (config.dataset.itemErrors) {
            Cms.clearErrors(config.dataset.itemErrors);
        }

        renderItemGallery(scope, []);
    }

    function renderItemGallery(scope, images) {
        var config = cfg(scope);
        var wrapId = scope === 'kibeho-event'
            ? 'kibeho_event_gallery_existing'
            : (scope === 'nyaruguru-activity' ? 'nyaruguru_activity_gallery_existing' : null);
        if (!wrapId) {
            return;
        }
        var wrap = document.getElementById(wrapId);
        if (!wrap) {
            return;
        }

        if (!images || !images.length) {
            wrap.innerHTML = '<p class="text-muted small mb-0">No gallery images yet.</p>';
            return;
        }

        var base = (config && config.dataset.storageBase) ? config.dataset.storageBase : '';
        var html = '<div class="row g-2">';
        images.forEach(function (image) {
            html += '<div class="col-4 col-md-3 position-relative" id="cms-item-gallery-' + image.id + '">' +
                '<img src="' + base + '/' + image.image + '" alt="" class="img-fluid rounded border" style="height:90px;width:100%;object-fit:cover;">' +
                '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" data-cms-item-gallery-delete="' + image.id + '" data-cms-item-gallery-scope="' + scope + '" title="Remove">' +
                '<i class="fa fa-times"></i></button></div>';
        });
        html += '</div>';
        wrap.innerHTML = html;
    }

    function populateItemForm(scope, data) {
        var config = cfg(scope);
        if (!config) {
            return;
        }

        var setValue = function (fieldId, value) {
            var el = document.getElementById(fieldId);
            if (el) {
                el.value = value == null ? '' : value;
            }
        };

        if (scope === 'kibeho-event') {
            setValue('kibeho_event_title', data.title || '');
            setValue('kibeho_event_description', data.description || '');
            setValue('kibeho_event_date', formatDate(data.event_date));
            setValue('kibeho_event_sort', data.sort_order ?? 0);
            setValue('kibeho_event_url', data.external_url || '');
            var active = document.getElementById('kibeho_event_active');
            if (active) {
                active.checked = !!data.is_active;
            }
            var imageInput = document.getElementById('kibeho_event_image');
            if (imageInput) {
                imageInput.value = '';
            }
            var galleryInput = document.getElementById('kibeho_event_gallery');
            if (galleryInput) {
                galleryInput.value = '';
            }
            var wrap = document.getElementById('kibeho_event_image_wrap');
            var img = document.getElementById('kibeho_event_image_preview');
            if (wrap && img) {
                if (data.image) {
                    img.src = (config.dataset.storageBase || '') + '/' + data.image;
                    wrap.style.display = 'block';
                } else {
                    wrap.style.display = 'none';
                }
            }
            renderItemGallery(scope, data.images || []);
            var titleEl = document.getElementById('kibehoEventModalTitle');
            if (titleEl) {
                titleEl.textContent = 'Edit activity';
            }
            return;
        }

        if (scope === 'nyaruguru-activity') {
            setValue('nyaruguru_activity_title', data.title || '');
            setValue('nyaruguru_activity_description', data.description || '');
            setValue('nyaruguru_activity_sort', data.sort_order ?? 0);
            setValue('nyaruguru_activity_url', data.external_url || '');
            active = document.getElementById('nyaruguru_activity_active');
            if (active) {
                active.checked = !!data.is_active;
            }
            imageInput = document.getElementById('nyaruguru_activity_image');
            if (imageInput) {
                imageInput.value = '';
            }
            galleryInput = document.getElementById('nyaruguru_activity_gallery');
            if (galleryInput) {
                galleryInput.value = '';
            }
            wrap = document.getElementById('nyaruguru_activity_image_wrap');
            img = document.getElementById('nyaruguru_activity_image_preview');
            if (wrap && img) {
                if (data.image) {
                    img.src = (config.dataset.storageBase || '') + '/' + data.image;
                    wrap.style.display = 'block';
                } else {
                    wrap.style.display = 'none';
                }
            }
            renderItemGallery(scope, data.images || []);
            titleEl = document.getElementById('nyaruguruActivityModalTitle');
            if (titleEl) {
                titleEl.textContent = 'Edit activity';
            }
            return;
        }

        if (scope === 'gikongoro-stat') {
            setValue('gikongoro_stat_label', data.label || '');
            setValue('gikongoro_stat_value', data.value || '');
            setValue('gikongoro_stat_icon', data.icon || '');
            setValue('gikongoro_stat_sort', data.sort_order ?? 0);
            active = document.getElementById('gikongoro_stat_active');
            if (active) {
                active.checked = !!data.is_active;
            }
            titleEl = document.getElementById('gikongoroStatModalTitle');
            if (titleEl) {
                titleEl.textContent = 'Edit statistic';
            }
        }
    }

    function editItem(scope, id) {
        var config = cfg(scope);
        if (!config || !config.dataset.itemShow) {
            window.alert('Configuration error. Please refresh the page.');
            return;
        }

        Cms.fetchJson(Cms.templateUrl(config.dataset.itemShow, id)).then(function (result) {
            if (!result.ok) {
                window.alert('Could not load this item. Please refresh and try again.');
                return;
            }

            state[scope] = id;
            populateItemForm(scope, result.data);

            if (config.dataset.itemErrors) {
                Cms.clearErrors(config.dataset.itemErrors);
            }

            Cms.showModal(config.dataset.itemModal);
        });
    }

    function deleteItem(scope, id) {
        var config = cfg(scope);
        if (!config || !config.dataset.itemDestroy) {
            return;
        }

        if (!window.confirm('Delete this item?')) {
            return;
        }

        Cms.fetchJson(Cms.templateUrl(config.dataset.itemDestroy, id), {
            method: 'DELETE',
        }).then(function (result) {
            if (result.ok && result.data.success) {
                window.location.reload();
            } else {
                window.alert('Could not delete. Please try again.');
            }
        });
    }

    function deleteItemGalleryImage(scope, id) {
        var config = cfg(scope);
        if (!config || !config.dataset.itemGalleryDestroyBase) {
            return;
        }

        if (!window.confirm('Remove this gallery image?')) {
            return;
        }

        Cms.fetchJson(Cms.appUrl(config.dataset.itemGalleryDestroyBase + '/' + id), {
            method: 'DELETE',
        }).then(function (result) {
            if (result.ok && result.data.success) {
                var el = document.getElementById('cms-item-gallery-' + id);
                if (el) {
                    el.remove();
                }
                if (!document.querySelector('[id^="cms-item-gallery-"]')) {
                    renderItemGallery(scope, []);
                }
            }
        });
    }

    function deleteGalleryImage(config, id) {
        if (!config.dataset.imageDestroyBase) {
            return;
        }

        if (!window.confirm('Remove this image?')) {
            return;
        }

        Cms.fetchJson(Cms.appUrl(config.dataset.imageDestroyBase + '/' + id), {
            method: 'DELETE',
        }).then(function (result) {
            if (result.ok && result.data.success) {
                document.querySelector('[data-image-id="' + id + '"]')?.remove();
            }
        });
    }

    function wirePageForms() {
        document.querySelectorAll('.cms-subresource-config').forEach(function (config) {
            var scope = config.dataset.scope;
            if (!scope) {
                return;
            }

            state[scope] = null;

            if (config.dataset.pageForm) {
                var pageForm = document.getElementById(config.dataset.pageForm);
                if (pageForm && pageForm.dataset.cmsBound !== '1') {
                    pageForm.dataset.cmsBound = '1';
                    pageForm.addEventListener('submit', function (e) {
                        e.preventDefault();
                        var formData = new FormData(pageForm);
                        if (config.dataset.summernoteField && window.CmsSummernote) {
                            CmsSummernote.syncFormData(formData, config.dataset.summernoteField);
                        }
                        Cms.submitFormData(Cms.appUrl(config.dataset.pageUpdate), formData, {
                            defaultError: 'Could not save page content.',
                        });
                    });
                }
            }

            if (config.dataset.itemForm) {
                var itemForm = document.getElementById(config.dataset.itemForm);
                if (itemForm && itemForm.dataset.cmsBound !== '1') {
                    itemForm.dataset.cmsBound = '1';
                    itemForm.addEventListener('submit', function (e) {
                        e.preventDefault();

                        if (config.dataset.itemErrors) {
                            Cms.clearErrors(config.dataset.itemErrors);
                        }

                        var formData = new FormData(itemForm);

                        if (config.dataset.itemActive) {
                            var active = document.getElementById(config.dataset.itemActive);
                            if (active && !active.checked) {
                                formData.delete('is_active');
                            } else {
                                formData.set('is_active', '1');
                            }
                        }

                        var currentId = state[scope];
                        var url = currentId
                            ? Cms.templateUrl(config.dataset.itemUpdate, currentId)
                            : Cms.appUrl(config.dataset.itemStore);

                        Cms.submitFormData(url, formData, {
                            modalId: config.dataset.itemModal,
                            errorsEl: config.dataset.itemErrors,
                            defaultError: 'Could not save. Please check the form and try again.',
                        });
                    });
                }
            }
        });
    }

    document.addEventListener('click', function (e) {
        var itemGalleryBtn = e.target.closest('[data-cms-item-gallery-delete]');
        if (itemGalleryBtn) {
            e.preventDefault();
            deleteItemGalleryImage(
                itemGalleryBtn.getAttribute('data-cms-item-gallery-scope'),
                itemGalleryBtn.getAttribute('data-cms-item-gallery-delete')
            );
            return;
        }

        var galleryBtn = e.target.closest('[data-cms-gallery-delete]');
        if (galleryBtn) {
            var galleryScope = galleryBtn.getAttribute('data-cms-gallery-scope');
            var imageId = galleryBtn.getAttribute('data-cms-gallery-delete');
            var galleryConfig = galleryScope ? cfg(galleryScope) : null;
            if (galleryConfig && imageId) {
                e.preventDefault();
                deleteGalleryImage(galleryConfig, imageId);
            }
            return;
        }

        var addBtn = e.target.closest('[data-cms-subitem-action="add"]');
        if (addBtn) {
            var addScope = addBtn.getAttribute('data-cms-subitem-scope');
            if (addScope) {
                resetItemForm(addScope);
            }
            return;
        }

        var btn = e.target.closest('[data-cms-subitem-action]');
        if (!btn) {
            return;
        }

        var scope = btn.getAttribute('data-cms-subitem-scope');
        var action = btn.getAttribute('data-cms-subitem-action');
        var id = btn.getAttribute('data-cms-subitem-id');
        if (!scope || !action) {
            return;
        }

        if (action === 'edit' && id) {
            e.preventDefault();
            editItem(scope, id);
            return;
        }

        if (action === 'delete' && id) {
            e.preventDefault();
            deleteItem(scope, id);
        }
    });

    function initSummernoteFields() {
        if (!window.CmsSummernote || !window.jQuery) {
            return;
        }

        document.querySelectorAll('.cms-subresource-config').forEach(function (config) {
            if (!config.dataset.summernoteField || !config.dataset.summernoteHtml) {
                return;
            }

            var field = config.dataset.summernoteField;
            if (jQuery(field).length && !jQuery(field).next('.note-editor').length) {
                CmsSummernote.initOnReady(field, {
                    height: 220,
                    initialHtml: config.dataset.summernoteHtml,
                });
            }
        });
    }

    function init() {
        wirePageForms();
        initSummernoteFields();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    document.addEventListener('livewire:navigated', init);
})();
