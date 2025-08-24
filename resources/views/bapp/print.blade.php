@extends('layouts.master')

@section('page-title', 'Cetak BAPP - ' . ($bapp->kode_bapp ?? ''))

@push('styles')
<style>
    @media print {
        /* Hide everything by default */
        body * {
            visibility: hidden;
        }

        /* Only show the bapp container and its children */
        .bapp-container,
        .bapp-container * {
            visibility: visible;
        }

        /* Position the bapp container */
        .bapp-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 20px;
            margin: 0;
        }

        /* Hide specific elements that might still be visible */
        .app-header, .app-sidebar, .app-header-mobile, .app-header-menu, .app-footer,
        .breadcrumb, .page-title, .profile-dropdown, .user-menu, .app-navbar,
        .sidebar, .main-sidebar, .navbar-nav, .navbar-right, .user-panel,
        .sidebar-menu, .header, .main-header, .main-sidebar, .content-header,
        .main-footer, .pull-right, .pull-left, .hidden-print, nav, header, footer,
        .sidebar-toggle, .sidebar-menu > li.header, .navbar, .app-header, .app-header__logo,
        .app-header__content, .app-header__mobile-menu, .app-header__menu, .app-header__container,
        .app-header__right, .app-header__menu--profile, .app-header__menu--notifications,
        .app-header__menu--messages, .app-header__menu--settings, .app-header__menu--user {
            display: none !important;
        }
        @page {
            size: auto;
            margin: 10mm 5mm 10mm 5mm;
        }
        body {
            width: 100%;
            margin: 0;
            padding: 0;
            background: #fff;
            font-size: 12px;
            line-height: 1.3;
        }
        body * {
            visibility: visible;
        }
        .bapp-container {
            width: 100%;
            margin: 0;
            padding: 10px;
        }
        .no-print, .no-print *,
        .action-buttons,
        .signature-pad,
        .signature-buttons,
        .btn,
        .modal {
            display: none !important;
        }
        table {
            width: 100% !important;
            border-collapse: collapse;
            margin: 10px 0;
            page-break-inside: auto;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 4px 6px !important;
            text-align: left;
            font-size: 10px;
            word-wrap: break-word;
        }
        .table-responsive {
            overflow: visible !important;
            width: 100% !important;
        }
        .signature-box {
            margin-top: 20px;
            width: 40%;
            float: left;
            page-break-inside: avoid;
        }
        .signature-line {
            margin: 40px 0 10px;
            border-top: 1px solid #000;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid bapp-container">
    <!-- Status Badge - Top Right Corner -->
    <div class="status-badge-container">
        @php
            $status = $bapp->status ?? 'Draft';
            $statusClass = [
                'Diajukan' => 'status-pending',
                'Diperiksa' => 'status-pending',
                'Diverifikasi' => 'status-pending',
                'Disetujui' => 'status-active',
                'Ditolak' => 'status-rejected',
                'Selesai' => 'status-completed',
                'Draft' => 'status-draft'
            ][$status] ?? 'status-draft';
        @endphp
        <span class="status-badge {{ $statusClass }}">{{ $status }}</span>
    </div>

    <!-- Main Content -->
    @if($jenis === 'tebang')
        @include('bapp.show', ['bapp' => $bapp, 'isPrint' => true])
    @else
        @include('bapp.showangkut', ['bapp' => $bapp, 'isPrint' => true])
    @endif



@push('scripts')
<script>
    // Auto-print when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            window.print();
        }, 500);
    });

    // Close the window after printing
    window.onafterprint = function() {
        setTimeout(function() {
            window.close();
        }, 1000);
    };
</script>
@endpush

@endsection
