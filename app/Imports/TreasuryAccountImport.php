<?php

namespace App\Imports;

use App\Models\TreasuryAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TreasuryAccountImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading,
    ShouldQueue
{
    use SkipsFailures, Queueable;

    public function __construct(private readonly ?string $userId = null){}

    public int $tries = 3;
    public int $timeout = 120;
    private array $seenTreasuryAccount = [];

    public function headingRow(): int
    {
        return 1;
    }

    public function rules(): array
    {
        return [
            'account' => [
                'bail', 'required', 'string', 'max:34',
                Rule::unique('treasury_accounts', 'account'),
                // Проверка на дубликат в текущем CSV
                function (string $attribute, $value, \Closure $fail) {
                    $code = strtoupper((string)$value);
                    if (isset($this->seenTreasuryAccount[$code])) {
                        $fail('Дубликат Номер счёта в этом файле.');
                        return;
                    }
                    $this->seenTreasuryAccount[$code] = true;
                },
            ],
            'mfo' => 'required',
            'name' => 'required',
            'department' => 'nullable|string',
            'currency' => 'required|string|max:3',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'account.required' => 'Поле account обязательно',
            'mfo.required' => 'Поле mfo обязательно',
            'name.required' => 'Поле name обязательно',
            'department.string' => 'Поле department должно быть строкой.',
            'currency.required' => 'Поле currency обязательно'
        ];
    }

    public function model(array $row): TreasuryAccount
    {
        return new TreasuryAccount([
            'id' => (string) Str::uuid(),
            'account' => $row['account'],
            'mfo' => $row['mfo'],
            'name' => $row['name'],
            'department' => $row['department'],
            'currency' => $row['currency'],
            'created_by' => $this->userId,
            'updated_by' => $this->userId,
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
