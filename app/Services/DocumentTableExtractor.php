<?php

namespace App\Services;

use ZipArchive;
use DOMDocument;
use DOMXPath;

class DocumentTableExtractor
{
    /**
     * Mengekstrak baris dan kolom tabel dari file (.csv, .xlsx, .xls, .docx, .pdf, .txt, dll).
     * Mengembalikan array 2D: [ [col1, col2, ...], [col1, col2, ...], ... ]
     *
     * @param string $filePath Path file fisik di server
     * @param string $extension Ekstensi file (csv, xlsx, xls, docx, pdf, txt, excel, dll)
     * @return array
     */
    public static function extract(string $filePath, string $extension, ?string $context = null): array
    {
        $extension = strtolower(trim($extension));

        // Cek berdasarkan ekstensi eksplisit
        $rows = match ($extension) {
            'csv', 'txt' => self::extractCsv($filePath),
            'xlsx', 'xlsm', 'xltx' => self::extractXlsx($filePath, $context),
            'xls', 'excel' => self::extractXls($filePath),
            'docx' => self::extractDocx($filePath),
            'pdf' => self::extractPdf($filePath),
            'html', 'htm' => self::extractHtmlTable((string) file_get_contents($filePath)),
            default => [],
        };

        if (!empty($rows)) {
            return $rows;
        }

        // Auto-detection fallback berdasarkan magic bytes jika ekstensi tidak standar atau hasil kosong
        return self::autoDetectAndExtract($filePath);
    }

    /**
     * Ekstraksi dari file Excel modern (.xlsx / .xlsm)
     */
    public static function extractXlsx(string $filePath, ?string $context = null): array
    {
        if (!class_exists('ZipArchive')) {
            return self::extractCsv($filePath);
        }
        $rows = [];
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // 1. Baca shared strings (xl/sharedStrings.xml)
        $sharedStrings = [];
        $ssIndex = $zip->locateName('xl/sharedStrings.xml');
        if ($ssIndex === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                if (strtolower($zip->getNameIndex($i)) === 'xl/sharedstrings.xml') {
                    $ssIndex = $i;
                    break;
                }
            }
        }
        if ($ssIndex !== false) {
            $ssContent = $zip->getFromIndex($ssIndex);
            if ($ssContent) {
                $dom = new DOMDocument();
                libxml_use_internal_errors(true);
                $dom->loadXML($ssContent);
                libxml_clear_errors();

                $siNodes = $dom->getElementsByTagName('si');
                foreach ($siNodes as $si) {
                    $sharedStrings[] = trim($si->textContent);
                }
            }
        }

        // 2. Discover seluruh worksheet secara urut dari xl/workbook.xml & xl/_rels/workbook.xml.rels
        $sheetFiles = [];
        $wbContent = $zip->getFromName('xl/workbook.xml');
        $wbRelsContent = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($wbContent && $wbRelsContent) {
            $relsDom = new DOMDocument();
            libxml_use_internal_errors(true);
            $relsDom->loadXML($wbRelsContent);
            libxml_clear_errors();
            $relMap = [];
            foreach ($relsDom->getElementsByTagName('Relationship') as $rel) {
                $rId = $rel->getAttribute('Id');
                $target = ltrim($rel->getAttribute('Target'), '/');
                if (!str_starts_with($target, 'xl/')) {
                    $target = 'xl/' . $target;
                }
                $relMap[$rId] = $target;
            }

            $wbDom = new DOMDocument();
            libxml_use_internal_errors(true);
            $wbDom->loadXML($wbContent);
            libxml_clear_errors();

            $discoveredSheets = [];
            foreach ($wbDom->getElementsByTagName('sheet') as $s) {
                $sName = trim($s->getAttribute('name'));
                $rId = $s->getAttribute('r:id');
                if ($rId && isset($relMap[$rId])) {
                    $discoveredSheets[] = [
                        'name' => $sName,
                        'path' => $relMap[$rId],
                    ];
                }
            }

            // Jika dalam konteks import jadwal: utamakan sheet jadwal, abaikan sheet tata usaha, outsourching, guru nominatif, jarik
            if ($context === 'schedule' && !empty($discoveredSheets)) {
                $scheduleSheets = array_filter($discoveredSheets, function ($ds) {
                    $sn = strtolower($ds['name']);
                    return str_contains($sn, 'jadwal') || str_contains($sn, 'schedule') || str_contains($sn, 'roster') ||
                           preg_match('/^(x|xi|xii)\b/i', $sn);
                });

                if (!empty($scheduleSheets)) {
                    foreach ($scheduleSheets as $ss) {
                        $sheetFiles[] = $ss['path'];
                    }
                } else {
                    $filteredSheets = array_filter($discoveredSheets, function ($ds) {
                        $sn = strtolower($ds['name']);
                        return !str_contains($sn, 'tata usaha') && !str_contains($sn, 'tu') &&
                               !str_contains($sn, 'outsourcing') && !str_contains($sn, 'outsourching') &&
                               !str_contains($sn, 'satpam') && !str_contains($sn, 'pegawai') &&
                               !str_contains($sn, 'tendik') && !str_contains($sn, 'jarik') &&
                               !str_contains($sn, 'cs') && !str_contains($sn, 'tanda terima') &&
                               !str_contains($sn, 'nominatif');
                    });

                    if (!empty($filteredSheets)) {
                        foreach ($filteredSheets as $fs) {
                            $sheetFiles[] = $fs['path'];
                        }
                    } else {
                        foreach ($discoveredSheets as $ds) {
                            $sheetFiles[] = $ds['path'];
                        }
                    }
                }
            } elseif ($context === 'teacher' && !empty($discoveredSheets)) {
                $guruSheets = array_filter($discoveredSheets, function ($ds) {
                    $sn = strtolower($ds['name']);
                    return str_contains($sn, 'guru') || str_contains($sn, 'pengajar') || str_contains($sn, 'pendidik');
                });

                if (!empty($guruSheets)) {
                    foreach ($guruSheets as $gs) {
                        $sheetFiles[] = $gs['path'];
                    }
                } else {
                    $filteredSheets = array_filter($discoveredSheets, function ($ds) {
                        $sn = strtolower($ds['name']);
                        return !str_contains($sn, 'tata usaha') && !str_contains($sn, 'tu') &&
                               !str_contains($sn, 'outsourcing') && !str_contains($sn, 'outsourching') &&
                               !str_contains($sn, 'satpam') && !str_contains($sn, 'pegawai') &&
                               !str_contains($sn, 'tendik') && !str_contains($sn, 'sheet 1') &&
                               !str_contains($sn, 'sheet1');
                    });

                    if (!empty($filteredSheets)) {
                        foreach ($filteredSheets as $fs) {
                            $sheetFiles[] = $fs['path'];
                        }
                    } else {
                        foreach ($discoveredSheets as $ds) {
                            $sheetFiles[] = $ds['path'];
                        }
                    }
                }
            } else {
                foreach ($discoveredSheets as $ds) {
                    $sheetFiles[] = $ds['path'];
                }
            }
        }

        // Fallback jika relasi workbook tidak ada: ambil semua xl/worksheets/sheet*.xml
        if (empty($sheetFiles)) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (preg_match('/^xl\/worksheets\/sheet\d+\.xml$/i', $name)) {
                    $sheetFiles[] = $name;
                }
            }
            sort($sheetFiles, SORT_NATURAL);
        }

        // 3. Baca dan parsing SEMUA worksheet yang ditemukan
        foreach ($sheetFiles as $sheetPath) {
            $sheetContent = $zip->getFromName($sheetPath);
            if (!$sheetContent) {
                continue;
            }

            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadXML($sheetContent);
            libxml_clear_errors();

            $rowNodes = $dom->getElementsByTagName('row');

            foreach ($rowNodes as $rowNode) {
                $cellNodes = $rowNode->getElementsByTagName('c');
                $indexedRow = [];
                $currentCol = 0;

                foreach ($cellNodes as $c) {
                    $ref = $c->getAttribute('r');
                    $type = $c->getAttribute('t');

                    // Tentukan indeks kolom dari cell reference (misal A1 -> 0, B1 -> 1, C1 -> 2)
                    if ($ref && preg_match('/^([A-Z]+)(\d+)$/i', $ref, $m)) {
                        $colLetters = strtoupper($m[1]);
                        $colIndex = 0;
                        $len = strlen($colLetters);
                        for ($k = 0; $k < $len; $k++) {
                            $colIndex = $colIndex * 26 + (ord($colLetters[$k]) - ord('A') + 1);
                        }
                        $targetCol = $colIndex - 1;
                    } else {
                        $targetCol = $currentCol;
                    }
                    $currentCol = $targetCol + 1;

                    // Ambil nilai sel
                    $val = '';
                    if ($type === 's') {
                        // Shared string index
                        $vNode = $c->getElementsByTagName('v')->item(0);
                        $idx = $vNode ? intval($vNode->textContent) : -1;
                        $val = $sharedStrings[$idx] ?? '';
                    } elseif ($type === 'inlineStr') {
                        $tNode = $c->getElementsByTagName('t')->item(0);
                        $val = $tNode ? $tNode->textContent : '';
                    } else {
                        $vNode = $c->getElementsByTagName('v')->item(0);
                        $val = $vNode ? $vNode->textContent : '';
                    }

                    $indexedRow[$targetCol] = trim($val);
                }

                if (!empty($indexedRow)) {
                    $maxIdx = max(array_keys($indexedRow));
                    $fullRow = [];
                    for ($ci = 0; $ci <= $maxIdx; $ci++) {
                        $fullRow[] = $indexedRow[$ci] ?? '';
                    }

                    // Masukkan jika setidaknya satu kolom tidak kosong
                    if (count(array_filter($fullRow, fn($v) => $v !== '')) > 0) {
                        $rows[] = $fullRow;
                    }
                }
            }
        }

        $zip->close();
        return $rows;
    }

    /**
     * Ekstraksi dari file Excel lama (.xls atau file HTML/CSV yang diberi ekstensi .xls)
     */
    public static function extractXls(string $filePath): array
    {
        // 1. Cek apakah ini sebenarnya file ZIP (.xlsx dengan ekstensi .xls)
        $headerBytes = @file_get_contents($filePath, false, null, 0, 4);
        if ($headerBytes === "PK\x03\x04" && class_exists('ZipArchive')) {
            $res = self::extractXlsx($filePath);
            if (!empty($res)) {
                return $res;
            }
        }

        $content = (string) file_get_contents($filePath);
        if (trim($content) === '') {
            return [];
        }

        // 2. Cek apakah ini tabel HTML yang diekspor sebagai .xls
        if (stripos($content, '<table') !== false || stripos($content, '<tr') !== false) {
            $htmlRows = self::extractHtmlTable($content);
            if (!empty($htmlRows)) {
                return $htmlRows;
            }
        }

        // 3. Cek apakah ini file teks / CSV
        $csvRows = self::extractCsv($filePath);
        if (!empty($csvRows)) {
            return $csvRows;
        }

        return [];
    }

    /**
     * Ekstraksi tabel dari format HTML (umum dihasilkan dari export sistem web)
     */
    public static function extractHtmlTable(string $htmlContent): array
    {
        $rows = [];
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $htmlContent);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $trNodes = $xpath->query('//tr');

        foreach ($trNodes as $tr) {
            $cells = $xpath->query('.//td|.//th', $tr);
            $row = [];
            foreach ($cells as $cell) {
                $row[] = trim(preg_replace('/\s+/', ' ', $cell->textContent));
            }
            if (count(array_filter($row, fn($v) => $v !== '')) > 0) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Ekstraksi dari file CSV / Text
     */
    public static function extractCsv(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return [];
        }

        // Sample 5 baris pertama untuk deteksi delimiter dominan
        $sample = '';
        for ($i = 0; $i < 5 && !feof($handle); $i++) {
            $sample .= fgets($handle);
        }
        rewind($handle);

        $semicolons = substr_count($sample, ';');
        $commas = substr_count($sample, ',');
        $tabs = substr_count($sample, "\t");
        $pipes = substr_count($sample, '|');

        $delimiter = ',';
        $maxCount = $commas;
        if ($semicolons > $maxCount) {
            $delimiter = ';';
            $maxCount = $semicolons;
        }
        if ($tabs > $maxCount) {
            $delimiter = "\t";
            $maxCount = $tabs;
        }
        if ($pipes > $maxCount) {
            $delimiter = '|';
            $maxCount = $pipes;
        }

        while (($data = fgetcsv($handle, null, $delimiter, '"', "\\")) !== false) {
            // Safety fallback: jika baris hanya 1 kolom tapi berisi pemisah delimiter lain
            if (count($data) === 1 && is_string($data[0])) {
                if ($delimiter !== ';' && str_contains($data[0], ';')) {
                    $data = str_getcsv($data[0], ';', '"', "\\");
                } elseif ($delimiter !== ',' && str_contains($data[0], ',')) {
                    $data = str_getcsv($data[0], ',', '"', "\\");
                } elseif ($delimiter !== "\t" && str_contains($data[0], "\t")) {
                    $data = str_getcsv($data[0], "\t", '"', "\\");
                }
            }

            $cleanRow = array_map(function ($val) {
                return trim((string) $val);
            }, $data);

            if (count(array_filter($cleanRow, fn($v) => $v !== '')) > 0) {
                $rows[] = $cleanRow;
            }
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Ekstraksi dari file Word (.docx) dengan membaca tabel di word/document.xml
     */
    public static function extractDocx(string $filePath): array
    {
        if (!class_exists('ZipArchive')) {
            return [];
        }
        $rows = [];
        $zip = new ZipArchive();

        if ($zip->open($filePath) !== true) {
            return [];
        }

        $xmlIndex = $zip->locateName('word/document.xml');
        if ($xmlIndex === false) {
            $zip->close();
            return [];
        }

        $xmlContent = $zip->getFromIndex($xmlIndex);
        $zip->close();

        if (!$xmlContent) {
            return [];
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadXML($xmlContent);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        // Cari tabel pertama yang memiliki baris dan sel
        $tables = $xpath->query('//w:tbl');
        if ($tables->length === 0) {
            return [];
        }

        // Ambil tabel yang memiliki baris terbanyak
        $targetTable = null;
        $maxRows = 0;
        foreach ($tables as $tbl) {
            $trList = $xpath->query('.//w:tr', $tbl);
            if ($trList->length > $maxRows) {
                $maxRows = $trList->length;
                $targetTable = $tbl;
            }
        }

        if (!$targetTable) {
            return [];
        }

        $trElements = $xpath->query('.//w:tr', $targetTable);
        foreach ($trElements as $tr) {
            $tcElements = $xpath->query('.//w:tc', $tr);
            $row = [];
            foreach ($tcElements as $tc) {
                $textNodes = $xpath->query('.//w:t', $tc);
                $cellText = '';
                foreach ($textNodes as $t) {
                    $cellText .= $t->nodeValue . ' ';
                }
                $row[] = trim(preg_replace('/\s+/', ' ', $cellText));
            }

            if (count(array_filter($row, fn($v) => $v !== '')) > 0) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Ekstraksi dari file PDF (.pdf) menggunakan Smalot PDF Parser (Koordinat Visual & Stream Text)
     */
    public static function extractPdf(string $filePath): array
    {
        $rawContent = @file_get_contents($filePath);

        // 1. Prioritas Utama untuk Jadwal: Deteksi PDF Jadwal Pelajaran FET Timetable (SMK / SMA)
        if ($rawContent) {
            $fetRows = self::extractFetPdf($filePath, $rawContent);
            if (!empty($fetRows) && count($fetRows) > 1) {
                return $fetRows;
            }
        }

        $rows = [];

        // 2. Ekstraksi cerdas menggunakan Smalot PDF Parser
        try {
            if (class_exists(\Smalot\PdfParser\Parser::class)) {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($filePath);
                $pages = $pdf->getPages();

                foreach ($pages as $page) {
                    // A. Prioritas Utama: Ekstraksi berbasis koordinat visual (getDataTm)
                    try {
                        $dataTm = $page->getDataTm();
                        if (!empty($dataTm)) {
                            $pageRows = self::extractTableFromTmCoordinates($dataTm);
                            if (!empty($pageRows)) {
                                foreach ($pageRows as $r) {
                                    $rows[] = $r;
                                }
                                continue;
                            }
                        }
                    } catch (\Throwable $te) {
                        // Jika getDataTm gagal pada halaman tertentu, lanjut ke fallback text
                    }

                    // B. Fallback per halaman: Ekstraksi teks baris dengan deteksi pemisah kolom cerdas
                    try {
                        $pageText = $page->getText();
                        if (trim($pageText) !== '') {
                            $pageRows = self::parseTextIntoTableRows($pageText);
                            foreach ($pageRows as $r) {
                                $rows[] = $r;
                            }
                        }
                    } catch (\Throwable $pe) {
                        // Abaikan error per halaman
                    }
                }

                if (!empty($rows)) {
                    return $rows;
                }
            }
        } catch (\Throwable $e) {
            // Lanjut ke fallback native parser
        }

        // 3. Fallback parser stream biner PDF murni
        if (!$rawContent) {
            return [];
        }

        $text = self::decodePdfContent($rawContent);
        return self::parseTextIntoTableRows($text);
    }

    /**
     * Ekstraksi khusus untuk file Jadwal Pelajaran FET Timetable PDF (format standar ekspor FET/Chrome)
     */
    public static function extractFetPdf(string $filePath, ?string $rawContent = null): array
    {
        $raw = $rawContent ?? @file_get_contents($filePath);
        if (!$raw) return [];

        preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $raw, $matches);
        if (empty($matches[1])) return [];

        $days = [
            1 => ['name' => 'Senin',  'xMin' => 67.0,  'xMax' => 167.0, 'targetX' => 70.0,  'targetW' => 96.6],
            2 => ['name' => 'Selasa', 'xMin' => 167.0, 'xMax' => 264.0, 'targetX' => 167.8, 'targetW' => 96.3],
            3 => ['name' => 'Rabu',   'xMin' => 264.0, 'xMax' => 361.0, 'targetX' => 264.6, 'targetW' => 96.3],
            4 => ['name' => 'Kamis',  'xMin' => 361.0, 'xMax' => 458.0, 'targetX' => 361.5, 'targetW' => 96.3],
            5 => ['name' => 'Jumat',  'xMin' => 458.0, 'xMax' => 560.0, 'targetX' => 458.3, 'targetW' => 96.3],
        ];

        $timeSlotY = [
            ['time' => '07:15:00', 'y' => 80.2],
            ['time' => '08:00:00', 'y' => 120.8],
            ['time' => '08:45:00', 'y' => 161.5],
            ['time' => '09:45:00', 'y' => 202.1],
            ['time' => '10:30:00', 'y' => 242.8],
            ['time' => '11:15:00', 'y' => 283.4],
            ['time' => '12:30:00', 'y' => 324.0],
            ['time' => '13:15:00', 'y' => 364.6],
            ['time' => '14:00:00', 'y' => 405.2],
            ['time' => '14:45:00', 'y' => 445.9],
            ['time' => '15:30:00', 'y' => 486.5],
        ];

        $rows = [];
        $rows[] = ['putaran', 'hari', 'nip_guru', 'nama_guru', 'mata_pelajaran', 'kelas', 'ruangan', 'jam_mulai', 'jam_selesai'];

        foreach ($matches[1] as $streamBytes) {
            $stream = @gzuncompress($streamBytes);
            if ($stream === false) continue;
            if (!str_contains($stream, 'Senin') || (!str_contains($stream, 'Selasa') && !str_contains($stream, 'Rabu'))) {
                continue;
            }

            // Temukan Nama Kelas di halaman
            $className = null;
            if (preg_match('/\[?\((X|XI|XII)\s*([A-Za-z0-9]+)\)\]?\s*TJ/i', $stream, $cm)) {
                $className = trim($cm[1] . ' ' . $cm[2]);
            } elseif (preg_match('/\((X|XI|XII)\s*([A-Za-z0-9]+)\)\s*Tj/i', $stream, $cm)) {
                $className = trim($cm[1] . ' ' . $cm[2]);
            }
            if (!$className) continue;

            // Cari semua garis horizontal (border sel) di stream
            preg_match_all('/([\d\.]+)\s+([\d\.]+)\s+([\d\.]+)\s+([\d\.]+)\s+re/s', $stream, $rects);

            // Ekstraksi perintah teks dengan koordinat X, Y
            $lines = preg_split('/[\r\n]+/', $stream);
            $items = [];
            $tm = [1, 0, 0, 1, 0, 0];
            $tlm = [1, 0, 0, 1, 0, 0];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === 'BT') {
                    $tm = [1, 0, 0, 1, 0, 0];
                    $tlm = [1, 0, 0, 1, 0, 0];
                    continue;
                }
                if (preg_match('/^([-\d\.]+)\s+([-\d\.]+)\s+([-\d\.]+)\s+([-\d\.]+)\s+([-\d\.]+)\s+([-\d\.]+)\s+Tm$/', $line, $m)) {
                    $tm = [(float)$m[1], (float)$m[2], (float)$m[3], (float)$m[4], (float)$m[5], (float)$m[6]];
                    $tlm = $tm;
                    continue;
                }
                if (preg_match('/^([-\d\.]+)\s+([-\d\.]+)\s+Td$/', $line, $m)) {
                    $tlm[4] += (float)$m[1] * $tlm[0];
                    $tlm[5] += (float)$m[2] * $tlm[3];
                    $tm = $tlm;
                    continue;
                }
                
                $text = null;
                if (preg_match('/^\(((?:\\\\.|[^\\\\)])*)\)\s*Tj$/', $line, $m)) {
                    $text = $m[1];
                } elseif (preg_match('/^\[(.*)\]\s*TJ$/', $line, $m)) {
                    preg_match_all('/\(((?:\\\\.|[^\\\\)])*)\)/', $m[1], $subs);
                    $text = implode('', $subs[1]);
                }
                if ($text !== null) {
                    // Buka escape string PDF: \( \) \\
                    $text = preg_replace('/\\\\([()\\\\])/', '$1', $text);
                }
                
                if ($text !== null && trim($text) !== '') {
                    $items[] = ['x' => $tm[4], 'y' => $tm[5], 'text' => trim($text)];
                }
            }

            // Lebar kolom hari berbeda-beda di setiap halaman (FET menyesuaikan dengan isi),
            // maka posisi kolom dibaca dari garis header tabel (y ≈ 64.9).
            $headerCols = [];
            for ($i = 0; $i < count($rects[0]); $i++) {
                $ry = (float)$rects[2][$i];
                $rx = (float)$rects[1][$i];
                if ((float)$rects[4][$i] < 1.0 && $ry > 62.0 && $ry < 67.0 && $rx >= 69.0) {
                    $headerCols[(string)round($rx, 1)] = ['x' => $rx, 'w' => (float)$rects[3][$i]];
                }
            }
            uasort($headerCols, fn($a, $b) => $a['x'] <=> $b['x']);
            $headerCols = array_values($headerCols);
            // Abaikan baris judul penuh (lebar > 400) jika ikut terbaca
            $headerCols = array_values(array_filter($headerCols, fn($c) => $c['w'] < 400));

            $pageDays = [];
            if (count($headerCols) === 5) {
                $dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
                foreach ($headerCols as $k => $c) {
                    $pageDays[] = [
                        'name' => $dayNames[$k],
                        'xMin' => $c['x'] - 1.5,
                        'xMax' => $c['x'] + $c['w'] + 1.5,
                        'targetX' => $c['x'],
                        'targetW' => $c['w'],
                    ];
                }
            } else {
                $pageDays = $days;
            }

            // Proses setiap kolom hari (Senin s.d Jumat)
            foreach ($pageDays as $dayInfo) {
                $dayName = $dayInfo['name'];
                $colItems = array_filter($items, function($it) use ($dayInfo) {
                    return $it['x'] >= $dayInfo['xMin'] && $it['x'] < $dayInfo['xMax'] && $it['y'] >= 80 && $it['y'] <= 520;
                });
                if (empty($colItems)) continue;

                // Cari garis border sel tabel untuk kolom ini
                $ys = [];
                for ($i = 0; $i < count($rects[0]); $i++) {
                    $rx = (float)$rects[1][$i];
                    $ry = (float)$rects[2][$i];
                    $rw = (float)$rects[3][$i];
                    $rh = (float)$rects[4][$i];
                    if ($rh < 1.0 && abs($rx - $dayInfo['targetX']) < 1.5 && abs($rw - $dayInfo['targetW']) < 1.5 && $ry >= 79.0 && $ry <= 520.0) {
                        $ys[] = round($ry, 1);
                    }
                }
                $ys = array_values(array_unique($ys));
                sort($ys);

                // Garis batas sel digambar berpasangan (bawah sel atas + atas sel bawah, selisih ±1.7pt)
                $groups = [];
                foreach ($ys as $y) {
                    if (!empty($groups) && ($y - $groups[count($groups) - 1]['hi']) < 4.0) {
                        $groups[count($groups) - 1]['hi'] = $y;
                    } else {
                        $groups[] = ['lo' => $y, 'hi' => $y];
                    }
                }

                $colBorders = [];
                for ($g = 0; $g < count($groups) - 1; $g++) {
                    $colBorders[] = [$groups[$g]['hi'], $groups[$g + 1]['lo']];
                }

                if (empty($colBorders)) {
                    $colBorders = [[80.2, 486.5]];
                }

                // Kelompokkan teks per sel berdasarkan garis batas sel
                foreach ($colBorders as [$topY, $botY]) {
                    if (($botY - $topY) < 10.0) continue;

                    $cellItems = array_filter($colItems, function($it) use ($topY, $botY) {
                        return $it['y'] >= ($topY - 2.0) && $it['y'] <= ($botY + 2.0);
                    });
                    if (empty($cellItems)) continue;

                    usort($cellItems, function($a, $b) {
                        return abs($a['y'] - $b['y']) < 1.0 ? $a['x'] <=> $b['x'] : $a['y'] <=> $b['y'];
                    });

                    $cleanCombined = trim(preg_replace('/\s+/', ' ', implode(' ', array_map(fn($it) => $it['text'], $cellItems))));
                    if ($cleanCombined === '' || $cleanCombined === '-x-' || $cleanCombined === '-x' || $cleanCombined === '-') {
                        continue;
                    }

                    // Tentukan jam mulai dan selesai berdasarkan koordinat border
                    $startTime = '07:15:00';
                    $endTime   = '08:45:00';

                    $minTopDiff = 9999;
                    foreach ($timeSlotY as $ts) {
                        $diff = abs($ts['y'] - $topY);
                        if ($diff < $minTopDiff) {
                            $minTopDiff = $diff;
                            $startTime = $ts['time'];
                        }
                    }

                    $minBotDiff = 9999;
                    foreach ($timeSlotY as $ts) {
                        $diff = abs($ts['y'] - $botY);
                        if ($diff < $minBotDiff) {
                            $minBotDiff = $diff;
                            $endTime = $ts['time'];
                        }
                    }

                    if ($startTime >= $endTime) {
                        foreach ($timeSlotY as $ts) {
                            if ($ts['time'] > $startTime) {
                                $endTime = $ts['time'];
                                break;
                            }
                        }
                    }

                    $teacherName = '';
                    $teacherNip = '';
                    $subjectName = '';
                    $roomName = '';

                    // Ekstraksi nama guru dan kode/nomor: "82. RAHMAWATI YOGA PAMUNGKAS, M.Pd"
                    if (preg_match('/"(\d+\.?\s*)?([^"]+)"?/', $cleanCombined, $tm)) {
                        $rawTeacher = trim($tm[2]);
                        $rawTeacher = preg_replace('/"$/', '', $rawTeacher);
                        if (!empty($tm[1])) {
                            $teacherNip = trim(str_replace('.', '', $tm[1]));
                        }
                        if (preg_match('/\b(LAB\s*(?:IPA|PB|\d+)|L\.\s*(?:IPA|PB|\d+)|OR\d*|HALL|RUANG\s*\w+)\b/i', $rawTeacher, $rm)) {
                            $roomName = trim($rm[0]);
                            $rawTeacher = trim(str_replace($rm[0], '', $rawTeacher));
                        }
                        $teacherName = trim($rawTeacher);

                        $quotePos = strpos($cleanCombined, '"');
                        if ($quotePos !== false) {
                            $subjectName = trim(substr($cleanCombined, 0, $quotePos));
                        }
                    } else {
                        $subjectName = $cleanCombined;
                    }

                    if (empty($roomName)) {
                        if (preg_match('/\b(LAB\s*(?:IPA|PB|\d+)|L\.\s*(?:IPA|PB|\d+)|OR\d*|HALL|RUANG\s*[A-Za-z0-9]+|R\.\s*[A-Za-z0-9]+)\b/i', $cleanCombined, $rm)) {
                            $roomName = trim($rm[0]);
                        } elseif (preg_match('/(?:\s+|\b)(\d{1,2})\s*$/', $cleanCombined, $numM)) {
                            // Angka mandiri di akhir sel jadwal (nomor ruang teori seperti 10, 12, 14, 25, 34)
                            $roomName = $numM[1];
                        }
                    }

                    if (empty($subjectName)) {
                        $subjectName = 'Pembelajaran ' . $className;
                    }

                    $rows[] = [
                        '1',
                        $dayName,
                        $teacherNip,
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

        return $rows;
    }

    /**
     * Merekonstruksi tabel dari koordinat visual teks PDF (X, Y).
     */
    private static function extractTableFromTmCoordinates(array $dataTm): array
    {
        if (empty($dataTm)) {
            return [];
        }

        // Urutkan elemen: Y menurun (dari atas ke bawah halaman), lalu X menaik (kiri ke kanan)
        usort($dataTm, function ($a, $b) {
            $yA = (float)($a[0][5] ?? 0);
            $yB = (float)($b[0][5] ?? 0);
            if (abs($yA - $yB) <= 4.0) {
                return ((float)($a[0][4] ?? 0)) <=> ((float)($b[0][4] ?? 0));
            }
            return $yB <=> $yA;
        });

        // Kelompokkan menjadi baris-baris visual
        $rawRows = [];
        $currentRow = [];
        $currentY = null;

        foreach ($dataTm as $item) {
            $x = (float)($item[0][4] ?? 0);
            $y = (float)($item[0][5] ?? 0);
            $text = trim((string)($item[1] ?? ''));
            if ($text === '') {
                continue;
            }

            if ($currentY === null || abs($y - $currentY) > 4.0) {
                if (!empty($currentRow)) {
                    $rawRows[] = $currentRow;
                }
                $currentRow = [['x' => $x, 'text' => $text]];
                $currentY = $y;
            } else {
                $currentRow[] = ['x' => $x, 'text' => $text];
            }
        }
        if (!empty($currentRow)) {
            $rawRows[] = $currentRow;
        }

        // Gabungkan kata-kata berdekatan dalam satu kolom (gap <= 20pt) dan pisahkan jika beda kolom (gap > 20pt)
        $tableRows = [];
        foreach ($rawRows as $rowItems) {
            $cols = [];
            $currentCell = '';
            $lastXEnd = null;

            foreach ($rowItems as $it) {
                $x = $it['x'];
                $txt = $it['text'];
                $approxWidth = strlen($txt) * 5.5;

                if ($lastXEnd === null) {
                    $currentCell = $txt;
                } else {
                    $gap = $x - $lastXEnd;
                    if ($gap <= 20) {
                        $currentCell .= ' ' . $txt;
                    } else {
                        $cols[] = trim($currentCell);
                        $currentCell = $txt;
                    }
                }
                $lastXEnd = $x + $approxWidth;
            }
            if (trim($currentCell) !== '') {
                $cols[] = trim($currentCell);
            }

            if (count($cols) > 0) {
                $tableRows[] = $cols;
            }
        }

        return $tableRows;
    }

    /**
     * Memecah string teks PDF menjadi baris dan kolom tabel secara cerdas
     */
    private static function parseTextIntoTableRows(string $text): array
    {
        $rows = [];
        $lines = preg_split('/[\r\n]+/', $text);

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            $cols = self::splitPdfLineIntoCols($trimmed);
            $cols = array_map('trim', $cols);
            $cols = array_values(array_filter($cols, fn($v) => $v !== ''));
            if (count($cols) > 0) {
                $rows[] = $cols;
            }
        }

        return $rows;
    }

    /**
     * Pemisah kolom cerdas untuk baris teks PDF
     */
    private static function splitPdfLineIntoCols(string $trimmed): array
    {
        // 1. Tab separated
        if (str_contains($trimmed, "\t")) {
            return explode("\t", $trimmed);
        }
        // 2. Pipe separated
        if (str_contains($trimmed, '|')) {
            return explode('|', $trimmed);
        }
        // 3. Semicolon separated
        if (str_contains($trimmed, ';')) {
            return explode(';', $trimmed);
        }
        // 4. Multi-space (2 spasi atau lebih) - ini adalah pemisah standar kolom pada teks PDF tabel
        if (preg_match('/\s{2,}/', $trimmed)) {
            return preg_split('/\s{2,}/', $trimmed);
        }
        // 5. Comma separated HANYA JIKA memiliki minimal 3 koma dan bukan hanya koma nama gelar
        if (substr_count($trimmed, ',') >= 2) {
            $parsed = str_getcsv($trimmed, ',', '"', "\\");
            if (count($parsed) >= 3) {
                return $parsed;
            }
        }

        return [$trimmed];
    }

    /**
     * Auto-detection jika format file tidak sesuai nama ekstensinya
     */
    private static function autoDetectAndExtract(string $filePath): array
    {
        $magic = @file_get_contents($filePath, false, null, 0, 8);
        if (!$magic) {
            return [];
        }

        // ZIP magic (docx atau xlsx)
        if (str_starts_with($magic, "PK\x03\x04") && class_exists('ZipArchive')) {
            $xlsx = self::extractXlsx($filePath);
            if (!empty($xlsx)) {
                return $xlsx;
            }
            return self::extractDocx($filePath);
        }

        // PDF magic
        if (str_starts_with($magic, "%PDF")) {
            return self::extractPdf($filePath);
        }

        // HTML
        $head = (string) file_get_contents($filePath, false, null, 0, 512);
        if (stripos($head, '<html') !== false || stripos($head, '<table') !== false) {
            return self::extractHtmlTable((string) file_get_contents($filePath));
        }

        // Default CSV
        return self::extractCsv($filePath);
    }

    /**
     * Helper dekompresi dan ekstraksi teks stream PDF
     */
    private static function decodePdfContent(string $data): string
    {
        $extractedText = '';

        if (preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $data, $matches)) {
            foreach ($matches[1] as $stream) {
                $decompressed = @gzuncompress($stream);
                if ($decompressed === false) {
                    $decompressed = @gzinflate($stream);
                }
                if ($decompressed === false) {
                    $decompressed = @gzinflate(substr($stream, 2));
                }
                if ($decompressed === false) {
                    $decompressed = $stream;
                }

                // Operator Tj (string tunggal dalam tanda kurung)
                if (preg_match_all('/\((.*?)\)\s*T[jJ]/s', $decompressed, $textMatches)) {
                    $extractedText .= implode(' ', $textMatches[1]) . "\n";
                }
                // Operator TJ (array string)
                if (preg_match_all('/\[(.*?)\]\s*TJ/s', $decompressed, $arrayMatches)) {
                    foreach ($arrayMatches[1] as $arr) {
                        if (preg_match_all('/\((.*?)\)/s', $arr, $subMatches)) {
                            $extractedText .= implode('', $subMatches[1]) . ' ';
                        }
                    }
                    $extractedText .= "\n";
                }
                // Operator Tj untuk hex string <...>
                if (preg_match_all('/<([0-9a-fA-F]+)>\s*T[jJ]/s', $decompressed, $hexMatches)) {
                    $hexRow = '';
                    foreach ($hexMatches[1] as $h) {
                        $hexRow .= @hex2bin($h);
                    }
                    if (trim($hexRow) !== '') {
                        $extractedText .= $hexRow . "\n";
                    }
                }
            }
        }

        if (trim($extractedText) === '') {
            if (preg_match_all('/\((.*?)\)\s*T[jJ]/s', $data, $plainMatches)) {
                $extractedText = implode(' ', $plainMatches[1]);
            }
        }

        return $extractedText;
    }
}
