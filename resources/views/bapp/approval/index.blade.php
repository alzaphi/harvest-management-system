@extends('layouts.master')

@endphp
@push('styles')
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .status-badge {
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 600;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .status-diajukan {
        color: #8a6d3b;
        background-color: #fcf8e3;
        border: 1px solid #faebcc;
    }
    
    .status-disetujui {
        color: #3c763d;
        background-color: #dff0d8;
        border: 1px solid #d6e9c6;
    }
    
    .status-ditolak {
        color: #a94442;
        background-color: #f2dede;
        border: 1px solid #ebccd1;
    }
    
    .table th {
        background-color: #1d43aeff;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    /* Sembunyikan search box di atas */
    .dataTables_filter {
        display: none;
    }
    
    .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .dataTables_filter input {
        margin-left: 0.5rem !important;
        border: 1px solid #d1d3e2;
        border-radius: 0.35rem;
        padding: 0.375rem 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Approval BAPP</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar BAPP Menunggu Persetujuan</h6>
        </div>
        <div class="card-body">
            @if($bapps->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                    <p class="text-muted">Tidak ada BAPP yang menunggu persetujuan</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered" id="approvalTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No. BAPP</th>
                                <th>Tanggal BAPP</th>
                                <th>Vendor</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bapps as $bapp)
                            <tr>
                                <td>{{ $bapp->kode_bapp }}</td>
                                <td>{{ \Carbon\Carbon::parse($bapp->tanggal_bapp)->format('d/m/Y') }}</td>
                                <td>{{ $bapp->vendor ? $bapp->vendor->nama_vendor : 'N/A' }}</td>
                                <td>
                                    @php
                                        $statusClass = '';
                                        switch(strtolower($bapp->status)) {
                                            case 'disetujui':
                                                $statusClass = 'status-disetujui';
                                                break;
                                            case 'ditolak':
                                                $statusClass = 'status-ditolak';
                                                break;
                                            case 'pending_approval':
                                            case 'pending_vendor':
                                                $statusClass = 'status-pending';
                                                break;
                                            default:
                                                $statusClass = 'status-diajukan';
                                        }
                                    @endphp
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $bapp->status)) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('bapp.approval.show', $bapp->kode_bapp) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi DataTable
        var table = $('#approvalTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
            "language": {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "zeroRecords": "Tidak ada data yang ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada data tersedia",
                "infoFiltered": "(difilter dari _MAX_ total data)",
                "paginate": {
                    "previous": "<i class='fas fa-chevron-left'></i>",
                    "next": "<i class='fas fa-chevron-right'></i>"
                }
            },
            "columnDefs": [
                { "orderable": false, "targets": [4] } // Non-aktifkan sorting untuk kolom aksi
            ]
        });

        // Inisialisasi tooltip
        $('[data-toggle="tooltip"]').tooltip();

        // Tambahkan input pencarian untuk setiap kolom
        $('#approvalTable thead tr').clone(true).appendTo('#approvalTable thead');
        $('#approvalTable thead tr:eq(1) th').each(function (i) {
            var title = $(this).text();
            if (i < 4) { // Hanya tambahkan input search untuk kolom 0-3
                $(this).html('<input type="text" class="form-control form-control-sm" placeholder="Cari ' + title + '" />');
                $('input', this).on('keyup change', function () {
                    if (table.column(i).search() !== this.value) {
                        table.column(i).search(this.value).draw();
                    }
                });
            } else {
                $(this).html('');
            }
        });
    });
</script>
@endpush