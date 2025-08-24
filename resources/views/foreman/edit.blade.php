@extends('layouts.master')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/vendor-angkut.css') }}">
@endpush

@section('content')
<div class="vendor-container">
    <h2>Edit Data Mandor</h2>
    
    <form action="{{ route('foreman.update', $foreman->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="kode_mandor" class="form-label">Kode Mandor</label>
            <input type="text" id="kode_mandor" 
                   class="form-input bg-gray-100"
                   value="{{ $foreman->kode_mandor }}" readonly>
        </div>

        <div class="form-group">
            <label for="nama_mandor" class="form-label">Nama Mandor</label>
            <input type="text" name="nama_mandor" id="nama_mandor" 
                   class="form-input @error('nama_mandor') border-red-500 @enderror"
                   value="{{ old('nama_mandor', $foreman->nama_mandor) }}" required>
            @error('nama_mandor')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" 
                   class="form-input @error('email') border-red-500 @enderror"
                   value="{{ old('email', $foreman->email) }}" required>
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="no_hp" class="form-label">No HP</label>
            <input type="text" name="no_hp" id="no_hp" 
                   class="form-input @error('no_hp') border-red-500 @enderror"
                   value="{{ old('no_hp', $foreman->no_hp) }}" 
                   oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                   minlength="10"
                   maxlength="13"
                   pattern="[0-9]{10,13}"
                   title="Nomor HP harus terdiri dari 10-13 angka"
                   required>
            @error('no_hp')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
            <p class="text-sm text-gray-500 mt-1">Masukkan 10-13 digit nomor HP (hanya angka)</p>
        </div>

        <div class="form-group">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" 
                    class="form-select @error('status') border-red-500 @enderror" required>
                <option value="Aktif" {{ old('status', $foreman->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="Nonaktif" {{ old('status', $foreman->status) == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            @error('status')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Buttons -->
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('foreman.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
