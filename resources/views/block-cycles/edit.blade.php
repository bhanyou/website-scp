@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Siklus Blok</h1>
        <p class="page-subtitle">Perbarui data nama siklus dan tanggal periode pembelajaran</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <form action="{{ route('block-cycles.update', $cycle->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="cycle">Nama Siklus / Putaran</label>
            <input type="text" id="cycle" name="cycle" value="{{ old('cycle', $cycle->cycle) }}" required>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="start_date">Tanggal Mulai</label>
                <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $cycle->start_date?->format('Y-m-d')) }}" required>
            </div>

            <div class="form-group">
                <label for="end_date">Tanggal Selesai</label>
                <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $cycle->end_date?->format('Y-m-d')) }}" required>
            </div>
        </div>

        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                Simpan Perubahan
            </button>
            <a href="{{ route('block-cycles.index') }}" class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection