@extends('layouts.master')

@push('styles')
<link href="{{ asset('css/user-management.css') }}" rel="stylesheet">
@endpush

@section('content')

@if(session('success'))
    <div class="mb-4 px-4 py-2 bg-green-100 border border-green-300 text-green-800 rounded">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 px-4 py-2 bg-red-100 border border-red-300 text-red-800 rounded">
        {{ session('error') }}
    </div>
@endif

<div class="container user-management-container">
    <div class="mb-4">
        <h2>Tambah User Baru</h2>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Informasi User</h5>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}" autocomplete="off" class="user-form">
                @csrf

                <div class="form-group">
                    <label for="role_name" class="form-label">Role</label>
                    <div class="input-group mb-2">
                        <select class="form-select @error('role_name') is-invalid @enderror" id="role_name" name="role_name" required>
                            <option value="" selected disabled>Pilih Role</option>
                            @foreach($roles as $value => $label)
                                <option value="{{ $value }}" {{ old('role_name') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                            <option value="new_role">+ Tambah Role Baru</option>
                        </select>
                    </div>

                    <div class="input-group new-role-field" style="display: none; margin-top: 10px;">
                        <input type="text" class="form-control @error('new_role_name') is-invalid @enderror"
                               id="new_role_name" name="new_role_name"
                               placeholder="Masukkan nama role baru" required>
                        <button class="btn btn-primary" type="button" id="saveRoleBtn">Tambah</button>
                        @error('new_role_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    @error('role_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Field Vendor (hanya muncul untuk role vendor) -->
                <div class="form-group vendor-field" style="display: none;">
                    <label for="vendor_id" class="form-label">Pilih Vendor</label>
                    <select class="form-select @error('vendor_id') is-invalid @enderror" id="vendor_id" name="vendor_id">
                        <option value="" disabled selected>Pilih Vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->nama_vendor }} ({{ $vendor->no_hp }})
                            </option>
                        @endforeach
                    </select>
                    @error('vendor_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Username akan diambil dari nomor HP vendor</small>
                </div>

                <!-- Field Foreman (hanya muncul untuk role mandor) -->
                <div class="form-group foreman-field" style="display: none;">
                    <label for="foreman_id" class="form-label">Pilih Mandor</label>
                    <select class="form-select @error('foreman_id') is-invalid @enderror" id="foreman_id" name="foreman_id">
                        <option value="" disabled selected>Pilih Mandor</option>
                        @foreach($foremen as $foreman)
                            <option value="{{ $foreman->id }}" {{ old('foreman_id') == $foreman->id ? 'selected' : '' }}>
                                {{ $foreman->nama_mandor }} ({{ $foreman->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('foreman_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Email mandor akan digunakan sebagai username</small>
                </div>

                <!-- Field Email (untuk selain vendor dan mandor) -->
                <div class="form-group email-field" style="display: none;">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Email akan digunakan sebagai username</small>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-input-group">
                        <input type="text" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                        <button type="button" class="toggle-password" data-target="password">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                    <div class="password-input-group">
                        <input type="text" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        <button type="button" class="toggle-password" data-target="password_confirmation">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                </div>
                        <style>
            .form-actions {
                display: flex;
                justify-content: flex-start;
                gap: 0.5rem;
                margin-top: 1.5rem;
            }
            .btn {
                padding: 0.5rem 1rem;
                border-radius: 0.25rem;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
                border: 1px solid transparent;
            }
            .btn-primary {
                background-color: #2563eb;
                color: white;
            }
            .btn-primary:hover {
                background-color: #1d4ed8;
            }
            .btn-secondary {
                background-color: #e0e7ff;
                color: #1e40af;
            }
            .btn-secondary:hover {
                background-color: #c7d2fe;
            }
            .password-strength {
                font-size: 0.875rem;
                margin-top: 0.25rem;
            }
                        </style>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role_name');
        const newRoleField = document.querySelector('.new-role-field');
        const newRoleInput = document.getElementById('new_role_name');
        const vendorField = document.querySelector('.vendor-field');
        const emailField = document.querySelector('.email-field');
        const foremanField = document.querySelector('.foreman-field');

        // Tampilkan/sembunyikan field role baru
        function toggleNewRoleField() {
            if (roleSelect.value === 'new_role') {
                newRoleField.style.display = 'block';
                newRoleInput.setAttribute('required', 'required');
                // Sembunyikan field lain yang tidak diperlukan
                if (vendorField) vendorField.style.display = 'none';
                if (emailField) emailField.style.display = 'none';
                if (foremanField) foremanField.style.display = 'none';
            } else {
                newRoleField.style.display = 'none';
                newRoleInput.removeAttribute('required');
            }
        }

        // Inisialisasi saat halaman dimuat
        toggleNewRoleField();

        // Event listener untuk perubahan role
        roleSelect.addEventListener('change', function() {
            toggleNewRoleField();
            // Kosongkan field role baru saat ganti ke role yang lain
            if (this.value !== 'new_role') {
                newRoleInput.value = '';
            }
            // Panggil fungsi toggleFieldsByRole yang sudah ada
            toggleFieldsByRole();
        });

        // Handle role selection change
        roleSelect.addEventListener('change', function() {
            const newRoleField = document.querySelector('.new-role-field');
            const addRoleBtn = document.getElementById('addRoleBtn');

            if (this.value === 'new_role') {
                // Show new role input field
                newRoleField.style.display = 'flex';
                addRoleBtn.style.display = 'none';
                // Hide other role-dependent fields
                toggleFieldsByRole();
            } else {
                // Hide new role input field
                newRoleField.style.display = 'none';
                addRoleBtn.style.display = 'none';
                toggleFieldsByRole();
            }
        });

        // Simple function to save role to localStorage
        function saveRole(roleName) {
            // Get existing roles or initialize empty array
            let roles = [];
            try {
                roles = JSON.parse(localStorage.getItem('customRoles') || '[]');
            } catch (e) {
                console.log('No existing roles found, initializing...');
            }

            // Check if role already exists (case insensitive)
            const roleExists = roles.some(r => r.toLowerCase() === roleName.toLowerCase());

            if (!roleExists) {
                roles.push(roleName);
                localStorage.setItem('customRoles', JSON.stringify(roles));
                console.log('Saved roles:', roles);
            }

            return roles;
        }

        // Simple function to load roles into select
        function loadRoles() {
            const roleSelect = document.getElementById('role_name');
            if (!roleSelect) return;

            // Get default roles from server
            const defaultRoles = @json(array_keys($roles));

            // Get custom roles from localStorage
            let customRoles = [];
            try {
                customRoles = JSON.parse(localStorage.getItem('customRoles') || '[]');
                console.log('Loaded custom roles:', customRoles);
            } catch (e) {
                console.log('No custom roles found');
            }

            // Clear all options except the first one and 'new_role'
            const optionsToKeep = Array.from(roleSelect.options).filter(opt =>
                opt.value === '' || opt.value === 'new_role' || defaultRoles.includes(opt.value)
            );

            // Clear and rebuild options
            roleSelect.innerHTML = '';
            optionsToKeep.forEach(opt => roleSelect.add(opt));

            // Add custom roles before 'new_role' option
            customRoles.forEach(role => {
                if (!defaultRoles.includes(role)) {
                    const option = new Option(role, role);
                    roleSelect.insertBefore(option, roleSelect.lastElementChild);
                }
            });
        }

        // Load roles when page loads
        document.addEventListener('DOMContentLoaded', loadRoles);

        // Handle add role button click
        document.getElementById('saveRoleBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            const newRoleName = document.getElementById('new_role_name').value.trim();
            const roleSelect = document.getElementById('role_name');

            if (!newRoleName) {
                alert('Silakan masukkan nama role baru');
                return;
            }

            // Save the new role to localStorage
            saveRole(newRoleName);

            // Reload roles to update the dropdown
            loadRoles();

            // Select the new role
            roleSelect.value = newRoleName;

            // Clear and hide the new role input
            document.getElementById('new_role_name').value = '';
            document.querySelector('.new-role-field').style.display = 'none';

            console.log('Role added and saved:', newRoleName);
        });

        // Handle role selection change
        roleSelect.addEventListener('change', function() {
            const newRoleField = document.querySelector('.new-role-field');

            if (this.value === 'new_role') {
                // Show new role input field
                newRoleField.style.display = 'flex';
            } else if (this.value) {
                // Hide new role field if an existing role is selected
                newRoleField.style.display = 'none';
            } else {
                // Hide if no role is selected
                newRoleField.style.display = 'none';
            }
        });

        // Handle form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const roleSelect = document.getElementById('role_name');

            // If 'Tambah Role Baru' is selected but no role was added
            if (roleSelect.value === 'new_role') {
                e.preventDefault();
                alert('Silakan tambahkan role baru terlebih dahulu');
                return false;
            }

            // If we have a valid role, allow form submission
            return true;
        });
        const nameInput = document.getElementById('name');

        // Data vendor untuk autofill
        const vendorData = @json($vendors->mapWithKeys(function($vendor) {
            return [$vendor->id => $vendor->nama_vendor];
        }));

        // Data foreman untuk autofill
        const foremanData = @json($foremen->mapWithKeys(function($foreman) {
            return [$foreman->id => $foreman->nama_mandor];
        }));

        // Fungsi untuk mengisi nama berdasarkan pilihan
        function fillName() {
            const selectedRole = roleSelect.value;

            if (selectedRole === 'vendor') {
                const vendorId = document.getElementById('vendor_id').value;
                if (vendorId && vendorData[vendorId]) {
                    nameInput.value = vendorData[vendorId];
                }
            } else if (selectedRole === 'mandor') {
                const foremanId = document.getElementById('foreman_id').value;
                if (foremanId && foremanData[foremanId]) {
                    nameInput.value = foremanData[foremanId];
                }
            }
        }

        // Event listener untuk perubahan pilihan vendor
        document.getElementById('vendor_id')?.addEventListener('change', function() {
            if (roleSelect.value === 'vendor') {
                fillName();
            }
        });

        // Event listener untuk perubahan pilihan foreman
        document.getElementById('foreman_id')?.addEventListener('change', function() {
            if (roleSelect.value === 'mandor') {
                fillName();
            }
        });

        // Event listener untuk perubahan role
        roleSelect.addEventListener('change', function() {
            // Kosongkan nama saat ganti role
            nameInput.value = '';
            // Isi nama jika sudah ada pilihan
            if (this.value === 'vendor' && document.getElementById('vendor_id')?.value) {
                fillName();
            } else if (this.value === 'mandor' && document.getElementById('foreman_id')?.value) {
                fillName();
            }
        });

        // Fungsi untuk menampilkan/menyembunyikan field berdasarkan role
        function toggleFieldsByRole() {
            const selectedRole = roleSelect.value;

            // Sembunyikan semua field terlebih dahulu
            vendorField.style.display = 'none';
            foremanField.style.display = 'none';
            emailField.style.display = 'none';

            // Reset required attributes
            document.getElementById('vendor_id').required = false;
            document.getElementById('foreman_id').required = false;
            document.getElementById('email').required = false;

            if (selectedRole === 'vendor') {
                vendorField.style.display = 'block';
                document.getElementById('vendor_id').required = true;
            } else if (selectedRole === 'mandor') {
                foremanField.style.display = 'block';
                document.getElementById('foreman_id').required = true;
            } else if (selectedRole) {
                emailField.style.display = 'block';
                document.getElementById('email').required = true;
            }
        }

        // Panggil fungsi saat halaman dimuat
        toggleFieldsByRole();

        // Panggil fungsi saat role berubah
        roleSelect.addEventListener('change', toggleFieldsByRole);

        // Function to toggle password visibility
        function togglePasswordVisibility(targetId) {
            const input = document.getElementById(targetId);
            const icon = document.querySelector(`button[data-target="${targetId}"] i`);

            if (input.type === 'text') {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Set role yang dipilih sebelumnya jika ada error
        @if(old('role_name'))
            const roleSelect = document.getElementById('role_name');
            roleSelect.value = '{{ old('role_name') }}';
            toggleFieldsByRole();
        @endif

        // Set password fields to be visible by default
        document.querySelectorAll('.password-input-group input').forEach(input => {
            input.type = 'text';
        });

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                togglePasswordVisibility(targetId);
            });
        });
    });
</script>
@endpush

@endsection
