<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Exports\SwiftSampleExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class ExportSwiftsCsv extends Command
{
    protected $signature = 'swifts:export {limit=10000} {--random}';
    protected $description = 'Export N swifts to CSV';

    public function handle(): int
    {
        $limit  = (int)$this->argument('limit');
        $random = (bool)$this->option('random');

        $filename = "exports/swifts_{$limit}" . ($random ? '_random' : '') . ".csv";
        Excel::store(new SwiftSampleExport($limit, $random), $filename, null, ExcelFormat::CSV);

        $this->info("Saved to storage/app/{$filename}");
        return self::SUCCESS;
    }
}
