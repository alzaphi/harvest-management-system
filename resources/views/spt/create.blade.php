@extends('layouts.master')

@php
    $header = 'Surat Perintah Tebang (SPT)';
    $breadcrumb = [
        ['title' => 'Surat Perintah Tebang (SPT)', 'url' => route('spt.index')],
        ['title' => 'Tambah Surat Perintah Tebang (SPT)']
    ];
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('css/spt.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Style untuk Select2 */
.select2-container--default .select2-selection--single {
    height: 38px;
    padding: 0 12px;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background-color: #f9fafb;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    font-size: 0.875rem;
    line-height: 38px;
    color: #111827;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

/* Center the selected/placeholder text */
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px;
    padding-left: 0;
    color: #6b7280;
}

/* When there's a selected value */
.select2-container--default .select2-selection--single .select2-selection__rendered[title] {
    color: #111827;
}

/* Arrow icon positioning */
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
    width: 30px;
    top: 1px;
}

.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--single {
    border-color: #3b82f6;
    box-shadow: 0 0 0 1px #3b82f6, 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    outline: none;
    background-color: #fff;
}

/* Hover state */
.select2-container--default .select2-selection--single:hover {
    background-color: #f3f4f6;
}

/* Disabled state */
.select2-container--default .select2-selection--single[aria-disabled=true] {
    background-color: #f3f4f6;
    opacity: 0.75;
}

/* Dropdown styles */
.select2-container--default .select2-dropdown {
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    background-color: #fff;
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    margin-bottom: 5px;
    font-size: 0.875rem;
}

/* Dropdown items */
.select2-container--default .select2-results__option {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    color: #374151;
}

.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: #f3f4f6;
    color: #111827;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #3b82f6;
    color: white;
}

/* Arrow color */
.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: #6b7280 transparent transparent transparent;
    border-style: solid;
    border-width: 5px 4px 0 4px;
    margin-left: 0;
    margin-top: -2px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
    border-color: transparent transparent #6b7280 transparent;
    border-width: 0 4px 5px 4px;
}

/* Make sure the select2 matches the height of other form elements */
.select2-container .select2-selection--single {
    height: 38px !important;
}

.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #9ca3af;
}
</style>
@endpush

@section('content')
<div class="spt-container">
    <h2>Tambah Surat Perintah Tebang (SPT) Baru</h2>

    <form action="{{ route('spt.store') }}" method="POST" enctype="multipart/form-data" class="mt-6" onsubmit="document.getElementById('jumlah_tenaga_kerja').readOnly = false;">
        @csrf

        <div class="form-group mb-6">
            <label for="kode_spt" class="block text-sm font-medium text-gray-700 mb-1">Nomor SPT</label>
            <input type="text" id="kode_spt" name="kode_spt"
                   class="form-input w-full rounded-md shadow-sm"
                   value="{{ old('kode_spt', $nextSPTNumber) }}"
                   required readonly>
            @error('kode_spt')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tanggal Tebang -->
        <div class="form-group mb-6">
            <label for="tanggal_tebang" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Tebang</label>
            <input type="text" id="tanggal_tebang" name="tanggal_tebang"
                   class="datepicker form-input w-full rounded-md shadow-sm"
                   value="{{ old('tanggal_tebang') }}" required>
            @error('tanggal_tebang')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Vendor Tebang -->
        <div class="form-group mb-6">
            <label for="kode_vendor_tebang" class="block text-sm font-medium text-gray-700 mb-1">Vendor Tebang</label>
            <select id="kode_vendor_tebang" name="kode_vendor_tebang"
                    class="form-select w-full rounded-md shadow-sm" required>
                <option value="">Pilih Tanggal Tebang terlebih dahulu</option>
            </select>
            @error('kode_vendor_tebang')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Kode Petak dan Diawasi Oleh -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="form-group">
                <label for="kode_petak" class="block text-sm font-medium text-gray-700 mb-1">Kode Petak</label>
                <select id="kode_petak" name="kode_petak"
                        class="form-select w-full rounded-md shadow-sm" required>
                    <option value="">Pilih Kode Petak</option>
                </select>
                @error('kode_petak')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Sub Block Information (Read-only) -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-500 mb-1">Estate</label>
                <div id="estate-display" class="p-2 bg-gray-100 rounded-md text-gray-700">-</div>
                <input type="hidden" id="estate" name="estate" value="">
            </div>

            <div class="form-group">
                <label class="block text-sm font-medium text-gray-500 mb-1">Divisi</label>
                <div id="divisi-display" class="p-2 bg-gray-100 rounded-md text-gray-700">-</div>
                <input type="hidden" id="divisi" name="divisi" value="">
            </div>

            <div class="form-group">
                <label class="block text-sm font-medium text-gray-500 mb-1">Luas Area (Ha)</label>
                <div id="luas_area-display" class="p-2 bg-gray-100 rounded-md text-gray-700">-</div>
                <input type="hidden" id="luas_area" name="luas_area" value="">
            </div>

            <div class="form-group">
                <label class="block text-sm font-medium text-gray-500 mb-1">Zona</label>
                <div id="zona-display" class="p-2 bg-gray-100 rounded-md text-gray-700">-</div>
                <input type="hidden" id="zona" name="zona" value="">
            </div>

            <div class="form-group">
                <label for="kode_mandor" class="block text-sm font-medium text-gray-700 mb-1">Diawasi Oleh</label>
                <select id="kode_mandor" name="kode_mandor"
                        class="form-select w-full rounded-md shadow-sm" required
                        {{ !old('kode_petak') ? 'disabled' : '' }}>
                    <option value="">Pilih Petak terlebih dahulu</option>
                    @if(old('kode_petak') && $mandors->count() > 0)
                        @foreach($mandors as $mandor)
                            <option value="{{ $mandor->kode_mandor }}" {{ old('kode_mandor') == $mandor->kode_mandor ? 'selected' : '' }}>
                                {{ $mandor->kode_mandor }} - {{ $mandor->nama_mandor }}
                            </option>
                        @endforeach
                    @endif
                </select>
                <div id="mandor-loading" class="hidden mt-1 text-sm text-blue-600">Memuat data mandor...</div>
                <div id="no-mandor" class="hidden mt-1 text-sm text-red-600">Tidak ada mandor yang ditugaskan di petak ini</div>
                @error('kode_mandor')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Jumlah Tenaga Kerja -->
        <div class="form-group mb-6">
            <label for="jumlah_tenaga_kerja" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Tenaga Kerja</label>
            <div class="relative">
                <input type="number" id="jumlah_tenaga_kerja" name="jumlah_tenaga_kerja"
                       class="form-input w-full rounded-md shadow-sm bg-gray-100"
                       value="{{ old('jumlah_tenaga_kerja') }}"
                       min="1"
                       readonly
                       required>
                <input type="hidden" name="jumlah_tenaga_kerja_value" id="jumlah_tenaga_kerja_value" value="{{ old('jumlah_tenaga_kerja') }}">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            <p class="mt-1 text-xs text-gray-500">Jumlah tenaga kerja akan terisi otomatis sesuai data vendor</p>
            @error('jumlah_tenaga_kerja')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Jenis Tebang -->
        <div class="form-group mb-6">
            <label for="jenis_tebang" class="block text-sm font-medium text-gray-700 mb-1">Jenis Tebang</label>
            <select id="jenis_tebang" name="jenis_tebang"
                    class="form-select w-full rounded-md shadow-sm" required>
                <option value="">Pilih Jenis Tebang</option>
                <option value="Manual" {{ old('jenis_tebang') == 'Manual' ? 'selected' : '' }}>Manual</option>
                <option value="Semi-Mekanis" {{ old('jenis_tebang') == 'Semi-Mekanis' ? 'selected' : '' }}>Semi-Mekanis</option>
                <option value="Mekanis" {{ old('jenis_tebang') == 'Mekanis' ? 'selected' : '' }}>Mekanis</option>
            </select>
            @error('jenis_tebang')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group mt-6">
            <label for="catatan" class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
            <textarea id="catatan" name="catatan" rows="3"
                     class="form-textarea w-full rounded-md shadow-sm">{{ old('catatan') }}</textarea>
            @error('catatan')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <div class="form-group">
                <label for="dibuat_oleh" class="block text-sm font-medium text-gray-700 mb-1">Dibuat Oleh</label>
                <input type="text" id="dibuat_oleh" name="dibuat_oleh"
                       class="form-input w-full rounded-md shadow-sm bg-gray-100"
                       value="Asst. Divisi Plantation" readonly>
                @error('dibuat_oleh')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="diperiksa_oleh" class="block text-sm font-medium text-gray-700 mb-1">Diperiksa Oleh</label>
                <input type="text" id="diperiksa_oleh" name="diperiksa_oleh"
                       class="form-input w-full rounded-md shadow-sm bg-gray-100"
                       value="Asst. Manager Plantation" readonly>
                @error('diperiksa_oleh')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="disetujui_oleh" class="block text-sm font-medium text-gray-700 mb-1">Disetujui Oleh</label>
                <input type="text" id="disetujui_oleh" name="disetujui_oleh"
                       class="form-input w-full rounded-md shadow-sm bg-gray-100"
                       value="Manager" readonly>
                @error('disetujui_oleh')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end mt-8 space-x-4">
            <a href="{{ route('spt.index') }}" class="btn btn-secondary">
                Batal
            </a>
            <button type="submit" class="btn btn-primary">
                Simpan SPT
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Initialize datepicker
    document.addEventListener('DOMContentLoaded', function() {
        // Get the minimum harvest date from the server
        const minHarvestDate = '{{ $minHarvestDate }}';

        // Initialize datepicker with minDate set to planned_harvest_date
        const datepicker = flatpickr("#tanggal_tebang", {
            dateFormat: "Y-m-d",
            locale: "id",
            allowInput: true,
            minDate: minHarvestDate,
            onChange: function(selectedDates, dateStr, instance) {
                if (dateStr) {
                    // Convert selected date to Date object for comparison
                    const selectedDate = new Date(dateStr);
                    const minDate = new Date(minHarvestDate);

                    // If selected date is before min date, reset to min date
                    if (selectedDate < minDate) {
                        instance.setDate(minDate);
                        return;
                    }

                    fetchAvailability(dateStr);
                } else {
                    // Reset the form if date is cleared
                    resetFormFields();
                }
            },
            onReady: function(selectedDates, dateStr, instance) {
                // If there's a minHarvestDate, set the initial date to it
                if (minHarvestDate) {
                    instance.setDate(minHarvestDate);
                    fetchAvailability(minHarvestDate);
                }
            }
        });

        // Add a note about the minimum date
        const dateInput = document.querySelector('#tanggal_tebang');
        if (dateInput && minHarvestDate) {
            const note = document.createElement('small');
            note.className = 'block mt-1 text-sm text-gray-500';
            const minDate = new Date(minHarvestDate);
            const formattedDate = minDate.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            note.textContent = 'Tanggal tebang minimal adalah ' + formattedDate;
            dateInput.parentNode.insertBefore(note, dateInput.nextSibling);
        }

        // Initialize form state
        resetFormFields();

        // Function to fetch availability data
        function fetchAvailability(date) {
            // Show loading state
            $('#kode_vendor_tebang').prop('disabled', true).html('<option value="">Memuat data vendor...</option>').trigger('change');
            $('#kode_petak').prop('disabled', true).html('<option value="">Memuat data petak...</option>');
            $('#kode_mandor').prop('disabled', true).html('<option value="">Pilih Petak terlebih dahulu</option>');
            $('#jumlah_tenaga_kerja').val('').prop('disabled', true);

            // Show loading state
            console.log('Fetching availability for date:', date);
            const loadingSwal = Swal.fire({
                title: 'Memuat Data',
                html: 'Sedang memeriksa ketersediaan vendor dan petak...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Build the full URL to help with debugging
            const url = `/spt/availability/${encodeURIComponent(date)}`;
            console.log('Making request to:', url);

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    loadingSwal.close();

                    if (response.success) {
                        // Update vendors dropdown
                        let vendorOptions = '<option value="">Pilih Vendor</option>';
                        if (response.vendors && response.vendors.length > 0) {
                            response.vendors.forEach(vendor => {
                                const selected = '{{ old('kode_vendor_tebang') }}' === vendor.id ? 'selected' : '';
                                vendorOptions += `<option value="${vendor.id}" ${selected}>${vendor.name} (${vendor.spt_count} SPT pada tanggal ini)</option>`;
                            });
                        } else {
                            vendorOptions = '<option value="">Tidak ada vendor tersedia</option>';
                        }
                        $('#kode_vendor_tebang').html(vendorOptions).prop('disabled', false).trigger('change');

                        // Update petak dropdown
                        let petakOptions = '<option value="">Pilih Kode Petak</option>';
                        if (response.harvest_sub_blocks && response.harvest_sub_blocks.length > 0) {
                            response.harvest_sub_blocks.forEach(block => {
                                const selected = '{{ old('kode_petak') }}' === block.kode_petak ? 'selected' : '';
                                const luasArea = block.luas_area ? parseFloat(block.luas_area).toFixed(2) : '0.00';
                                const vendorCount = block.vendor_count || 0;
                                const vendorText = vendorCount > 0 ? ` - ${vendorCount} Vendor` : '';
                                petakOptions += `<option value="${block.kode_petak}" ${selected} data-luas-area="${luasArea}">
                                    ${block.kode_petak} (${luasArea} Ha${vendorText})
                                </option>`;
                            });
                        } else {
                            petakOptions = '<option value="">Tidak ada petak tersedia</option>';
                        }
                        $('#kode_petak').html(petakOptions).prop('disabled', false);

                        // If there was a previous selection (form validation failed), try to restore it
                        @if(old('kode_petak'))
                            // Trigger change event to load mandors
                            $('#kode_petak').trigger('change');
                        @endif
                    } else {
                        Swal.fire('Error', response.message || 'Gagal memuat data ketersediaan', 'error');
                        resetFormFields();
                    }
                },
                error: function(xhr, status, error) {
                    loadingSwal.close();
                    console.error('AJAX Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });

                    let errorMessage = 'Terjadi kesalahan saat memuat data';

                    try {
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            // Try to parse as JSON if possible
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response && response.message) {
                                    errorMessage = response.message;
                                }
                            } catch (e) {
                                // If not JSON, use the raw response
                                errorMessage = xhr.responseText || errorMessage;
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing error response:', e);
                    }

                    Swal.fire({
                        title: 'Error',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });

                    resetFormFields();
                }
            });
        }

        // Function to fetch sub-block information
        function fetchSubBlockInfo(kodePetak) {
            if (!kodePetak) {
                // Reset sub-block info if no kode_petak is selected
                resetSubBlockInfo();
                return;
            }

            // Show loading state
            $('#estate-display, #divisi-display, #luas_area-display, #zona-display').html('<span class="text-gray-400">Memuat...</span>');
            $('#mandor-loading').removeClass('hidden');
            $('#no-mandor').addClass('hidden');

            console.log('Fetching sub-block info for kode_petak:', kodePetak);
            
            // Fetch sub-block information
            $.ajax({
                url: `/spt/sub-block-info/${encodeURIComponent(kodePetak)}`,
                method: 'GET',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Sub-block info response:', response);
                    $('#mandor-loading').addClass('hidden');

                    if (response.success && response.data) {
                        const subBlock = response.data;
                        console.log('Sub-block data:', subBlock);

                        // Update display fields
                        if ($('#estate-display').length) $('#estate-display').text(subBlock.estate || '-');
                        if ($('#divisi-display').length) $('#divisi-display').text(subBlock.divisi || '-');
                        if ($('#luas_area-display').length) $('#luas_area-display').text(subBlock.luas_area ? parseFloat(subBlock.luas_area).toFixed(2) + ' Ha' : '0.00 Ha');
                        if ($('#zona-display').length) $('#zona-display').text(subBlock.zona || '-');

                        // Update hidden form fields
                        if ($('#estate').length) $('#estate').val(subBlock.estate || '');
                        if ($('#divisi').length) $('#divisi').val(subBlock.divisi || '');
                        if ($('#luas_area').length) $('#luas_area').val(subBlock.luas_area || '');
                        if ($('#zona').length) $('#zona').val(subBlock.zona || '');
                    } else {
                        console.error('No data in response or request failed');
                        resetSubBlockInfo();
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching sub-block info:', status, error);
                    $('#mandor-loading').addClass('hidden');
                    resetSubBlockInfo();
                    
                    // Show error message to user
                    Swal.fire({
                        title: 'Error',
                        text: 'Gagal memuat informasi petak. Silakan coba lagi.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }

        // Function to reset sub-block information
        function resetSubBlockInfo() {
            // Reset display fields
            $('#estate-display, #divisi-display, #zona-display').text('-');
            $('#luas_area-display').text('0.00 Ha');

            // Reset hidden form fields
            $('#estate, #divisi, #luas_area, #zona').val('');
        }

        // Function to reset form fields
        function resetFormFields() {
            $('#kode_vendor_tebang').html('<option value="">Pilih Tanggal Tebang terlebih dahulu</option>').prop('disabled', true);
            $('#kode_petak').html('<option value="">Pilih Tanggal Tebang terlebih dahulu</option>').prop('disabled', true);
            $('#kode_mandor').html('<option value="">Pilih Petak terlebih dahulu</option>').prop('disabled', true);
            $('#estate-display, #divisi-display, #luas_area-display, #zona-display').text('-');
            $('#estate, #divisi, #luas_area, #zona').val('');
            $('#mandor-loading').addClass('hidden');
            $('#no-mandor').addClass('hidden');

            // Reset hidden fields
            $('input[type="hidden"][name^="kode_mandor"], input[type="hidden"][name^="nama_mandor"]').val('');

            // Reset form values
            $('#jumlah_tenaga, #jenis_tebang, #catatan').val('');

            // Reset file inputs
            $('input[type="file"]').val('');

            // Reset previews
            $('.signature-preview').html('<div class="text-gray-400">Belum ada tanda tangan</div>');

            // Reset file input labels
            $('.file-input-label').text('Pilih File');

            // Reset file input value
            $('.file-input').val('');

            // Reset sub-block info
            resetSubBlockInfo();
        }

        // Function to update worker count based on selected vendor
        function updateWorkerCount(kodeVendor) {
            const workerCountField = $('#jumlah_tenaga_kerja');

            if (!kodeVendor) {
                workerCountField.val('');
                return;
            }

            // Show loading state
            workerCountField.val('Memuat...');

            // Fetch vendor details
            const url = `/vendor/${encodeURIComponent(kodeVendor)}/details`;
            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data.jumlah_tenaga_kerja) {
                        // Update worker count field with the value from vendor
                        workerCountField.val(response.data.jumlah_tenaga_kerja);
                    } else {
                        // If no worker count found, show error
                        workerCountField.val('Data tidak ditemukan');
                        console.error('Gagal memuat jumlah tenaga kerja:', response.message || 'Data tidak tersedia');
                    }
                },
                error: function(xhr) {
                    workerCountField.val('Error memuat data');
                    console.error('Error fetching worker count:', xhr.responseText);
                }
            });
        }

        // Handle kode_vendor_tebang change event
        $(document).on('change', '#kode_vendor_tebang', function() {
            const kodeVendor = $(this).val();
            updateWorkerCount(kodeVendor);

            // Also update the hidden field
            $.ajax({
                url: `/vendor/${encodeURIComponent(kodeVendor)}/details`,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data.jumlah_tenaga_kerja) {
                        // Update both visible and hidden fields
                        $('#jumlah_tenaga_kerja').val(response.data.jumlah_tenaga_kerja);
                        $('#jumlah_tenaga_kerja_value').val(response.data.jumlah_tenaga_kerja);
                    }
                }
            });
        });

        // Handle kode_petak change event
        $(document).on('change', '#kode_petak', function() {
            const kodePetak = $(this).val();
            const mandorSelect = $('#kode_mandor');

            // Reset mandor dropdown
            mandorSelect.html('<option value="">Memuat data mandor...</option>').prop('disabled', true);

            // Reset sub-block info
            resetSubBlockInfo();

            // Show loading state for mandor
            $('#mandor-loading').removeClass('hidden');
            $('#no-mandor').addClass('hidden');

            if (!kodePetak) {
                mandorSelect.html('<option value="">Pilih Petak terlebih dahulu</option>').prop('disabled', true);
                return;
            }

            // Fetch sub-block information first
            fetchSubBlockInfo(kodePetak);

            // Fetch mandors for the selected petak
            $.ajax({
                url: `/spt/mandors/date/${encodeURIComponent(kodePetak)}`,
                method: 'GET',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#mandor-loading').addClass('hidden');

                    if (response.success && response.mandors && response.mandors.length > 0) {
                        let options = '<option value="">Pilih Mandor</option>';
                        response.mandors.forEach(mandor => {
                            options += `<option value="${mandor.kode_mandor}">${mandor.kode_mandor} - ${mandor.nama_mandor}</option>`;
                        });

                        // Update the select with available mandors
                        mandorSelect.html(options).prop('disabled', false);
                    } else {
                        mandorSelect.html('<option value="">Tidak ada mandor tersedia</option>');
                        $('#no-mandor').removeClass('hidden');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading mandors:', xhr);
                    mandorSelect.html('<option value="">Error memuat data mandor</option>');
                    $('#mandor-loading').addClass('hidden');
                }
            });
        });

        // DOM elements
        const mandorSelect = document.getElementById('kode_mandor');
        const mandorLoading = document.getElementById('mandor-loading');
        const noMandor = document.getElementById('no-mandor');

        // Set initial state for the form
        resetFormFields();

        // If there's a date already selected (form validation failed), fetch availability
        @if(old('tanggal_tebang'))
            $('#tanggal_tebang').val('{{ old('tanggal_tebang') }}');
            fetchAvailability('{{ old('tanggal_tebang') }}');
        @endif

        if (petakSelect) {
            petakSelect.addEventListener('change', function() {
                const kodePetak = this.value;

                // Reset mandor select
                mandorSelect.innerHTML = '<option value="">Memuat data mandor...</option>';
                mandorSelect.disabled = true;
                mandorLoading.classList.remove('hidden');
                noMandor.classList.add('hidden');

                if (!kodePetak) {
                    mandorSelect.innerHTML = '<option value="">Pilih Petak terlebih dahulu</option>';
                    mandorSelect.disabled = true;
                    mandorLoading.classList.add('hidden');
                    document.getElementById('subBlockDetails').classList.add('hidden');
                    return;
                }

                // Fetch mandors for selected petak
                fetch(`/spt/mandors/${kodePetak}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Mandors data:', data); // Debug log

                        // Update mandor select
                        mandorSelect.innerHTML = '<option value="">Pilih Mandor</option>';

                        if (data.success && data.data && data.data.length > 0) {
                            data.data.forEach(mandor => {
                                if (mandor.kode_mandor && mandor.nama_mandor) {
                                    const option = document.createElement('option');
                                    option.value = mandor.kode_mandor;
                                    option.textContent = `${mandor.kode_mandor} - ${mandor.nama_mandor}`;
                                    mandorSelect.appendChild(option);
                                }
                            });
                            noMandor.classList.add('hidden');
                        } else {
                            console.warn('No mandors found or invalid data format:', data);
                            mandorSelect.innerHTML = '<option value="">Tidak ada mandor</option>';
                            noMandor.classList.remove('hidden');
                        }

                        mandorSelect.disabled = false;
                        mandorLoading.classList.add('hidden');
                    })
                    .catch(error => {
                        console.error('Error fetching mandors:', error);
                        mandorSelect.innerHTML = '<option value="">Gagal memuat data mandor</option>';
                        mandorSelect.disabled = false;
                        mandorLoading.classList.add('hidden');
                        noMandor.classList.add('hidden');
                    });
            });

            // Trigger change event if petak is already selected (form validation failed)
            if (petakSelect.value) {
                petakSelect.dispatchEvent(new Event('change'));
            }
        }
    });

    // Close alert message
    function setupAlertCloseButtons() {
        document.querySelectorAll('.close-alert').forEach(button => {
            button.removeEventListener('click', handleAlertClose);
            button.addEventListener('click', handleAlertClose);
        });
    }

    function handleAlertClose(e) {
        e.preventDefault();
        const alert = this.closest('.alert-message');
        if (alert) {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        setupAlertCloseButtons();
    });
</script>

<script>
    // Initialize Select2 for vendor tebang field to match form styling
    $(document).ready(function() {
        // Initialize vendor tebang
        $('#kode_vendor_tebang').select2({
            placeholder: 'Cari vendor',
            allowClear: true,
            width: '100%',
            dropdownParent: $('.spt-container')
        });

        // Initialize kode_petak with Select2
        $('#kode_petak').select2({
            placeholder: 'Cari kode petak',
            allowClear: true,
            width: '100%',
            dropdownParent: $('.spt-container')
        });
    });
</script>
@endpush
