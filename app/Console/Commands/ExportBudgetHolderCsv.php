<?php

namespace App\Console\Commands;

use App\Exports\BudgetHolderSampleExport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class ExportBudgetHolderCsv extends Command
{
    protected $signature = 'budgets:export {limit=10000} {--random}';

    protected $description = 'Export N budget holders to CSV';

    public function handle()
    {
        $limit = (int)$this->argument('limit');
        $random = (int)$this->option('random');

        $filename = "exports/budget_holders_{$limit}" . ($random ? "_random" : '') . ".csv";
        Excel::store(new BudgetHolderSampleExport($limit, $random), $filename, null, ExcelFormat::CSV);

        $this->info("Budget Holders Saved to storage/app/{$filename}");

        return self::SUCCESS;
    }
}
