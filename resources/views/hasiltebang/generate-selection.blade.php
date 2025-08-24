@extends('layouts.master')

@section('page-title', 'Generate BAPP Tebang - ' . $vendor->nama_vendor)

@push('styles')
<style>
    .page-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }
    
    .page-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .page-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }
    
    .vendor-info {
        color: #4a5568;
        font-size: 1rem;
    }
    
    .card {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }
    
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }
    
    .data-table thead {
        background-color: #f7fafc;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .data-table th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 600;
        color: #4a5568;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }
    
    .data-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #edf2f7;
        color: #4a5568;
    }
    
    .data-table tbody tr:hover {
        background-color: #f8fafc;
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    
    .status-generated {
        background-color: #dcfce7;
        color: #166534;
    }
    
    .status-not-generated {
        background-color: #fee2e2;
        color: #991b1b;
    }
    
    .action-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        padding: 1.5rem 1rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1.25rem;
        border-radius: 0.375rem;
        font-weight: 500;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
    }
    
    .btn-primary {
        background-color: #3182ce;
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #2c5282;
    }
    
    .btn-primary:disabled {
        background-color: #cbd5e0;
        cursor: not-allowed;
    }
    
    .btn-outline {
        background-color: white;
        border-color: #cbd5e0;
        color: #4a5568;
    }
    
    .btn-outline:hover {
        background-color: #f7fafc;
    }
    
    .no-data {
        padding: 2rem;
        text-align: center;
        color: #718096;
    }
    
    /* Checkbox styling */
    .checkbox-container {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .checkbox {
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid #cbd5e0;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .checkbox:checked {
        background-color: #3182ce;
        border-color: #3182ce;
    }
    
    .checkbox:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .page-container {
            padding: 1rem 0.5rem;
        }
        
        .data-table th, 
        .data-table td {
            padding: 0.5rem 0.75rem;
        }
    }
</style>
@endpush

@section('content')
<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">Generate BAPP Tebang - {{ $vendor->nama_vendor }} ({{ $vendor->kode_vendor }})</h1>
    </div>

    <div class="card">
        <form id="generateBappForm" action="{{ route('bapp.confirm') }}" method="POST">
            @csrf
            <input type="hidden" name="vendor_kode" value="{{ $vendor->kode_vendor }}">
            <input type="hidden" name="jenis_bapp" value="tebang">

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="40" class="text-center">
                                <input type="checkbox" id="selectAll" class="checkbox">
                            </th>
                            <th>No</th>
                            <th>Kode Hasil Tebang</th>
                            <th>Tanggal Timbang</th>
                            <th>Petak</th>
                            <th>Divisi</th>
                            <th>Total (Ton)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hasilTebangs as $index => $hasil)
                            @php
                                $disabled = $hasil->status === 'generated';
                                $statusClass = $hasil->status === 'generated' ? 'status-generated' : 'status-not-generated';
                            @endphp
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox"
                                        name="hasil_tebang_ids[]"
                                        value="{{ $hasil->id }}"
                                        class="checkbox {{ $disabled ? 'disabled-checkbox' : '' }}"
                                        {{ $disabled ? 'disabled' : '' }}>
                                </td>
                                <td>{{ $index + 1 }}</td>
                                <td class="font-medium">{{ $hasil->kode_hasil_tebang }}</td>
                                <td>{{ \Carbon\Carbon::parse($hasil->tanggal_timbang)->format('d/m/Y') }}</td>
                                <td>{{ $hasil->kode_petak }}</td>
                                <td>{{ $hasil->divisi ?? '-' }}</td>
                                <td class="font-medium">{{ number_format($hasil->netto2, 2) }}</td>
                                <td>
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $hasil->status ?? 'not_generated')) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="no-data">Tidak ada data hasil tebangan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="action-buttons">
                <button type="button" class="btn btn-outline" onclick="window.history.back()">
                    <i class="fas fa-arrow-left mr-2"></i> Batal
                </button>
                <button type="submit" id="generateBtn" class="btn btn-primary" disabled>
                    <i class="fas fa-file-export mr-2"></i> Generate BAPP
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('generateBappForm');
        const selectAll = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.checkbox:not(:disabled)');
        const generateBtn = document.getElementById('generateBtn');

        // Prevent default form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate if at least one checkbox is checked
            const checkboxes = document.querySelectorAll('input[name="hasil_tebang_ids[]"]:checked');
            if (checkboxes.length === 0) {
                alert('Pilih setidaknya satu data hasil tebangan');
                return false;
            }
            
            // Submit the form programmatically
            this.submit();
        });

        function updateGenerateButtonState() {
            const anyChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
            generateBtn.disabled = !anyChecked;
        }

        // Select all functionality
        selectAll.addEventListener('change', function () {
            const isChecked = this.checked;
            rowCheckboxes.forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = isChecked;
                }
            });
            updateGenerateButtonState();
        });

        // Individual checkbox change
        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateGenerateButtonState();
                
                // Uncheck "select all" if any checkbox is unchecked
                if (!this.checked && selectAll.checked) {
                    selectAll.checked = false;
                }
                
                // Check "select all" if all checkboxes are checked
                const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked || cb.disabled);
                selectAll.checked = allChecked;
            });
        });
        
        // Initial button state
        updateGenerateButtonState();
    });
</script>
@endpush
