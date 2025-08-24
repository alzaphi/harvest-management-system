@extends('layouts.master')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
@endpush

@section('content')
<div class="vendor-container">
    <h2>Tambah Mandor Baru</h2>
    
    <form action="{{ route('foreman.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label for="kode_mandor" class="form-label">Kode Mandor</label>
            <input type="text" id="kode_mandor" 
                   class="form-input bg-gray-100"
                   value="{{ $nextKodeMandor }}" readonly>
        </div>

        <div class="form-group">
            <label for="nama_mandor" class="form-label">Nama Mandor</label>
            <input type="text" name="nama_mandor" id="nama_mandor" 
                   class="form-input @error('nama_mandor') border-red-500 @enderror"
                   value="{{ old('nama_mandor') }}" required>
            @error('nama_mandor')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" 
                   class="form-input @error('email') border-red-500 @enderror"
                   value="{{ old('email') }}" required>
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="no_hp" class="form-label">No HP</label>
            <input type="text" name="no_hp" id="no_hp" 
                   class="form-input @error('no_hp') border-red-500 @enderror"
                   value="{{ old('no_hp') }}" required>
            @error('no_hp')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" 
                    class="form-select @error('status') border-red-500 @enderror" required>
                <option value="Aktif" {{ old('status', 'Aktif') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="Nonaktif" {{ old('status') == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            @error('status')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Buttons -->
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('foreman.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
