@extends('layouts.master')

@section('page-title', 'Pilih Data Hasil Tebang')

@php
$header = 'Pilih Data Hasil Tebang';
$breadcrumb = [
    ['title' => 'Dashboard', 'url' => route('dashboard')],
    ['title' => 'Hasil Tebangan', 'url' => route('hasil-tebang.index')],
    ['title' => 'Pilih Data Hasil Tebang']
];
@endphp
@push('styles')
<style>
    .selection-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        padding: 24px;
        max-width: 600px;
        margin: 0 auto;
        transition: transform 0.2s ease;
    }
    .selection-card:hover {
        transform: translateY(-2px);
    }
    .form-label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }
    select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        color: #374151;
        background-color: #fff;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    select:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
    }
    .btn-primary {
        background-color: #2563eb;
        color: white;
        padding: 10px 16px;
        border-radius: 8px;
        font-weight: 600;
        transition: background-color 0.2s ease;
    }
    .btn-primary:hover {
        background-color: #1d4ed8;
    }
    .btn-secondary {
        background-color: #f3f4f6;
        color: #374151;
        padding: 10px 16px;
        border-radius: 8px;
        font-weight: 600;
        transition: background-color 0.2s ease;
    }
    .btn-secondary:hover {
        background-color: #e5e7eb;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-2xl font-bold text-[#1e40af] mb-6 text-center">Pilih Data Hasil Tebang untuk Diedit</h2>

    <div class="selection-card">
        <form id="selectionForm" class="space-y-6">
            @csrf
            <div>
                <label for="kode_hasil_tebang" class="form-label">
                    Pilih Kode Hasil Tebang
                </label>
                <select id="kode_hasil_tebang" name="id">
                    <option value="">-- Pilih Kode Hasil Tebang --</option>
                    @foreach($allHasilTebang as $hasil)
                        <option value="{{ $hasil->id }}">
                            {{ $hasil->kode_hasil_tebang }} |
                            {{ $hasil->vendorTebang->nama_vendor ?? '-' }} |
                            {{ $hasil->vendorAngkut->nama_vendor ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-between pt-4 border-t border-gray-200">
                <a href="{{ route('hasil-tebang.index') }}" class="btn-secondary no-underline">
                    ← Kembali
                </a>
                <button type="button" onclick="submitForm()" class="btn-primary">
                    Lanjutkan ke Edit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function submitForm() {
    const id = document.getElementById('kode_hasil_tebang').value;
    if (id) {
        window.location.href = "{{ route('hasil-tebang.edit.form', '') }}/" + id;
    } else {
        alert('Silakan pilih kode hasil tebang terlebih dahulu');
    }
}
</script>
@endsection
