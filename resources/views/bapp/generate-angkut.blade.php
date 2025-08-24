@extends('layouts.master')

@section('page-title', 'Pilih Data Hasil Tebang untuk BAPP Angkut - ' . $vendor->nama_vendor)

@push('styles')
<style>
    .selection-table {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0;
        font-size: 0.9rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .selection-table th, 
    .selection-table td {
        border: 1px solid #e2e8f0;
        padding: 0.75rem;
        text-align: left;
    }
    
    .selection-table th {
        background-color: #f8fafc;
        font-weight: 600;
        color: #4a5568;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }
    
    .selection-table tr:nth-child(even) {
        background-color: #f8fafc;
    }
    
    .selection-table tr:hover {
        background-color: #f1f5f9;
    }
    
    .btn-generate {
        background-color: #2563eb;
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 0.375rem;
        font-weight: 500;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        font-size: 0.875rem;
    }
    
    .btn-generate:hover {
        background-color: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .btn-generate:disabled {
        background-color: #bfdbfe;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    
    .btn-back {
        color: #4b5563;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-weight: 500;
        transition: all 0.2s;
        text-decoration: none;
        border: 1px solid #e5e7eb;
        background-color: white;
    }
    
    .btn-back:hover {
        background-color: #f3f4f6;
        color: #1f2937;
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
    }
    
    .status-generated {
        background-color: #dcfce7;
        color: #166534;
    }
    
    .status-not-generated {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .alert {
        padding: 1rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 0.375rem;
    }
    
    .alert-warning {
        color: #92400e;
        background-color: #fef3c7;
        border-color: #fde68a;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-semibold mb-6">Pilih Data Hasil Tebang untuk BAPP Angkut - {{ $vendor->nama_vendor }} ({{ $vendor->kode_vendor }})</h2>
        
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('bapp.angkut.confirm') }}" method="POST" id="bappAngkutForm">
            @csrf
            <input type="hidden" name="vendor_kode" value="{{ $vendor->kode_vendor }}">
                
            @if($hasilTebang->where('status_angkut', '!=', 'Generated')->count() > 0)
                <div class="overflow-x-auto">
                    <table class="selection-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>Kode Hasil Tebang</th>
                                <th>Tanggal Timbang</th>
                                <th>Petak</th>
                                <th>Divisi</th>
                                <th>Jenis Tebang</th>
                                <th class="text-right">Tonase (ton)</th>
                                <th class="text-right">Sortase (ton)</th>
                                <th class="text-right">Tonase Final (ton)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hasilTebang as $item)
                                @if($item->status_angkut !== 'Generated')
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="hasil_tebang_ids[]" value="{{ $item->kode_hasil_tebang }}" class="hasil-checkbox">
                                    </td>
                                    <td>{{ $item->kode_hasil_tebang }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal_timbang)->format('d/m/Y') }}</td>
                                    <td>{{ $item->kode_petak }}</td>
                                    <td>{{ $item->divisi ?? '-' }}</td>
                                    <td>{{ $item->jenis_tebang ?? '-' }}</td>
                                    <td class="text-right">{{ number_format($item->netto1, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->sortase, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->netto2, 2) }}</td>
                                    <td>
                                        <span class="status-badge status-not-generated">
                                            Not Generated
                                        </span>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-6 flex justify-between items-center">
                    <a href="{{ route('hasil-tebang.show', ['kode_vendor' => $vendor->kode_vendor, 'jenis' => 'angkut']) }}" class="btn-back">
                        Kembali
                    </a>
                    <button type="submit" class="btn-generate" id="generateBtn" disabled>
                        Lanjutkan Konfirmasi BAPP Angkut
                    </button>
                </div>
            @else
                <div class="alert alert-warning">
                    <p>Tidak ada hasil tebangan yang tersedia untuk dibuatkan BAPP Angkut.</p>
                </div>
                <div class="mt-4">
                    <a href="{{ route('hasil-tebang.show', ['kode_vendor' => $vendor->kode_vendor, 'jenis' => 'angkut']) }}" class="btn-back">
                        Kembali ke daftar hasil tebangan
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.hasil-checkbox');
        const generateBtn = document.getElementById('generateBtn');
        
        // Handle select all checkbox
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = selectAll.checked;
                });
                updateGenerateButton();
            });
        }
        
        // Handle individual checkbox changes
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectAllCheckbox();
                updateGenerateButton();
            });
        });
        
        // Update select all checkbox state
        function updateSelectAllCheckbox() {
            if (!selectAll) return;
            
            const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
            const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
            
            if (allChecked) {
                selectAll.checked = true;
                selectAll.indeterminate = false;
            } else if (anyChecked) {
                selectAll.checked = false;
                selectAll.indeterminate = true;
            } else {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            }
        }
        
        // Update generate button state
        function updateGenerateButton() {
            if (!generateBtn) return;
            
            const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
            generateBtn.disabled = !anyChecked;
        }
    });
</script>
@endpush
@endsection