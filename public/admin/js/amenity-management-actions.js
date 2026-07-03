/**
 * Amenities CMS — search, add, edit, delete.
 * Inline scripts in Livewire slots do not run after SPA navigation; this file loads once from adminBase.
 */
(function () {
    if (window.__amenityManagementActionsInitialized) {
        return;
    }
    window.__amenityManagementActionsInitialized = true;

    var Cms = window.CmsAdmin;
    if (!Cms) {
        return;
    }

    window.__amenityMgmtCurrentId = null;

    function cfg() {
        return document.getElementById('amenity-mgmt-config');
    }

    function onAmenitiesPage() {
        return !!cfg();
    }

    function resetAmenityForm() {
        if (!onAmenitiesPage()) {
            return;
        }

        window.__amenityMgmtCurrentId = null;
        var form = document.getElementById('amenityForm');
        if (!form) {
            return;
        }

        form.reset();
        form.classList.remove('was-validated');
        document.getElementById('amenity_id').value = '';
        document.getElementById('amenityModalTitle').textContent = 'Add New Amenity';
        Cms.clearErrors('amenityFormErrors');
        form.querySelectorAll('.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
        });
    }

    window.resetAmenityForm = resetAmenityForm;

    function filterAmenityRows(query) {
        var tbody = document.getElementById('amenityTableBody');
        if (!tbody) {
            return;
        }

        var normalized = (query || '').trim().toLowerCase();
        var rows = tbody.querySelectorAll('tr[data-amenity-row]');
        var visible = 0;

        rows.forEach(function (row) {
            var haystack = (row.getAttribute('data-search-text') || '').toLowerCase();
            var match = !normalized || haystack.indexOf(normalized) !== -1;
            row.classList.toggle('d-none', !match);
            if (match) {
                visible += 1;
            }
        });

        var noResults = document.getElementById('amenityNoResultsRow');
        if (noResults) {
            noResults.classList.toggle('d-none', visible > 0 || rows.length === 0);
        }

        var countEl = document.getElementById('amenitySearchCount');
        if (countEl) {
            if (!normalized) {
                countEl.textContent = rows.length ? rows.length + ' amenities' : '';
            } else {
                countEl.textContent = 'Showing ' + visible + ' of ' + rows.length;
            }
        }
    }

    function initAmenitySearch() {
        var input = document.getElementById('amenitySearchInput');
        if (!input || input.dataset.bound === '1') {
            if (input) {
                filterAmenityRows(input.value);
            }
            return;
        }

        input.dataset.bound = '1';
        input.addEventListener('input', function () {
            filterAmenityRows(input.value);
        });
        filterAmenityRows(input.value);
    }

    document.addEventListener('input', function (e) {
        if (e.target && e.target.id === 'amenitySearchInput') {
            filterAmenityRows(e.target.value);
        }
    });

    document.addEventListener('click', function (e) {
        var addBtn = e.target.closest('[data-open-add-amenity-modal]');
        if (addBtn && onAmenitiesPage()) {
            e.preventDefault();
            resetAmenityForm();
            Cms.showModal('amenityModal');
            return;
        }

        var btn = e.target.closest('[data-amenity-action]');
        if (!btn || !onAmenitiesPage()) {
            return;
        }

        var action = btn.getAttribute('data-amenity-action');
        var id = btn.getAttribute('data-amenity-id');
        if (!action || !id) {
            return;
        }

        var c = cfg();

        if (action === 'edit') {
            Cms.fetchJson(Cms.templateUrl(c.dataset.urlShow, id), {
                headers: { Accept: 'application/json' },
            }).then(function (result) {
                if (!result.ok) {
                    window.alert('Could not load amenity. Please refresh and try again.');
                    return;
                }

                var data = result.data;
                window.__amenityMgmtCurrentId = id;
                document.getElementById('amenity_id').value = data.id;
                document.getElementById('amenity_title').value = data.title || '';
                document.getElementById('amenity_icon').value = data.icon || '';
                document.getElementById('amenityModalTitle').textContent = 'Edit Amenity';
                Cms.clearErrors('amenityFormErrors');
                Cms.showModal('amenityModal');
            });
            return;
        }

        if (action === 'delete') {
            if (!window.confirm('Are you sure you want to delete this amenity?')) {
                return;
            }

            Cms.fetchJson(Cms.templateUrl(c.dataset.urlDestroy, id), {
                method: 'DELETE',
            }).then(function (result) {
                if (result.ok && result.data.success) {
                    window.location.reload();
                }
            });
        }
    });

    document.addEventListener('submit', function (e) {
        if (!onAmenitiesPage() || e.target.id !== 'amenityForm') {
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        var form = e.target;
        var submitBtn = form.querySelector('button[type="submit"]');
        var spinner = submitBtn ? submitBtn.querySelector('.spinner-border') : null;

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            form.querySelectorAll(':invalid').forEach(function (field) {
                field.classList.add('is-invalid');
            });
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
        }
        if (spinner) {
            spinner.classList.remove('d-none');
        }

        Cms.clearErrors('amenityFormErrors');

        var c = cfg();
        var currentId = window.__amenityMgmtCurrentId;
        var url = currentId
            ? Cms.templateUrl(c.dataset.urlUpdate, currentId)
            : Cms.appUrl(c.dataset.urlStore);

        Cms.fetchJson(url, {
            method: 'POST',
            body: new FormData(form),
        }).then(function (result) {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
            if (spinner) {
                spinner.classList.add('d-none');
            }

            if (result.ok && result.data.success) {
                Cms.hideModal('amenityModal');
                window.setTimeout(function () {
                    window.location.reload();
                }, 300);
                return;
            }

            var data = result.data || {};
            var errorHtml = '<strong>Please fix the following errors:</strong><ul class="mb-0">';
            if (data.errors) {
                Object.keys(data.errors).forEach(function (field) {
                    errorHtml += '<li>' + data.errors[field][0] + '</li>';
                    var input = form.querySelector('[name="' + field + '"]');
                    if (input) {
                        input.classList.add('is-invalid');
                    }
                });
            } else if (data.message) {
                errorHtml += '<li>' + data.message + '</li>';
            } else {
                errorHtml += '<li>An error occurred. Please try again.</li>';
            }
            errorHtml += '</ul>';

            var errorDiv = document.getElementById('amenityFormErrors');
            if (errorDiv) {
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = errorHtml;
            }
        }).catch(function (err) {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
            if (spinner) {
                spinner.classList.add('d-none');
            }

            var errorDiv = document.getElementById('amenityFormErrors');
            if (errorDiv) {
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = '<strong>Error:</strong> ' + (err.message || 'An error occurred. Please try again.');
            }
        });
    });

    document.addEventListener('DOMContentLoaded', initAmenitySearch);
    document.addEventListener('livewire:navigated', initAmenitySearch);
})();
