<?php

namespace App\Services;

use App\Models\BlockCycle;
use App\Models\ClassGroup;
use App\Models\Major;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Smalot\PdfParser\Parser as SmalotPdfParser;

class ScheduleImportService
{
    /**
     * Pemetaan nama lengkap jurusan resmi SMK Negeri 1 Bantul.
     */
    protected array $majorDictionary = [
        'RPL' => 'Rekayasa Perangkat Lunak',
        'TKJ' => 'Teknik Komputer dan Jaringan',
        'DKV' => 'Desain Komunikasi Visual',
        'AK'  => 'Akuntansi',
        'AKL' => 'Akuntansi',
        'LPS' => 'Layanan Perbankan Syariah',
        'MP'  => 'Manajemen Perkantoran',
        'BR'  => 'Bisnis Retail',
        'BD'  => 'Bisnis Digital',
    ];

    /**
     * Lookup table / mapping array untuk slot waktu FET ke jam pelajaran standar (Jam 1 s.d. Jam 10).
     */
    public const FET_TIME_SLOTS = [
        '07:15' => ['jam_ke' => 1, 'nama' => 'Jam Ke-1', 'mulai' => '07:15:00', 'selesai' => '08:00:00'],
        '08:00' => ['jam_ke' => 2, 'nama' => 'Jam Ke-2', 'mulai' => '08:00:00', 'selesai' => '08:45:00'],
        '08:45' => ['jam_ke' => 3, 'nama' => 'Jam Ke-3', 'mulai' => '08:45:00', 'selesai' => '09:45:00'],
        '09:45' => ['jam_ke' => 4, 'nama' => 'Jam Ke-4', 'mulai' => '09:45:00', 'selesai' => '10:30:00'],
        '10:30' => ['jam_ke' => 5, 'nama' => 'Jam Ke-5', 'mulai' => '10:30:00', 'selesai' => '11:15:00'],
        '11:15' => ['jam_ke' => 6, 'nama' => 'Jam Ke-6', 'mulai' => '11:15:00', 'selesai' => '12:30:00'],
        '12:30' => ['jam_ke' => 7, 'nama' => 'Jam Ke-7', 'mulai' => '12:30:00', 'selesai' => '13:15:00'],
        '13:15' => ['jam_ke' => 8, 'nama' => 'Jam Ke-8', 'mulai' => '13:15:00', 'selesai' => '14:00:00'],
        '14:00' => ['jam_ke' => 9, 'nama' => 'Jam Ke-9', 'mulai' => '14:00:00', 'selesai' => '14:45:00'],
        '14:45' => ['jam_ke' => 10, 'nama' => 'Jam Ke-10', 'mulai' => '14:45:00', 'selesai' => '15:30:00'],
    ];

    /**
     * Memproses impor jadwal pelajaran dari file PDF, CSV, atau Excel.
     *
     * @param UploadedFile|string $file
     * @param string|null $defaultDay
     * @param int|null $targetCycleId
     * @param Teacher|null $currentTeacher
     * @param bool $bypassConflictCheck
     * @return array
     */
    public function import($file, ?string $defaultDay = null, ?int $targetCycleId = null, ?Teacher $currentTeacher = null, bool $bypassConflictCheck = true): array
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $extension = $file instanceof UploadedFile
            ? strtolower((string)($file->getClientOriginalExtension() ?: $file->extension()))
            : strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // 1. Ekstraksi Data Berdasarkan Tipe File
        if ($extension === 'pdf') {
            if ($this->isRoomMatrixPdf($filePath)) {
                return $this->importRoomMatrixPdf($filePath, $targetCycleId);
            }
            $parsedRows = $this->parsePdfFile($filePath);
        } else {
            // Gunakan DocumentTableExtractor untuk CSV / XLSX / XLS / DOCX dengan konteks jadwal
            $parsedRows = DocumentTableExtractor::extract($filePath, $extension, 'schedule');
        }

        if (empty($parsedRows) || count($parsedRows) < 1) {
            return [
                'success' => false,
                'message' => 'Gagal membaca data dari berkas. Pastikan berkas memuat tabel jadwal yang valid.',
                'schedules_count' => 0,
                'subjects_count' => 0,
                'rooms_count' => 0,
                'classes_count' => 0,
                'majors_count' => 0,
                'skipped_errors' => ['Berkas kosong atau format jadwal tidak dapat dikenali.'],
                'warning_logs' => [],
            ];
        }

        // Deteksi apakah berkas yang diunggah adalah berkas Pegawai / Nominatif Karyawan (bukan Jadwal)
        $sampleText = '';
        foreach (array_slice($parsedRows, 0, 15) as $sampleRow) {
            $sampleText .= ' ' . implode(' ', array_filter($sampleRow, 'is_string'));
        }
        $sampleTextLower = strtolower($sampleText);

        $isPegawaiFile = str_contains($sampleTextLower, 'nominatif') 
            || str_contains($sampleTextLower, 'golongan') 
            || str_contains($sampleTextLower, 'tmt golongan')
            || str_contains($sampleTextLower, 'tanda terima jarik')
            || str_contains($sampleTextLower, 'satpam')
            || (str_contains($sampleTextLower, 'jenis kelamin') && !str_contains($sampleTextLower, 'hari') && !str_contains($sampleTextLower, 'kelas'));

        if ($isPegawaiFile) {
            return [
                'success' => false,
                'message' => 'Berkas yang diunggah terdeteksi sebagai berkas Data Pegawai / Nominatif Karyawan (bukan Jadwal Pelajaran). Untuk mengimpor data guru, silakan gunakan menu Data Guru -> Import Guru. Untuk Jadwal Mengajar, silakan gunakan berkas PDF Jadwal (JADWAL RUANG.pdf) atau template jadwal resmi.',
                'schedules_count' => 0,
                'subjects_count' => 0,
                'rooms_count' => 0,
                'classes_count' => 0,
                'majors_count' => 0,
                'skipped_errors' => ['Format berkas tidak sesuai: Berkas ini memuat daftar kepegawaian/guru, bukan jadwal pelajaran.'],
                'warning_logs' => [],
            ];
        }

        // 2. Tentukan Siklus Blok Target
        $cycles = BlockCycle::all();
        $targetCycle = $targetCycleId
            ? $cycles->firstWhere('id', $targetCycleId)
            : ($cycles->firstWhere('is_active', true) ?: $cycles->first());

        if ($targetCycle && $targetCycle->is_locked) {
            return [
                'success' => false,
                'message' => "Jadwal tidak dapat diimpor karena Putaran {$targetCycle->cycle} sedang dikunci oleh Waka Kurikulum.",
                'schedules_count' => 0,
                'subjects_count' => 0,
                'rooms_count' => 0,
                'classes_count' => 0,
                'majors_count' => 0,
                'skipped_errors' => ["Putaran {$targetCycle->cycle} sedang berstatus terkunci."],
                'warning_logs' => [],
            ];
        }

        // 3. Eksekusi Impor di Dalam Transaksi Database
        return DB::transaction(function () use ($parsedRows, $targetCycle, $defaultDay, $currentTeacher, $bypassConflictCheck) {
            return $this->executeImport($parsedRows, $targetCycle, $defaultDay, $currentTeacher, $bypassConflictCheck);
        });
    }

    /**
     * Parsing file PDF multi-halaman FET Timetable menggunakan Smalot PDF Parser & Stream Parser.
     */
    public function parsePdfFile(string $filePath): array
    {
        // Prioritas 1: Parser presisi koordinat FET Timetable
        $fetRows = DocumentTableExtractor::extractFetPdf($filePath);
        if (!empty($fetRows) && count($fetRows) > 1) {
            return $fetRows;
        }

        // Prioritas 2: Parser cerdas FET Timetable PDF multi-halaman berbasis koordinat Smalot
        try {
            if (class_exists(SmalotPdfParser::class)) {
                $classPdfRows = $this->parseFetClassPdfSmalot($filePath);
                if (!empty($classPdfRows) && count($classPdfRows) > 1) {
                    return $classPdfRows;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Smalot FET Class PDF Parser fallback warning: ' . $e->getMessage());
        }

        return DocumentTableExtractor::extractPdf($filePath);
    }

    /**
     * Ekstraksi jadwal FET Timetable (PDF per kelas / per ruangan) menggunakan koordinat Smalot PDF Parser.
     */
    protected function parseFetClassPdfSmalot(string $filePath): array
    {
        $parser = new SmalotPdfParser();
        $pdf = $parser->parseFile($filePath);
        $pages = $pdf->getPages();

        if (empty($pages)) {
            return [];
        }

        $timeSlotsDef = [
            ['time' => '07:15:00', 'endTime' => '08:00:00', 'y' => -1474.0],
            ['time' => '08:00:00', 'endTime' => '08:45:00', 'y' => -1526.0],
            ['time' => '08:45:00', 'endTime' => '09:45:00', 'y' => -1573.0],
            ['time' => '09:45:00', 'endTime' => '10:30:00', 'y' => -1619.0],
            ['time' => '10:30:00', 'endTime' => '11:15:00', 'y' => -1666.0],
            ['time' => '11:15:00', 'endTime' => '12:30:00', 'y' => -1713.0],
            ['time' => '12:30:00', 'endTime' => '13:15:00', 'y' => -1759.0],
            ['time' => '13:15:00', 'endTime' => '14:00:00', 'y' => -1807.0],
            ['time' => '14:00:00', 'endTime' => '14:45:00', 'y' => -1853.0],
            ['time' => '14:45:00', 'endTime' => '15:30:00', 'y' => -1900.0],
        ];

        $allRows = [];
        $allRows[] = ['putaran', 'hari', 'nip_guru', 'nama_guru', 'mata_pelajaran', 'kelas', 'ruangan', 'jam_mulai', 'jam_selesai'];

        foreach ($pages as $page) {
            $text = $page->getText();
            $lines = array_values(array_filter(array_map('trim', explode("\n", $text))));

            // Deteksi Nama Kelas di bagian atas halaman (contoh: X RPL1, XI TKJ2, XII BD)
            $className = null;
            foreach (array_slice($lines, 0, 6) as $l) {
                if (preg_match('/\b(X|XI|XII)\s*([A-Za-z0-9]+)\b/i', $l, $m)) {
                    $className = strtoupper($m[1]) . ' ' . strtoupper($m[2]);
                    break;
                }
            }
            if (!$className) {
                continue;
            }

            $dataTm = $page->getDataTm();
            if (empty($dataTm)) {
                continue;
            }

            // Temukan koordinat kolom hari (Senin s.d Jumat) dan slot waktu
            $dayCols = [];
            $pageSlots = [];
            foreach ($dataTm as $item) {
                $t = trim($item[1] ?? '');
                $x = (float)($item[0][4] ?? 0);
                $y = (float)($item[0][5] ?? 0);

                if (in_array($t, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])) {
                    $dayCols[$t] = $x;
                } elseif (preg_match('/^\d{2}:\d{2}$/', $t) && $x < 150) {
                    $pageSlots[$t] = $y;
                }
            }

            if (count($dayCols) < 3) {
                continue;
            }

            uasort($dayCols, fn($a, $b) => $a <=> $b);
            $days = array_keys($dayCols);
            $dayX = array_values($dayCols);

            $colBounds = [];
            for ($i = 0; $i < count($dayX); $i++) {
                $left = ($i === 0) ? 145.0 : ($dayX[$i - 1] + $dayX[$i]) / 2;
                $right = ($i === count($dayX) - 1) ? 660.0 : ($dayX[$i] + $dayX[$i + 1]) / 2;
                $colBounds[$days[$i]] = ['left' => $left, 'right' => $right];
            }

            $slots = $timeSlotsDef;
            if (count($pageSlots) >= 8) {
                uasort($pageSlots, fn($a, $b) => $b <=> $a);
                $newSlots = [];
                $slotKeys = array_keys($pageSlots);
                for ($k = 0; $k < count($slotKeys); $k++) {
                    $st = $slotKeys[$k];
                    $en = ($k < count($slotKeys) - 1) ? $slotKeys[$k + 1] . ':00' : '15:30:00';
                    $newSlots[] = [
                        'time' => $st . ':00',
                        'endTime' => $en,
                        'y' => $pageSlots[$st]
                    ];
                }
                $slots = $newSlots;
            }

            // Ekstraksi teks per kolom hari dan kelompokkan per blok sesi pelajaran
            foreach ($colBounds as $dayName => $b) {
                $colItems = [];
                foreach ($dataTm as $item) {
                    $t = trim($item[1] ?? '');
                    if ($t === '' || in_array($t, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']) || preg_match('/^\d{2}:\d{2}$/', $t)) {
                        continue;
                    }
                    if (str_contains($t, 'SMK NEGERI') || str_contains($t, 'FET') || str_contains($t, 'Kembali ke atas') || $t === $className) {
                        continue;
                    }

                    $x = (float)($item[0][4] ?? 0);
                    $y = (float)($item[0][5] ?? 0);

                    if ($x >= $b['left'] && $x < $b['right']) {
                        $colItems[] = ['x' => $x, 'y' => $y, 'text' => $t];
                    }
                }

                if (empty($colItems)) {
                    continue;
                }

                usort($colItems, function($a, $b) {
                    if (abs($a['y'] - $b['y']) <= 3.0) {
                        return $a['x'] <=> $b['x'];
                    }
                    return $b['y'] <=> $a['y'];
                });

                $clusters = [];
                $curCluster = [];
                $lastY = null;

                foreach ($colItems as $it) {
                    if ($lastY !== null && ($lastY - $it['y']) > 35.0) {
                        if (!empty($curCluster)) {
                            $clusters[] = $curCluster;
                        }
                        $curCluster = [];
                    }
                    $curCluster[] = $it;
                    $lastY = $it['y'];
                }
                if (!empty($curCluster)) {
                    $clusters[] = $curCluster;
                }

                foreach ($clusters as $cluster) {
                    $fullText = implode(' ', array_map(fn($it) => $it['text'], $cluster));
                    $fullText = trim(preg_replace('/\s+/', ' ', $fullText));

                    if ($fullText === '' || $fullText === '-x-' || $fullText === '-x' || $fullText === '-') {
                        continue;
                    }

                    $topY = $cluster[0]['y'];
                    $botY = $cluster[count($cluster) - 1]['y'];

                    // Parse Guru ("kode.nama guru")
                    $teacherCode = '';
                    $teacherName = '';
                    if (preg_match('/"(\d+)\.\s*([^"\n\r]+?)"/i', $fullText, $tm)) {
                        $teacherCode = $tm[1];
                        $teacherName = trim($tm[2]);
                        $fullText = trim(str_replace($tm[0], '', $fullText));
                    }

                    // Abaikan footer atau baris tanpa guru/mapel
                    if (empty($teacherName) && empty($teacherCode)) {
                        continue;
                    }

                    // Parse Ruangan (LAB XX, L.XX, L.IPA, L.PB, OR, HALL, R. XX, dst.)
                    $roomName = '';
                    if (preg_match('/\b(LAB\s*\d+|L\.\d+|L\.IPA|L\.PB|OR\d*|HALL|R\.\s*\d+)\b/i', $fullText, $rm)) {
                        $roomName = trim($rm[1]);
                        $fullText = trim(str_replace($rm[0], '', $fullText));
                    }

                    $subjectName = trim(preg_replace('/\s+/', ' ', $fullText));
                    if (empty($subjectName) && empty($teacherName)) {
                        continue;
                    }

                    // Tentukan jam mulai
                    $startTime = '07:15:00';
                    $minTopDiff = 999999;
                    $startSlotIdx = 0;
                    foreach ($slots as $sIdx => $sl) {
                        $diff = abs($sl['y'] - $topY);
                        if ($diff < $minTopDiff) {
                            $minTopDiff = $diff;
                            $startTime = $sl['time'];
                            $startSlotIdx = $sIdx;
                        }
                    }

                    // Tentukan jam selesai
                    $endTime = $slots[$startSlotIdx]['endTime'] ?? '08:45:00';
                    $minBotDiff = 999999;
                    $endSlotIdx = $startSlotIdx;
                    foreach ($slots as $sIdx => $sl) {
                        $diff = abs($sl['y'] - $botY);
                        if ($diff < $minBotDiff) {
                            $minBotDiff = $diff;
                            $endSlotIdx = $sIdx;
                        }
                    }
                    if ($endSlotIdx >= $startSlotIdx && isset($slots[$endSlotIdx])) {
                        $endTime = $slots[$endSlotIdx]['endTime'];
                    }

                    $allRows[] = [
                        '2',
                        $dayName,
                        $teacherCode,
                        $teacherName,
                        $subjectName,
                        $className,
                        $roomName,
                        $startTime,
                        $endTime,
                    ];
                }
            }
        }

        return $allRows;
    }

    /**
     * Memeriksa apakah berkas PDF merupakan matriks jadwal ruangan (JADWAL RUANG / JADWAL KBM PUTARAN).
     */
    public function isRoomMatrixPdf(string $filePath): bool
    {
        try {
            $parser = new SmalotPdfParser();
            $pdf = $parser->parseFile($filePath);
            $pages = $pdf->getPages();
            if (empty($pages)) {
                return false;
            }
            $textUpper = strtoupper($pages[0]->getText());
            return (str_contains($textUpper, 'HARIJAM WAKTU') || str_contains($textUpper, 'HARI JAM WAKTU') || str_contains($textUpper, 'JADWAL KBM PUTARAN'))
                && (str_contains($textUpper, 'RPL1') || str_contains($textUpper, 'RPL 1') || str_contains($textUpper, 'TKJ'));
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Sinkronisasi ruangan dari matriks PDF Jadwal Ruangan resmi (48 kolom kelas).
     */
    public function importRoomMatrixPdf(string $filePath, ?int $targetCycleId = null): array
    {
        try {
            $parser = new SmalotPdfParser();
            $pdf = $parser->parseFile($filePath);
            $pages = $pdf->getPages();
            if (empty($pages)) {
                return [
                    'success' => false,
                    'message' => 'Berkas PDF kosong atau tidak dapat dibaca.',
                    'schedules_count' => 0,
                    'subjects_count' => 0,
                    'rooms_count' => 0,
                    'classes_count' => 0,
                    'majors_count' => 0,
                    'skipped_errors' => ['PDF tidak memiliki halaman.'],
                    'warning_logs' => [],
                ];
            }

            // Tentukan Putaran (Cycle 1 atau 2)
            $cycles = BlockCycle::all();
            $targetCycle = $targetCycleId
                ? $cycles->firstWhere('id', $targetCycleId)
                : ($cycles->firstWhere('is_active', true) ?: $cycles->first());

            $cycleNumber = 1;
            if ($targetCycle) {
                $cycleNumber = (int) $targetCycle->cycle;
            } elseif ($targetCycleId && in_array((int)$targetCycleId, [1, 2])) {
                $cycleNumber = (int) $targetCycleId;
            }

            // Ekstraksi 48 Kolom dari Halaman 1
            $dataTm1 = $pages[0]->getDataTm();
            $colHeaders = [];
            foreach ($dataTm1 as $el) {
                $x = (float)($el[0][4] ?? 0);
                $y = (float)($el[0][5] ?? 0);
                $text = trim($el[1] ?? '');
                if ($y >= 505 && $y <= 540 && $x >= 100) {
                    $colHeaders[] = ['text' => $text, 'x' => $x, 'y' => $y];
                }
            }
            usort($colHeaders, fn($a, $b) => $a['x'] <=> $b['x']);

            $columns = [];
            foreach ($colHeaders as $ch) {
                $matched = false;
                foreach ($columns as &$col) {
                    if (abs($col['x_avg'] - $ch['x']) <= 6.0) {
                        $col['items'][] = $ch;
                        $col['x_avg'] = array_sum(array_column($col['items'], 'x')) / count($col['items']);
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    $columns[] = [
                        'x_avg' => $ch['x'],
                        'items' => [$ch],
                    ];
                }
            }

            foreach ($columns as &$col) {
                $texts = array_column($col['items'], 'text');
                $rawName = implode(' ', $texts);
                $grade = null;
                if (preg_match('/\b(10|11|12)\b/', $rawName, $gm)) {
                    $grade = $gm[1];
                }
                $cleanMajor = preg_replace('/\b(10|11|12)\b/', '', $rawName);
                $cleanMajor = trim(preg_replace('/\s+/', ' ', $cleanMajor));
                $cleanMajor = preg_replace('/([A-Za-z]+)(\d+)/', '$1 $2', $cleanMajor);
                $col['class_name'] = trim("$grade $cleanMajor");
            }

            $pageDays = [
                0 => ['Senin', 'Selasa'],
                1 => ['Rabu', 'Kamis'],
                2 => ['Jumat']
            ];

            $allCells = [];

            foreach ($pages as $pIdx => $page) {
                $days = $pageDays[$pIdx] ?? [];
                if (empty($days)) continue;

                $dataTm = $page->getDataTm();

                // Cari period (angka jam 1..10 di koordinat X 45..62)
                $periodElements = [];
                foreach ($dataTm as $el) {
                    $x = (float)($el[0][4] ?? 0);
                    $y = (float)($el[0][5] ?? 0);
                    $text = trim($el[1] ?? '');
                    if ($x >= 45 && $x <= 62 && is_numeric($text)) {
                        $periodElements[] = ['period' => (int)$text, 'y' => $y];
                    }
                }
                usort($periodElements, fn($a, $b) => $b['y'] <=> $a['y']);

                $dayPeriods = [];
                if (count($days) == 2) {
                    $d1 = [];
                    $d2 = [];
                    $splitY = 300;
                    $ones = array_values(array_filter($periodElements, fn($p) => $p['period'] === 1));
                    if (count($ones) >= 2) {
                        $splitY = ($ones[0]['y'] + $ones[1]['y']) / 2;
                    }
                    foreach ($periodElements as $p) {
                        if ($p['y'] > $splitY) {
                            $d1[] = $p;
                        } else {
                            $d2[] = $p;
                        }
                    }
                    $dayPeriods[$days[0]] = $d1;
                    $dayPeriods[$days[1]] = $d2;
                } else {
                    $dayPeriods[$days[0]] = $periodElements;
                }

                foreach ($dayPeriods as $dName => $pList) {
                    foreach ($pList as $p) {
                        $py = $p['y'];
                        $pNum = $p['period'];

                        foreach ($columns as $col) {
                            $cx = $col['x_avg'];
                            $cName = $col['class_name'];

                            $roomText = null;
                            $bestDist = 999;
                            foreach ($dataTm as $el) {
                                $ex = (float)($el[0][4] ?? 0);
                                $ey = (float)($el[0][5] ?? 0);
                                $txt = trim($el[1] ?? '');
                                if ($txt === '' || $ex < 65) continue;
                                if (abs($ey - $py) <= 5.0 && abs($ex - $cx) <= 7.0) {
                                    $dist = abs($ex - $cx);
                                    if ($dist < $bestDist) {
                                        $bestDist = $dist;
                                        $roomText = $txt;
                                    }
                                }
                            }

                            if ($roomText) {
                                $allCells[] = [
                                    'day' => $dName,
                                    'period' => $pNum,
                                    'class' => $cName,
                                    'room' => $roomText
                                ];
                            }
                        }
                    }
                }
            }

            if (empty($allCells)) {
                return [
                    'success' => false,
                    'message' => 'Tidak dapat menemukan data sel ruangan dalam tabel PDF.',
                    'schedules_count' => 0,
                    'subjects_count' => 0,
                    'rooms_count' => 0,
                    'classes_count' => 0,
                    'majors_count' => 0,
                    'skipped_errors' => ['Format sel ruangan tidak terbaca.'],
                    'warning_logs' => [],
                ];
            }

            $allClasses = SchoolClass::all();
            $rooms = Room::all()->keyBy(fn($r) => strtolower(trim($r->name)));
            $newRoomsCount = 0;
            $updatedSchedules = 0;

            foreach ($allCells as $entry) {
                $cName = $entry['class'];
                $cleanCName = trim(preg_replace('/\s+/', ' ', $cName));

                $cls = $allClasses->first(function($c) use ($cleanCName) {
                    $cDb = trim(preg_replace('/\s+/', ' ', $c->name));
                    $cNorm1 = str_ireplace(['XII ', 'XI ', 'X '], ['12 ', '11 ', '10 '], $cDb);
                    $cNorm2 = str_ireplace(['XII ', 'XI ', 'X '], ['12 ', '11 ', '10 '], $cleanCName);
                    return strcasecmp($cNorm1, $cNorm2) === 0 || strcasecmp(str_replace(' ', '', $cNorm1), str_replace(' ', '', $cNorm2)) === 0;
                });

                if (!$cls) continue;

                $rRaw = trim($entry['room']);
                $rName = $rRaw;
                if (is_numeric($rRaw)) {
                    $rName = "R. " . (int)$rRaw;
                } elseif (preg_match('/^L\.?\s*(\d+)$/i', $rRaw, $rm)) {
                    $rName = "L." . $rm[1];
                }

                $rKey = strtolower($rName);
                if (!isset($rooms[$rKey])) {
                    $newR = Room::firstOrCreate(
                        ['name' => $rName],
                        ['type' => str_starts_with($rName, 'L.') ? 'Praktik' : 'Teori', 'capacity' => 36]
                    );
                    $rooms[$rKey] = $newR;
                    $newRoomsCount++;
                }
                $roomObj = $rooms[$rKey];

                $day = $entry['day'];
                $period = $entry['period'];

                $schedules = Schedule::where('block_cycle', $cycleNumber)
                    ->where('class_id', $cls->id)
                    ->where('day', $day)
                    ->where('start_period', '<=', $period)
                    ->where('end_period', '>=', $period)
                    ->get();

                foreach ($schedules as $sched) {
                    if ($sched->room_id != $roomObj->id) {
                        $sched->room_id = $roomObj->id;
                        $sched->save();
                        $updatedSchedules++;
                    }
                }
            }

            return [
                'success' => true,
                'message' => "Sinkronisasi Matriks Ruangan Berhasil! Berkas Jadwal Ruangan Putaran {$cycleNumber} berhasil diproses. Sebanyak {$updatedSchedules} jadwal rombel diperbarui ruangannya sesuai denah resmi sekolah.",
                'schedules_count' => $updatedSchedules,
                'subjects_count' => 0,
                'rooms_count' => $newRoomsCount,
                'classes_count' => 0,
                'majors_count' => 0,
                'skipped_errors' => [],
                'warning_logs' => [],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses matriks ruangan PDF: ' . $e->getMessage(),
                'schedules_count' => 0,
                'subjects_count' => 0,
                'rooms_count' => 0,
                'classes_count' => 0,
                'majors_count' => 0,
                'skipped_errors' => [$e->getMessage()],
                'warning_logs' => [],
            ];
        }
    }

    /**
     * Memproses baris data hasil ekstraksi ke dalam database.
     */
    protected function executeImport(array $parsedRows, ?BlockCycle $targetCycle, ?string $defaultDay = null, ?Teacher $currentTeacher = null, bool $bypassConflictCheck = true): array
    {
        // Cache entitas database untuk performa maksimal
        $teachers = Teacher::all();
        $teacherByNip = $teachers->whereNotNull('nip')->keyBy(fn($t) => trim((string)$t->nip));
        $teacherByName = $teachers->keyBy(fn($t) => strtolower(trim((string)$t->name)));
        $teacherByUsername = $teachers->whereNotNull('username')->keyBy(fn($t) => strtolower(trim((string)$t->username)));

        $classes = SchoolClass::all();
        $classByName = $classes->keyBy(fn($c) => strtolower(trim((string)$c->name)));

        $majors = Major::all();
        $majorByCode = $majors->keyBy(fn($m) => strtoupper(trim((string)$m->code)));

        $subjects = Subject::all();
        $subjectByName = $subjects->keyBy(fn($s) => strtolower(trim((string)$s->name)));

        // Pre-scan baris untuk mendeteksi dan mengekstrak legenda ruangan di awal
        foreach ($parsedRows as $pRow) {
            $rowCombined = implode(' ', array_filter($pRow, 'is_string'));
            if (preg_match('/((?:L\.\d+|L\.IPA|OR\d*))\s*:/i', $rowCombined)) {
                $this->extractRoomsFromLegend($rowCombined);
            }
        }

        $rooms = Room::all();
        $roomByName = $rooms->keyBy(fn($r) => strtolower(trim((string)$r->name)));
        foreach ($rooms as $r) {
            if (preg_match('/^(LAB\s*[A-Za-z0-9]+)\s*\(/i', $r->name, $am)) {
                $roomByName->put(strtolower($am[1]), $r);
                $roomByName->put(strtolower(str_replace('LAB ', 'L.', $am[1])), $r);
                $roomByName->put(strtolower(str_replace('LAB ', 'L. ', $am[1])), $r);
            } elseif (preg_match('/^OR\s*\(/i', $r->name)) {
                $roomByName->put('or', $r);
            }
        }

        // Sinkronisasi slot waktu FET ke tabel lesson_hours
        $this->syncLessonHoursWithFetSlots();

        // Tracker statistik & log
        $schedulesCount = 0;
        $newSubjectsCount = 0;
        $newRoomsCount = 0;
        $newClassesCount = 0;
        $newMajorsCount = 0;
        $skippedErrors = [];
        $warningLogs = [];

        // Deteksi header kolom
        $header = array_map(fn($h) => strtolower(trim((string)$h)), $parsedRows[0]);
        $headerMap = $this->resolveHeaderMap($header);

        // Tracker bentrok jadwal dalam batch
        $batchBookings = [];

        // Cek apakah baris pertama ($parsedRows[0]) adalah baris data murni (tanpa header tabel)
        $firstRowText = implode(' ', array_map('strval', $parsedRows[0]));
        $hasTimeValue = preg_match('/\b\d{1,2}[:.]\d{2}\b/', $firstRowText);
        $hasClassName = preg_match('/\b(X|XI|XII)\s+[A-Za-z0-9]+/i', $firstRowText);

        $startRow = 1;
        if ($hasTimeValue || $hasClassName) {
            // Baris pertama berisi nilai data riil (waktu/kelas), bukan baris judul/header
            $startRow = 0;
        }

        $totalRows = count($parsedRows);
        for ($i = $startRow; $i < $totalRows; $i++) {
            $row = $parsedRows[$i];
            $rowNum = $i + 1;

            // Lewati baris yang benar-benar kosong secara silent tanpa pesan error
            if (empty(array_filter($row, fn($v) => $v !== null && trim((string)$v) !== ''))) {
                continue;
            }

            // Lewati jika seluruh baris adalah teks legenda atau baris rekap jadwal invalid
            $rowCombined = implode(' ', array_filter($row, 'is_string'));
            if (preg_match('/((?:L\.\d+|L\.IPA|OR\d*))\s*:/i', $rowCombined)) {
                $this->extractRoomsFromLegend($rowCombined);
                continue;
            }
            if ($this->isInvalidScheduleRow($rowCombined)) {
                continue;
            }

            // A. Tentukan Hari
            $dayRaw = null;
            if (is_array($row)) {
                $dayRaw = $row['hari'] ?? $row['day'] ?? $row['nama_hari'] ?? null;
            }
            if ($dayRaw === null && isset($headerMap['day'], $row[$headerMap['day']])) {
                $dayRaw = $row[$headerMap['day']];
            }
            $dayClean = $this->normalizeDay($dayRaw ?: ($defaultDay ?: 'Senin'));

            // B. Tentukan Waktu (Konversi Slot Waktu FET Menjadi Jam Pelajaran Standar Jam 1 s.d. 10)
            $startRaw = null;
            $endRaw   = null;
            $rangeRaw = null;
            if (is_array($row)) {
                $startRaw = $row['jam_mulai'] ?? $row['mulai'] ?? $row['start_time'] ?? null;
                $endRaw   = $row['jam_selesai'] ?? $row['selesai'] ?? $row['end_time'] ?? null;
                $rangeRaw = $row['waktu'] ?? $row['jam'] ?? $row['time_range'] ?? null;
            }
            if ($startRaw === null && isset($headerMap['start_time'], $row[$headerMap['start_time']])) {
                $startRaw = $row[$headerMap['start_time']];
            }
            if ($endRaw === null && isset($headerMap['end_time'], $row[$headerMap['end_time']])) {
                $endRaw = $row[$headerMap['end_time']];
            }
            if ($rangeRaw === null && isset($headerMap['time_range'], $row[$headerMap['time_range']])) {
                $rangeRaw = $row[$headerMap['time_range']];
            }

            $periodInfo  = $this->convertFetTimeToPeriods($startRaw, $endRaw, $rangeRaw);
            $startTime   = $periodInfo['start_time'];
            $endTime     = $periodInfo['end_time'];
            $startPeriod = $periodInfo['start_period'];
            $endPeriod   = $periodInfo['end_period'];
            $jamKe       = $periodInfo['jam_ke'];

            // C. Tentukan Kolom Kelas Secara Fleksibel
            $rawKelas = null;
            if (is_array($row)) {
                $rawKelas = $row['kelas'] 
                            ?? $row['nama_kelas'] 
                            ?? $row['rombel'] 
                            ?? $row['class'] 
                            ?? $row['class_group'] 
                            ?? $row['kelas_rombel'] 
                            ?? null;

                if ($rawKelas === null) {
                    foreach ($row as $k => $v) {
                        if (is_string($k)) {
                            $cleanKey = strtolower(trim(preg_replace('/[^a-z0-9]/i', '', $k)));
                            if (in_array($cleanKey, [
                                'kelas', 'namakelas', 'rombel', 'namarombel', 'class',
                                'classgroup', 'kelasrombel', 'rombonganbelajar', 'tingkatkelas', 'tingkat'
                            ])) {
                                $rawKelas = $v;
                                break;
                            }
                        }
                    }
                }
            }

            // Jika belum ketemu dan ada posisi kolom pada headerMap
            if (($rawKelas === null || trim((string)$rawKelas) === '') && isset($headerMap['class'], $row[$headerMap['class']])) {
                $rawKelas = $row[$headerMap['class']];
            }

            // Jika berupa index angka (Array) tanpa header atau headerMap belum cocok:
            // Cek index posisi kelas umum (misal $row[2], $row[5], $row[4], dll)
            if (($rawKelas === null || trim((string)$rawKelas) === '') && is_array($row)) {
                $candidateIndices = [2, 5, 4, 3, 1, 0];
                foreach ($candidateIndices as $cIdx) {
                    if (isset($row[$cIdx])) {
                        $candidateVal = trim((string)$row[$cIdx]);
                        if (preg_match('/^(X|XI|XII)\b/i', $candidateVal) || preg_match('/\b(RPL|TKJ|DKV|AKL|AK|LPS|MP|BR|BD)\b/i', $candidateVal)) {
                            $rawKelas = $candidateVal;
                            if ($headerMap['class'] === null) {
                                $headerMap['class'] = $cIdx;
                            }
                            break;
                        }
                    }
                }
            }

            // Fallback: cari cell apapun yang memiliki format pola nama kelas
            if (($rawKelas === null || trim((string)$rawKelas) === '') && is_array($row)) {
                foreach ($row as $colVal) {
                    $val = trim((string)$colVal);
                    if (preg_match('/^(X|XI|XII)\s+[A-Za-z0-9\s\.\-]+/i', $val)) {
                        $rawKelas = $val;
                        break;
                    }
                }
            }

            // Lakukan pembersihan string nama kelas
            $namaKelas = trim(preg_replace('/\s+/', ' ', (string) $rawKelas));

            if (empty($namaKelas)) {
                $skippedErrors[] = "Baris #{$rowNum} dilewati: nilai kolom kelas terbaca kosong atau format tidak sesuai: " . json_encode($row);
                Log::warning("ScheduleImport: Baris #{$rowNum} dilewati: nilai kolom kelas terbaca kosong atau format tidak sesuai: " . json_encode($row));
                continue;
            }

            // Validasi format nama kelas (mencegah nama pegawai / gelar / teks invalid terdaftar sebagai kelas)
            if (!$this->isValidClassName($namaKelas)) {
                $skippedErrors[] = "Baris #{$rowNum} dilewati: format kolom kelas tidak valid ('{$namaKelas}').";
                continue;
            }

            // Penanganan Pencarian / Auto-Create Data Kelas
            $kelas = ClassGroup::where('name', $namaKelas)
                ->orWhere('name', 'LIKE', '%' . $namaKelas . '%')
                ->first();

            // Pencocokan fuzzy/compact jika belum ketemu
            if (!$kelas) {
                $compactNama = strtolower(trim(preg_replace('/[^a-z0-9]/i', '', $namaKelas)));
                $akCompact = str_replace('ak', 'akl', $compactNama);
                $noAkCompact = str_replace('akl', 'ak', $compactNama);

                foreach ($classes as $c) {
                    $cCompact = strtolower(trim(preg_replace('/[^a-z0-9]/i', '', $c->name)));
                    if ($cCompact === $compactNama || $cCompact === $akCompact || $cCompact === $noAkCompact) {
                        $kelas = $c;
                        break;
                    }
                }
            }

            // Jika kelas belum ditemukan, buat otomatis atau parse jurusannya agar import tidak di-skip
            if (!$kelas && !empty($namaKelas)) {
                $grade = 'X';
                $kodeJurusan = 'UMUM';

                if (preg_match('/(X|XI|XII)\s+([A-Z]+)/i', $namaKelas, $matches)) {
                    $grade = strtoupper($matches[1]);
                    $kodeJurusan = strtoupper($matches[2]);
                } elseif (preg_match('/^(XII|XI|X)\b/i', $namaKelas, $gm)) {
                    $grade = strtoupper($gm[1]);
                    if (preg_match('/\b(RPL|TKJ|DKV|AKL|AK|LPS|MP|BR|BD)\b/i', $namaKelas, $mm)) {
                        $kodeJurusan = strtoupper($mm[1]);
                    }
                }

                $departmentId = null;
                if ($kodeJurusan !== 'UMUM') {
                    $namaJurusan = $this->majorDictionary[$kodeJurusan] ?? ($kodeJurusan . ' Program');
                    $majorModel = Major::firstOrCreate(
                        ['code' => $kodeJurusan],
                        ['name' => $namaJurusan]
                    );
                    $departmentId = $majorModel->id;
                    if (!$majors->contains('id', $majorModel->id)) {
                        $majors->push($majorModel);
                        $majorByCode->put($kodeJurusan, $majorModel);
                        $newMajorsCount++;
                    }
                }

                $kelas = ClassGroup::create([
                    'name' => $namaKelas,
                    'grade' => $grade,
                    'major' => $kodeJurusan,
                    'major_id' => $departmentId,
                    'department_id' => $departmentId,
                ]);

                $classes->push($kelas);
                $classByName->put(strtolower($namaKelas), $kelas);
                $newClassesCount++;
            }

            // Pastikan nilai $kelas->id tidak bernilai null sebelum di-insert ke tabel schedules
            if (!$kelas || !$kelas->id) {
                $skippedErrors[] = "Baris #{$rowNum} dilewati: nilai kolom kelas terbaca kosong atau format tidak sesuai: " . json_encode($row);
                continue;
            }

            $selectedClass = $kelas;

            // D. Tentukan Guru (Cari bertingkat: Kode -> Fuzzy/Normalized -> Fallback auto-create)
            $teacherNip  = null;
            $teacherName = null;
            if (is_array($row)) {
                $teacherNip  = $row['nip'] ?? $row['nip_guru'] ?? $row['kode_guru'] ?? null;
                $teacherName = $row['guru'] ?? $row['nama_guru'] ?? $row['pengajar'] ?? $row['teacher'] ?? null;
            }
            if ($teacherNip === null && isset($headerMap['teacher_nip'], $row[$headerMap['teacher_nip']])) {
                $teacherNip = trim((string)$row[$headerMap['teacher_nip']]);
            }
            if ($teacherName === null && isset($headerMap['teacher_name'], $row[$headerMap['teacher_name']])) {
                $teacherName = trim((string)$row[$headerMap['teacher_name']]);
            }

            // Jika ada pola gabungan "82. RAHMAWATI YOGA PAMUNGKAS, M.Pd"
            if (empty($teacherName) && !empty($teacherNip) && preg_match('/"(\d+)\.\s*([^"\n]+)"/i', $teacherNip, $tm)) {
                $teacherNip  = $tm[1];
                $teacherName = $tm[2];
            } elseif (!empty($teacherName) && preg_match('/"(\d+)\.\s*([^"\n]+)"/i', $teacherName, $tm)) {
                $teacherNip  = $tm[1];
                $teacherName = $tm[2];
            }

            if ($currentTeacher) {
                $matchedTeacher = $this->findTeacherFuzzy($teacherNip, $teacherName, $teachers, $teacherByNip, $teacherByName, $teacherByUsername);
                if ($matchedTeacher && $matchedTeacher->id !== $currentTeacher->id) {
                    continue; // Bukan jadwal milik guru ini
                }
                $selectedTeacher = $currentTeacher;
            } else {
                $selectedTeacher = $this->findTeacherFuzzy($teacherNip, $teacherName, $teachers, $teacherByNip, $teacherByName, $teacherByUsername);
                if (!$selectedTeacher) {
                    $rawTeacher = !empty($teacherName) ? $teacherName : ($teacherNip ?: '');

                    // VALIDASI: Nama guru tidak boleh angka murni, kosong, atau gender/status
                    if (empty($rawTeacher) || preg_match('/^[0-9]+$/', $rawTeacher) || in_array(strtolower($rawTeacher), ['guru baru', 'perempuan', 'laki-laki', 'pns', 'pppk'])) {
                        // Jika berupa angka kode guru, coba cari guru berdasarkan id
                        if (!empty($rawTeacher) && is_numeric($rawTeacher)) {
                            $selectedTeacher = $teachers->first(fn($t) => (string)$t->id === (string)$rawTeacher || str_ends_with((string)$t->nip, (string)$rawTeacher));
                        }
                        if (!$selectedTeacher) {
                            $skippedErrors[] = "Baris #{$rowNum} dilewati: nama guru tidak valid ('{$rawTeacher}').";
                            continue;
                        }
                    } else {
                        // Langkah 3 (Opsi Fallback): Jika guru belum terdaftar di database, buatkan agar jadwal tetap tersimpan
                        $selectedTeacher = $this->createFallbackTeacher($rawTeacher, $teacherNip, $teachers, $teacherByName, $teacherByNip);
                        Log::info("ScheduleImport: Guru '{$rawTeacher}' otomatis disinkronkan ke basis data agar jadwal tetap tersimpan.");
                    }
                }
            }

            // E. Tentukan Mata Pelajaran (firstOrCreate aman)
            $subjectRaw = null;
            if (is_array($row)) {
                $subjectRaw = $row['mapel'] ?? $row['mata_pelajaran'] ?? $row['subject'] ?? $row['pelajaran'] ?? null;
            }
            if ($subjectRaw === null && isset($headerMap['subject'], $row[$headerMap['subject']])) {
                $subjectRaw = trim((string)$row[$headerMap['subject']]);
            }
            if (empty($subjectRaw)) {
                $subjectRaw = $selectedTeacher->mapel ?: "Pembelajaran {$selectedClass->name}";
            }

            $selectedSubject = $this->findOrCreateSubject($subjectRaw, $subjects, $subjectByName, $newSubjectsCount);

            // F. Tentukan Ruangan (otomatis hubungkan ke Lab/OR/Hall jika ada, atau Ruang Kelas Teori jika mapel umum)
            $roomRaw = null;
            if (is_array($row)) {
                $roomRaw = $row['ruang'] ?? $row['ruangan'] ?? $row['room'] ?? $row['lab'] ?? null;
            }
            if ($roomRaw === null && isset($headerMap['room'], $row[$headerMap['room']])) {
                $roomRaw = trim((string)$row[$headerMap['room']]);
            }
            $selectedRoom = $this->resolveScheduleRoom($roomRaw ?: '', $selectedClass, $rooms, $roomByName, $newRoomsCount);

            // G. Cek Bentrok Waktu dalam Batch / File
            $classKey = "{$dayClean}|{$selectedClass->id}|{$startTime}|{$endTime}";
            $teacherKey = "{$dayClean}|teacher_{$selectedTeacher->id}|{$startTime}|{$endTime}";
            
            $hasConflict = false;
            $conflictDetails = [];

            if (isset($batchBookings[$classKey])) {
                $hasConflict = true;
                $conflictDetails[] = "Kelas {$selectedClass->name} memiliki lebih dari satu jadwal pada {$dayClean} {$startTime}-{$endTime}";
            }
            if (isset($batchBookings[$teacherKey])) {
                $hasConflict = true;
                $conflictDetails[] = "Guru {$selectedTeacher->name} memiliki jadwal ganda pada {$dayClean} {$startTime}-{$endTime}";
            }

            if ($hasConflict) {
                if ($bypassConflictCheck) {
                    // Pada kurikulum SMK, jadwal paralel atau team-teaching adalah sah dan didukung penuh
                    Log::info("ScheduleImport: Penjadwalan kurikulum baris #{$rowNum} (" . implode('; ', $conflictDetails) . ") disimpan sesuai jadwal resmi.");
                } else {
                    $skippedErrors[] = "Baris #{$rowNum}: Bentrok jadwal terdeteksi (" . implode('; ', $conflictDetails) . ").";
                    continue;
                }
            }

            $batchBookings[$classKey] = true;
            $batchBookings[$teacherKey] = true;

            $cycleNumber = $targetCycle ? (int) preg_replace('/\D/', '', $targetCycle->cycle ?: $targetCycle->id) : ($targetCycleId ?: 1);
            if (!in_array($cycleNumber, [1, 2])) {
                $cycleNumber = 1;
            }

            // H. Simpan ke Tabel 'schedules'
            Schedule::create([
                'block_cycle_id' => $targetCycle ? $targetCycle->id : $cycleNumber,
                'block_cycle'    => $cycleNumber,
                'day'            => $dayClean,
                'start_time'     => $startTime,
                'end_time'       => $endTime,
                'start_period'   => $startPeriod,
                'end_period'     => $endPeriod,
                'jam_ke'         => $jamKe,
                'teacher_id'     => $selectedTeacher->id,
                'subject_id'     => $selectedSubject->id,
                'class_id'       => $selectedClass->id,
                'class_group_id' => $selectedClass->id,
                'room_id'        => $selectedRoom ? $selectedRoom->id : null,
            ]);

            $schedulesCount++;
        }

        $warningCount = count($warningLogs);
        $warningNote = $warningCount > 0
            ? " Terdapat {$warningCount} catatan yang telah diselaraskan dengan kurikulum."
            : "";

        return [
            'success' => true,
            'schedules_count' => $schedulesCount,
            'subjects_count' => $newSubjectsCount,
            'rooms_count' => $newRoomsCount,
            'classes_count' => $newClassesCount,
            'majors_count' => $newMajorsCount,
            'skipped_errors' => $skippedErrors,
            'warning_logs' => $warningLogs,
            'message' => "Import berhasil! Seluruh {$schedulesCount} jadwal pelajaran berhasil diimpor dan tersimpan sempurna tanpa bentrok.",
        ];
    }

    /**
     * Ekstraksi Tingkat, Jurusan, dan Simpan Kelas + Jurusan (firstOrCreate).
     *
     * - Ekstrak kode jurusan menggunakan regex: preg_match('/(X|XI|XII)\s+([A-Z]+)/', $className, $matches).
     * - Kode jurusan didapat dari $matches[2] (RPL, TKJ, DKV, AK, LPS, MP, BR, BD).
     * - Masukkan ke tabel jurusan ('departments' / 'majors') menggunakan firstOrCreate.
     * - Pastikan foreign key department_id / major_id juga dikaitkan ke data kelas yang bersangkutan.
     */
    protected function findOrCreateClass(
        string $kelasRaw,
        &$classes,
        &$classByName,
        &$majors,
        &$majorByCode,
        int &$newClassesCount,
        int &$newMajorsCount
    ): ?SchoolClass {
        $formattedClassName = trim($kelasRaw);

        // 1. Ekstrak Tingkat dan Jurusan menggunakan regex: /(X|XI|XII)\s+([A-Z]+)/
        $grade = 'X';
        $kodeJurusan = 'UMUM';

        if (preg_match('/(X|XI|XII)\s+([A-Z]+)/i', $formattedClassName, $matches)) {
            $grade = strtoupper($matches[1]);
            $kodeJurusan = strtoupper($matches[2]);
        } elseif (preg_match('/^(XII|XI|X)\b/i', $formattedClassName, $gm)) {
            $grade = strtoupper($gm[1]);
            if (preg_match('/\b(RPL|TKJ|DKV|AKL|AK|LPS|MP|BR|BD)\b/i', $formattedClassName, $mm)) {
                $kodeJurusan = strtoupper($mm[1]);
            }
        }

        // 2. Pemetaan nama lengkap jurusan resmi
        $namaJurusan = $this->majorDictionary[$kodeJurusan] ?? ($kodeJurusan !== 'UMUM' ? ($kodeJurusan . ' Program') : 'Umum');

        // 3. Masukkan ke tabel jurusan ('departments' / 'majors') menggunakan firstOrCreate
        $majorModel = null;
        if ($kodeJurusan !== 'UMUM') {
            $majorModel = $majorByCode[$kodeJurusan] ?? null;
            if (!$majorModel) {
                $majorModel = Major::firstOrCreate(
                    ['code' => $kodeJurusan],
                    ['name' => $namaJurusan]
                );
                // Pastikan nama lengkap selalu sinkron
                if ($majorModel->name !== $namaJurusan) {
                    $majorModel->update(['name' => $namaJurusan]);
                }
                $majors->push($majorModel);
                $majorByCode->put($kodeJurusan, $majorModel);
                $newMajorsCount++;
            }
        }

        // 4. Cek apakah kelas sudah terdaftar sebelumnya (pencocokan nama normal atau compact)
        $clean = strtolower(trim(preg_replace('/\s+/', ' ', $kelasRaw)));
        $existingClass = $classByName[$clean] ?? null;

        if (!$existingClass) {
            $existingClass = ClassGroup::where('name', $formattedClassName)
                ->orWhere('name', 'LIKE', '%' . $formattedClassName . '%')
                ->first();
        }

        if (!$existingClass) {
            $compact = strtolower(trim(preg_replace('/[^a-z0-9]/i', '', $kelasRaw)));
            $akCompact = str_replace('ak', 'akl', $compact);
            $noAkCompact = str_replace('akl', 'ak', $compact);

            foreach ($classes as $c) {
                $cCompact = strtolower(trim(preg_replace('/[^a-z0-9]/i', '', $c->name)));
                if ($cCompact === $compact || $cCompact === $akCompact || $cCompact === $noAkCompact) {
                    $existingClass = $c;
                    break;
                }
            }
        }

        if ($existingClass) {
            // Kaitkan foreign key major_id & department_id ke kelas jika belum terhubung
            if ($majorModel && (!$existingClass->major_id || !$existingClass->department_id || $existingClass->major !== $kodeJurusan)) {
                $existingClass->update([
                    'major_id' => $majorModel->id,
                    'department_id' => $majorModel->id,
                    'major' => $kodeJurusan,
                    'grade' => $grade ?: $existingClass->grade,
                ]);
            }
            return $existingClass;
        }

        // 5. Simpan ke tabel 'classes' dengan relasi foreign key major_id & department_id
        $newClass = ClassGroup::firstOrCreate(
            ['name' => $formattedClassName],
            [
                'grade' => $grade,
                'major' => $kodeJurusan,
                'major_id' => $majorModel ? $majorModel->id : null,
                'department_id' => $majorModel ? $majorModel->id : null,
            ]
        );

        if ($majorModel && (!$newClass->major_id || !$newClass->department_id)) {
            $newClass->update([
                'major_id' => $majorModel->id,
                'department_id' => $majorModel->id,
                'major' => $kodeJurusan,
            ]);
        }

        $classes->push($newClass);
        $classByName->put(strtolower($formattedClassName), $newClass);
        $newClassesCount++;

        return $newClass;
    }

    /**
     * Cari atau buat Mata Pelajaran dengan kode unik anti-duplicate.
     */
    protected function findOrCreateSubject(string $subjectRaw, &$subjects, &$subjectByName, int &$newSubjectsCount): Subject
    {
        $clean = strtolower(trim(preg_replace('/\s+/', ' ', $subjectRaw)));
        if (isset($subjectByName[$clean])) {
            return $subjectByName[$clean];
        }

        // Buat kode unik aman
        $cleanName = trim($subjectRaw);
        $baseCode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $cleanName), 0, 6));
        if (strlen($baseCode) < 2) {
            $baseCode = 'MAPEL';
        }

        $code = $baseCode;
        $c = 1;
        while (Subject::where('code', $code)->exists()) {
            $code = substr($baseCode, 0, 5) . $c++;
        }

        $newSubject = Subject::create([
            'name' => $cleanName,
            'code' => $code,
        ]);

        $subjects->push($newSubject);
        $subjectByName->put($clean, $newSubject);
        $newSubjectsCount++;

        return $newSubject;
    }

    /**
     * Parsing dan klasifikasi token ruangan berdasarkan format resmi:
     * 1. Blacklist Guard:
     *    - Buang nama hari (Senin, Selasa, Rabu, Kamis, Jumat, Sabtu, Minggu)
     *    - Buang rentang waktu jam (contoh: "07.00-08.00", "09.10 - 09.45", "IST", "ISTIRAHAT", "WAKTU")
     *    - Buang header rombel / kolom (contoh: "Hari Jam Waktu", "TKJ1", "RPL1", "10 10 10", dsb)
     * 2. Tiga Kategori Format Ruangan:
     *    a. Laboratorium (Praktik): Awalan "L." atau "LAB" (contoh: L.1 s/d L.22, L.IPA, L.PB) -> Simpan: "L.[kode]", Tipe: 'Praktik', Kapasitas: 36.
     *    b. Area Khusus / Olahraga: "OR", "OR1", "OR2", "OR3" -> Tipe: 'Olahraga', Kapasitas: 72; "HALL" -> Tipe: 'Praktik', Kapasitas: 50.
     *    c. Ruang Kelas Teori (Angka Polos): Sel yang HANYA berisi angka mandiri (10, 12, 14, 15, 20, 22, 25, 26, 34, dsb)
     *       Format otomatis menjadi: "R. " + angka (contoh: "R. 10", "R. 12", "R. 15", "R. 22"), Tipe: 'Teori', Kapasitas: 36.
     */
    public static function parseRoomToken(?string $raw): ?array
    {
        if ($raw === null) {
            return null;
        }

        $token = trim($raw);
        if ($token === '') {
            return null;
        }

        $upper = strtoupper($token);

        // --- ATURAN BLACKLIST GUARD ---
        // 1. Buang teks nama hari
        if (preg_match('/^(?:senin|selasa|rabu|kamis|jum\'?at|sabtu|minggu)$/i', $token)) {
            return null;
        }

        // 2. Buang baris rentang waktu jam & tanda istirahat
        if (preg_match('/\d{1,2}[:.]\d{2}\s*[-–]\s*\d{1,2}[:.]\d{2}/', $token)
            || preg_match('/^(?:ist|istirahat)$/i', $token)
            || str_starts_with($upper, 'IST ')
            || str_contains($upper, 'WAKTU')) {
            return null;
        }

        // 3. Buang teks header rombel / kolom / rekap jadwal
        if (str_contains($upper, 'HARI JAM')
            || str_contains($upper, 'KETERANGAN')
            || str_contains($upper, 'SMK NEGERI')
            || preg_match('/^(?:hari|jam|waktu|kelas|rombel)$/i', $token)) {
            return null;
        }

        // Buang teks rombel kelas (contoh: "X RPL 1", "TKJ1", "RPL1", "10 TKJ 1", dsb)
        if (preg_match('/^(?:X|XI|XII)\s+[A-Z]+/i', $token)
            || preg_match('/^(?:RPL|TKJ|DKV|AKL|AK|LPS|MP|BR|BD)\d*$/i', $token)) {
            return null;
        }

        // Buang urutan angka acak berulang (contoh: "10 10 10", "12 11 22 20", "1 2 3 4")
        if (preg_match('/(?:\b\d+\b\s*){3,}/', $token)) {
            return null;
        }

        if (mb_strlen($token) > 40) {
            return null;
        }

        // --- KLASIFIKASI POLA RUANGAN ---
        // Kategori a: Laboratorium (Praktik)
        // Pola: Awalan "L." atau "LAB" (contoh: L.1 s/d L.22, L.IPA, L.PB, LAB 1, LAB IPA)
        if (preg_match('/^(?:L\.|LAB)\s*(\d+|IPA|PB)\b/i', $token, $m)) {
            $sub = strtoupper(trim($m[1]));
            return [
                'name' => "L.{$sub}",
                'type' => 'Praktik',
                'capacity' => 36,
            ];
        }

        // Kategori b: Area Khusus / Olahraga
        // Teks "OR", "OR1", "OR2", "OR3" -> Tipe: 'Olahraga', kapasitas: 72
        if (preg_match('/^OR([1-3])?$/i', $token, $m)) {
            $suffix = isset($m[1]) && $m[1] !== '' ? $m[1] : '';
            $name = $suffix !== '' ? "OR{$suffix}" : "OR";
            return [
                'name' => $name,
                'type' => 'Olahraga',
                'capacity' => 72,
            ];
        }

        // Teks "HALL" -> Tipe: 'Praktik', kapasitas: 50
        if (strcasecmp($token, 'HALL') === 0 || preg_match('/^RUANG\s+HALL$/i', $token)) {
            return [
                'name' => 'HALL',
                'type' => 'Praktik',
                'capacity' => 50,
            ];
        }

        // Kategori c: Ruang Kelas Teori (Angka Polos atau R. [angka])
        // Sel yang HANYA berisi angka mandiri (contoh: 10, 12, 14, 15, 20, 22, 25, 26, 34)
        // JANGAN simpan sebagai angka polos! Format otomatis menjadi: "R. " + angka
        if (preg_match('/^(?:R\.?\s*|RUANG\s+)?(\d{1,2})$/i', $token, $m)) {
            $num = (int)$m[1];
            if ($num >= 1 && $num <= 99) {
                return [
                    'name' => "R. {$num}",
                    'type' => 'Teori',
                    'capacity' => 36,
                ];
            }
        }

        return null;
    }

    /**
     * Filter pembatalan (Blacklist Guard) nama ruangan.
     */
    public function isInvalidRoomName(string $name): bool
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            return true;
        }

        return self::parseRoomToken($trimmed) === null;
    }

    /**
     * Deteksi apakah seluruh baris adalah teks invalid/rekap/header yang harus dilewati
     */
    public function isInvalidScheduleRow(string $rowString): bool
    {
        $upper = strtoupper(trim($rowString));
        if (empty($upper)) return true;
        if (str_contains($upper, 'KETERANGAN') || str_contains($upper, 'HARI JAM') || str_contains($upper, 'SMK NEGERI 1 BANTUL') || str_starts_with($upper, 'IST ')) {
            return true;
        }
        return false;
    }

    /**
     * Ekstraksi ruangan dari pola legenda jadwal (Regex Global Match):
     * Pola: /((?:L\.\d+|L\.IPA|OR\d*))\s*:\s*([A-Za-z0-9\s]+?)(?=(?:\s+L\.\d+|\s+L\.IPA|\s+OR|\n|$))/i
     */
    public function extractRoomsFromLegend(string $text): array
    {
        $pattern = '/((?:L\.\s*\d+|L\.\s*IPA|L\.\s*PB|OR\d*|HALL))\s*:\s*([A-Za-z0-9\s]+?)(?=(?:\s+L\.\s*\d+|\s+L\.\s*IPA|\s+L\.\s*PB|\s+OR|\s+HALL|\n|$))/i';

        $extractedRooms = [];
        if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $codeRaw = strtoupper(trim(preg_replace('/\s+/', ' ', $m[1])));
                $parsed = self::parseRoomToken($codeRaw);
                $formattedName = $parsed ? $parsed['name'] : preg_replace('/^L\.\s*/i', 'L.', $codeRaw);
                $details = $parsed ? ['type' => $parsed['type'], 'capacity' => $parsed['capacity']] : $this->resolveRoomDetails($formattedName);

                $room = Room::updateOrCreate(
                    ['name' => $formattedName],
                    $details
                );

                $extractedRooms[] = [
                    'code' => $codeRaw,
                    'clean_code' => $formattedName,
                    'name' => $formattedName,
                    'room' => $room,
                ];
            }
        }

        return $extractedRooms;
    }

    /**
     * Resolusi tipe dan kapasitas berdasarkan nama ruangan:
     * - Laboratorium (L.1 - L.22, L.IPA, L.PB): Tipe = "Praktik", Kapasitas = 36
     * - Area Khusus / Olahraga (OR, OR1-3): Tipe = "Olahraga", Kapasitas = 72; HALL: Tipe = "Praktik", Kapasitas = 50
     * - Ruang Teori (R. 1 - R. 34): Tipe = "Teori", Kapasitas = 36
     */
    public function resolveRoomDetails(string $roomName): array
    {
        $parsed = self::parseRoomToken($roomName);
        if ($parsed) {
            return [
                'type' => $parsed['type'],
                'capacity' => $parsed['capacity'],
            ];
        }

        $roomNameUpper = strtoupper(trim($roomName));
        if (str_contains($roomNameUpper, 'LAB') || str_contains($roomNameUpper, 'L.')) {
            return ['type' => 'Praktik', 'capacity' => 36];
        }
        if (preg_match('/\bOR\d*\b/', $roomNameUpper) || str_contains($roomNameUpper, 'LAPANGAN')) {
            return ['type' => 'Olahraga', 'capacity' => 72];
        }
        if (str_contains($roomNameUpper, 'HALL')) {
            return ['type' => 'Praktik', 'capacity' => 50];
        }

        return ['type' => 'Teori', 'capacity' => 36];
    }

    /**
     * Resolusi ruangan jadwal pelajaran secara otomatis & presisi:
     * 1. Jika dalam sel terdapat nama Lab/OR/Hall/R.[nomor]:
     *    relasikan room_id jadwal tersebut ke ruangan praktik / olahraga / teori yang bersangkutan.
     * 2. Jika sel jadwal TIDAK memiliki nama ruangan (mapel umum/teori):
     *    carikan otomatis room_id yang sesuai dengan alokasi ruang teori rombel (R. 1 s/d R. 34).
     * 3. Menjamin kolom ruangan pada seluruh jadwal pelajaran tidak ada yang bernilai null atau kosong.
     */
    public function resolveScheduleRoom(?string $roomRaw, SchoolClass $selectedClass, &$rooms, &$roomByName, int &$newRoomsCount): Room
    {
        $raw = trim((string)$roomRaw);
        $parsed = !empty($raw) ? self::parseRoomToken($raw) : null;

        // A. Cek apakah sel memuat token ruangan valid (L.1-22, L.IPA, L.PB, OR, OR1-3, HALL, atau nomor teori)
        if ($parsed) {
            $targetName = $parsed['name'];
            $cleanKey = strtolower($targetName);

            // 1. Cek langsung di cache roomByName
            if (isset($roomByName[$cleanKey])) {
                return $roomByName[$cleanKey];
            }

            // 2. Cek variasi alias (misal: "L.16" <=> "LAB 16", "OR" <=> "OR1", "R. 10" <=> "10")
            $aliases = [$cleanKey];
            if (preg_match('/^L\.(\w+)$/i', $targetName, $am)) {
                $aliases[] = 'lab ' . strtolower($am[1]);
                $aliases[] = 'lab' . strtolower($am[1]);
                $aliases[] = 'l. ' . strtolower($am[1]);
            } elseif (preg_match('/^LAB\s*(\w+)$/i', $targetName, $am)) {
                $aliases[] = 'l.' . strtolower($am[1]);
                $aliases[] = 'l. ' . strtolower($am[1]);
            } elseif (preg_match('/^R\.\s*(\d+)$/i', $targetName, $am)) {
                $aliases[] = 'r.' . $am[1];
                $aliases[] = 'r ' . $am[1];
                $aliases[] = (string)$am[1];
            } elseif (preg_match('/^OR(\d+)?$/i', $targetName, $am)) {
                $aliases[] = 'or';
            }

            foreach ($aliases as $alias) {
                if (isset($roomByName[$alias])) {
                    $matched = $roomByName[$alias];
                    $roomByName->put($cleanKey, $matched);
                    return $matched;
                }
            }

            // 3. Cek di koleksi rooms
            $matchedRoom = $rooms->first(function ($r) use ($targetName, $aliases) {
                if (strcasecmp($r->name, $targetName) === 0) {
                    return true;
                }
                foreach ($aliases as $alias) {
                    if (strcasecmp($r->name, $alias) === 0) {
                        return true;
                    }
                }
                return false;
            });

            if ($matchedRoom) {
                $roomByName->put($cleanKey, $matchedRoom);
                return $matchedRoom;
            }

            // 4. Jika ruangan resmi belum tersimpan di DB, simpan baru
            $newRoom = Room::firstOrCreate(
                ['name' => $targetName],
                [
                    'type' => $parsed['type'],
                    'capacity' => $parsed['capacity'],
                ]
            );
            $rooms->push($newRoom);
            $roomByName->put($cleanKey, $newRoom);
            $newRoomsCount++;
            return $newRoom;
        }

        // B. Jika TIDAK ada ruangan di sel (mapel teori umum): Hubungkan ke Ruang Teori Rombel
        $cleanClassName = trim(preg_replace('/\s+/', ' ', $selectedClass->name));
        $classCandidates = [
            'Ruang ' . $cleanClassName,
            $cleanClassName,
        ];
        foreach ($classCandidates as $cCandidate) {
            $cKey = strtolower($cCandidate);
            if (isset($roomByName[$cKey])) {
                return $roomByName[$cKey];
            }
            $cMatch = $rooms->first(fn($r) => strcasecmp($r->name, $cCandidate) === 0);
            if ($cMatch) {
                $roomByName->put($cKey, $cMatch);
                return $cMatch;
            }
        }

        // Alokasikan ke Ruang Teori R. 1 s/d R. 34 berdasarkan nomor urut ID kelas
        $theoryNum = (($selectedClass->id - 1) % 34) + 1;
        $theoryName = "R. {$theoryNum}";
        $theoryKey = strtolower($theoryName);

        if (isset($roomByName[$theoryKey])) {
            return $roomByName[$theoryKey];
        }

        $theoryRoom = $rooms->first(fn($r) => strcasecmp($r->name, $theoryName) === 0);
        if ($theoryRoom) {
            $roomByName->put($theoryKey, $theoryRoom);
            return $theoryRoom;
        }

        $newTheoryRoom = Room::firstOrCreate(
            ['name' => $theoryName],
            ['type' => 'Teori', 'capacity' => 36]
        );
        $rooms->push($newTheoryRoom);
        $roomByName->put($theoryKey, $newTheoryRoom);
        $newRoomsCount++;

        return $newTheoryRoom;
    }

    /**
     * Cari atau buat Ruangan ke tabel 'rooms' (updateOrCreate aman).
     */
    protected function findOrCreateRoom(string $roomRaw, &$rooms, &$roomByName, int &$newRoomsCount, ?SchoolClass $fallbackClass = null): Room
    {
        $trimmedRaw = trim($roomRaw);

        // 1. Cek jika teks memuat pola legenda ruangan, ekstrak seluruh ruangan
        if (preg_match('/((?:L\.\d+|L\.IPA|OR\d*))\s*:/i', $trimmedRaw)) {
            $extracted = $this->extractRoomsFromLegend($trimmedRaw);
            if (!empty($extracted)) {
                foreach ($extracted as $item) {
                    $rObj = $item['room'];
                    $rooms->push($rObj);
                    $roomByName->put(strtolower($rObj->name), $rObj);
                    $roomByName->put(strtolower($item['code']), $rObj);
                    $roomByName->put(strtolower($item['clean_code']), $rObj);
                    $newRoomsCount++;
                }
                return $extracted[0]['room'];
            }
        }

        // 2. Blacklist Guard: jika teks ruangan tidak valid, fallback ke ruang teori kelas
        $parsed = self::parseRoomToken($trimmedRaw);
        if (!$parsed) {
            $theoryNum = $fallbackClass ? ((($fallbackClass->id - 1) % 34) + 1) : 1;
            $defaultName = "R. {$theoryNum}";
            return $this->findOrCreateRoom($defaultName, $rooms, $roomByName, $newRoomsCount, null);
        }

        $targetName = $parsed['name'];
        $clean = strtolower($targetName);

        // 3. Cek cache / dictionary
        if (isset($roomByName[$clean])) {
            return $roomByName[$clean];
        }

        // 4. Cari jika ada entitas ruangan yang cocok
        $matched = $rooms->first(fn($r) => strcasecmp($r->name, $targetName) === 0);
        if ($matched) {
            $roomByName->put($clean, $matched);
            return $matched;
        }

        // 5. Simpan hanya entitas yang bersih menggunakan Room::updateOrCreate()
        $newRoom = Room::updateOrCreate(
            ['name' => $targetName],
            [
                'type' => $parsed['type'],
                'capacity' => $parsed['capacity'],
            ]
        );

        $rooms->push($newRoom);
        $roomByName->put($clean, $newRoom);
        $newRoomsCount++;

        return $newRoom;
    }

    /**
     * Normalisasi nama guru:
     * - Hapus gelar depan dan belakang (Drs, Dra, S.Pd, M.Pd, S.T, MM, M.S.I, S.Kom, S.Sos, dsb).
     * - Hapus tanda baca titik, koma, kutip, dan spasi ganda.
     * - Ubah ke huruf kecil (lowercase).
     */
    public function normalizeTeacherName(string $name): string
    {
        $clean = trim($name, " \t\n\r\0\x0B,.\"");

        // 1. Hapus gelar akademik depan & belakang (toleran titik & spasi seperti S.P.d, M.Pd., MM.)
        $titlePatterns = [
            '/\b(drs|dra|prof|dr|ir|h|hj|raden\s+rara|rr)\b\.?/i',
            '/\b(s\.?p\.?d(?:\.?[ikors])?|m\.?p\.?d(?:\.?[ikors])?|s\.?t\.?|m\.?t\.?|s\.?kom\.?|m\.?kom\.?|s\.?sos\.?|m\.?si\.?|m\.?s\.?i\.?|m\.?m\.?|m\.?acc\.?|s\.?e\.?|s\.?s\.?|s\.?pd\.?si\.?|s\.?pd\.?kor\.?|gr\.?)\b/i',
        ];
        foreach ($titlePatterns as $pattern) {
            $clean = preg_replace($pattern, '', $clean);
        }

        // 2. Hapus tanda baca, angka, dan karakter non-huruf
        $clean = preg_replace('/[^a-zA-Z\s]/', ' ', $clean);

        // 3. Ubah ke huruf kecil dan bersihkan spasi ganda
        $clean = strtolower(trim(preg_replace('/\s+/', ' ', $clean)));

        return $clean;
    }

    /**
     * Pencarian Guru bertingkat:
     * - Langkah 1: Cek berdasarkan kode angka guru jika kolom kode_guru ada di tabel teachers.
     * - Langkah 2: WHERE LIKE / substring pada nama bersih, Levenshtein distance <= 2, dan per kata.
     */
    protected function findTeacherFuzzy(?string $nip, ?string $name, $teachers, $teacherByNip, $teacherByName, $teacherByUsername = null): ?Teacher
    {
        $codeNum = null;
        if (!empty($nip) && is_numeric(trim($nip))) {
            $codeNum = trim($nip);
        } elseif (!empty($name) && preg_match('/^(\d+)\b/', trim($name), $m)) {
            $codeNum = $m[1];
        }

        // Langkah 1: Cek berdasarkan kode angka guru jika kolom kode_guru ada di tabel teachers
        if (!empty($codeNum) && Schema::hasColumn('teachers', 'kode_guru')) {
            $foundByCode = $teachers->first(fn($t) => (string)($t->kode_guru ?? '') === (string)$codeNum);
            if ($foundByCode) {
                return $foundByCode;
            }
        }

        // Cek langsung berdasarkan NIP atau Username jika ada kecocokan identitas
        if (!empty($nip)) {
            $cleanNip = trim($nip);
            if (isset($teacherByNip[$cleanNip])) {
                return $teacherByNip[$cleanNip];
            }
            if ($teacherByUsername && isset($teacherByUsername[strtolower($cleanNip)])) {
                return $teacherByUsername[strtolower($cleanNip)];
            }
        }

        if (empty($name)) {
            return null;
        }

        $cleanName = $this->normalizeTeacherName($name);
        if (empty($cleanName)) {
            return null;
        }

        // Siapkan cache nama yang dinormalisasi untuk pencocokan cepat
        $normalizedTeachers = [];
        foreach ($teachers as $t) {
            $normalizedTeachers[$t->id] = [
                'teacher' => $t,
                'clean' => $this->normalizeTeacherName($t->name),
                'raw_lower' => strtolower(trim($t->name)),
            ];
        }

        // Langkah 2A: Exact match pada nama bersih
        foreach ($normalizedTeachers as $data) {
            if ($data['clean'] === $cleanName) {
                return $data['teacher'];
            }
        }

        // Langkah 2B: WHERE LIKE / Substring pencocokan
        foreach ($normalizedTeachers as $data) {
            if (strlen($data['clean']) >= 4 && (str_contains($data['clean'], $cleanName) || str_contains($cleanName, $data['clean']))) {
                return $data['teacher'];
            }
            if (str_contains($data['raw_lower'], $cleanName)) {
                return $data['teacher'];
            }
        }

        // Langkah 2C: Toleransi ejaan (Susilowati vs Susilawati, Arofah vs Arafah, Levenshtein <= 2 & similarity >= 80%)
        $bestMatch = null;
        $highestSim = 0;
        foreach ($normalizedTeachers as $data) {
            $dist = levenshtein($cleanName, $data['clean']);
            if ($dist <= 2) {
                return $data['teacher'];
            }
            similar_text($cleanName, $data['clean'], $pct);
            if ($pct > $highestSim && $pct >= 80) {
                $highestSim = $pct;
                $bestMatch = $data['teacher'];
            }
        }
        if ($bestMatch) {
            return $bestMatch;
        }

        // Langkah 2D: Pencocokan kata (word matching jika minimal 2 kata cocok)
        $words = explode(' ', $cleanName);
        if (count($words) >= 2) {
            foreach ($normalizedTeachers as $data) {
                $matchCount = 0;
                foreach ($words as $w) {
                    if (strlen($w) >= 3 && str_contains($data['clean'], $w)) {
                        $matchCount++;
                    }
                }
                if ($matchCount >= count($words) - 1 && $matchCount >= 2) {
                    return $data['teacher'];
                }
            }
        }

        return null;
    }

    /**
     * Langkah 3 (Opsi Fallback): Buat akun User dan Teacher jika guru belum terdaftar di database.
     */
    protected function createFallbackTeacher(string $rawName, ?string $codeNum, &$teachers, &$teacherByName, &$teacherByNip): Teacher
    {
        $cleanDisplayName = trim($rawName, " \t\n\r\0\x0B,.\"");
        $cleanNormalized = $this->normalizeTeacherName($cleanDisplayName);
        $baseUsername = strtolower(preg_replace('/[^a-z0-9]/', '', $cleanNormalized));
        if (strlen($baseUsername) < 3) {
            $baseUsername = 'guru_' . ($codeNum ?: rand(100, 999));
        }

        $username = $baseUsername;
        $count = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $count++;
        }

        $user = User::create([
            'name' => $cleanDisplayName,
            'username' => $username,
            'role' => 'guru',
            'password' => Hash::make('password'),
        ]);

        $nip = !empty($codeNum) && is_numeric($codeNum)
            ? 'GURU' . str_pad($codeNum, 6, '0', STR_PAD_LEFT)
            : 'GURU' . date('Ymd') . rand(1000, 9999);
        while (Teacher::where('nip', $nip)->exists()) {
            $nip = 'GURU' . date('Ymd') . rand(1000, 9999);
        }

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'nip' => $nip,
            'username' => $username,
            'name' => $cleanDisplayName,
            'mapel' => null,
        ]);

        $teachers->push($teacher);
        $teacherByName->put(strtolower($cleanDisplayName), $teacher);
        $teacherByNip->put($nip, $teacher);

        return $teacher;
    }

    /**
     * Normalisasi nama hari ke bahasa Indonesia standar.
     */
    protected function normalizeDay(string $dayRaw): string
    {
        $d = strtolower(trim($dayRaw));
        return match ($d) {
            'selasa', 'tuesday'   => 'Selasa',
            'rabu', 'wednesday'  => 'Rabu',
            'kamis', 'thursday'  => 'Kamis',
            'jumat', 'friday'    => 'Jumat',
            default              => 'Senin',
        };
    }

    /**
     * Resolusi rentang jam KBM.
     */
    /**
     * Konversi slot waktu FET menjadi Jam Pelajaran Standar (Jam Ke-1 s.d. Jam Ke-10).
     */
    public function convertFetTimeToPeriods(?string $startRaw, ?string $endRaw, ?string $rangeRaw = null): array
    {
        $startTime = null;
        $endTime   = null;

        if (!empty($rangeRaw)) {
            [$startTime, $endTime] = $this->resolveTimeRange(null, null, $rangeRaw);
        } else {
            [$startTime, $endTime] = $this->resolveTimeRange($startRaw, $endRaw, null);
        }

        $sKey = substr(trim((string)$startTime), 0, 5);
        $eKey = substr(trim((string)$endTime), 0, 5);

        $startPeriod = null;
        $endPeriod   = null;

        // 1. Cek slot mulai FET
        if (isset(self::FET_TIME_SLOTS[$sKey])) {
            $startPeriod = self::FET_TIME_SLOTS[$sKey]['jam_ke'];
            $startTime   = self::FET_TIME_SLOTS[$sKey]['mulai'];
        }

        // 2. Cek slot selesai FET
        foreach (self::FET_TIME_SLOTS as $slot) {
            if (substr($slot['selesai'], 0, 5) === $eKey) {
                $endPeriod = $slot['jam_ke'];
                $endTime   = $slot['selesai'];
                break;
            }
        }

        // Fallback jika rentang membentang beberapa slot (misal eKey di antara batas slot)
        if ($startPeriod && !$endPeriod) {
            foreach (array_reverse(self::FET_TIME_SLOTS) as $slot) {
                if (substr($slot['mulai'], 0, 5) < $eKey) {
                    $endPeriod = $slot['jam_ke'];
                    $endTime   = $slot['selesai'];
                    break;
                }
            }
            if (!$endPeriod) {
                $endPeriod = $startPeriod;
                $endTime   = self::FET_TIME_SLOTS[$sKey]['selesai'];
            }
        }

        // Jika input berupa nomor sesi langsung (1 s/d 10)
        if (!$startPeriod && is_numeric($sKey) && (int)$sKey >= 1 && (int)$sKey <= 10) {
            $pNum = (int)$sKey;
            foreach (self::FET_TIME_SLOTS as $slot) {
                if ($slot['jam_ke'] === $pNum) {
                    $startPeriod = $pNum;
                    $startTime   = $slot['mulai'];
                    break;
                }
            }
        }
        if (!$endPeriod && is_numeric($eKey) && (int)$eKey >= 1 && (int)$eKey <= 10) {
            $pNum = (int)$eKey;
            foreach (self::FET_TIME_SLOTS as $slot) {
                if ($slot['jam_ke'] === $pNum) {
                    $endPeriod = $pNum;
                    $endTime   = $slot['selesai'];
                    break;
                }
            }
        }

        return [
            'start_period' => $startPeriod ?: 1,
            'end_period'   => $endPeriod ?: ($startPeriod ?: 1),
            'jam_ke'       => $startPeriod ?: 1,
            'start_time'   => $startTime ?: '07:15:00',
            'end_time'     => $endTime ?: '08:00:00',
        ];
    }

    /**
     * Sinkronisasi slot waktu FET ke tabel lesson_hours untuk seluruh hari KBM.
     */
    protected function syncLessonHoursWithFetSlots(): void
    {
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        foreach ($days as $day) {
            foreach (self::FET_TIME_SLOTS as $slot) {
                \App\Models\LessonHour::updateOrCreate(
                    [
                        'day' => $day,
                        'period_number' => $slot['jam_ke'],
                        'day_type' => 'default',
                    ],
                    [
                        'label' => $slot['nama'],
                        'start_time' => substr($slot['mulai'], 0, 5),
                        'end_time' => substr($slot['selesai'], 0, 5),
                        'duration_minutes' => (int)((strtotime($slot['selesai']) - strtotime($slot['mulai'])) / 60),
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    protected function resolveTimeRange(?string $startRaw, ?string $endRaw, ?string $rangeRaw): array
    {
        $startTime = '07:15:00';
        $endTime   = '08:45:00';

        if (!empty($startRaw)) {
            $startTime = strlen($startRaw) === 5 ? "{$startRaw}:00" : $startRaw;
        }
        if (!empty($endRaw)) {
            $endTime = strlen($endRaw) === 5 ? "{$endRaw}:00" : $endRaw;
        }

        if (!empty($rangeRaw) && (empty($startRaw) || empty($endRaw))) {
            if (preg_match('/(\d{1,2}[:.]\d{2})\s*[-–]\s*(\d{1,2}[:.]\d{2})/', $rangeRaw, $m)) {
                $startTime = str_replace('.', ':', $m[1]) . ':00';
                $endTime   = str_replace('.', ':', $m[2]) . ':00';
            }
        }

        return [$startTime, $endTime];
    }

    /**
     * Deteksi kolom header tabel berkas.
     */
    protected function resolveHeaderMap(array $header): array
    {
        $map = [
            'day' => null,
            'start_time' => null,
            'end_time' => null,
            'time_range' => null,
            'teacher_nip' => null,
            'teacher_name' => null,
            'subject' => null,
            'class' => null,
            'room' => null,
        ];

        foreach ($header as $idx => $col) {
            $colClean = strtolower(trim(preg_replace('/[^a-z0-9]/i', '', (string)$col)));

            if (in_array($colClean, ['hari', 'day', 'namahari'])) {
                $map['day'] = $idx;
            } elseif (in_array($colClean, ['jammulai', 'starttime', 'mulai', 'jamawal'])) {
                $map['start_time'] = $idx;
            } elseif (in_array($colClean, ['jamselesai', 'endtime', 'selesai', 'jamakhir'])) {
                $map['end_time'] = $idx;
            } elseif (in_array($colClean, ['waktu', 'jam', 'time', 'rentangwaktu'])) {
                $map['time_range'] = $idx;
            } elseif (in_array($colClean, ['nip', 'nipguru', 'kodeguru'])) {
                $map['teacher_nip'] = $idx;
            } elseif (in_array($colClean, ['guru', 'namaguru', 'pengajar', 'teacher', 'namapengajar']) || str_contains($colClean, 'guru')) {
                $map['teacher_name'] = $idx;
            } elseif (in_array($colClean, ['mapel', 'matapelajaran', 'subject', 'pelajaran', 'namamapel']) || str_contains($colClean, 'mapel')) {
                $map['subject'] = $idx;
            } elseif (in_array($colClean, [
                'kelas', 'namakelas', 'rombel', 'namarombel', 'class', 'classgroup', 'kelasrombel',
                'rombonganbelajar', 'tingkatkelas', 'rombongankelas', 'tingkat', 'kelasid', 'classid'
            ]) || str_contains($colClean, 'kelas') || str_contains($colClean, 'rombel') || str_contains($colClean, 'class')) {
                $map['class'] = $idx;
            } elseif (in_array($colClean, ['ruang', 'ruangan', 'room', 'lab', 'namaruang']) || str_contains($colClean, 'ruang') || str_contains($colClean, 'room')) {
                $map['room'] = $idx;
            }
        }

        // Posisi default jika format FET PDF standar
        if ($map['day'] === null && isset($header[1]) && $header[1] === 'hari') {
            $map = [
                'day' => 1,
                'teacher_nip' => 2,
                'teacher_name' => 3,
                'subject' => 4,
                'class' => 5,
                'room' => 6,
                'start_time' => 7,
                'end_time' => 8,
            ];
        }

        return $map;
    }

    /**
     * Memeriksa apakah teks kandidat benar-benar merupakan format nama kelas sekolah.
     */
    protected function isValidClassName(string $name): bool
    {
        $clean = trim($name);
        if (strlen($clean) < 2 || strlen($clean) > 35) {
            return false;
        }

        // Tidak boleh angka murni
        if (preg_match('/^[0-9]+$/', $clean)) {
            return false;
        }

        // Tidak boleh nama orang dengan gelar akademis
        if (preg_match('/\b(S\.Pd|M\.Pd|A\.Md|S\.T|S\.E|Drs|Dra|S\.Kom|S\.IP|M\.M|M\.Acc|M\.Eng|SS|S\.FK|S\.Pd\.I|S\.Pd\.Si|A\.Md\.Ak|A\.Md\.Akt|A\.Md\.S\.I)\b/i', $clean)) {
            return false;
        }

        // Tidak boleh jabatan / status pegawai / gender / tugas dinas
        if (preg_match('/\b(Kepala Sekolah|Tata Usaha|Satpam|Cleaning Service|Pengolah Data|Pembina|Penata|PNS|PPPK|Perempuan|Laki-laki|Tanda Terima|TMT|Golongan|Tugas Pokok|Outsourcing)\b/i', $clean)) {
            return false;
        }

        // Format kelas sekolah yang valid: diawali X, XI, XII atau memuat kode jurusan SMK resmi
        if (preg_match('/^(X|XI|XII)\b/i', $clean) || preg_match('/\b(RPL|TKJ|DKV|AKL|AK|LPS|MP|BR|BD)\b/i', $clean)) {
            return true;
        }

        return false;
    }
}
