<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\BlockCycle;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $rooms = Room::orderByRaw("FIELD(type, 'Praktik', 'Olahraga', 'Teori'), LENGTH(name), name ASC")
            ->paginate($perPage)
            ->withQueryString();

        return view('rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('rooms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:rooms,name'],
            'type' => ['nullable', 'string', 'in:Praktik,Olahraga,Teori'],
            'capacity' => ['nullable', 'integer', 'min:1'],
        ]);

        $parsed = \App\Services\ScheduleImportService::parseRoomToken($validated['name']);
        if ($parsed) {
            $validated['name'] = $parsed['name'];
            if (empty($validated['type'])) {
                $validated['type'] = $parsed['type'];
            }
            if (empty($validated['capacity'])) {
                $validated['capacity'] = $parsed['capacity'];
            }
        } elseif (empty($validated['type'])) {
            $details = app(\App\Services\ScheduleImportService::class)->resolveRoomDetails($validated['name']);
            $validated['type'] = $details['type'] ?? 'Teori';
        }

        Room::create($validated);

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Data ruangan berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $room = Room::findOrFail($id);

        return view('rooms.show', compact('room'));
    }

    public function edit(string $id)
    {
        $room = Room::findOrFail($id);

        return view('rooms.edit', compact('room'));
    }

    public function update(Request $request, string $id)
    {
        $room = Room::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rooms', 'name')->ignore($room->id),
            ],
            'type' => ['nullable', 'string', 'in:Praktik,Olahraga,Teori'],
            'capacity' => ['nullable', 'integer', 'min:1'],
        ]);

        $parsed = \App\Services\ScheduleImportService::parseRoomToken($validated['name']);
        if ($parsed) {
            $validated['name'] = $parsed['name'];
            if (empty($validated['type'])) {
                $validated['type'] = $parsed['type'];
            }
            if (empty($validated['capacity'])) {
                $validated['capacity'] = $parsed['capacity'];
            }
        } elseif (empty($validated['type'])) {
            $details = app(\App\Services\ScheduleImportService::class)->resolveRoomDetails($validated['name']);
            $validated['type'] = $details['type'] ?? $room->type ?? 'Teori';
        }

        $room->update($validated);

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Data ruangan berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $room = Room::findOrFail($id);

        $room->delete();

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Data ruangan berhasil dihapus.');
    }

    /**
     * Menghapus seluruh data ruangan.
     */
    public function destroyAll()
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'waka') {
            abort(403, 'Hanya Waka Kurikulum yang dapat menghapus seluruh data ruangan.');
        }

        $lockedCycleIds = BlockCycle::where('is_locked', true)->pluck('id');
        $lockedRoomIds = Schedule::whereIn('block_cycle_id', $lockedCycleIds)
            ->pluck('room_id')
            ->unique()
            ->filter();

        $countDeleted = 0;
        DB::transaction(function () use ($lockedRoomIds, &$countDeleted) {
            $query = Room::query();
            if ($lockedRoomIds->isNotEmpty()) {
                $query->whereNotIn('id', $lockedRoomIds);
            }
            $countDeleted = $query->delete();
        });

        $message = "Seluruh data ruangan berhasil dihapus ({$countDeleted} ruangan).";
        if ($lockedRoomIds->isNotEmpty()) {
            $message .= " Beberapa ruangan tidak dihapus karena masih digunakan pada jadwal siklus yang dikunci.";
        }

        return redirect()->route('rooms.index')->with('success', $message);
    }

    /**
     * Download format template CSV/Excel untuk import data ruangan.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_ruangan.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $output = fopen('php://output', 'w');

            // UTF-8 BOM untuk kompatibilitas langsung di Microsoft Excel Windows
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header kolom
            fputcsv($output, [
                'nama_ruangan',
                'tipe',
                'kapasitas',
            ]);

            // Baris contoh 1
            fputcsv($output, [
                'Lab Komputer 1',
                'Praktik',
                '36',
            ]);

            // Baris contoh 2
            fputcsv($output, [
                'R. Teori A',
                'Teori',
                '32',
            ]);

            // Baris contoh 3
            fputcsv($output, [
                'Lapangan Utama',
                'Olahraga',
                '72',
            ]);

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import data ruangan dari file Excel / CSV lokal.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
        ], [
            'file.required' => 'File CSV/Excel wajib dipilih.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $file = $request->file('file');
        $extension = strtolower(trim((string) $file->getClientOriginalExtension()));
        if (empty($extension)) {
            $extension = strtolower(trim((string) $file->extension()));
        }

        $filePath = $file->getRealPath();
        $allRows = \App\Services\DocumentTableExtractor::extract($filePath, $extension);

        if (empty($allRows)) {
            return redirect()
                ->route('rooms.index')
                ->with('error', 'Gagal membaca tabel data dari file yang diunggah. Pastikan file (.xlsx, .xls, .docx, .pdf, atau .csv) memuat baris data yang valid.');
        }

        // Cari baris header secara cerdas di antara 10 baris pertama
        $headerRowIndex = null;
        $headerMap = [];

        foreach (array_slice($allRows, 0, 10, true) as $rowIndex => $candidateRow) {
            $tempMap = [];
            foreach ($candidateRow as $colIdx => $col) {
                $clean = strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string)$col)));
                $clean = preg_replace('/[^a-z0-9]/', '', $clean);

                if (empty($clean)) {
                    continue;
                }

                if (!isset($tempMap['name']) && (str_contains($clean, 'ruang') || str_contains($clean, 'room') || str_contains($clean, 'nama') || str_contains($clean, 'lab'))) {
                    $tempMap['name'] = $colIdx;
                } elseif (!isset($tempMap['building']) && (str_contains($clean, 'gedung') || str_contains($clean, 'lokasi') || str_contains($clean, 'building'))) {
                    $tempMap['building'] = $colIdx;
                } elseif (!isset($tempMap['capacity']) && (str_contains($clean, 'kapasitas') || str_contains($clean, 'capacity') || str_contains($clean, 'kuota'))) {
                    $tempMap['capacity'] = $colIdx;
                }
            }

            if (isset($tempMap['name'])) {
                $headerRowIndex = $rowIndex;
                $headerMap = $tempMap;
                break;
            }
        }

        if ($headerRowIndex === null) {
            return redirect()
                ->route('rooms.index')
                ->with('error', 'Header kolom wajib (nama_ruangan) tidak ditemukan di dalam berkas. Pastikan berkas memuat kolom dengan judul Nama Ruangan.');
        }

        $allRows = array_slice($allRows, $headerRowIndex + 1);

        if (empty($allRows)) {
            return redirect()
                ->route('rooms.index')
                ->with('error', 'Berkas tidak memuat baris data ruangan yang dapat diproses.');
        }

        $createdCount = 0;
        $updatedCount = 0;
        $skippedRows = [];
        $rowNumber = 1;

        $scheduleService = app(\App\Services\ScheduleImportService::class);

        DB::beginTransaction();
        try {
            foreach ($allRows as $row) {
                $rowNumber++;

                // Lewati baris kosong
                if (empty(array_filter($row, fn($val) => trim((string)$val) !== ''))) {
                    continue;
                }

                $rowString = implode(' ', array_filter($row, fn($val) => is_string($val) && trim($val) !== ''));

                // 1. Ekstraksi Menggunakan Pola Legenda (Regex Global Match)
                if (preg_match('/((?:L\.\d+|L\.IPA|OR\d*))\s*:/i', $rowString)) {
                    $legendRooms = $scheduleService->extractRoomsFromLegend($rowString);
                    if (!empty($legendRooms)) {
                        $createdCount += count($legendRooms);
                    }
                    continue;
                }

                $name = isset($headerMap['name'], $row[$headerMap['name']]) ? trim((string)$row[$headerMap['name']]) : '';
                $rawCapacity = isset($headerMap['capacity'], $row[$headerMap['capacity']]) ? trim((string)$row[$headerMap['capacity']]) : null;

                if ($name === '') {
                    $skippedRows[] = "Baris #{$rowNumber}: Nama ruangan tidak boleh kosong.";
                    continue;
                }

                // 2. Filter Pembatalan (Blacklist Guard) & Parsing Format Standar
                $parsed = \App\Services\ScheduleImportService::parseRoomToken($name);
                if (!$parsed) {
                    $skippedRows[] = "Baris #{$rowNumber}: Format teks bukan entitas ruangan yang valid ('" . mb_substr($name, 0, 30) . "...').";
                    continue;
                }

                $formattedName = $parsed['name'];
                $finalType = $parsed['type'];
                $finalCapacity = $parsed['capacity'];

                if ($rawCapacity !== null && $rawCapacity !== '') {
                    $cleanCap = preg_replace('/[^0-9]/', '', $rawCapacity);
                    if ($cleanCap !== '') {
                        $finalCapacity = (int) $cleanCap;
                    }
                }

                // 3. Eksekusi Simpan: Simpan hanya entitas yang bersih menggunakan Room::updateOrCreate()
                $existing = Room::whereRaw('LOWER(name) = ?', [strtolower($formattedName)])->first();
                if ($existing) {
                    $existing->update([
                        'name' => $formattedName,
                        'type' => $existing->type ?: $finalType,
                        'capacity' => $finalCapacity !== null ? $finalCapacity : $existing->capacity,
                    ]);
                    $updatedCount++;
                } else {
                    Room::updateOrCreate(
                        ['name' => $formattedName],
                        [
                            'type' => $finalType,
                            'capacity' => $finalCapacity,
                        ]
                    );
                    $createdCount++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()
                ->route('rooms.index')
                ->with('error', 'Terjadi kesalahan saat memproses data: ' . $e->getMessage());
        }

        $message = "Import data ruangan berhasil. {$createdCount} ruangan baru ditambahkan";
        if ($updatedCount > 0) {
            $message .= ", {$updatedCount} ruangan diperbarui";
        }
        $message .= '.';

        if (!empty($skippedRows)) {
            $message .= ' Beberapa baris dilewati: ' . implode(', ', array_slice($skippedRows, 0, 3));
            if (count($skippedRows) > 3) {
                $message .= ' dan ' . (count($skippedRows) - 3) . ' baris lainnya.';
            }
        }

        return redirect()
            ->route('rooms.index')
            ->with('success', $message);
    }
}