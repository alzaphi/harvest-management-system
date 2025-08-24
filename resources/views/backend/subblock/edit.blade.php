@extends('layouts.master')

@section('title', 'Edit Sub Block')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
<style>
    .code-group {
        padding: 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background-color: #f9fafb;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="vendor-container">
    <h2>Edit Sub Block: {{ $subblock->kode_petak }}</h2>

    <form action="{{ route('sub-blocks.update', $subblock->id) }}" method="POST" id="subBlockForm">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="kode_petak" class="form-label">Kode Petak</label>
            <div class="relative">
                <input type="text" name="kode_petak" id="kode_petak" class="form-input w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       value="{{ old('kode_petak', $subblock->kode_petak) }}" placeholder="Input Kode Petak..." required>
            </div>
            @error('kode_petak')
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
        </div>

        <!-- Estate Selection -->
        <div class="form-group">
            <label for="estate" class="form-label">Estate</label>
            @php
                $currentEstate = str_replace('Estate ', '', $subblock->estate);
            @endphp
            <select name="estate" id="estate" class="form-select" required>
                <option value="">Pilih Estate</option>
                <option value="LKL" {{ old('estate', $currentEstate) == 'LKL' ? 'selected' : '' }}>LKL</option>
                <option value="PLG" {{ old('estate', $currentEstate) == 'PLG' ? 'selected' : '' }}>PLG</option>
                <option value="RST" {{ old('estate', $currentEstate) == 'RST' ? 'selected' : '' }}>RST</option>
            </select>
            @error('estate')
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
        </div>

        <!-- Divisi Selection -->
        <div class="form-group">
            <label for="divisi" class="form-label">Divisi</label>
            <select name="divisi" id="divisi" class="form-select" required>
                <option value="">Pilih Divisi</option>
                @if(isset($estatesWithDivisions[$subblock->estate]))
                    @foreach($estatesWithDivisions[$subblock->estate] as $divisi)
                        <option value="{{ $divisi }}" {{ old('divisi', $subblock->divisi) == $divisi ? 'selected' : '' }}>{{ $divisi }}</option>
                    @endforeach
                @endif
            </select>
            @error('divisi')
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
        </div>

        <!-- Blok Selection -->
        <div class="form-group">
            <label for="blok" class="form-label">Blok</label>
            <select name="blok" id="blok" class="form-select" required>
                <option value="">Pilih Blok</option>
                @if(isset($blocks))
                    @foreach($blocks as $blok)
                        <option value="{{ $blok }}" {{ old('blok', $subblock->blok) == $blok ? 'selected' : '' }}>{{ $blok }}</option>
                    @endforeach
                @endif
            </select>
            @error('blok')
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="luas_ha" class="form-label">Luas (Ha)</label>
            <div class="relative">
                <input type="number" step="0.01" name="luas_area" id="luas_area" class="form-input w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       value="{{ old('luas_area', $subblock->luas_area) }}" placeholder="Input Luas (Ha)..." required>
            </div>
            @error('luas_area')
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="zona" class="form-label">Zona</label>
            @php
                $selectedZona = old('zona', $subblock->zona);
                // Debug information
                \Log::info('Zona Debug', [
                    'subblock_zona' => $subblock->zona,
                    'old_zona' => old('zona'),
                    'selected_zona' => $selectedZona,
                    'is_null' => is_null($subblock->zona),
                    'empty' => empty($subblock->zona)
                ]);
            @endphp
            <select name="zona" id="zona" class="form-select">
                <option value="">Pilih Zona (Opsional)</option>
                <option value="1" {{ $selectedZona == '1' ? 'selected' : '' }}>1</option>
                <option value="2" {{ $selectedZona == '2' ? 'selected' : '' }}>2</option>
                <option value="3" {{ $selectedZona == '3' ? 'selected' : '' }}>3</option>
                <option value="4" {{ $selectedZona == '4' ? 'selected' : '' }}>4</option>
            </select>
            @error('zona')
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
            <div class="mt-1 text-sm text-gray-500">
                Current value: {{ $selectedZona ?? 'Tidak ada nilai' }}
            </div>
        </div>

        <div class="form-group">
            <div class="flex items-center">
                <!-- Hidden input to ensure a value is always submitted -->
                <input type="hidden" name="aktif" value="0">
                <input type="checkbox" name="aktif" id="aktif" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" value="1" {{ old('aktif', $subblock->aktif) ? 'checked' : '' }}>
                <label for="aktif" class="ml-2 block text-sm text-gray-900">
                    Aktif
                </label>
            </div>
            @error('aktif')
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="keterangan" class="form-label">Keterangan</label>
            <div class="relative">
                <textarea name="keterangan" id="keterangan" class="form-input w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="3" placeholder="Input Keterangan...">{{ old('keterangan', $subblock->keterangan) }}</textarea>
            </div>
            @error('keterangan')
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="geom_json" class="form-label">Data Peta (GeoJSON Format)</label>
            <div class="relative">
                @php
                    $geomJson = old('geom_json', is_array($subblock->geom_json) ? json_encode($subblock->geom_json, JSON_PRETTY_PRINT) : $subblock->geom_json);
                @endphp
                <textarea name="geom_json" id="geom_json" class="form-input w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm" rows="10" placeholder='{
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
}'>{{ $geomJson }}</textarea>
            </div>
            <div class="text-sm text-gray-500 mt-1">
                Pastikan format JSON sesuai dengan contoh di atas. Harap gunakan tipe "Polygon" dan koordinat yang valid.
            </div>
            @error('geom_json')
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex justify-end mt-6">
            <a href="{{ route('sub-blocks.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                Batal
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            var estateSelect = document.getElementById('estate');
            var divisiSelect = document.getElementById('divisi');
            var blokSelect = document.getElementById('blok');
            var form = document.getElementById('subBlockForm');
            var estatesWithDivisions = @json($estatesWithDivisions);
            var currentEstate = '{{ $subblock->estate }}';
            var currentDivisi = '{{ $subblock->divisi }}';
            var currentBlok = '{{ $subblock->blok }}';

            // Flag to track if block was changed
            var blockChanged = false;

            // Function to load divisions based on selected estate
            function loadDivisions(estate) {
                if (!divisiSelect) return;

                // Clear current options
                divisiSelect.innerHTML = '<option value="">Pilih Divisi</option>';

                if (estate && estatesWithDivisions[estate]) {
                    // Add new options
                    estatesWithDivisions[estate].forEach(function(divisi) {
                        var option = document.createElement('option');
                        option.value = divisi;
                        option.textContent = divisi;
                        if (divisi === currentDivisi) {
                            option.selected = true;
                        }
                        divisiSelect.appendChild(option);
                    });
                }

                // Trigger change to load blocks
                if (divisiSelect.dispatchEvent) {
                    divisiSelect.dispatchEvent(new Event('change'));
                }
            }

            // Function to load blocks when division is selected
            function loadBlocks(divisi) {
                if (!blokSelect) return;

                if (divisi) {
                    blokSelect.disabled = false;

                    // Show loading
                    blokSelect.innerHTML = '<option value="">Memuat blok...</option>';

                    // Fetch blocks via AJAX using the same route as create form
                    fetch('{{ route("sub-blocks.get-blocks-by-division") }}?divisi=' + encodeURIComponent(divisi))
                        .then(function(response) {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(function(data) {
                            // Clear existing options
                            blokSelect.innerHTML = '<option value="">Pilih Blok</option>';

                            // Add blocks to the select
                            if (data && data.length > 0) {
                                data.forEach(function(item) {
                                    var option = new Option(item.blok, item.blok);
                                    if (item.blok === currentBlok) {
                                        option.selected = true;
                                    }
                                    blokSelect.add(option);
                                });
                            } else {
                                blokSelect.innerHTML = '<option value="">Tidak ada blok tersedia</option>';
                            }
                        })
                        .catch(function(error) {
                            console.error('Error loading blocks:', error);
                            blokSelect.innerHTML = '<option value="">Gagal memuat blok</option>';
                        });
                } else {
                    blokSelect.disabled = true;
                    blokSelect.innerHTML = '<option value="">Pilih Blok</option>';
                }
            }

            // Initialize form on page load
            document.addEventListener('DOMContentLoaded', function() {
                // Set the estate value and load its divisions
                if (estateSelect && currentEstate) {
                    // Force set the estate value and trigger change event
                    estateSelect.value = currentEstate;
                    var event = new Event('change');
                    estateSelect.dispatchEvent(event);

                    // Set the division value after a small delay
                    if (divisiSelect && currentDivisi) {
                        setTimeout(function() {
                            // Make sure the divisions are loaded first
                            if (divisiSelect.options.length > 1) {
                                divisiSelect.value = currentDivisi;
                                // Trigger change event for division to load blocks
                                var divEvent = new Event('change');
                                divisiSelect.dispatchEvent(divEvent);

                                // Set the block value after blocks are loaded
                                if (blokSelect && currentBlok) {
                                    setTimeout(function() {
                                        blokSelect.value = currentBlok;
                                        // Add hidden input to maintain block value
                                        var hiddenBlockInput = document.createElement('input');
                                        hiddenBlockInput.type = 'hidden';
                                        hiddenBlockInput.name = 'blok';
                                        hiddenBlockInput.value = currentBlok;
                                        form.appendChild(hiddenBlockInput);
                                    }, 500);
                                }
                            }
                        }, 300);
                    }
                }
            });

            // Event listeners
            // Load divisions when estate is selected
            if (estateSelect) {
                estateSelect.addEventListener('change', function() {
                    var estate = this.value;
                    console.log('Estate changed to:', estate);

                    if (estate) {
                        // Enable and clear division select
                        if (divisiSelect) {
                            divisiSelect.disabled = false;
                            divisiSelect.innerHTML = '<option value="">Pilih Divisi</option>';

                            // Load divisions for the selected estate
                            var divisions = @json($estatesWithDivisions);
                            if (divisions[estate]) {
                                divisions[estate].forEach(function(divisi) {
                                    var option = new Option(divisi, divisi);
                                    if (divisi === currentDivisi) {
                                        option.selected = true;
                                    }
                                    divisiSelect.add(option);
                                });
                            }
                        }

                        // Clear blocks when estate changes
                        if (blokSelect) {
                            blokSelect.disabled = true;
                            blokSelect.innerHTML = '<option value="">Pilih Blok</option>';
                            blockChanged = true;
                        }
                    } else {
                        if (divisiSelect) {
                            divisiSelect.disabled = true;
                            divisiSelect.innerHTML = '<option value="">Pilih Divisi</option>';
                        }
                        if (blokSelect) {
                            blokSelect.disabled = true;
                            blokSelect.innerHTML = '<option value="">Pilih Blok</option>';
                            blockChanged = true;
                        }
                    }
                });
            }

            // Load blocks when division is selected
            if (divisiSelect) {
                divisiSelect.addEventListener('change', function() {
                    var divisi = this.value;
                    console.log('Division changed to:', divisi);

                    if (divisi) {
                        loadBlocks(divisi);
                        blockChanged = true; // Mark that block selection was changed
                    } else {
                        if (blokSelect) {
                            blokSelect.disabled = true;
                            blokSelect.innerHTML = '<option value="">Pilih Blok</option>';
                        }
                    }
                });
            }

            // Track block changes
            if (blokSelect) {
                blokSelect.addEventListener('change', function() {
                    console.log('Block changed to:', this.value);
                    blockChanged = true;
                });
            }

            // Add form submission handler for form validation
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Ensure the block value is included in the form submission
                    if (blokSelect && !blockChanged) {
                        // If block wasn't changed, ensure the original value is submitted
                        var hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'blok';
                        hiddenInput.value = currentBlok;
                        form.appendChild(hiddenInput);
                    }

                    e.preventDefault(); // Prevent default form submission

                    // Validate GeoJSON before submission
                    const geojsonInput = document.getElementById('geom_json');
                    if (geojsonInput && geojsonInput.value.trim() !== '') {
                        try {
                            const geojson = JSON.parse(geojsonInput.value);

                            // Basic validation
                            if (geojson.type !== 'Polygon') {
                                alert('Tipe GeoJSON harus berupa "Polygon"');
                                return false;
                            }

                            if (!Array.isArray(geojson.coordinates) || !Array.isArray(geojson.coordinates[0])) {
                                alert('Format koordinat tidak valid');
                                return false;
                            }

                            const coords = geojson.coordinates[0];
                            if (coords.length < 4) {
                                alert('Polygon membutuhkan minimal 4 titik koordinat');
                                return false;
                            }

                            // Check if polygon is closed (first and last points are the same)
                            const first = coords[0];
                            const last = coords[coords.length - 1];
                            if (first[0] !== last[0] || first[1] !== last[1]) {
                                alert('Polygon harus tertutup (titik awal dan akhir harus sama)');
                                return false;
                            }

                        } catch (error) {
                            alert('Format GeoJSON tidak valid: ' + error.message);
                            return false;
                        }
                    }

                    // If validation passes, submit the form
                    form.submit();
                });
            }
        });
    })();
</script>
@endpush
