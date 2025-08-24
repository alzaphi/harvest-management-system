@extends('layouts.master')

@push('styles')
    <link href="{{ asset('css/profile.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto p-2">
        <div class="bg-white rounded-lg shadow-sm">
            <!-- Header with Name and Role -->
            <div class="profile-header">
                <div class="header-content">
                    <h1 class="profile-name">{{ $user->name }}</h1>
                    <span class="role-badge">{{ $user->role_name ?? 'User' }}</span>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="profile-section">
                <div>
                    <h3 class="section-title">Informasi Akun</h3>
                </div>
                
                <div class="profile-grid">
                    <div class="profile-field">
                        <span class="field-label">Nama</span>
                        <span class="field-value">{{ $user->name }}</span>
                    </div>
                    
                    @if($user->role_name !== 'vendor')
                    <div class="profile-field">
                        <span class="field-label">Email</span>
                        <span class="field-value">{{ $user->username ?? '-' }}</span>
                    </div>
                    @endif
                    
                    @if($user->role_name === 'vendor' && $profileData && $profileData->count() > 0)
                        @php
                            $firstVendor = $profileData->first();
                        @endphp
                        <div class="profile-field">
                            <span class="field-label">Kode Vendor</span>
                            <div class="field-value">
                                {{ $profileData->pluck('kode_vendor')->implode(', ') }}
                            </div>
                        </div>
                        <div class="profile-field">
                            <span class="field-label">Jumlah Tenaga Kerja</span>
                            <span class="field-value">{{ $firstVendor->jumlah_tenaga_kerja ?? '-' }}</span>
                        </div>
                        <div class="profile-field">
                            <span class="field-label">No. HP</span>
                            <span class="field-value">{{ $firstVendor->no_hp ?? '-' }}</span>
                        </div>
                        <div class="profile-field">
                            <span class="field-label">No. Rekening</span>
                            <span class="field-value">{{ $firstVendor->nomor_rekening ?? '-' }}</span>
                        </div>
                        <div class="profile-field">
                            <span class="field-label">Nama Bank</span>
                            <span class="field-value">{{ $firstVendor->nama_bank ?? '-' }}</span>
                        </div>
                    @elseif($user->role_name === 'mandor' && $profileData)
                        <div class="profile-field">
                            <span class="field-label">Kode Mandor</span>
                            <span class="field-value">{{ $profileData['kode_mandor'] ?? '-' }}</span>
                        </div>
                        <div class="profile-field">
                            <span class="field-label">No. HP</span>
                            <span class="field-value">{{ $profileData['no_hp'] ?? '-' }}</span>
                        </div>
                    @endif
                </div>
            </div>

                <!-- Password Update Form -->
                <div class="profile-section">
                    <div>
                        <h3 class="section-title">Ubah Password</h3>                    </div>
                    
                    <form action="{{ route('profile.password.update') }}" method="POST" class="password-form">
                        @csrf
                        @method('POST')
                        
                        <div class="form-group">
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <div class="password-input-container">
                                <input type="text" name="current_password" id="current_password" 
                                       class="form-input" required>
                                <span class="toggle-password" data-target="current_password">
                                    <i class="fas fa-eye-slash"></i>
                                </span>
                            </div>
                            @error('current_password')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password" class="form-label">Password Baru</label>
                            <div class="password-input-container">
                                <input type="password" name="new_password" id="new_password" 
                                       class="form-input" required>
                                <span class="toggle-password" data-target="new_password">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            @error('new_password')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <div class="password-input-container">
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation" 
                                       class="form-input" required>
                                <span class="toggle-password" data-target="new_password_confirmation">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key mr-2"></i> Update Password
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline">
                                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Last Updated -->
                <div class="last-updated-container">
                    <span>Terakhir diperbarui: {{ $user->updated_at->format('d M Y H:i') }}</span>
                </div>
            </div> <!-- Close card content -->
        </div> <!-- Close card -->
    </div> <!-- Close container -->

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi toggle password
        function initPasswordToggle(button) {
            const targetId = button.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = button.querySelector('i');
            
            // Set initial state for current password (visible)
            if (targetId === 'current_password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
            
            button.addEventListener('click', function() {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }

        // Initialize all password toggles
        document.querySelectorAll('.toggle-password').forEach(initPasswordToggle);

        @if(session('status'))
            alert('{{ session('status') }}');
        @endif

        @if($errors->any())
            alert('{{ $errors->first() }}');
        @endif
    });
</script>
@endpush
@endsection
