@extends('layouts.master')

@php
$header = 'Tambah Harvest Sub Block';
$breadcrumb = [
    ['title' => 'Harvest Sub Block', 'url' => route('harvest-sub-blocks.index')],
    ['title' => $header]
];
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .form-container {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        padding: 2rem;
        margin: 0 auto;
        max-width: 1200px;
    }

    .form-container h1 {
        color: #2d3748;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .form-label {
        font-weight: 500;
        color: #4a5568;
        margin-bottom: 0.5rem;
        display: block;
        font-size: 0.875rem;
    }

    .form-control, .form-select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        transition: all 0.2s;
        font-size: 0.875rem;
        height: calc(1.5em + 0.75rem + 2px);
    }

    .form-control:focus, .form-select:focus {
        border-color: #4299e1;
        box-shadow: 0 0 0 0.2rem rgba(66, 153, 225, 0.25);
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .btn {
        font-weight: 500;
        padding: 0.5rem 1.25rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .btn-primary {
        background-color: #4299e1;
        border-color: #4299e1;
    }

    .btn-primary:hover {
        background-color: #3182ce;
        border-color: #2c5282;
    }

    .btn-outline-secondary {
        color: #4a5568;
        border-color: #cbd5e0;
    }

    .btn-outline-secondary:hover {
        background-color: #f7fafc;
        border-color: #a0aec0;
    }

    .invalid-feedback {
        font-size: 0.75rem;
        margin-top: 0.25rem;
        color: #e53e3e;
    }

    .is-invalid {
        border-color: #fc8181;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: flex-end;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
    }

    .text-danger {
        color: #e53e3e;
    }

    textarea.form-control {
        min-height: 100px;
    }

    .select2-container--default .select2-selection--single {
        height: 38px;
        padding: 6px 12px;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        background-color: #fff;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #4299e1;
        box-shadow: 0 0 0 0.2rem rgba(66, 153, 225, 0.25);
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 24px;
        padding-left: 0;
        color: #374151;
    }

    .select2-container--default .select2-dropdown {
        border: 1px solid #4299e1;
        border-radius: 0.375rem;
        box-shadow: 0 4px 6px -1px rgba(66, 153, 225, 0.2), 0 2px 4px -1px rgba(66, 153, 225, 0.1);
    }

    .select2-search--dropdown .select2-search__field {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
        margin-bottom: 5px;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #f3f4f6;
        color: #1f2937;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #4299e1;
        color: white;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #6b7280 transparent transparent transparent;
    }

    .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
        border-color: transparent transparent #6b7280 transparent;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="form-container">
        <h1>Tambah Data Panen</h1>
        <form action="{{ route('harvest-sub-blocks.store') }}" method="POST" class="needs-validation" novalidate>
                    @csrf

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="kode_petak" class="form-label">Kode Petak <span class="text-danger">*</span></label>
                                <select name="kode_petak" id="kode_petak" class="form-select @error('kode_petak') is-invalid @enderror" required>
                                    <option value="">-- Pilih Sub-block --</option>
                                    @foreach($subBlocks as $subBlock)
                                        <option value="{{ $subBlock->kode_petak }}"
                                                data-estate="{{ $subBlock->estate }}"
                                                data-divisi="{{ $subBlock->divisi }}"
                                                data-age-months="{{ $subBlock->age_months }}"
                                                data-zona="{{ $subBlock->zona }}"
                                                data-luas-area="{{ $subBlock->luas_area }}"
                                                {{ old('kode_petak') == $subBlock->kode_petak ? 'selected' : '' }}>
                                            {{ $subBlock->kode_petak }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kode_petak')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Estate</label>
                                <input type="text" id="estate_display" class="form-control" readonly>
                                <input type="hidden" name="estate" id="estate">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Divisi</label>
                                <input type="text" id="divisi_display" class="form-control" readonly>
                                <input type="hidden" name="divisi" id="divisi">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="age_months" class="form-label">Umur (Bulan) <span class="text-danger">*</span></label>
                                <input type="number" name="age_months" id="age_months" class="form-control @error('age_months') is-invalid @enderror" value="{{ old('age_months') }}" min="1" required>
                                @error('age_months')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="yield_estimate_tph" class="form-label">Estimasi (ton/ha) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="yield_estimate_tph" id="yield_estimate_tph" class="form-control @error('yield_estimate_tph') is-invalid @enderror" value="{{ old('yield_estimate_tph') }}" min="0" required>
                                @error('yield_estimate_tph')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="planned_harvest_date" class="form-label">Rencana Panen <span class="text-danger">*</span></label>
                                <input type="date" name="planned_harvest_date" id="planned_harvest_date" class="form-control @error('planned_harvest_date') is-invalid @enderror" value="{{ old('planned_harvest_date') }}" required>
                                @error('planned_harvest_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="harvest_season" class="form-label">Musim Panen <span class="text-danger">*</span></label>
                                <input type="text" name="harvest_season" id="harvest_season" class="form-control @error('harvest_season') is-invalid @enderror" value="{{ old('harvest_season') }}" required>
                                @error('harvest_season')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="luas_area" class="form-label">Luas Area (ha) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="luas_area" id="luas_area" class="form-control @error('luas_area') is-invalid @enderror" value="{{ old('luas_area') }}" min="0" required>
                                @error('luas_area')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="priority_level" class="form-label">Prioritas <span class="text-danger">*</span></label>
                                <select name="priority_level" id="priority_level" class="form-select @error('priority_level') is-invalid @enderror" required>
                                    <option value="">-- Pilih Prioritas --</option>
                                    <option value="1" {{ old('priority_level') == 1 ? 'selected' : '' }}>1 - Prioritas Tertinggi</option>
                                    <option value="2" {{ old('priority_level') == 2 ? 'selected' : '' }}>2 - Prioritas Menengah</option>
                                    <option value="3" {{ old('priority_level') == 3 ? 'selected' : '' }}>3 - Prioritas Standar</option>
                                </select>
                                @error('priority_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="remarks" class="form-label">Keterangan</label>
                                <input type="text" name="remarks" id="remarks" class="form-control @error('remarks') is-invalid @enderror" value="{{ old('remarks') }}">
                                @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="action-buttons">
                            <a href="{{ route('harvest-sub-blocks.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                            @can('create-harvest-sub-block')
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Data
                            </button>
                            @endcan
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2 for kode_petak
        $('#kode_petak').select2({
            placeholder: 'Cari kode petak',
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 1,
            matcher: function(params, data) {
                if ($.trim(params.term) === '') {
                    return data;
                }
                if (typeof data.text === 'undefined') {
                    return null;
                }
                var searchTerm = params.term.toLowerCase();
                var text = data.text.toLowerCase();
                if (text.indexOf(searchTerm) > -1) {
                    return data;
                }
                return null;
            }
        });

        // Handle estate and division display when sub-block is selected
        function updateEstateAndDivision() {
            const selectedOption = $('#kode_petak option:selected');
            if (selectedOption.length && selectedOption.data('estate')) {
                // Update estate and division
                const estate = selectedOption.data('estate') || '';
                const divisi = selectedOption.data('divisi') || '';
                const ageMonths = selectedOption.data('age-months') || '';
                const zona = selectedOption.data('zona') || '';
                const luasArea = selectedOption.data('luas-area') || '';
                
                // Update display fields
                $('#estate_display').val(estate);
                $('#estate').val(estate);
                $('#divisi_display').val(divisi);
                $('#divisi').val(divisi);
                
                // Update age months
                if (ageMonths) {
                    $('#age_months').val(ageMonths);
                }

                // Update luas area
                if (luasArea) {
                    // Format luas area to 2 decimal places
                    const formattedLuasArea = parseFloat(luasArea).toFixed(2);
                    $('#luas_area').val(formattedLuasArea);
                }

                // If age is 10, set yield estimate to 50
                if (parseInt(ageMonths) === 10) {
                    $('#yield_estimate_tph').val('50');
                }

                // Set priority based on zona (1-3)
                if (zona && zona >= 1 && zona <= 3) {
                    $('#priority_level').val(zona).trigger('change');
                }
            } else {
                // Clear fields if no option is selected
                $('#estate_display').val('');
                $('#estate').val('');
                $('#divisi_display').val('');
                $('#divisi').val('');
                $('#age_months').val('');
                $('#luas_area').val('');
            }
        }

        // Initial update
        updateEstateAndDivision();

        // Update when selection changes using Select2's change event
        $('#kode_petak').on('change', updateEstateAndDivision);

        // Auto-fill harvest season when planned harvest date changes
        document.getElementById('planned_harvest_date').addEventListener('change', function() {
            const harvestDate = new Date(this.value);
            if (!isNaN(harvestDate.getTime())) {
                document.getElementById('harvest_season').value = harvestDate.getFullYear();
            }
        });

        // Form validation
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    });
</script>
@endpush
@endsection
