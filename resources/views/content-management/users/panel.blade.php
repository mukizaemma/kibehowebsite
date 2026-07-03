{{-- User management UI (embedded in Settings tab or standalone page). --}}
<div id="user-mgmt-config" class="d-none"
    data-can-manage="{{ !empty($isManager) ? '1' : '0' }}"
    data-url-show="{{ route('content-management.users.show', ['id' => '__ID__'], false) }}"
    data-url-store="{{ route('content-management.users.store', [], false) }}"
    data-url-update="{{ route('content-management.users.update', ['id' => '__ID__'], false) }}"
    data-url-destroy="{{ route('content-management.users.destroy', ['id' => '__ID__'], false) }}"
    data-url-verify="{{ route('content-management.users.verify-email', ['id' => '__ID__'], false) }}"
    data-url-resend="{{ route('content-management.users.resend-verification', ['id' => '__ID__'], false) }}"
    data-url-reset-password="{{ route('content-management.users.reset-password', ['id' => '__ID__'], false) }}"
></div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-1">Users</h5>
            <p class="text-muted small mb-0">Assign <strong>Admin</strong> (content only) or <strong>Normal User</strong> (no admin). Super Admin accounts are created via seeding only.</p>
        </div>
        @if(!empty($isManager))
        <button type="button" class="btn btn-primary" data-open-add-user-modal>
            <i class="fa fa-plus me-2"></i>Add New User
        </button>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Email Verified</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->role)
                                <span class="badge bg-info">{{ $user->role->name }}</span>
                            @else
                                <span class="badge bg-secondary">No Role Assigned</span>
                            @endif
                        </td>
                        <td>
                            @if($user->email_verified_at)
                                <span class="badge bg-success">Verified</span>
                            @else
                                <span class="badge bg-warning">Not Verified</span>
                            @endif
                        </td>
                        <td><span class="badge bg-{{ $user->status == 'Active' ? 'success' : 'danger' }}">{{ $user->status }}</span></td>
                        <td>
                            <div class="btn-group js-user-actions" role="group">
                                @if(!empty($isManager))
                                @if(!$user->email_verified_at)
                                <button type="button" class="btn btn-sm btn-success" data-user-action="verify" data-user-id="{{ $user->id }}" title="Verify Email">
                                    <i class="fa fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-info" data-user-action="resend" data-user-id="{{ $user->id }}" title="Resend Verification">
                                    <i class="fa fa-envelope"></i>
                                </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-warning" data-user-action="edit" data-user-id="{{ $user->id }}" title="Edit User">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" data-user-action="reset-password" data-user-id="{{ $user->id }}" title="Reset Password">
                                    <i class="fa fa-key"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" data-user-action="delete" data-user-id="{{ $user->id }}" title="Delete User">
                                    <i class="fa fa-trash"></i>
                                </button>
                                @else
                                <span class="text-muted small">Read only</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(!empty($isManager))
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal"></button>
            </div>
            <form id="userForm">
                <div class="modal-body">
                    <div id="userFormErrors" class="alert alert-danger d-none" role="alert"></div>
                    <input type="hidden" id="user_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" id="user_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" id="user_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span id="passwordLabel">*</span></label>
                        <input type="password" class="form-control" id="user_password" name="password" required>
                        <small class="form-text text-muted">Minimum 8 characters</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign Role *</label>
                        <select class="form-control form-select" id="user_role_id" name="role_id" required>
                            <option value="">-- Select Role --</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" data-role-name="{{ $role->name }}">
                                {{ $role->name }}
                                @if($role->description)
                                    — {{ $role->description }}
                                @endif
                            </option>
                            @endforeach
                            @if(isset($superAdminRole) && $superAdminRole)
                            <option value="{{ $superAdminRole->id }}" id="role_option_super_admin" hidden data-role-name="{{ $superAdminRole->name }}">
                                {{ $superAdminRole->name }} — seeded only; keep or change to Admin / Normal User
                            </option>
                            @endif
                        </select>
                        <small class="form-text text-muted">Super Admin is not listed — use only for the seeded system account.</small>
                    </div>
                    <div class="mb-3" id="verifyImmediatelyContainer">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="verify_immediately" name="verify_immediately" value="1" checked>
                            <label class="form-check-label" for="verify_immediately">
                                <strong>Verify email immediately</strong>
                            </label>
                        </div>
                        <small class="form-text text-muted">If checked, the user will be verified immediately without sending a verification email.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="resetPasswordForm">
                <div class="modal-body">
                    <input type="hidden" id="reset_password_user_id" name="user_id">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle me-2"></i>You can reset this user's password without knowing their current password.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password *</label>
                        <input type="password" class="form-control" id="new_password" name="password" required minlength="8">
                        <small class="form-text text-muted">Minimum 8 characters</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password *</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="password_confirmation" required minlength="8">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
