<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlockCycle;
use Illuminate\Http\Request;

class BlockCycleApiController extends Controller
{
    /**
     * Memastikan pengguna yang mengakses API adalah Waka.
     */
    private function authorizeWaka(Request $request)
    {
        if ($request->user()->role !== 'waka') {
            return response()->json([
                'success' => false,
                'message' => 'Akses hanya diperbolehkan untuk Waka Kurikulum.',
            ], 403);
        }

        return null;
    }

    /**
     * GET /api/block-cycles
     *
     * Mengambil daftar siklus blok.
     */
    public function index(Request $request)
    {
        if ($response = $this->authorizeWaka($request)) {
            return $response;
        }

        $cycles = BlockCycle::with('lockedBy')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data siklus blok berhasil diambil.',
            'data' => $cycles->map(function ($cycle) {
                return [
                    'id' => $cycle->id,
                    'cycle' => $cycle->cycle,
                    'start_date' => $cycle->start_date,
                    'end_date' => $cycle->end_date,
                    'is_active' => (bool) $cycle->is_active,
                    'is_locked' => (bool) $cycle->is_locked,
                    'locked_by' => $cycle->locked_by,
                    'locked_at' => $cycle->locked_at,
                    'locked_by_user' => $cycle->lockedBy?->name,
                ];
            })->values(),
        ]);
    }

    /**
     * POST /api/block-cycles/{id}/toggle-active
     *
     * Mengaktifkan siklus yang dipilih
     * dan menonaktifkan siklus lainnya.
     */
    public function toggleActive(Request $request, string $id)
    {
        if ($response = $this->authorizeWaka($request)) {
            return $response;
        }

        $cycle = BlockCycle::find($id);

        if (!$cycle) {
            return response()->json([
                'success' => false,
                'message' => 'Siklus blok tidak ditemukan.',
            ], 404);
        }

        // Nonaktifkan semua siklus lain.
        BlockCycle::where('id', '!=', $cycle->id)
            ->update([
                'is_active' => false,
            ]);

        // Aktifkan siklus yang dipilih.
        $cycle->update([
            'is_active' => true,
        ]);

        $cycle->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Siklus aktif berhasil diubah.',
            'data' => [
                'id' => $cycle->id,
                'cycle' => $cycle->cycle,
                'is_active' => (bool) $cycle->is_active,
                'is_locked' => (bool) $cycle->is_locked,
            ],
        ]);
    }

    /**
     * POST /api/block-cycles/{id}/toggle-lock
     *
     * Mengunci atau membuka kunci siklus.
     */
    public function toggleLock(Request $request, string $id)
    {
        if ($response = $this->authorizeWaka($request)) {
            return $response;
        }

        $cycle = BlockCycle::find($id);

        if (!$cycle) {
            return response()->json([
                'success' => false,
                'message' => 'Siklus blok tidak ditemukan.',
            ], 404);
        }

        // Jika sudah terkunci, buka kunci.
        if ($cycle->is_locked) {
            $cycle->update([
                'is_locked' => false,
                'locked_by' => null,
                'locked_at' => null,
            ]);

            $cycle->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Putaran berhasil dibuka.',
                'data' => [
                    'id' => $cycle->id,
                    'cycle' => $cycle->cycle,
                    'is_locked' => false,
                    'locked_by' => null,
                    'locked_at' => null,
                ],
            ]);
        }

        // Jika belum terkunci, kunci siklus.
        $cycle->update([
            'is_locked' => true,
            'locked_by' => $request->user()->id,
            'locked_at' => now(),
        ]);

        $cycle->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Putaran berhasil dikunci.',
            'data' => [
                'id' => $cycle->id,
                'cycle' => $cycle->cycle,
                'is_locked' => true,
                'locked_by' => $cycle->locked_by,
                'locked_at' => $cycle->locked_at,
            ],
        ]);
    }
}