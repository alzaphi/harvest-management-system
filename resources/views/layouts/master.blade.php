<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-jbm.png') }}?v=2">


    <title>{{ 'Harvest Management System' }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo-jbm.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/logo-jbm.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-jbm.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- JS (with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100 min-h-screen flex">
    <!-- Sidebar -->
    <div class="sidebar-wrapper">
        @include('partials.sidebar')
    </div>

    <!-- Main Content -->
    <div class="main-content-wrapper flex-1 flex flex-col overflow-hidden">
        <!-- Topbar -->
        <header class="topbar-wrapper">
            @include('partials.topbar')
        </header>

        <!-- Content -->
        <main class="content-wrapper flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 px-6 py-4">
            <!-- Notification Messages -->
            <!-- @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif -->

            <!-- @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Terjadi kesalahan!</strong> Silakan periksa form di bawah ini.
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif -->

            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- Local DataTables Indonesian Language -->
    <script src="{{ asset('js/dataTables.indonesian.lang.js') }}"></script>
    <script>
        // Configure DataTables defaults
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                url: "{{ asset('js/dataTables.indonesian.lang.js') }}"
            },
            responsive: true,
            processing: true,
            serverSide: false
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar di mobile
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebarWrapper = document.querySelector('.sidebar-wrapper');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebarWrapper.classList.toggle('hidden');
                });
            }

            // Toggle dropdown user
            const userMenuButton = document.getElementById('user-menu-button');
            const userDropdown = document.getElementById('user-dropdown');
            
            if (userMenuButton && userDropdown) {
                userMenuButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('hidden');
                });
            }

            // Tutup dropdown saat klik di luar
            document.addEventListener('click', function() {
                if (userDropdown && !userDropdown.classList.contains('hidden')) {
                    userDropdown.classList.add('hidden');
                }
            });

            // Toggle dropdown sidebar
            const dropdownButtons = document.querySelectorAll('.sidebar-dropdown > .sidebar-link');
            dropdownButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const parent = this.parentElement;
                    const isOpen = parent.classList.contains('open');
                    
                    // Tutup semua dropdown yang lain
                    document.querySelectorAll('.sidebar-dropdown').forEach(item => {
                        if (item !== parent) {
                            item.classList.remove('open');
                        }
                    });
                    
                    // Toggle dropdown yang diklik
                    if (!isOpen) {
                        parent.classList.add('open');
                    } else {
                        parent.classList.remove('open');
                    }
                });
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebarWrapper = document.querySelector('.sidebar-wrapper');
            if (window.innerWidth >= 768) {
                sidebarWrapper.classList.remove('hidden');
            }
        });

        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });

            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Show success/error message with SweetAlert2 if present in session
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '{{ session('success') }}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true
            });
        @endif
        
        @if(session('toast'))
            const toast = @json(session('toast'));
            Swal.fire({
                icon: toast.type || 'success',
                title: toast.title || 'Berhasil',
                text: toast.message,
                toast: true,
                position: toast.position || 'top-end',
                showConfirmButton: toast.showConfirmButton || false,
                timer: toast.timer || 5000,
                timerProgressBar: toast.timerProgressBar || true
            });
        @endif
    </script>
    @stack('scripts')
</body>
</html>
