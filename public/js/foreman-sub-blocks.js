document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    const table = $('#foremanSubBlocksTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('foreman-sub-blocks.datatable') }}',
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'kode_petak', name: 'kode_petak' },
            { data: 'divisi', name: 'divisi' },
            { data: 'kode_mandor', name: 'kode_mandor' },
            { data: 'nama_mandor', name: 'nama_mandor' },
            { 
                data: 'tanggal_kerja', 
                name: 'tanggal_kerja',
                render: function(data) {
                    if (!data) return '';
                    const date = new Date(data);
                    return date.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });
                }
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    return `
                        <div class="btn-group" role="group">
                            <a href="/foreman-sub-blocks/${row.id}" class="btn btn-sm btn-info" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/foreman-sub-blocks/${row.id}/edit" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="${row.id}" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[1, 'asc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json'
        },
        responsive: true,
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
              "<'row'<'col-sm-12'tr>>" +
              "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });

    // Handle delete button click
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        if (confirm('Apakah Anda yakin ingin menghapus data mandor sub block ini?')) {
            $.ajax({
                url: `/foreman-sub-blocks/${id}`,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        table.ajax.reload();
                        showAlert('Data berhasil dihapus', 'success');
                    } else {
                        showAlert('Gagal menghapus data', 'error');
                    }
                },
                error: function() {
                    showAlert('Terjadi kesalahan saat menghapus data', 'error');
                }
            });
        }
    });

    // Show alert message
    function showAlert(message, type = 'success') {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('.container-fluid').prepend(alertHtml);
        
        // Auto hide alert after 5 seconds
        setTimeout(() => {
            $('.alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
