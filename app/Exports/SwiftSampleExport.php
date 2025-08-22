<?php

namespace App\Exports;

use App\Models\Swift;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SwiftSampleExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private int $limit = 10000, private bool $random = false) {}

    public function query()
    {
        $q = Swift::query()->select(['swift_code','bank_name','country','city','address']);

        // Быстрый детерминированный отбор первых N
        if (! $this->random) {
            return $q->orderBy('id')->limit($this->limit);
        }

        // Случайные 10k: inRandomOrder() может быть дорогим на 100k+ строк в PG.
        // На больших объёмах лучше делать свою стратегию случайной выборки.
        return $q->inRandomOrder()->limit($this->limit);
    }

    public function headings(): array
    {
        return ['swift_code', 'bank_name', 'country', 'city', 'address'];
    }

    public function map($row): array
    {
        return [
            $row->swift_code,
            $row->bank_name,
            $row->country,
            $row->city,
            $row->address,
        ];
    }
}
