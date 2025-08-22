<?php

namespace App\Imports;

use App\Models\BudgetHolder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class BudgetHolderImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    SkipsOnError,
    WithBatchInserts,
    WithChunkReading,
    ShouldQueue
{
    use SkipsFailures, SkipsErrors, Queueable;

    public function __construct(private readonly ?string $userId = null) {}

    public int $tries = 3;
    public int $timeout = 120;

    private array $seenTin = [];

    public function headingRow(): int
    {
        return 1;
    }

    /**
     * Приводим входные данные к каноничному виду ДО валидации.
     */
    public function prepareForValidation($row, $index)
    {
        // tin: только цифры
        $row['tin'] = isset($row['tin'])
            ? preg_replace('/\D/', '', (string) $row['tin'])
            : null;

        // phone: только цифры + обязательный ведущий '+'
        if (!empty($row['phone'])) {
            $digits = preg_replace('/\D/', '', (string) $row['phone']);
            $row['phone'] = $digits !== '' ? '+'.$digits : null;
        } else {
            $row['phone'] = null;
        }

        // подчищаем строки
        foreach (['name','region','district','address','responsible'] as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null) {
                $row[$key] = trim((string) $row[$key]) ?: null;
            }
        }

        return $row;
    }

    public function rules(): array
    {
        return [
            'tin' => [
                'bail', 'required', 'regex:/^\d{9,14}$/',
                // если есть soft deletes у budget_holders — используйте whereNull('deleted_at')
                Rule::unique('budget_holders', 'tin')/*->whereNull('deleted_at')*/,
                function (string $attribute, $value, \Closure $fail) {
                    // тут уже приходит очищенное значение
                    if (isset($this->seenTin[$value])) {
                        $fail('Дубликат ИНН в этом файле.');
                        return;
                    }
                    $this->seenTin[$value] = true;
                },
            ],
            'name'        => ['bail', 'required', 'string', 'max:255'],
            'region'      => ['bail', 'nullable', 'string', 'max:120'],
            'district'    => ['bail', 'nullable', 'string', 'max:120'],
            'address'     => ['nullable', 'string', 'max:255'],
            // после prepareForValidation телефон всегда в формате +цифры
            'phone'       => ['nullable', 'regex:/^\+\d{7,15}$/'],
            'responsible' => ['bail', 'nullable', 'string', 'max:120'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'tin.required' => 'Поле tin обязательно для заполнения.',
            'tin.regex'    => 'Поле tin должно содержать 9–14 цифр без пробелов и символов.',
            'tin.unique'   => 'Такой tin уже существует в системе.',

            'name.required' => 'Поле name обязательно для заполнения.',
            'name.string'   => 'Поле name должно быть строкой.',
            'name.max'      => 'Поле name не должно превышать 255 символов.',

            'region.string' => 'Поле region должно быть строкой.',
            'region.max'    => 'Поле region не должно превышать 120 символов.',

            'district.string' => 'Поле district должно быть строкой.',
            'district.max'    => 'Поле district не должно превышать 120 символов.',

            'address.string' => 'Поле address должно быть строкой.',
            'address.max'    => 'Поле address не должно превышать 255 символов.',

            'phone.regex' => 'Поле phone должно начинаться с "+" и содержать 7–15 цифр.',

            'responsible.string' => 'Поле responsible должно быть строкой.',
            'responsible.max'    => 'Поле responsible не должно превышать 120 символов.',
        ];
    }

    public function model(array $row): ?BudgetHolder
    {
        // данные уже нормализованы в prepareForValidation
        return new BudgetHolder([
            'tin'         => $row['tin'],
            'name'        => $row['name'] ?? null,
            'region'      => $row['region'] ?? null,
            'district'    => $row['district'] ?? null,
            'address'     => $row['address'] ?? null,
            'phone'       => $row['phone'] ?? null,
            'responsible' => $row['responsible'] ?? null,
            'created_by'  => $this->userId,
            'updated_by'  => $this->userId,
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

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            Log::warning('Импорт не прошёл валидацию', [
                'row'    => $failure->row(),
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
