<?php

namespace App\Exports;

use App\Models\TreasuryAccount;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TreasuryAccountSampleExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private int $limit = 10000, private bool $random = false){}

    public function query()
    {
        $q = TreasuryAccount::query()->select([
            'account',
            'mfo',
            'name',
            'department',
            'currency',
        ]);

        if(!$this->random){
            return $q->orderBy('id')->limit($this->limit);
        }

        return $q->inRandomOrder()->limit($this->limit);
    }

    public function headings(): array
    {
        return [
            'account',
            'mfo',
            'name',
            'department',
            'currency'
        ];
    }

    public function map($row): array
    {
        return [
            $row->account,
            $row->mfo,
            $row->name,
            $row->department,
            $row->currency
        ];
    }
}
