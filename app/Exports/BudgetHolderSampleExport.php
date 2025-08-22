<?php

namespace App\Exports;

use App\Models\BudgetHolder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BudgetHolderSampleExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private int $limit = 10000, private bool $random = false){}

    public function query()
    {
        $q = BudgetHolder::query()->select([
            'tin',
            'name',
            'region',
            'district',
            'address',
            'phone',
            'responsible'
        ]);

        if (!$this->random) {
            return $q->orderBy('id')->limit($this->limit);
        }

        return $q->inRandomOrder()->limit($this->limit);
    }

    public function headings(): array
    {
        return [
            'tin',
            'name',
            'region',
            'district',
            'address',
            'phone',
            'responsible'
        ];
    }

    public function map($row): array
    {
        $phone = $row->phone;

        if ($phone && $phone[0] !== '+') {
            $digits = preg_replace('/\D/', '', $phone);
            $phone  = $digits ? '+' . $digits : null;
        }

        $phoneForCsv = $phone ? "'".$phone : null;

        return [
            $row->tin,
            $row->name,
            $row->region,
            $row->district,
            $row->address,
            $phoneForCsv,
            $row->responsible,
        ];
    }

}
