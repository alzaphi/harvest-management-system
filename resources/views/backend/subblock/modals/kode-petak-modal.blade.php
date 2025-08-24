<!-- Modal Kode Petak -->
@if(isset($subBlocks) && $subBlocks->count() > 0)
<div class="modal fade" id="kodePetakModal" tabindex="-1" aria-hidden="true">
    @push('scripts')
    <script>
    // Make function globally available
    window.loadKodePetakModal = function() {
        // Check if modal already exists
        if ($('#kodePetakModal').length === 0) {
            // Show loading state
            const loadingHtml = `
                <div class="modal fade" id="kodePetakModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-light">
                                <h5 class="modal-title fw-semibold">Memuat Data Kode Petak...</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Sedang memuat daftar kode petak...</p>
                            </div>
                        </div>
                    </div>
                </div>`;

            $('body').append(loadingHtml);
            const tempModal = new bootstrap.Modal(document.getElementById('kodePetakModal'), {
                backdrop: 'static',
                keyboard: false
            });
            tempModal.show();

            // Fetch the actual modal content
            $.ajax({
                url: '{{ route("get-kode-petak-modal") }}',
                method: 'GET',
                dataType: 'html',
                success: function(html) {
                    // Remove loading modal
                    tempModal.hide();
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('overflow', '');
                    $('body').css('padding-right', '');
                    $('#kodePetakModal').remove();

                    // Add new modal content
                    $('body').append(html);

                    // Show the new modal
                    const newModal = new bootstrap.Modal(document.getElementById('kodePetakModal'), {
                        backdrop: 'static',
                        keyboard: false
                    });
                    newModal.show();
                },
                error: function(xhr, status, error) {
                    console.error('Error loading Kode Petak modal:', error);
                    tempModal.hide();
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('overflow', '');
                    $('body').css('padding-right', '');
                    $('#kodePetakModal').remove();

                    // Show error message
                    alert('Gagal memuat daftar kode petak. Silakan muat ulang halaman dan coba lagi.');
                }
            });
        } else {
            // If modal already exists, just show it
            const modal = new bootstrap.Modal(document.getElementById('kodePetakModal'), {
                backdrop: 'static',
                keyboard: false
            });
            modal.show();
        }
    }
    $(document).ready(function() {
        // Initialize modal
        const kodePetakModal = new bootstrap.Modal(document.getElementById('kodePetakModal'), {
            backdrop: 'static',
            keyboard: false
        });

        // Handle search functionality
        $('#searchKodePetak').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('#kodePetakTableBody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Handle select button click
        $(document).on('click', '.select-kode-petak', function() {
            const row = $(this).closest('tr');
            const kodePetak = row.data('kode');
            const estate = row.data('estate');
            const divisi = row.data('divisi');
            const blok = row.data('blok');
            const luas = row.data('luas');
            const geom = row.data('geom');

            // Set values in the form
            $('#kode_petak').val(kodePetak);
            $('#estateSelect').val(estate).trigger('change');
            $('#divisiSelect').val(divisi).trigger('change');
            $('#blokSelect').val(blok);
            $('#luas_area').val(luas);

            // Close the modal
            kodePetakModal.hide();
        });

        // Handle modal hidden event
        $('#kodePetakModal').on('hidden.bs.modal', function () {
            // Clear search when modal is closed
            $('#searchKodePetak').val('').trigger('keyup');
        });
    });
    </script>
    @endpush
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-semibold">Pilih Kode Petak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="searchKodePetak" class="form-control form-control-sm" placeholder="Cari kode petak...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Petak</th>
                                <th>Estate</th>
                                <th>Divisi</th>
                                <th>Blok</th>
                                <th class="text-end">Luas (ha)</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="kodePetakTableBody">
                            @foreach($subBlocks as $subBlock)
                                <tr data-kode="{{ $subBlock->kode_petak }}"
                                    data-estate="{{ $subBlock->estate }}"
                                    data-divisi="{{ $subBlock->divisi }}"
                                    data-blok="{{ $subBlock->blok }}"
                                    data-luas="{{ $subBlock->luas_area }}"
                                    data-geom="{{ $subBlock->geom }}">
                                    <td>{{ $subBlock->kode_petak }}</td>
                                    <td>{{ $subBlock->estate }}</td>
                                    <td>{{ $subBlock->divisi }}</td>
                                    <td>{{ $subBlock->blok }}</td>
                                    <td class="text-end">{{ number_format($subBlock->luas_area, 2) }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary select-kode-petak">
                                            <i class="fas fa-check me-1"></i> Pilih
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endif
