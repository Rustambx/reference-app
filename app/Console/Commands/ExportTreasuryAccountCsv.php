<?php

namespace App\Console\Commands;

use App\Exports\TreasuryAccountSampleExport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class ExportTreasuryAccountCsv extends Command
{
    protected $signature = 'accounts:export {limit=10000} {--random}';

    protected $description = 'Export N treasury accounts to CSV';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int)$this->argument('limit');
        $random = (bool)$this->option('random');

        $filename = "exports/treasury_accounts_{$limit}" . ($random ? '_random' : '') . '.csv';
        Excel::store(new TreasuryAccountSampleExport($limit, $random), $filename, null, ExcelFormat::CSV);

        $this->info("Saved to storage/app/{$filename}");

        return self::SUCCESS;
    }
}
