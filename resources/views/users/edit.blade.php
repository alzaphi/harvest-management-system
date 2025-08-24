@extends('layouts.master')

@push('styles')
<link href="{{ asset('css/user-management.css') }}" rel="stylesheet">
<link href="{{ asset('css/vendor-angkut.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="vendor-container">
    <h2>Ubah Password</h2>

    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="mb-4 px-4 py-2 bg-green-100 border border-green-300 text-green-800 rounded relative alert-message">
                    {{ session('success') }}
                    <button type="button" class="close-alert absolute top-0 right-0 px-3 py-2 text-green-800 hover:text-green-900">
                        &times;
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 px-4 py-2 bg-red-100 border border-red-300 text-red-800 rounded relative alert-message">
                    {{ session('error') }}
                    <button type="button" class="close-alert absolute top-0 right-0 px-3 py-2 text-red-800 hover:text-red-900">
                        &times;
                    </button>
                </div>
            @endif

            <form action="{{ route('users.update-password', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                </div>

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" value="{{ $user->username }}" readonly>
                </div>

                <div class="form-group">
                    <label for="role_name" class="form-label">Role</label>
                    <input type="text" class="form-control" value="{{ ucfirst($user->role_name) }}" readonly>
                </div>

                <div class="form-group">
                    <label for="current_password" class="form-label">Password Saat Ini</label>
                    <div class="password-input-group">
                        <input type="text" class="form-control @error('current_password') is-invalid @enderror" 
                               id="current_password" name="current_password" required>
                        <button type="button" class="toggle-password" data-target="current_password">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="new_password" class="form-label">Password Baru</label>
                    <div class="password-input-group">
                        <input type="text" class="form-control @error('new_password') is-invalid @enderror" 
                               id="new_password" name="new_password" required>
                        <button type="button" class="toggle-password" data-target="new_password">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                        @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                    <div class="password-input-group">
                        <input type="text" class="form-control" 
                               id="new_password_confirmation" name="new_password_confirmation" required>
                        <button type="button" class="toggle-password" data-target="new_password_confirmation">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle password visibility toggle
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                togglePasswordVisibility(targetId);
            });
        });

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

        // Set password fields to be visible by default
        document.querySelectorAll('.password-input-group input').forEach(input => {
            input.type = 'text';
        });

        // Handle alert close button
        document.querySelectorAll('.close-alert').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.alert-message').style.display = 'none';
            });
        });
    });
</script>
@endpush
@endsection
