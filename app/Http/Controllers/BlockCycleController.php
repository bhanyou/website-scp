<?php

namespace App\Http\Controllers;

use App\Models\BlockCycle;
use Illuminate\Http\Request;

class BlockCycleController extends Controller
{
    public function index()
    {
        $cycles = BlockCycle::with('lockedBy')
            ->withCount('schedules')
            ->orderBy('id', 'asc')
            ->take(2)
            ->get();

        return view('block-cycles.index', compact('cycles'));
    }

    public function create()
    {
        return redirect()
            ->route('block-cycles.index')
            ->with('info', 'Siklus blok telah ditetapkan tetap (hanya Putaran 1 dan Putaran 2).');
    }

    public function store(Request $request)
    {
        return redirect()
            ->route('block-cycles.index')
            ->with('error', 'Tidak dapat menambah siklus baru. Sistem hanya menggunakan Putaran 1 dan Putaran 2.');
    }

    public function show(string $id)
    {
        $cycle = BlockCycle::with(['lockedBy', 'schedules'])
            ->findOrFail($id);

        return view('block-cycles.show', compact('cycle'));
    }

    public function edit(string $id)
    {
        return redirect()
            ->route('block-cycles.index')
            ->with('info', 'Siklus blok telah ditetapkan tetap dan tidak dapat diedit.');
    }

    public function update(Request $request, string $id)
    {
        return redirect()
            ->route('block-cycles.index')
            ->with('error', 'Siklus blok telah ditetapkan tetap dan tidak dapat diubah.');
    }

    public function toggleActive(string $id)
    {
        $cycle = BlockCycle::findOrFail($id);

        // Nonaktifkan semua siklus lain
        BlockCycle::where('id', '!=', $cycle->id)
            ->update([
                'is_active' => false,
            ]);

        // Aktifkan siklus yang dipilih
        $cycle->update([
            'is_active' => true,
        ]);

        $cycleName = str_starts_with(strtolower($cycle->cycle), 'putaran') ? $cycle->cycle : 'Putaran ' . $cycle->cycle;

        return redirect()
            ->route('block-cycles.index')
            ->with('success', "{$cycleName} berhasil diaktifkan sebagai siklus pembelajaran.");
    }

    public function toggleLock(string $id)
    {
        $cycle = BlockCycle::findOrFail($id);

        if ($cycle->is_locked) {
            $cycle->update([
                'is_locked' => false,
                'locked_by' => null,
                'locked_at' => null,
            ]);

            return redirect()
                ->route('block-cycles.index')
                ->with('success', 'Status kunci jadwal putaran berhasil dibuka.');
        }

        $cycle->update([
            'is_locked' => true,
            'locked_by' => auth()->id(),
            'locked_at' => now(),
        ]);

        return redirect()
            ->route('block-cycles.index')
            ->with('success', 'Status jadwal putaran berhasil dikunci.');
    }

    public function destroy(string $id)
    {
        return redirect()
            ->route('block-cycles.index')
            ->with('error', 'Siklus blok tetap tidak dapat dihapus.');
    }
}