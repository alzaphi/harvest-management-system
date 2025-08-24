@extends('layouts.master')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .code-group {
        padding: 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background-color: #f9fafb;
        margin-bottom: 1rem;
    }
    /* Select2 Custom Styles */

    .select2-search {
    display: block !important;
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
</style>
@endpush

@section('content')
<div class="vendor-container">
    <h2 class="mb-4" style="color: #1e40af;">Tambah Sub Block Baru</h2>

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Validasi Gagal</p>
            <ul class="list-disc pl-5 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('sub-blocks.store') }}" method="POST" id="subBlockForm">
        @csrf
        @method('POST')

        <!-- Estate Selection -->
        <div class="form-group">
            <label for="estate" class="form-label">Estate</label>
            <select name="estate" id="estate" class="form-select" required onchange="enableDivisi()">
                <option value="">Pilih Estate</option>
                <option value="LKL" {{ old('estate') == 'LKL' ? 'selected' : '' }}>LKL</option>
                <option value="PLG" {{ old('estate') == 'PLG' ? 'selected' : '' }}>PLG</option>
                <option value="RST" {{ old('estate') == 'RST' ? 'selected' : '' }}>RST</option>
            </select>
            @error('estate')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Divisi Selection -->
        <div class="form-group">
            <label for="divisi" class="form-label">Divisi</label>
            <select name="divisi" id="divisi" class="form-select" disabled required>
                <option value="">Pilih Divisi</option>
                @if(old('divisi') && old('estate'))
                    @foreach($estatesWithDivisions[old('estate')] ?? [] as $divisi)
                        <option value="{{ $divisi }}" {{ old('divisi') == $divisi ? 'selected' : '' }}>
                            {{ $divisi }}
                        </option>
                    @endforeach
                @endif
            </select>
            @error('divisi')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Blok Selection -->
        <div class="form-group">
            <label for="blok" class="form-label">Blok</label>
            <select name="blok" id="blok" class="form-select" disabled required>
                <option value="">Pilih Blok</option>
                @if(old('blok') && old('divisi'))
                    @foreach($blocks ?? [] as $block)
                        <option value="{{ $block }}" {{ old('blok') == $block ? 'selected' : '' }}>
                            {{ $block }}
                        </option>
                    @endforeach
                @endif
            </select>
            @error('blok')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Kode Petak (auto-generated) -->
        <div class="form-group">
            <label for="kode_petak" class="form-label">Kode Petak</label>
            <div class="relative">
                <input type="text" name="kode_petak" id="kode_petak" class="form-input w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100"
                       value="{{ old('kode_petak') }}" placeholder="Kode akan digenerate otomatis" readonly>
            </div>
            @error('kode_petak')
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
        </div>

        <!-- Luas Area -->
        <div class="form-group">
            <label for="luas_area" class="form-label">Luas Area (ha)</label>
            <div class="relative">
                <input type="number" step="0.01" name="luas_area" id="luas_area"
                       class="form-input w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       value="{{ old('luas_area') }}" placeholder="Input Luas Area..." required>
            </div>
            @error('luas_area')
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
        </div>

        <!-- Umur (Bulan) -->
        <div class="form-group">
            <label for="age_months" class="form-label">Umur (Bulan)</label>
            <div class="relative">
                <input type="number" name="age_months" id="age_months"
                       class="form-input w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       value="{{ old('age_months') }}" placeholder="Input Umur dalam Bulan...">
            </div>
            @error('age_months')
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
        </div>

        <!-- Zona -->
        <div class="form-group">
            <label for="zona" class="form-label">Zona</label>
            <select name="zona" id="zona" class="form-select" required>
                <option value="">Pilih Zona</option>
                <option value="Zone 1" {{ old('zona') == 'Zone 1' ? 'selected' : '' }}>1</option>
                <option value="Zone 2" {{ old('zona') == 'Zone 2' ? 'selected' : '' }}>2</option>
                <option value="Zone 3" {{ old('zona') == 'Zone 3' ? 'selected' : '' }}>3</option>
                <option value="Zone 4" {{ old('zona') == 'Zone 4' ? 'selected' : '' }}>4</option>
            </select>
            @error('zona')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Keterangan -->
        <div class="form-group">
            <label for="keterangan" class="form-label">Keterangan</label>
            <div class="relative">
                <textarea name="keterangan" id="keterangan" class="form-input w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="3" placeholder="Input Keterangan...">{{ old('keterangan') }}</textarea>
            </div>
            @error('keterangan')
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
        </div>

        <!-- Data Peta (GeoJSON Format) -->
        <div class="form-group">
            <label for="geom_json" class="form-label">Data Peta (GeoJSON Format)</label>
            <div class="relative">
                <textarea name="geom_json" id="geom_json" class="form-input w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="6" placeholder='{
  "type": "Polygon",
  "coordinates": [
    [
      [122.01, -4.55],
      [122.02, -4.55],
      [122.02, -4.56],
      [122.01, -4.56],
      [122.01, -4.55]
    ]
  ]
}'>{{ old('geom_json') }}</textarea>
            </div>
            <div class="text-sm text-gray-500 mt-1">
                Pastikan format JSON sesuai dengan contoh di atas. Harap gunakan tipe "Polygon" dan koordinat yang valid.
            </div>
            @error('geom_json')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Aktif -->
        <div class="form-group">
            <div class="flex items-center">
                <input type="hidden" name="aktif" value="0">
                <input type="checkbox" name="aktif" id="aktif" class="form-checkbox" value="1"
                       {{ old('aktif', '1') == '1' ? 'checked' : '' }}>
                <label for="aktif" class="ml-2">Aktif</label>
            </div>
            @error('aktif')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('sub-blocks.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i> Simpan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    function initializeDivisiSelect2() {
        $('#divisi').select2({
            placeholder: 'Cari divisi',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#divisi').parent(),
            minimumInputLength: 0,
            minimumResultsForSearch: 0 // Pastikan search box selalu muncul
        });
    }

    function enableDivisi() {
        const estate = document.getElementById('estate').value;
        const $divisiSelect = $('#divisi');
        
        if (estate) {
            $divisiSelect.prop('disabled', false);
            
            // Hancurkan Select2 jika sudah diinisialisasi
            if ($divisiSelect.hasClass('select2-hidden-accessible')) {
                $divisiSelect.select2('destroy');
            }
            
            // Inisialisasi ulang Select2
            initializeDivisiSelect2();
        } else {
            // Hancurkan Select2 jika sudah diinisialisasi
            if ($divisiSelect.hasClass('select2-hidden-accessible')) {
                $divisiSelect.select2('destroy');
            }
            $divisiSelect.prop('disabled', true);
        }
        
        // Reset dependent fields
        $('#blok').prop('disabled', true).val('');
        $('#kode_petak').val('');
    }

    $(document).on('change', '#divisi', function() {
        enableBlok();
    });
    
    $(document).on('select2:select', '#divisi', function() {
        enableBlok();
    });
    
    function enableBlok() {
        const divisi = $('#divisi').val();
        const $blokSelect = $('#blok');
        
        if (divisi) {
            $blokSelect.prop('disabled', false);
            
            // Hapus opsi yang ada kecuali opsi pertama
            $blokSelect.find('option:not(:first)').remove();
            
            // Dapatkan data blok dari PHP yang sudah tersedia di view
            const blocks = @json($blocks ?? []);
            const estate = $('#estate').val();
            const selectedDivisi = divisi;
            
            console.log('All blocks:', blocks);
            console.log('Filtering with - Estate:', estate, 'Divisi:', selectedDivisi);
            
            // Filter blok berdasarkan estate dan divisi yang dipilih
            const filteredBlocks = blocks.filter(block => {
                // Pastikan properti ada sebelum membandingkan
                const estateMatch = !estate || !block.estate || block.estate === estate;
                const divisiMatch = !selectedDivisi || !block.divisi || block.divisi === selectedDivisi;
                const hasBlok = block.blok && block.blok.trim() !== '';
                
                const match = estateMatch && divisiMatch && hasBlok;
                console.log('Checking block:', block, 'Match:', match);
                return match;
            });
            
            console.log('Filtered blocks:', filteredBlocks);
            
            // Ambil daftar blok unik dan urutkan
            const uniqueBlocks = [...new Set(filteredBlocks.map(block => block.blok))].sort();
            
            console.log('Unique blocks to show:', uniqueBlocks);
            
            // Tambahkan opsi blok
            uniqueBlocks.forEach(blok => {
                if (blok) { // Pastikan blok tidak kosong
                    $blokSelect.append(new Option(blok, blok));
                }
            });
            
            // Hapus inisialisasi Select2 jika ada
            if ($blokSelect.hasClass('select2-hidden-accessible')) {
                $blokSelect.select2('destroy');
            }
            
            // Pastikan field blok dalam keadaan enabled
            $blokSelect.prop('disabled', false);
            
            // Trigger event change untuk mengupdate kode petak
            $blokSelect.trigger('change');
        } else {
            $blokSelect.prop('disabled', true).val('');
            $('#kode_petak').val('');
        }
    }

    $(document).on('change', '#blok', function() {
        generateKodePetak();
    });
    
    async function generateKodePetak() {
        const blok = $('#blok').val();
        const kodePetakInput = $('#kode_petak');
        
        if (!blok) {
            kodePetakInput.val('');
            return;
        }
        
        try {
            // Ambil kode estate (2 karakter pertama)
            const estate = $('#estate').val();
            const kodeEstate = estate ? estate.substring(0, 2).toUpperCase() : '';
            
            // Ambil kode divisi (3 karakter terakhir)
            const divisi = $('#divisi').val();
            const kodeDivisi = divisi ? divisi.substring(Math.max(divisi.length - 3, 0)).toUpperCase() : '';
            
            // Format kode petak
            const kodePetak = `${kodeEstate}${kodeDivisi}${blok}`.toUpperCase();
            
            // Set nilai kode petak
            kodePetakInput.val(kodePetak);
            
        } catch (error) {
            console.error('Error generating kode petak:', error);
            kodePetakInput.val('');
        }
    }

    async function generateKodePetak() {
        const blok = document.getElementById('blok').value;
        const kodePetakInput = document.getElementById('kode_petak');
        
        if (blok) {
            try {
                // Get existing codes for this block from the server
                const response = await fetch(`/api/sub-blocks/codes?blok=${encodeURIComponent(blok)}`);
                const existingCodes = await response.json();
                
                // Find the next available code starting from 01
                let nextNumber = 1;
                let newCode = '';
                let attempts = 0;
                const maxAttempts = 100; // 01-99 with all possible letters
                
                // Generate all possible codes until we find one that doesn't exist
                while (attempts < maxAttempts) {
                    const paddedNumber = String(nextNumber).padStart(2, '0');
                    
                    // Try all possible letters for this number
                    for (let i = 0; i < 26; i++) {
                        const randomLetter = String.fromCharCode(65 + i); // A-Z
                        const potentialCode = blok + paddedNumber + randomLetter;
                        
                        // Check if this exact code already exists
                        if (!existingCodes.includes(potentialCode)) {
                            kodePetakInput.value = potentialCode;
                            return;
                        }
                        attempts++;
                    }
                    
                    // Move to next number if no available code found with current number
                    nextNumber++;
                    
                    // Reset to 01 if we reach 100
                    if (nextNumber > 99) nextNumber = 1;
                }
                
                // If we've tried all possible combinations
                alert('Tidak dapat menghasilkan kode petak baru. Semua kombinasi untuk blok ini sudah digunakan.');
                kodePetakInput.value = '';
                
            } catch (error) {
                console.error('Error generating kode petak:', error);
                // Fallback to simple generation if API call fails
                const randomLetter = String.fromCharCode(65 + Math.floor(Math.random() * 26));
                kodePetakInput.value = blok + '01' + randomLetter;
            }
        } else {
            kodePetakInput.value = '';
        }
    }

    // Fungsi untuk menginisialisasi Select2 pada field blok
    function initializeBlokSelect2() {
        // Pastikan Select2 belum diinisialisasi sebelumnya
        if ($('#blok').hasClass('select2-hidden-accessible')) {
            $('#blok').select2('destroy');
        }
        
        // Inisialisasi Select2
        $('#blok').select2({
            placeholder: 'Cari blok',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#blok').parent()
        });
    }
    
    // Inisialisasi saat dokumen siap
    $(document).ready(function() {
        // Inisialisasi Select2 pada blok jika sudah ada opsi
        if ($('#blok option').length > 1) {
            initializeBlokSelect2();
        }
    });
    
    // Inisialisasi ulang Select2 saat field blok di-update
    $(document).on('change', '#divisi', function() {
        // Tunggu sebentar untuk memastikan opsi blok sudah di-update
        setTimeout(function() {
            if ($('#blok').length && !$('#blok').prop('disabled')) {
                initializeBlokSelect2();
            }
        }, 100);
    });

    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            var estateSelect = document.getElementById('estate');
            var divisiSelect = document.getElementById('divisi');
            var blokSelect = document.getElementById('blok');
            var form = document.getElementById('subBlockForm');
            var submitBtn = form.querySelector('button[type="submit"]');
            var originalSubmitBtnText = submitBtn.innerHTML;

            // Function to show loading state
            function setLoading(isLoading) {
                if (isLoading) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                } else {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalSubmitBtnText;
                }
            }

            // Function to show success message
            function showSuccessMessage(message) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: message || 'Data berhasil disimpan',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route("sub-blocks.index") }}';
                    }
                });
            }

            // Function to validate GeoJSON format
            function validateGeoJSON(geojson) {
                try {
                    const data = JSON.parse(geojson);
                    if (!data.type || data.type !== 'Polygon') {
                        return 'Tipe GeoJSON harus berupa "Polygon"';
                    }
                    if (!Array.isArray(data.coordinates) || data.coordinates.length === 0) {
                        return 'Koordinat tidak valid';
                    }
                    // Validate polygon coordinates
                    const coords = data.coordinates[0];
                    if (coords.length < 4) {
                        return 'Polygon membutuhkan minimal 4 titik koordinat';
                    }
                    // Check if first and last points are the same (closed polygon)
                    const first = coords[0];
                    const last = coords[coords.length - 1];
                    if (first[0] !== last[0] || first[1] !== last[1]) {
                        return 'Polygon harus tertutup (titik awal dan akhir harus sama)';
                    }
                    return null;
                } catch (e) {
                    return 'Format GeoJSON tidak valid: ' + e.message;
                }
            }

            // Function to show error messages
            function showErrorMessages(errors) {
                var errorMessage = '';
                Object.entries(errors).forEach(function(entry) {
                    var key = entry[0];
                    var value = entry[1];
                    errorMessage += value.join('<br>') + '<br>';
                });

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    html: errorMessage || 'Terjadi kesalahan saat menyimpan data',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
            }

            // Load divisions when estate is selected
            if (estateSelect) {
                estateSelect.addEventListener('change', function() {
                    var estate = this.value;

                    if (estate) {
                        divisiSelect.disabled = false;

                        // Clear existing options
                        divisiSelect.innerHTML = '<option value="">Pilih Divisi</option>';

                        // Add divisions for the selected estate
                        var divisions = @json($estatesWithDivisions);
                        if (divisions[estate]) {
                            divisions[estate].forEach(function(divisi) {
                                var option = new Option(divisi, divisi);
                                divisiSelect.add(option);
                            });
                        }
                    } else {
                        divisiSelect.disabled = true;
                        divisiSelect.innerHTML = '<option value="">Pilih Divisi</option>';
                        if (blokSelect) {
                            blokSelect.disabled = true;
                            blokSelect.innerHTML = '<option value="">Pilih Blok</option>';
                        }
                    }
                });
            }

            // Load blocks when division is selected
            if (divisiSelect) {
                divisiSelect.addEventListener('change', function() {
                    var divisi = this.value;

                    if (divisi && blokSelect) {
                        blokSelect.disabled = false;

                        // Fetch blocks via AJAX
                        fetch('{{ route("sub-blocks.get-blocks-by-division") }}?divisi=' + encodeURIComponent(divisi))
                            .then(function(response) {
                                return response.json();
                            })
                            .then(function(data) {
                                // Clear existing options
                                blokSelect.innerHTML = '<option value="">Pilih Blok</option>';

                                // Add blocks to the select
                                data.forEach(function(item) {
                                    var option = new Option(item.blok, item.blok);
                                    blokSelect.add(option);
                                });
                            })
                            .catch(function(error) {
                                console.error('Error loading blocks:', error);
                            });
                    } else if (blokSelect) {
                        blokSelect.disabled = true;
                        blokSelect.innerHTML = '<option value="">Pilih Blok</option>';
                    }
                });
            }

            // If editing and estate is already selected, trigger change event
            @if(old('estate'))
                if (estateSelect) {
                    estateSelect.dispatchEvent(new Event('change'));
                }
            @endif

            // If editing and divisi is already selected, trigger change event
            @if(old('divisi'))
                // Small timeout to ensure the divisions are loaded first
                setTimeout(function() {
                    if (divisiSelect) {
                        divisiSelect.value = '{{ old("divisi") }}';
                        divisiSelect.dispatchEvent(new Event('change'));
                    }
                }, 300);
            @endif

            // Initialize Select2 when document is ready
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize for divisi if it's already enabled
                if (!document.getElementById('divisi').disabled) {
                    initializeDivisiSelect2();
                }
                
                // Rest of your existing DOMContentLoaded code
            });

            // Handle form submission with AJAX
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                // Validasi data peta
                const geomJsonInput = document.getElementById('geom_json');
                if (!geomJsonInput || !geomJsonInput.value) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Data Peta Kosong',
                        text: 'Mohon gambar area di peta terlebih dahulu sebelum menyimpan.',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Mengerti'
                    });
                    return false;
                }

                // Set loading state
                setLoading(true);

                // Submit form via AJAX
                const formData = new FormData(this);
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(function(response) {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || 'Terjadi kesalahan saat menyimpan data');
                        });
                    }
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: 'Data sub block berhasil disimpan',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = data.redirect || '{{ route("sub-blocks.index") }}';
                            }
                        });
                    } else {
                        throw new Error(data.message || 'Terjadi kesalahan saat menyimpan data');
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: error.message || 'Terjadi kesalahan saat menyimpan data',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'OK'
                    });
                })
                .finally(function() {
                    // Restore button state
                    setLoading(false);
                });

                return false;
            });
        });
    })();
</script>
@endpush
