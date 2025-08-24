// Indonesian translation for DataTables
// From: https://datatables.net/plug-ins/i18n/Indonesian

if (typeof $.fn.dataTable !== 'undefined') {
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            "sEmptyTable":   "Tidak ada data yang tersedia pada tabel ini",
            "sProcessing":   "Sedang memproses...",
            "sLengthMenu":   "Tampilkan _MENU_ data",
            "sZeroRecords":  "Tidak ditemukan data yang sesuai",
            "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 data",
            "sInfoFiltered": "(disaring dari _MAX_ data keseluruhan)",
            "sInfoPostFix":  "",
            "sSearch":       "Cari:",
            "sUrl":          "",
            "oPaginate": {
                "sFirst":    "Pertama",
                "sPrevious": "Sebelumnya",
                "sNext":     "Selanjutnya",
                "sLast":     "Terakhir"
            },
            "oAria": {
                "sSortAscending":  ": aktifkan untuk mengurutkan kolom secara naik",
                "sSortDescending": ": aktifkan untuk mengurutkan kolom secara menurun"
            },
            "select": {
                "rows": {
                    "_": "%d baris dipilih",
                    "0": "Klik pada baris untuk memilih",
                    "1": "1 baris dipilih"
                }
            }
        }
    });
}
