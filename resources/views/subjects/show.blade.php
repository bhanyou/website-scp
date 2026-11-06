@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Detail Mata Pelajaran</h1>
        <p class="page-subtitle">Informasi lengkap kode dan nama kurikulum</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <div class="detail-item">
        <strong>Kode Mata Pelajaran</strong>
        <p><span class="badge badge-info">{{ $subject->code }}</span></p>
    </div>

    <div class="detail-item">
        <strong>Nama Mata Pelajaran</strong>
        <p>{{ $subject->name }}</p>
    </div>

    <div class="detail-item">
        <strong>Kategori</strong>
        <p>
            @php $cat = $subject->category ?? 'Umum'; @endphp
            @if($cat === 'Kejuruan')
                <span class="badge" style="background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-weight: 600;">💻 Kejuruan</span>
            @elseif($cat === 'Olahraga')
                <span class="badge" style="background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-weight: 600;">⚽ Olahraga</span>
            @else
                <span class="badge" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-weight: 600;">📖 Umum</span>
            @endif
        </p>
    </div>

    <div class="detail-item">
        <strong>Beban Jam Pelajaran</strong>
        <p>{{ $subject->total_hours ?? 2 }} JP</p>
    </div>

    <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 1.5rem;">
        <a href="{{ route('subjects.edit', $subject->id) }}" class="btn btn-primary">
            Edit Mata Pelajaran
        </a>
        <a href="{{ route('subjects.index') }}" class="btn btn-secondary">
            ← Kembali ke Data Mata Pelajaran
        </a>
    </div>
</div>
@endsection
