<?php

namespace App\Imports;

use App\Models\Swift;
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

class SwiftImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading,
    ShouldQueue,
    SkipsOnError,
    WithUpserts
{
    use SkipsFailures, Queueable, SkipsErrors;

    private array $seenSwift = [];

    public function __construct(private readonly ?string $userId = null)
    {
    }

    public int $tries = 3;
    public int $timeout = 120;

    public function headingRow(): int
    {
        return 1;
    }

    public function rules(): array
    {
        return [
            'swift_code' => [
                'bail', 'required', 'string', 'max:11',
                function (string $attribute, $value, \Closure $fail) {
                    $code = strtoupper((string)$value);
                    if (isset($this->seenSwift[$code])) {
                        $fail('Дубликат SWIFT-кода в этом файле.');
                        return;
                    }
                    $this->seenSwift[$code] = true;
                },
            ],
            'bank_name' => ['bail', 'required', 'string', 'max:255'],
            'country' => ['bail', 'required', 'string', 'size:3'],
            'city' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'swift_code.required' => 'SWIFT-код обязателен',
            'swift_code.max' => 'SWIFT-код не должен превышать 11 символов',
            'swift_code.unique' => 'Такой SWIFT-код уже существует',
            'bank_name.required' => 'Название банка обязательно',
            'country.required' => 'Поле country должен быть строкой',
            'country.size' => 'Код страны должен состоять из 3 символов (ISO-3)',
            'city.string' => 'Поле city должен быть строкой',
            'address.string' => 'Поле address должен быть строкой'
        ];
    }

    public function model(array $row): ?Swift
    {
        $country = strtoupper((string)($row['country'] ?? ''));
        $swift = strtoupper((string)($row['swift_code'] ?? ''));

        return new Swift([
            'id' => (string)Str::uuid(),
            'swift_code' => $swift,
            'bank_name' => (string)($row['bank_name'] ?? ''),
            'country' => $country,
            'city' => $row['city'] ?? null,
            'address' => $row['address'] ?? null,
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
        return 'swift_code';
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
