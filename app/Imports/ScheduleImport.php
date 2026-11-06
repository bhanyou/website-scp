<?php

namespace App\Imports;

use App\Services\ScheduleImportService;

/**
 * Class ScheduleImport
 * Adapter/Wrapper untuk ScheduleImportService agar kompatibel
 * baik dipanggil sebagai Service maupun Import class.
 */
class ScheduleImport extends ScheduleImportService
{
    // Mewarisi seluruh metode dan logika parsing ScheduleImportService
}

