/**
 * System Users page — delegated clicks + modal helper via CmsAdmin.
 */
(function () {
    if (window.__userManagementActionsInitialized) {
        return;
    }
    window.__userManagementActionsInitialized = true;

    var Cms = window.CmsAdmin;
    if (!Cms) {
        return;
    }

    function cfg() {
        return document.getElementById('user-mgmt-config');
    }

    function canManageUsers() {
        var c = cfg();
        return c && c.dataset.canManage === '1';
    }

    window.closeUserModal = function () {
        Cms.hideModal('userModal');
    };

    window.closeResetPasswordModal = function () {
        Cms.hideModal('resetPasswordModal');
    };

    window.resetUserForm = function () {
        if (!canManageUsers()) {
            return;
        }

        var form = document.getElementById('userForm');
        if (!form) {
            return;
        }

        window.__userMgmtCurrentUserId = null;
        form.reset();
        document.getElementById('user_id').value = '';
        document.getElementById('user_password').required = true;
        document.getElementById('passwordLabel').textContent = '*';
        document.getElementById('userModalTitle').textContent = 'Add New User';
        var vic = document.getElementById('verifyImmediatelyContainer');
        if (vic) {
            vic.style.display = 'block';
        }
        var chk = document.getElementById('verify_immediately');
        if (chk) {
            chk.checked = true;
        }
        var superOpt = document.getElementById('role_option_super_admin');
        if (superOpt) {
            superOpt.setAttribute('hidden', 'hidden');
        }
        Cms.clearErrors('userFormErrors');
    };

    document.addEventListener('click', function (e) {
        var openBtn = e.target.closest('[data-open-add-user-modal]');
        if (openBtn && canManageUsers()) {
            e.preventDefault();
            window.resetUserForm();
            Cms.showModal('userModal');
            return;
        }

        var btn = e.target.closest('[data-user-action]');
        if (!btn || !cfg() || !canManageUsers()) {
            return;
        }

        var action = btn.getAttribute('data-user-action');
        var id = btn.getAttribute('data-user-id');
        if (!action || !id) {
            return;
        }

        var c = cfg();

        if (action === 'edit') {
            Cms.fetchJson(Cms.templateUrl(c.dataset.urlShow, id), {
                headers: { Accept: 'application/json' },
            }).then(function (result) {
                if (!result.ok) {
                    if (result.status === 403) {
                        window.alert('You are not allowed to edit this user with the current account.');
                        return;
                    }
                    window.alert('Could not load user. Please refresh and try again.');
                    return;
                }

                var data = result.data;
                window.__userMgmtCurrentUserId = id;
                var superOpt = document.getElementById('role_option_super_admin');
                if (superOpt) {
                    if (data.role && data.role.slug === 'super-admin') {
                        superOpt.removeAttribute('hidden');
                    } else {
                        superOpt.setAttribute('hidden', 'hidden');
                    }
                }
                document.getElementById('user_id').value = data.id;
                document.getElementById('user_name').value = data.name;
                document.getElementById('user_email').value = data.email;
                document.getElementById('user_role_id').value = data.role_id || '';
                document.getElementById('user_password').required = false;
                document.getElementById('passwordLabel').textContent = '(leave blank to keep current)';
                document.getElementById('userModalTitle').textContent = 'Edit User';
                var vic = document.getElementById('verifyImmediatelyContainer');
                if (vic) {
                    vic.style.display = 'none';
                }
                Cms.showModal('userModal');
            });
            return;
        }

        if (action === 'verify') {
            if (!window.confirm("Verify this user's email?")) {
                return;
            }
            Cms.fetchJson(Cms.templateUrl(c.dataset.urlVerify, id), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
            }).then(function (result) {
                if (result.ok && result.data.success) {
                    window.location.reload();
                }
            });
            return;
        }

        if (action === 'resend') {
            Cms.fetchJson(Cms.templateUrl(c.dataset.urlResend, id), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
            }).then(function (result) {
                if (result.ok && result.data.success) {
                    window.alert('Verification email sent successfully!');
                }
            });
            return;
        }

        if (action === 'reset-password') {
            var hid = document.getElementById('reset_password_user_id');
            var form = document.getElementById('resetPasswordForm');
            if (!hid || !form) {
                return;
            }
            hid.value = id;
            form.reset();
            hid.value = id;
            Cms.showModal('resetPasswordModal');
            return;
        }

        if (action === 'delete') {
            if (!window.confirm('Are you sure you want to delete this user?')) {
                return;
            }
            Cms.fetchJson(Cms.templateUrl(c.dataset.urlDestroy, id), {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
            }).then(function (result) {
                if (result.ok && result.data.success) {
                    window.location.reload();
                }
            });
        }
    });

    document.addEventListener('submit', function (e) {
        if (!cfg() || !canManageUsers()) {
            return;
        }

        if (e.target.id === 'resetPasswordForm') {
            e.preventDefault();
            var userId = document.getElementById('reset_password_user_id').value;
            var pwd = document.getElementById('new_password').value;
            var pwd2 = document.getElementById('new_password_confirmation').value;
            if (pwd !== pwd2) {
                window.alert('Passwords do not match!');
                return;
            }
            if (pwd.length < 8) {
                window.alert('Password must be at least 8 characters long!');
                return;
            }

            Cms.submitFormData(Cms.templateUrl(cfg().dataset.urlResetPassword, userId), (function () {
                var fd = new FormData();
                fd.append('password', pwd);
                fd.append('password_confirmation', pwd2);
                return fd;
            })(), {
                reload: false,
                onSuccess: function (result) {
                    window.alert(result.data.message || 'Password reset successfully!');
                    Cms.hideModal('resetPasswordModal');
                },
                defaultError: 'Failed to reset password.',
            });
            return;
        }

        if (e.target.id === 'userForm') {
            e.preventDefault();
            var currentId = window.__userMgmtCurrentUserId;
            var url = currentId
                ? Cms.templateUrl(cfg().dataset.urlUpdate, currentId)
                : Cms.appUrl(cfg().dataset.urlStore);

            Cms.submitFormData(url, new FormData(e.target), {
                modalId: 'userModal',
                errorsEl: 'userFormErrors',
                reloadDelay: 300,
            });
        }
    });

    window.__userMgmtCurrentUserId = null;
})();
