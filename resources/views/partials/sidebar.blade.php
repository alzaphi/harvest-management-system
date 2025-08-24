<div class="sidebar-wrapper">
<aside class="sidebar">
    <!-- Mobile Close Button -->
    <button id="sidebar-close" class="sidebar-close-btn block md:hidden" aria-label="Tutup Sidebar" style="position:absolute;top:1rem;right:1rem;z-index:50;background:none;border:none;color:#fff;font-size:2rem;display:none;">
        <span aria-hidden="true">&times;</span>
    </button>
    <!-- Header -->
    <div class="sidebar-header">
        <i class="fas fa-user-circle sidebar-icon"></i>
        <span class="sidebar-title">Harvest Management System</span>
    </div>

    <!-- Profile -->
    <div class="sidebar-profile">
        <img class="sidebar-avatar" src="{{ asset('images/avatar.png') }}" alt="Avatar">
        <div class="sidebar-profile-text">
            <p class="sidebar-welcome">Welcome,</p>
            <p class="sidebar-username">{{ ucfirst(auth()->user()->role_name) . ' - ' . auth()->user()->name }}</p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">

        <!-- Dashboard -->
        @can('view-dashboard')
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt sidebar-link-icon"></i>
            <span>Dashboard</span>
        </a>
        @endcan

        <!-- Harvest Planning -->
        @if(auth()->user()->can('view-gis-information') || auth()->user()->can('view-sub-block-information') || auth()->user()->can('view-foreman-sub-block'))
        <div class="sidebar-dropdown">
            <div class="sidebar-link">
                <i class="fas fa-calendar-check sidebar-link-icon"></i>
                <span>Harvest Planning</span>
            </div>
            <div class="sidebar-submenu">
                @can('view-gis-information')
                <a href="{{ route('gis.index') }}" class="sidebar-sublink {{ request()->routeIs('gis.*') ? 'active' : '' }}">
                    <i class="fas fa-map-marked-alt submenu-icon"></i> GIS Information
                </a>
                @endcan

                @if(auth()->user()->can('view-sub-block-information') || auth()->user()->can('view-foreman-sub-block'))
                <a href="{{ route('sub-blocks.index') }}" class="sidebar-sublink {{ request()->routeIs('sub-blocks.*') || request()->routeIs('status-sub-blocks.*') || request()->routeIs('harvest-sub-blocks.*') || request()->routeIs('foreman-sub-blocks.*') ? 'active' : '' }}">
                    <i class="fas fa-th-large submenu-icon"></i> Sub Block Information
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Vendor Management -->
        @if(auth()->user()->can('view-vendors') || auth()->user()->can('view-vehicles'))
        <div class="sidebar-dropdown">
            <div class="sidebar-link">
                <i class="fas fa-user-friends sidebar-link-icon"></i>
                <span>Vendor Management</span>
            </div>
            <div class="sidebar-submenu">
                @can('view-vendors')
                <a href="{{ route('vendor.index') }}" class="sidebar-sublink {{ request()->routeIs('vendor.*') ? 'active' : '' }}">
                    <i class="fas fa-user submenu-icon"></i> List Vendor
                </a>
                @endcan
                @can('view-vehicles')
                <a href="{{ route('vehicles.index') }}" class="sidebar-sublink {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
                    <i class="fas fa-shuttle-van submenu-icon"></i> List Kendaraan Vendor
                </a>
                @endcan
            </div>
        </div>
        @endif

        <!-- Foreman Management -->
        @can('view-mandors')
        <a href="{{ route('foreman.index') }}" class="sidebar-link {{ request()->routeIs('foreman.*') ? 'active' : '' }}">
            <i class="fas fa-hard-hat sidebar-link-icon"></i>
            <span>Mandor Management</span>
        </a>
        @endcan

        <!-- Harvest Activity -->
        @if(auth()->user()->can('view-spt') || auth()->user()->can('view-lkt') || auth()->user()->can('track-activity'))
        <div class="sidebar-dropdown">
            <div class="sidebar-link">
                <i class="fas fa-seedling sidebar-link-icon"></i>
                <span>Harvest Activity</span>
            </div>
            <div class="sidebar-submenu">
                @can('view-spt')
                <a href="{{ route('spt.index') }}" class="sidebar-sublink {{ request()->routeIs('spt.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard submenu-icon"></i> Surat Perintah Tebang (SPT)
                </a>
                @endcan
                @can('view-lkt')
                <a href="{{ route('lkt.index') }}" class="sidebar-sublink {{ request()->routeIs('lkt.*') ? 'active' : '' }}">
                    <i class="fas fa-file-contract submenu-icon"></i> Lembar Kerja Tebang (LKT)
                </a>
                @endcan
                @can('view-track-activity')
                <a href="{{ route('activity.tracking.index') }}" class="sidebar-sublink {{ request()->routeIs('activity.tracking.*') ? 'active' : '' }}">
                    <i class="fas fa-tasks submenu-icon"></i> Harvest Activity Tracking
                </a>
                @endcan
                @can('view-hasil-tebang')
                <a href="{{ route('hasil-tebang.index') }}" class="sidebar-sublink {{ request()->routeIs('hasil-tebang.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check submenu-icon"></i> Hasil Tebang
                </a>
                @endcan

            </div>
        </div>
        @endif

        <!-- Payment Management -->
        @if(auth()->user()->can('view-bapp') || auth()->user()->can('view-payment-calculation') || auth()->user()->can('view-rekap-bapp') || auth()->user()->hasRole('vendor'))
        <div class="sidebar-dropdown">
            <div class="sidebar-link">
                <i class="fas fa-money-bill-wave sidebar-link-icon"></i>
                <span>Payment Management</span>
            </div>
            <div class="sidebar-submenu">
                @can('view-bapp')
                <a href="{{ route('bapp.index', ['jenis' => 'tebang']) }}" class="sidebar-sublink {{ request()->routeIs('bapp.*') && !request()->routeIs('bapp.recap.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar submenu-icon"></i> BAPP
                </a>
                @endcan

                @can('view-bapp-recap')
                <a href="{{ route('bapp.recap.index') }}" class="sidebar-sublink {{ request()->routeIs('bapp.recap.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar submenu-icon"></i> Rekap BAPP
                </a>
                @endcan

                @if(auth()->user()->can('view-payment-calculation') || auth()->user()->hasRole('vendor'))
                <a href="{{ route('payment.index') }}" class="sidebar-sublink {{ request()->routeIs('payment.*') ? 'active' : '' }}">
                    <i class="fas fa-history submenu-icon"></i> History Pembayaran
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- To-Do Approval -->
        <!-- @php
            $user = auth()->user();
            $canViewSpt = $user->can('view-approval-spt');
            $canViewLkt = $user->can('view-approval-lkt');
            $canApproveBapp = $user->can('approve-bapp');
            $canApproveDana = $user->can('approve-dana');
        @endphp -->

        @if(auth()->user()->can('view-dana'))
        <div class="sidebar-dropdown">
            <div class="sidebar-link">
                <i class="fas fa-check-circle sidebar-link-icon"></i>
                <span>To-Do Approval</span>
            </div>
            <div class="sidebar-submenu">
                @can('view-dana')
                <a href="{{ route('spd.index') }}" class="sidebar-sublink {{ request()->routeIs('spd.*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave submenu-icon"></i> Approval Dana
                </a>
                @endcan
            </div>
        </div>
        @endif

        <!-- User & Role Management -->
        @if(auth()->user()->can('view-users') || auth()->user()->can('view-roles') || auth()->user()->can('view-permissions'))
        <div class="sidebar-dropdown">
            <div class="sidebar-link">
                <i class="fas fa-user-cog sidebar-link-icon"></i>
                <span>System & Access Control</span>
            </div>
            <div class="sidebar-submenu">
                @can('view-users')
                <a href="{{ route('users.index') }}" class="sidebar-sublink {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fas fa-user-plus submenu-icon"></i> User Account Registration
                </a>
                @endcan
                @can('view-permissions')
                <a href="{{ route('admin.permissions.index') }}" class="sidebar-sublink {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                    <i class="fas fa-key submenu-icon"></i> Permission Settings
                </a>
                @endcan
            </div>
        </div>
        @endif

    </nav>

    <!-- Footer -->
    <div class="sidebar-footer">
    &copy; 2025 PT. Jhonlin Batu Mandiri
    </div>
</aside>
</div>
