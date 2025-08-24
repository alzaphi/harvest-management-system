@extends('layouts.master')

@section('title', 'Role Permissions')

@push('styles')
<link href="{{ asset('css/vendor-angkut.css') }}" rel="stylesheet">
<link href="{{ asset('css/permission-settings.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid py-1">
    <div class="vendor-container">
        <h2 class="mb-3">Role Permissions</h2>
        
        <!-- Search Form -->
        <div class="search-container mb-4">
            <form action="{{ route('admin.permissions.index') }}" method="GET" class="search-form">
                <div class="input-group" style="width: 300px;">
                    <input type="text" 
                           name="search" 
                           class="form-control form-control-sm search-input" 
                           placeholder="Cari role..." 
                           value="{{ request('search') }}"
                           style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                    @if(request('search'))
                        <button type="button" 
                                class="btn btn-outline-secondary btn-sm clear-search"
                                onclick="window.location.href='{{ route('admin.permissions.index') }}'"
                                style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                            <i class="fas fa-times"></i>
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="vendor-table">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>ROLE NAME</th>
                        <th>PERMISSIONS</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @if($roles->count() > 0)
                    @foreach($roles as $index => $role)
                    @php $roleName = $role->name; @endphp
                    <tr>
                        <td>{{ $roles->firstItem() + $index }}</td>
                        <td class="text-uppercase">{{ str_replace('_', ' ', $roleName) }}</td>
                        <td>
                            @if(isset($rolePermissions[$roleName]) && count($rolePermissions[$roleName]) > 0)
                                <div style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                                    @foreach($rolePermissions[$roleName] as $permission)
                                        <span class="badge-permission">{{ $permission }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="no-permissions">No permissions assigned</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center">
                                <button class="btn btn-sm btn-primary assign-permission"
                                        data-role="{{ $roleName }}"
                                        data-permissions='{{ isset($rolePermissions[$roleName]) ? json_encode($rolePermissions[$roleName]) : "[]" }}'>
                                        EDIT
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach

                    @else
                        <tr>
                            <td colspan="4" class="text-center text-gray-500">Tidak ada role yang ditemukan</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Menampilkan {{ $roles->firstItem() }} - {{ $roles->lastItem() }} dari {{ $roles->total() }} data
            </div>
            <div>
                {{ $roles->links('pagination::bootstrap-5') }}
            </div>
         </div>
    </div>
</div>

<!-- Permission Modal -->
<div id="permissionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Assign Permissions</h5>
            <button type="button" class="close-btn">&times;</button>
        </div>
        <form id="permissionForm">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="role_name" class="form-label">Role</label>
                    <input type="text" class="form-control" id="role_name" name="role_name" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Available Permissions</label>
                    <div class="permission-container">
                        <!-- Dashboard -->
                        <div class="permission-group">
                            <h6 class="permission-group-title">Dashboard</h6>
                            @foreach($permissions as $permission)
                                @if(str_contains($permission, 'dashboard'))
                                    <div class="permission-item">
                                        <input type="checkbox"
                                            class="permission-checkbox"
                                            id="perm-{{ $permission }}"
                                            name="permissions[]"
                                            value="{{ $permission }}">
                                        <label for="perm-{{ $permission }}">{{ $permission }}</label>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <!-- Harvest Planning -->
                        <div class="permission-group">
                            <h6 class="permission-group-title">Harvest Planning</h6>
                            <div class="permission-subgroup">
                                <h6 class="permission-subgroup-title">GIS Information</h6>
                                @foreach($permissions as $permission)
                                    @if(str_contains($permission, 'gis') &&
                                        !str_contains($permission, 'user') &&
                                        !str_contains($permission, 'register'))
                                        <div class="permission-item">
                                            <input type="checkbox"
                                                class="permission-checkbox"
                                                id="perm-{{ $permission }}"
                                                name="permissions[]"
                                                value="{{ $permission }}">
                                            <label for="perm-{{ $permission }}">{{ $permission }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="permission-subgroup">
                                <h6 class="permission-subgroup-title">Sub Block</h6>
                                @foreach($permissions as $permission)
                                    @if(Str::contains($permission, 'sub-block-information'))
                                        <div class="permission-item">
                                            <input type="checkbox"
                                                   class="permission-checkbox"
                                                   id="perm-{{ $permission }}"
                                                   name="permissions[]"
                                                   value="{{ $permission }}">
                                            <label for="perm-{{ $permission }}">{{ $permission }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="permission-subgroup">
                                <h6 class="permission-subgroup-title">Status Sub Block</h6>
                                @foreach($permissions as $permission)
                                    @if(Str::contains($permission, 'status-sub-block'))
                                        <div class="permission-item">
                                            <input type="checkbox"
                                                   class="permission-checkbox"
                                                   id="perm-{{ $permission }}"
                                                   name="permissions[]"
                                                   value="{{ $permission }}">
                                            <label for="perm-{{ $permission }}">{{ $permission }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="permission-subgroup">
                                <h6 class="permission-subgroup-title">Foreman Sub Block</h6>
                                @foreach($permissions as $permission)
                                    @if(Str::contains($permission, 'foreman-sub-block'))
                                        <div class="permission-item">
                                            <input type="checkbox"
                                                   class="permission-checkbox"
                                                   id="perm-{{ $permission }}"
                                                   name="permissions[]"
                                                   value="{{ $permission }}">
                                            <label for="perm-{{ $permission }}">{{ $permission }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="permission-subgroup">
                                <h6 class="permission-subgroup-title">Harvest Sub Block</h6>
                                @foreach($permissions as $permission)
                                    @if(Str::contains($permission, 'harvest-sub-block'))
                                        <div class="permission-item">
                                        <input type="checkbox"
                                               class="permission-checkbox"
                                               id="perm-{{ $permission }}"
                                               name="permissions[]"
                                               value="{{ $permission }}">
                                        <label for="perm-{{ $permission }}">{{ $permission }}</label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <!-- Mandor Management -->
                    <div class="permission-group">
                        <h6 class="permission-group-title">Mandor Management</h6>
                        <div class="permission-subgroup">
                            <h6 class="permission-subgroup-title">Mandor</h6>
                                @foreach($permissions as $permission)
                                    @if(str_contains($permission, 'mandor') && !str_contains($permission, 'foreman-sub-block'))
                                        <div class="permission-item">
                                            <input type="checkbox"
                                                class="permission-checkbox"
                                                id="perm-{{ $permission }}"
                                                name="permissions[]"
                                                value="{{ $permission }}">
                                            <label for="perm-{{ $permission }}">{{ $permission }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- Vendor Management -->
                        <div class="permission-group">
                            <h6 class="permission-group-title">Vendor Management</h6>
                            <div class="permission-subgroup">
                                <h6 class="permission-subgroup-title">List Vendor</h6>
                                @foreach($permissions as $permission)
                                    @if(str_contains($permission, 'vendor'))
                                        <div class="permission-item">
                                            <input type="checkbox"
                                                class="permission-checkbox"
                                                id="perm-{{ $permission }}"
                                                name="permissions[]"
                                                value="{{ $permission }}">
                                            <label for="perm-{{ $permission }}">{{ $permission }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="permission-subgroup">
                                <h6 class="permission-subgroup-title">List Kendaraan Vendor</h6>
                                @foreach($permissions as $permission)
                                    @if(str_contains($permission, 'vehicle'))
                                        <div class="permission-item">
                                            <input type="checkbox"
                                                class="permission-checkbox"
                                                id="perm-{{ $permission }}"
                                                name="permissions[]"
                                                value="{{ $permission }}">
                                            <label for="perm-{{ $permission }}">{{ $permission }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- Harvest Activity -->
                        <div class="permission-group">
                            <h6 class="permission-group-title">Harvest Activity</h6>
                            <div class="permission-subgroup">
                                <h6 class="permission-subgroup-title">Surat Perintah Tebang (SPT)</h6>
                                @foreach($permissions as $permission)
                                    @if(str_contains($permission, 'spt'))
                                        <div class="permission-item">
                                            <input type="checkbox"
                                                class="permission-checkbox"
                                                id="perm-{{ $permission }}"
                                                name="permissions[]"
                                                value="{{ $permission }}">
                                            <label for="perm-{{ $permission }}">{{ $permission }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="permission-subgroup">
                                <h6 class="permission-subgroup-title">Lembar Kerja Tebang (LKT)</h6>
                                @foreach($permissions as $permission)
                                    @if(str_contains($permission, 'lkt'))
                                        <div class="permission-item">
                                            <input type="checkbox"
                                                class="permission-checkbox"
                                                id="perm-{{ $permission }}"
                                                name="permissions[]"
                                                value="{{ $permission }}">
                                            <label for="perm-{{ $permission }}">{{ $permission }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="permission-subgroup">
                                <h6 class="permission-subgroup-title">Activity Tracking</h6>
                                @foreach($permissions as $permission)
                                    @if(str_contains($permission, 'track-activity'))
                                        <div class="permission-item">
                                            <input type="checkbox"
                                                class="permission-checkbox"
                                                id="perm-{{ $permission }}"
                                                name="permissions[]"
                                                value="{{ $permission }}">
                                            <label for="perm-{{ $permission }}">{{ $permission }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- Payment Management -->
                        <div class="permission-group">
                            <h6 class="permission-group-title">Payment Management</h6>
                            <div class="permission-subgroup">
                                <h6 class="permission-subgroup-title">BAPP</h6>
                                @foreach($permissions as $permission)
                                    @if(str_contains($permission, 'bapp'))
                                        <div class="permission-item">
                                            <input type="checkbox"
                                                class="permission-checkbox"
                                                id="perm-{{ $permission }}"
                                                name="permissions[]"
                                                value="{{ $permission }}">
                                            <label for="perm-{{ $permission }}">{{ $permission }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="permission-subgroup">
                                <h6 class="permission-subgroup-title">Payment Calculation</h6>
                                @foreach($permissions as $permission)
                                    @if(str_contains($permission, 'payment-calculation'))
                                        <div class="permission-item">
                                            <input type="checkbox"
                                                class="permission-checkbox"
                                                id="perm-{{ $permission }}"
                                                name="permissions[]"
                                                value="{{ $permission }}">
                                            <label for="perm-{{ $permission }}">{{ $permission }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- To-Do Approval -->
                        <div class="permission-group">
                            <h6 class="permission-group-title">To-Do Approval</h6>
                            <div class="permission-subgroup">
                                <h6 class="permission-subgroup-title">Approval LKT</h6>
                                @foreach($permissions as $permission)
                                    @if(str_contains($permission, 'approval-lkt'))
                                        <div class="permission-item">
                                            <input type="checkbox"
                                                class="permission-checkbox"
                                                id="perm-{{ $permission }}"
                                                name="permissions[]"
                                                value="{{ $permission }}">
                                            <label for="perm-{{ $permission }}">{{ $permission }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- System & Access Control -->
                        <div class="permission-group">
                            <h6 class="permission-group-title">System & Access Control</h6>
                            <div class="permission-subgroup">
                                <h6 class="permission-subgroup-title">User Management</h6>
                                @foreach($permissions as $permission)
                                    @if(str_contains($permission, 'user') ||
                                        str_contains($permission, 'register') ||
                                        str_contains($permission, 'profile'))
                                        <div class="permission-item">
                                            <input type="checkbox"
                                                class="permission-checkbox"
                                                id="perm-{{ $permission }}"
                                                name="permissions[]"
                                                value="{{ $permission }}">
                                            <label for="perm-{{ $permission }}">{{ $permission }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="permission-subgroup">
                                <h6 class="permission-subgroup-title">Permission Settings</h6>
                                @foreach($permissions as $permission)
                                    @if(str_contains($permission, 'permission'))
                                        <div class="permission-item">
                                            <input type="checkbox"
                                                class="permission-checkbox"
                                                id="perm-{{ $permission }}"
                                                name="permissions[]"
                                                value="{{ $permission }}">
                                            <label for="perm-{{ $permission }}">{{ $permission }}</label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="addNewPermission">
                        Add New Permission
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary new-permission-close">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- New Permission Input -->
<div id="newPermissionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Add New Permission</h5>
            <button type="button" class="close-btn new-permission-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="newPermission" class="form-label">Permission Name</label>
                <input type="text" class="form-control" id="newPermission" placeholder="e.g., edit-profile">
                <small class="form-text text-muted">Use kebab-case (e.g., edit-profile, view-reports)</small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary new-permission-close">Cancel</button>
            <button type="button" class="btn btn-primary" id="saveNewPermission">Add Permission</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>

document.addEventListener('DOMContentLoaded', function() {
    // Tidak perlu manipulasi checkbox manual di sini
});

// Show modal function
function showModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
}

// Hide modal function
function hideModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.body.style.overflow = 'auto'; // Re-enable scrolling
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        hideModal(event.target.id);
    }
};

// Close alert message
document.addEventListener('DOMContentLoaded', function() {
    const closeButtons = document.querySelectorAll('.close-alert');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.alert-message').style.display = 'none';
        });
    });
});

$(document).ready(function() {
    // Handle assign permission button click
    $('.assign-permission').click(function() {
        const role = $(this).data('role');
        const permissions = $(this).data('permissions');

        // Set role name
        $('#role_name').val(role);

        // Reset all checkboxes first
        $('.permission-checkbox').prop('checked', false);

        // Check the checkboxes for this role's permissions
        if (permissions && permissions.length > 0) {
            permissions.forEach(permission => {
                // Escape special characters in permission name for jQuery selector
                const permissionEscaped = permission.replace(/([ #;%&,.+*~':"!^$[\]()=>|\/@])/g, '\\$1');
                $(`#perm-${permissionEscaped}`).prop('checked', true);
            });
        }

        // Show the modal
        showModal('permissionModal');
    });

    // Close modal when close button is clicked
    $(document).on('click', '.close-btn, .new-permission-close', function(e) {
        e.preventDefault();
        hideModal('permissionModal');
        hideModal('newPermissionModal');
    });

    // Handle form submission
    $('#permissionForm').on('submit', function(e) {
        e.preventDefault();

        const formData = {
            role_name: $('#role_name').val(),
            permissions: $('input.permission-checkbox:checked').map(function() {
                return $(this).val();
            }).get()
        };

        // Send AJAX request
        $.ajax({
            url: '{{ route("admin.permissions.store") }}',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                hideModal('permissionModal');
                if (response.success) {
                    // Show success toast notification
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Role permission berhasil disimpan!',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true
                    });

                    // Reload the page after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    const alertHtml = `
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="width: 100%; margin: 0 auto;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <div class="flex-grow-1">
                                    <strong>Error!</strong> ${response.message || 'An error occurred'}
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    `;
                    // Add error alert to the top of the content area
                    $('.content-wrapper').prepend('<div class="container">' + alertHtml + '</div>');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                alert(response.message || 'An error occurred while saving permissions');
            }
        });
    });

    // Handle add new permission button
    $('#addNewPermission').click(function() {
        showModal('newPermissionModal');
    });

    // Close new permission modal when close button is clicked
    $('.new-permission-close').click(function() {
        hideModal('newPermissionModal');
    });

    // Handle save new permission
    $('#saveNewPermission').click(function() {
        const permissionName = $('#newPermission').val().trim();

        if (!permissionName) {
            toastr.error('Please enter a permission name');
            return;
        }

        // Check if permission already exists
        if ($(`#perm-${permissionName}`).length) {
            toastr.warning('This permission already exists');
            return;
        }

        // Add new permission checkbox
        const newCheckbox = `
            <div class="col-md-4 mb-2">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input permission-checkbox"
                           id="perm-${permissionName}"
                           name="permissions[]"
                           value="${permissionName}" checked>
                    <label class="custom-control-label" for="perm-${permissionName}">
                        ${permissionName}
                    </label>
                </div>
            </div>
        `;

        $('.permission-checkboxes').append(newCheckbox);
        $('#newPermission').val('');
        hideModal('newPermissionModal');

        // Scroll to the new permission
        $(`#perm-${permissionName}`)[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    // Allow pressing Enter to save new permission
    $('#newPermission').keypress(function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#saveNewPermission').click();
        }
    });
});
</script>
@endpush
