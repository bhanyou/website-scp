@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Tambah Siklus / Putaran Blok</h1>
        <p class="page-subtitle">Form pembuatan periode putaran pembelajaran blok baru</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <form action="{{ route('block-cycles.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="cycle">Nama Siklus / Putaran</label>
            <input type="text" id="cycle" name="cycle" value="{{ old('cycle') }}" placeholder="Contoh: Putaran 1 / Blok Genap 2024" required>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="start_date">Tanggal Mulai</label>
                <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
            </div>

            <div class="form-group">
                <label for="end_date">Tanggal Selesai</label>
                <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required>
            </div>
        </div>

        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                Simpan Siklus
            </button>
            <a href="{{ route('block-cycles.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection