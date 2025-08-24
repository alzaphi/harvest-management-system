@extends('layouts.master')

@push('styles')
<link href="{{ asset('css/user-management.css') }}" rel="stylesheet">
<link href="{{ asset('css/vendor-angkut.css') }}" rel="stylesheet">
@endpush

@section('content')
<!-- Alert -->
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

<div class="vendor-container">
    <h2>User Account Registration</h2>

    <div class="vendor-header mb-2">
        <form action="{{ route('users.index') }}" method="GET" class="search-form" id="search-form">
            <div class="filter-group">
                <input type="text" 
                       name="search" 
                       id="search-input" 
                       placeholder="Cari nama atau username..."
                       value="{{ request('search') }}" 
                       class="search-input">
                
                <select name="role" id="role-filter" class="filter-select">
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" {{ request('role') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                @foreach(request()->except('search', 'role', 'page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
            </div>
        </form>

        <div class="btn-group">
            <a href="{{ route('users.create') }}" class="btn btn-primary tambah-vendor-btn">Tambah User</a>
        </div>
    </div>

    <div class="table-responsive mt-1">
                <table class="vendor-table">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>ROLE</th>
                            <th>NAMA</th>
                            <th>USERNAME</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @php
                                        $roleClass = [
                                            'admin' => 'badge-primary',
                                            'vendor' => 'badge-success',
                                            'mandor' => 'badge-warning',
                                            'gis_department' => 'badge-info',
                                            'PT PAG' => 'badge-role-pt-pag',
                                            'QA' => 'badge-role-qa',
                                            'Manager Finance' => 'badge-role-manager-finance',
                                            'Assistant Finance' => 'badge-role-assistant-finance',
                                            'Director' => 'badge-role-director',
                                            'GIS Division' => 'badge-role-gis-division',
                                            'Manager CDR' => 'badge-role-manager-cdr',
                                            'Assistant Manager CDR' => 'badge-role-assistant-manager-cdr',
                                            'Manager Plantation' => 'badge-role-manager-plantation',
                                            'Assistant Manager Plantation' => 'badge-role-assistant-manager-plantation',
                                            'Assistant Divisi Plantation' => 'badge-role-assistant-divisi-plantation'
                                        ][$user->role_name] ?? 'badge-secondary';
                                    @endphp
                                    <span class="badge {{ $roleClass }}">
                                        {{ strtoupper(str_replace('_', ' ', $user->role_name)) }}
                                    </span>
                                </td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->username }}</td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <!-- Barcode Button (Always takes space, but only visible for vendors) -->
                                        <div class="action-button-placeholder">
                                            @if($user->role_name === 'vendor')
                                            <a href="{{ route('barcode.show', $user->id) }}" 
                                               class="btn btn-sm btn-primary action-button-barcode"
                                               title="Lihat Barcode">
                                                <i class="fas fa-qrcode"></i>
                                            </a>
                                            @endif
                                        </div>
                                        
                                        <!-- Edit Button -->
                                        <a href="{{ route('users.edit-password', $user) }}" 
                                           class="btn btn-sm btn-secondary action-button">
                                            Edit
                                        </a>
                                        
                                        <!-- Delete Button -->
                                        <form action="{{ route('users.destroy', $user->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger action-button">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">Tidak ada data user</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <div class="flex justify-between items-center mt-4">
                    <div class="text-sm text-gray-500">
                        Menampilkan 
                        @if($users->count() > 0)
                            {{ $users->firstItem() }} - {{ $users->lastItem() }} 
                        @else
                            0
                        @endif
                        dari {{ $users->total() }} data
                    </div>
                    <div class="ml-auto">
                        {{ $users->appends(request()->query())->links('pagination::vendor-angkut') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let typingTimer;
        const typingInterval = 500; // 0.5 detik
        const searchInput = document.getElementById('search-input');
        const searchForm = document.getElementById('search-form');
        const roleFilter = document.getElementById('role-filter');

        // Handle search input with debounce
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    if (searchInput.value.length >= 2 || searchInput.value.length === 0) {
                        searchForm.submit();
                    }
                }, typingInterval);
            });
        }

        // Handle role filter change
        if (roleFilter) {
            roleFilter.addEventListener('change', function() {
                searchForm.submit();
            });
        }

        // Handle alert close button
        document.querySelectorAll('.close-alert').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.alert-message').style.display = 'none';
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert-message').forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            });
        }, 5000);
    });
</script>
@endpush

@endsection
