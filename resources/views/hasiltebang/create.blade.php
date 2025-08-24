@extends('layouts.master')

@php
$header = 'Hasil Tebangan';
$breadcrumb = [
    ['title' => 'Hasil Tebangan', 'url' => route('hasil-tebang.index')],
    ['title' => 'Tambah Data Hasil Tebang']
];
@endphp

@section('page-title', 'Tambah Data Hasil Tebang')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/hasil-tebang.css') }}">
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">Tambah Data Hasil Tebang</h2>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="hasilTebangForm" action="{{ route('hasil-tebang.store') }}" method="POST">
                        @csrf

                        <!-- Informasi Umum -->
                        <div class="form-section">
                            <h3 class="section-title">Informasi Umum</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Kode Hasil Tebang</label>
                                    <input type="text" class="form-control" name="kode_hasil_tebang" value="{{ $kodeHasilTebang }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label>Tanggal Timbang</label>
                                    <input type="date" class="form-control" name="tanggal_timbang" value="{{ old('tanggal_timbang', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="form-group mt-2">
                                <label>No LKT</label>
                                <select class="form-control" name="kode_lkt" id="kode_lkt" required>
                                    <option value="">-- Pilih No LKT --</option>
                                    @foreach($lkts as $lkt)
                                        <option value="{{ $lkt['kode_lkt'] }}"
                                            data-kode-spt="{{ $lkt['kode_spt'] }}"
                                            data-kode-petak="{{ $lkt['kode_petak'] }}"
                                            data-divisi="{{ $lkt['divisi'] }}"
                                            data-vendor-tebang="{{ $lkt['vendor_tebang'] }}"
                                            data-vendor-angkut="{{ $lkt['vendor_angkut'] }}"
                                            data-zonasi="{{ $lkt['zonasi'] }}"
                                            data-jenis-tebang="{{ $lkt['jenis_tebangan'] }}"
                                            data-kode-lambung="{{ $lkt['kode_lambung'] }}">
                                            {{ $lkt['kode_lkt'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Data LKT -->
                        <div class="form-section">
                            <h3 class="section-title">Data LKT</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>No SPT</label>
                                    <input type="text" class="form-control" name="kode_spt" id="kode_spt" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label>Kode Petak</label>
                                    <input type="text" class="form-control" name="kode_petak" id="kode_petak" readonly>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <label>Divisi</label>
                                    <input type="text" class="form-control" name="divisi" id="divisi" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label>Vendor Tebang</label>
                                    <input type="text" class="form-control" id="vendor_tebang_display" readonly>
                                    <input type="hidden" name="vendor_tebang" id="vendor_tebang">
                                </div>
                                <div class="col-md-4">
                                    <label>Vendor Angkut</label>
                                    <input type="text" class="form-control" id="vendor_angkut_display" readonly>
                                    <input type="hidden" name="vendor_angkut" id="vendor_angkut">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <label>Zonasi</label>
                                    <input type="text" class="form-control" name="zonasi" id="zonasi" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label>Jenis Tebang</label>
                                    <input type="text" class="form-control" name="jenis_tebang" id="jenis_tebang" readonly>
                                </div>
                            </div>
                            <div class="form-group mt-2">
                                <label>Supir</label>
                                <input type="text" class="form-control" id="kode_lambung_display" readonly>
                                <input type="hidden" name="kode_lambung" id="kode_lambung">

                            </div>
                        </div>

                        <!-- Data Timbangan -->
                        <div class="form-section">
                            <h3 class="section-title">Data Timbangan</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Total Bruto (ton)</label>
                                    <input type="number" class="form-control" name="bruto" id="bruto" step="0.01" required>
                                </div>
                                <div class="col-md-6">
                                    <label>Tanggal & Jam Bruto</label>
                                    <input type="datetime-local" class="form-control" name="tanggal_bruto" id="tanggal_bruto" required>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <label>Total Tarra (ton)</label>
                                    <input type="number" class="form-control" name="tarra" id="tarra" step="0.01" required>
                                </div>
                                <div class="col-md-6">
                                    <label>Tanggal & Jam Tarra</label>
                                    <input type="datetime-local" class="form-control" name="tanggal_tarra" id="tanggal_tarra" required>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <label>Netto 1 (ton)</label>
                                    <input type="number" class="form-control" name="netto1" id="netto1" step="0.01" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label>Sortase (ton)</label>
                                    <input type="number" class="form-control" name="sortase" id="sortase" step="0.01" required>
                                </div>
                            </div>
                            <div class="form-group mt-2">
                                <label>Netto 2 (ton)</label>
                                <input type="number" class="form-control" name="netto2" id="netto2" step="0.01" readonly>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="button" id="btnSimpan" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Data
                            </button>
                            <a href="{{ route('hasil-tebang.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#kode_lkt').on('change', function () {
            const opt = $(this).find(':selected');
            $('#kode_spt').val(opt.data('kode-spt'));
            $('#kode_petak').val(opt.data('kode-petak'));
            $('#divisi').val(opt.data('divisi'));
            $('#vendor_tebang_display').val(opt.data('vendor-tebang'));
            $('#vendor_tebang').val(opt.data('vendor-tebang'));
            $('#vendor_angkut_display').val(opt.data('vendor-angkut'));
            $('#vendor_angkut').val(opt.data('vendor-angkut'));
            $('#zonasi').val(opt.data('zonasi'));
            $('#jenis_tebang').val(opt.data('jenis-tebang'));
            $('#kode_lambung_display').val(opt.data('kode-lambung'));
            $('#kode_lambung').val(opt.data('kode-lambung'));

        });

        $('#bruto, #tarra').on('input', function () {
            const bruto = parseFloat($('#bruto').val()) || 0;
            const tarra = parseFloat($('#tarra').val()) || 0;
            $('#netto1').val((bruto - tarra).toFixed(2));
            const sortase = parseFloat($('#sortase').val()) || 0;
            $('#netto2').val((bruto - tarra - sortase).toFixed(2));
        });

        $('#sortase').on('input', function () {
            const netto1 = parseFloat($('#netto1').val()) || 0;
            const sortase = parseFloat($('#sortase').val()) || 0;
            $('#netto2').val((netto1 - sortase).toFixed(2));
        });

        // Handle tombol simpan
        $('#btnSimpan').click(function(e) {
            e.preventDefault();
            
            // Validasi form
            const form = document.getElementById('hasilTebangForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Tampilkan konfirmasi
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Apakah data yang Anda input sudah benar?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Periksa Kembali',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika dikonfirmasi, submit form
                    $('#hasilTebangForm').submit();
                }
            });
        });

        // Handle submit form dengan tombol Enter
        $('#hasilTebangForm').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#btnSimpan').click();
            }
        });
    });
</script>
@endpush
