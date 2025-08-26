<?php

namespace App\Imports;

use App\Models\TreasuryAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class TreasuryAccountImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    SKipsOnError,
    WithBatchInserts,
    WithChunkReading,
    ShouldQueue,
    WithUpserts
{
    use SkipsFailures, SkipsErrors, Queueable;

    public function __construct(private readonly ?string $userId = null)
    {
    }

    public int $tries = 3;
    public int $timeout = 120;

    private array $seenAccount = [];

    public function headingRow(): int
    {
        return 1;
    }

    public function rules(): array
    {
        return [
            'account' => [
                'bail', 'required', 'string', 'max:34',
                function (string $attribute, $value, \Closure $fail) {
                    if (isset($this->seenAccount[$value])) {
                        $fail('Дубликат Номер счёта в этом файле.');
                        return;
                    }
                    $this->seenAccount[$value] = true;
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
            'account.string' => 'Поле account должно быть строкой.',
            'mfo.required' => 'Поле mfo обязательно',
            'name.required' => 'Поле name обязательно',
            'department.string' => 'Поле department должно быть строкой.',
            'currency.required' => 'Поле currency обязательно'
        ];
    }

    public function model(array $row): TreasuryAccount
    {
        $account = strtoupper((string)($row['account'] ?? ''));

        return new TreasuryAccount([
            'id' => (string)Str::uuid(),
            'account' => $account,
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

    public function uniqueBy()
    {
        return 'account';
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            Log::warning('Импорт не прошёл валидацию', [
                'Строка' => $failure->row(),
                'values' => $failure->values(),
                'errors' => $failure->errors(),
            ]);
        }
    }

    public function onError(\Throwable $e)
    {
        Log::error('Ошибка обработки строки импорта', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
