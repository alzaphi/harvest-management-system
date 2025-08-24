@extends('layouts.master')

@section('page-title', 'Surat Perintah Tebang (SPT)')

@php
    $header = 'Surat Perintah Tebang (SPT)';
    $breadcrumb = [
        ['title' => 'Dashboard', 'url' => route('dashboard')],
        ['title' => 'Surat Perintah Tebang (SPT)', 'url' => route('spt.index')],
        ['title' => 'Edit ' . $spt->kode_spt]
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

.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--single {
    border-color: #3b82f6;
    box-shadow: 0 0 0 1px #3b82f6, 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    outline: none;
    background-color: #fff;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px;
    padding-left: 0;
    color: #6b7280;
}

.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #9ca3af;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
    width: 30px;
    top: 1px;
}

/* Rest of your existing styles */
</style>
@endpush

@section('content')
<div class="spt-container">
    <h2>Edit Surat Perintah Tebang (SPT)</h2>

    <form action="{{ route('spt.update', $spt->id) }}" method="POST" enctype="multipart/form-data" class="mt-6">
        @csrf
        @method('PUT')

        <div class="form-group mb-6">
            <label for="kode_spt" class="block text-sm font-medium text-gray-700 mb-1">Nomor SPT</label>
            <input type="text" id="kode_spt" name="kode_spt"
                   class="form-input w-full rounded-md shadow-sm bg-gray-100"
                   value="{{ old('kode_spt', $spt->kode_spt) }}" readonly>
            @error('kode_spt')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tanggal Tebang -->
        <div class="form-group mb-6">
            <label for="tanggal_tebang" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Tebang</label>
            <input type="text" id="tanggal_tebang" name="tanggal_tebang"
                   class="datepicker form-input w-full rounded-md shadow-sm"
                   value="{{ old('tanggal_tebang', \Carbon\Carbon::parse($spt->tanggal_tebang)->format('Y-m-d')) }}" required>
            @error('tanggal_tebang')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Vendor Tebang -->
        <div class="form-group mb-6">
            <label for="kode_vendor" class="block text-sm font-medium text-gray-700 mb-1">Vendor Tebang</label>
            <select id="kode_vendor" name="kode_vendor"
                    class="form-select w-full rounded-md shadow-sm" required>
                <option value="">Pilih Vendor</option>
                @php
                    $selectedVendor = old('kode_vendor', $spt->kode_vendor);
                @endphp
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->kode_vendor }}"
                        {{ $selectedVendor == $vendor->kode_vendor ? 'selected' : '' }}>
                        {{ $vendor->kode_vendor }} - {{ $vendor->nama_vendor }}
                    </option>
                @endforeach
            </select>
            @error('kode_vendor')
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
                    @foreach($harvestSubBlocks as $block)
                        <option value="{{ $block->kode_petak }}" 
                                data-luas-area="{{ $block->luas_area }}"
                                data-vendor-count="{{ $block->vendor_count }}"
                                {{ old('kode_petak', $spt->kode_petak) == $block->kode_petak ? 'selected' : '' }}>
                            {{ $block->kode_petak }} ({{ $block->blok }}) - 
                            Luas: {{ number_format($block->luas_area, 2) }} Ha - 
                            Vendor: {{ $block->vendor_count }}
                        </option>
                    @endforeach
                </select>
                @error('kode_petak')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Sub Block Information (Read-only) -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-500 mb-1">Estate</label>
                <div id="estate-display" class="p-2 bg-gray-100 rounded-md text-gray-700">{{ $spt->estate ?? '-' }}</div>
                <input type="hidden" id="estate" name="estate" value="{{ old('estate', $spt->estate) }}">
            </div>

            <div class="form-group">
                <label class="block text-sm font-medium text-gray-500 mb-1">Divisi</label>
                <div id="divisi-display" class="p-2 bg-gray-100 rounded-md text-gray-700">{{ $spt->divisi ?? '-' }}</div>
                <input type="hidden" id="divisi" name="divisi" value="{{ old('divisi', $spt->divisi) }}">
            </div>

            <div class="form-group">
                <label class="block text-sm font-medium text-gray-500 mb-1">Luas Area (Ha)</label>
                <div id="luas_area-display" class="p-2 bg-gray-100 rounded-md text-gray-700">{{ $spt->luas_area ?? '-' }}</div>
                <input type="hidden" id="luas_area" name="luas_area" value="{{ old('luas_area', $spt->luas_area) }}">
            </div>

            <div class="form-group">
                <label class="block text-sm font-medium text-gray-500 mb-1">Zona</label>
                <div id="zona-display" class="p-2 bg-gray-100 rounded-md text-gray-700">{{ $spt->zona ?? '-' }}</div>
                <input type="hidden" id="zona" name="zona" value="{{ old('zona', $spt->zona) }}">
            </div>

            <div class="form-group">
                <label for="kode_mandor" class="block text-sm font-medium text-gray-700 mb-1">Diawasi Oleh</label>
                <select id="kode_mandor" name="kode_mandor"
                        class="form-select w-full rounded-md shadow-sm" required
                        {{ !$spt->kode_petak ? 'disabled' : '' }}>
                    <option value="">Pilih Petak terlebih dahulu</option>
                    @if($spt->kode_petak && $mandors->count() > 0)
                        @foreach($mandors as $mandor)
                            <option value="{{ $mandor->kode_mandor }}" {{ old('kode_mandor', $spt->kode_mandor) == $mandor->kode_mandor ? 'selected' : '' }}>
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
                       value="{{ old('jumlah_tenaga_kerja', $spt->jumlah_tenaga_kerja) }}"
                       min="1"
                       readonly
                       required>
                <input type="hidden" name="jumlah_tenaga_kerja_value" id="jumlah_tenaga_kerja_value" value="{{ old('jumlah_tenaga_kerja', $spt->jumlah_tenaga_kerja) }}">
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
                <option value="Manual" {{ old('jenis_tebang', $spt->jenis_tebang) == 'Manual' ? 'selected' : '' }}>Manual</option>
                <option value="Semi-Mekanis" {{ old('jenis_tebang', $spt->jenis_tebang) == 'Semi-Mekanis' ? 'selected' : '' }}>Semi-Mekanis</option>
                <option value="Mekanis" {{ old('jenis_tebang', $spt->jenis_tebang) == 'Mekanis' ? 'selected' : '' }}>Mekanis</option>
            </select>
            @error('jenis_tebang')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group mt-6">
            <label for="catatan" class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
            <textarea id="catatan" name="catatan" rows="3"
                     class="form-textarea w-full rounded-md shadow-sm">{{ old('catatan', $spt->catatan) }}</textarea>
            @error('catatan')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <div class="form-group">
                <label for="dibuat_oleh" class="block text-sm font-medium text-gray-700 mb-1">Dibuat Oleh</label>
                <input type="text" id="dibuat_oleh" name="dibuat_oleh"
                       class="form-input w-full rounded-md shadow-sm"
                       value="{{ old('dibuat_oleh', $spt->dibuat_oleh) }}" required>
                @error('dibuat_oleh')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="diperiksa_oleh" class="block text-sm font-medium text-gray-700 mb-1">Diperiksa Oleh</label>
                <input type="text" id="diperiksa_oleh" name="diperiksa_oleh"
                       class="form-input w-full rounded-md shadow-sm"
                       value="{{ old('diperiksa_oleh', $spt->diperiksa_oleh) }}" required>
                @error('diperiksa_oleh')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="disetujui_oleh" class="block text-sm font-medium text-gray-700 mb-1">Disetujui Oleh</label>
                <input type="text" id="disetujui_oleh" name="disetujui_oleh"
                       class="form-input w-full rounded-md shadow-sm"
                       value="{{ old('disetujui_oleh', $spt->disetujui_oleh) }}" required>
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
                Update SPT
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        console.log('Initializing Select2 for dropdowns...');
        
        // Initialize Select2 for vendor dropdown
        const initSelect2 = function() {
            console.log('Initializing Select2...');
            
            // Destroy any existing Select2 instances
            if ($('#kode_vendor').hasClass('select2-hidden-accessible')) {
                $('#kode_vendor').select2('destroy');
            }
            
            // Initialize Select2 for vendor
            $('#kode_vendor').select2({
                placeholder: 'Cari vendor...',
                allowClear: true,
                width: '100%',
                dropdownParent: $('.spt-container'),
                minimumResultsForSearch: 0 // Always show search box
            });
            
            // Initialize Select2 for kode_petak
            $('#kode_petak').select2({
                placeholder: 'Cari kode petak...',
                allowClear: true,
                width: '100%',
                dropdownParent: $('.spt-container'),
                minimumResultsForSearch: 0 // Always show search box
            });
            
            // Force the search box to be visible
            $('#kode_vendor, #kode_petak').on('select2:open', function() {
                const placeholder = $(this).attr('id') === 'kode_vendor' ? 'Cari vendor...' : 'Cari kode petak...';
                $('.select2-search__field').attr('placeholder', placeholder);
            });
        };
        
        // Call the initialization function
        initSelect2();
        
        // Reinitialize Select2 if the options change
        $(document).on('change', '#tanggal_tebang', function() {
            setTimeout(initSelect2, 100); // Small delay to ensure options are loaded
        });
        
        // Function to fetch sub-block information
        function fetchSubBlockInfo(kodePetak) {
            if (!kodePetak) {
                resetSubBlockInfo();
                return;
            }

            fetch(`/spt/sub-block-info/${kodePetak}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update display fields
                        $('#estate-display').text(data.estate || '-');
                        $('#divisi-display').text(data.divisi || '-');
                        $('#luas_area-display').text(data.luas_area ? `${data.luas_area} Ha` : '-');
                        $('#zona-display').text(data.zona || '-');

                        // Update the hidden input fields
                        const estate = data.estate || '{{ $spt->estate ?? "" }}';
                        const divisi = data.divisi || '{{ $spt->divisi ?? "" }}';
                        const luasArea = data.luas_area || '{{ $spt->luas_area ?? "" }}';
                        const zona = data.zona || '{{ $spt->zona ?? "" }}';

                        $('#estate').val(estate);
                        $('#estate-display').text(estate || '-');
                        $('#divisi').val(divisi);
                        $('#divisi-display').text(divisi || '-');
                        $('#luas_area').val(luasArea);
                        $('#luas_area-display').text(luasArea || '-');
                        $('#zona').val(zona);
                        $('#zona-display').text(zona || '-');

                        // Ensure mandor is selected if it exists
                        setTimeout(() => {
                            const mandorSelect = $('#kode_mandor');
                            if (mandorSelect.length && '{{ $spt->kode_mandor }}') {
                                mandorSelect.val('{{ $spt->kode_mandor }}').trigger('change');
                            }
                        }, 500);

                        // Load mandors for this block
                        loadMandors(kodePetak);
                    } else {
                        resetSubBlockInfo();
                    }
                })
                .catch(error => {
                    console.error('Error fetching sub-block info:', error);
                    resetSubBlockInfo();
                });
        }

        function resetSubBlockInfo() {
            // Reset display fields
            $('#estate-display').text('-');
            $('#divisi-display').text('-');
            $('#luas_area-display').text('-');
            $('#zona-display').text('-');

            // Reset hidden inputs
            $('#estate').val('');
            $('#divisi').val('');
            $('#luas_area').val('');
            $('#zona').val('');
        }

        // Function to load mandors for a specific block
        function loadMandors(kodePetak) {
            const mandorSelect = $('#kode_mandor');
            mandorSelect.prop('disabled', true).html('<option value="">Memuat data mandor...</option>');
            $('#mandor-loading').removeClass('hidden');
            $('#no-mandor').addClass('hidden');

            if (!kodePetak) {
                mandorSelect.html('<option value="">Pilih Petak terlebih dahulu</option>').prop('disabled', true);
                $('#mandor-loading').addClass('hidden');
                return;
            }

            // AJAX call
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
                        response.mandors.forEach(function(mandor) {
                            const selected = (mandor.kode_mandor == '{{ old('kode_mandor', $spt->kode_mandor) }}') ? 'selected' : '';
                            options += `<option value="${mandor.kode_mandor}" ${selected}>${mandor.kode_mandor} - ${mandor.nama_mandor}</option>`;
                        });
                        mandorSelect.html(options).prop('disabled', false);
                        $('#no-mandor').addClass('hidden');
                        // Pilih mandor sesuai data lama (jika ada)
                        mandorSelect.val('{{ old('kode_mandor', $spt->kode_mandor) }}').trigger('change');
                        // Jika hanya ada satu mandor, tetap pilih otomatis
                        if (response.mandors.length === 1) {
                            mandorSelect.val(response.mandors[0].kode_mandor).trigger('change');
                        }
                    } else {
                        mandorSelect.html('<option value="">Tidak ada mandor yang tersedia</option>').prop('disabled', true);
                        $('#no-mandor').removeClass('hidden');
                    }
                },
                error: function(xhr, status, error) {
                    $('#mandor-loading').addClass('hidden');
                    mandorSelect.html('<option value="">Gagal memuat data mandor</option>').prop('disabled', true);
                    $('#no-mandor').removeClass('hidden');
                }
            });
        }

        // Handle vendor change to update jumlah_tenaga_kerja
        $(document).on('change', '#kode_vendor', function() {
            const vendorId = $(this).val();

            // In a real app, you would make an AJAX call to get the vendor's worker count
            // For now, we'll simulate it with a default value
            if (vendorId) {
                // Simulate API call to get vendor details
                setTimeout(() => {
                    // This is just a simulation - in a real app, you'd get this from the server
                    const workerCount = 10; // Default value

                    $('#jumlah_tenaga_kerja').val(workerCount);
                    $('#jumlah_tenaga_kerja_value').val(workerCount);
                }, 300);
            } else {
                $('#jumlah_tenaga_kerja').val('');
                $('#jumlah_tenaga_kerja_value').val('');
            }
        });

        // Handle kode_petak change to load sub-block info
        $(document).on('change', '#kode_petak', function() {
            const kodePetak = $(this).val();
            fetchSubBlockInfo(kodePetak);
        });

        // Initialize sub-block info if kode_petak is already selected
        @if($spt->kode_petak)
            fetchSubBlockInfo('{{ $spt->kode_petak }}', true);
        @endif

        // Initialize vendor selection and related fields
        @if($spt->kode_vendor_tebang)
            $(document).ready(function() {
                const vendorSelect = $('#kode_vendor');
                if (vendorSelect.length) {
                    // Get the selected vendor from the SPT data
                    const selectedVendor = '{{ $spt->kode_vendor }}';

                    // Set the selected value and trigger change
                    vendorSelect.val(selectedVendor).trigger('change');

                    // If select2 is used, update its display
                    if ($.fn.select2) {
                        vendorSelect.trigger({ type: 'select2:select' });
                    }

                    // Update jumlah_tenaga_kerja if exists
                    const workerCount = {{ $spt->jumlah_tenaga_kerja ?? 0 }};
                    if (workerCount > 0) {
                        $('#jumlah_tenaga_kerja').val(workerCount);
                        $('#jumlah_tenaga_kerja_value').val(workerCount);
                    }

                    // If we have a petak, trigger its change
                    @if($spt->kode_petak)
                        setTimeout(() => {
                            $('#kode_petak').trigger('change');
                        }, 100);
                    @endif
                }
            });
        @endif

        const datepicker = flatpickr("#tanggal_tebang", {
            dateFormat: "Y-m-d",
            locale: "id",
            allowInput: true,
            onChange: function(selectedDates, dateStr, instance) {
                if (dateStr) {
                    loadVendorsAndPetaks(dateStr);
                } else {
                    resetFormFields();
                }
            }
        });

        // Load vendors and petaks if date is already selected
        @if($spt->tanggal_tebang)
            loadVendorsAndPetaks('{{ $spt->tanggal_tebang->format('Y-m-d') }}');
        @endif
    });

    // Function to fetch sub-block information
    function fetchSubBlockInfo(kodePetak) {
        if (!kodePetak) {
            resetSubBlockInfo();
            return;
        }

        $.ajax({
            url: `/api/sub-blocks/${kodePetak}`,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    const subBlock = response.data;
                    // Update display fields
                    $('#estate-display').text(subBlock.estate || '-')
                        .parent().find('input').val(subBlock.estate || '');
                    $('#divisi-display').text(subBlock.divisi || '-')
                        .parent().find('input').val(subBlock.divisi || '');
                    $('#luas_area-display').text(subBlock.luas_area || '-')
                        .parent().find('input').val(subBlock.luas_area || '');
                    $('#zona-display').text(subBlock.zona || '-')
                        .parent().find('input').val(subBlock.zona || '');
                } else {
                    resetSubBlockInfo();
                    console.error('Failed to fetch sub-block info:', response.message);
                }
            },
            error: function(xhr, status, error) {
                resetSubBlockInfo();
                console.error('Error fetching sub-block info:', error);
            }
        });
    }

    // Function to reset sub-block information
    function resetSubBlockInfo() {
        $('[id$=-display]').text('-');
        $('input[name^=estate], input[name^=divisi], input[name^=luas_area], input[name^=zona]').val('');
    }

    // Function to load vendors and petaks based on selected date
    function loadVendorsAndPetaks(date) {
        const loadingSwal = Swal.fire({
            title: 'Memuat data...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const url = `/spt/availability/${date}`;

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
                            const selected = '{{ $spt->kode_vendor_tebang }}' === vendor.id ? 'selected' : '';
                            vendorOptions += `<option value="${vendor.id}" ${selected}>${vendor.name} (${vendor.spt_count} SPT pada tanggal ini)</option>`;
                        });
                    } else {
                        vendorOptions = '<option value="">Tidak ada vendor tersedia</option>';
                    }
                    $('#kode_vendor_tebang').html(vendorOptions).prop('disabled', false);

                    // Update petak dropdown
                    let petakOptions = '<option value="">Pilih Kode Petak</option>';
                    if (response.harvest_sub_blocks && response.harvest_sub_blocks.length > 0) {
                        response.harvest_sub_blocks.forEach(block => {
                            const selected = '{{ $spt->kode_petak }}' === block.kode_petak ? 'selected' : '';
                            const displayName = block.blok && block.blok !== 'N/A' ?
                                `${block.blok} ${block.kode_petak}` :
                                block.kode_petak;
                            petakOptions += `<option value="${block.kode_petak}" ${selected}>${displayName} (${block.spt_count} SPT pada tanggal ini)</option>`;
                        });
                    } else {
                        petakOptions = '<option value="">Tidak ada petak tersedia</option>';
                    }
                    $('#kode_petak').html(petakOptions).prop('disabled', false);

                    // If there was a previous selection, try to restore it
                    if ('{{ $spt->kode_petak }}') {
                        $('#kode_petak').trigger('change');
                        // Also fetch the sub-block info for the selected kode_petak
                        fetchSubBlockInfo('{{ $spt->kode_petak }}');
                    }
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
                    responseText: xhr.responseText
                });

                let errorMessage = 'Terjadi kesalahan saat memuat data. Silakan coba lagi.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                Swal.fire('Error', errorMessage, 'error');
                resetFormFields();
            }
        });
    }

    // Function to reset form fields
    function resetFormFields() {
        $('#kode_vendor_tebang').html('<option value="">Pilih Tanggal Tebang terlebih dahulu</option>').prop('disabled', true);
        $('#kode_petak').html('<option value="">Pilih Tanggal Tebang terlebih dahulu</option>').prop('disabled', true);
        resetSubBlockInfo();
    }

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

    // Handle kode_petak change event
    $(document).on('change', '#kode_petak', function() {
        const kodePetak = $(this).val();
        fetchSubBlockInfo(kodePetak);
    });

    document.addEventListener('DOMContentLoaded', function() {
        setupAlertCloseButtons();

        // Initialize sub-block info if kode_petak is already selected
        @if($spt->kode_petak)
            fetchSubBlockInfo('{{ $spt->kode_petak }}');
        @endif

        // Event listener untuk perubahan kode_petak
        $('#kode_petak').on('change', function() {
            const kodePetak = $(this).val();
            fetchSubBlockInfo(kodePetak); // ini juga akan memanggil loadMandors(kodePetak)
        });
    });
</script>
@endpush
