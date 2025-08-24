@extends('layouts.master')

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
    
    .status-diperiksa {
        color: #0c5460;
        background-color: #d1ecf1;
        border: 1px solid #bee5eb;
    }
    
    .status-diverifikasi {
        color: #856404;
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
    }
    
    .status-disetujui {
        color: #155724;
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
    }
    
    .status-selesai {
        color: #0c5460;
        background-color: #d1ecf1;
        border: 1px solid #bee5eb;
    }
    
    .status-ditolak {
        color: #721c24;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
    }
    
    .table th {
        background-color: #1d43ae;
        color: white;
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
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Approval SPD</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar SPD Menunggu Persetujuan</h6>
        </div>
        <div class="card-body">
            @if($spds->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                    <p class="text-muted">Tidak ada SPD yang menunggu persetujuan</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered" id="approvalTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No. SPD</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Status</th>
                                <th>Diajukan Oleh</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                            <tr class="filters">
                                <th><input type="search" class="form-control form-control-sm" placeholder="Cari..."></th>
                                <th><input type="search" class="form-control form-control-sm" placeholder="Cari..."></th>
                                <th><input type="search" class="form-control form-control-sm" placeholder="Cari..."></th>
                                <th><input type="search" class="form-control form-control-sm" placeholder="Cari..."></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($spds->where('status', '!=', 'Draft') as $spd)
                            <tr>
                                <td>{{ $spd->no_spd }}</td>
                                <td>{{ $spd->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @php
                                        $statusClass = [
                                            'Diajukan' => 'status-diajukan',
                                            'Diperiksa' => 'status-diperiksa',
                                            'Diverifikasi' => 'status-diverifikasi',
                                            'Disetujui' => 'status-disetujui',
                                            'Selesai' => 'status-selesai',
                                            'Ditolak' => 'status-ditolak'
                                        ][$spd->status] ?? 'status-secondary';
                                    @endphp
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ $spd->status }}
                                    </span>
                                </td>
                                <td>{{ $spd->diajukanOleh->name ?? ($spd->diajukan_oleh ?? 'System') }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('spd.approval.show', $spd->id) }}" 
                                           class="btn btn-sm btn-info"
                                           data-toggle="tooltip" 
                                           title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($showApprovalButtons ?? false)
                                        <a href="{{ route('spd.approval.show', $spd->id) }}" 
                                           class="btn btn-sm btn-primary"
                                           data-toggle="tooltip" 
                                           title="Proses Persetujuan">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                    </div>
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
            { "orderable": false, "targets": [4] }, // Non-aktifkan sorting untuk kolom aksi
            { "searchable": false, "targets": [4] } // Non-aktifkan pencarian untuk kolom aksi
        ],
        "initComplete": function() {
            // Inisialisasi tooltip
            $('[data-toggle="tooltip"]').tooltip();
            
            // Tambahkan input pencarian untuk setiap kolom
            this.api().columns().every(function(index) {
                var column = this;
                if (index < 4) { // Hanya untuk kolom 0-3
                    $('input', $('.filters th').eq(index)).on('keyup change', function() {
                        if (column.search() !== this.value) {
                            column.search(this.value).draw();
                        }
                    });
                }
            });
        }
    });

    // Tambahkan class 'filters' ke baris header kedua
    $('#approvalTable thead tr:eq(1)').addClass('filters');
});
</script>
@endpush