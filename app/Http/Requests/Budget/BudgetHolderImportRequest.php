<?php

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;

class BudgetHolderImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:csv,txt|max:40960',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Поле file обязательно',
            'file.file' => 'Поле file должен быть файлом',
            'file.mimes' => 'Доступные типы: csv, txt',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if (!$this->hasFile('file')) {
                return;
            }

            $path = $this->file('file')->getRealPath();
            if (!$path || !is_readable($path)) {
                $v->errors()->add('file', 'Файл недоступен для чтения');
                return;
            }

            $fh = fopen($path, 'rb');
            if (!$fh) {
                $v->errors()->add('file', 'Не удалось открыть файл');
                return;
            }

            $delimiters = [';', ','];
            $headers = null;
            foreach ($delimiters as $d) {
                rewind($fh);
                $headers = fgetcsv($fh, 0, $d);
                if (is_array($headers) && count($headers) > 1) {
                    $delimiter = $d;
                    break;
                }
            }

            if (!isset($delimiter)) {
                $v->errors()->add('file', 'Не удалось определить разделитель CSV (ожидается , или ;).');
                fclose($fh);
                return;
            }

            $expected = ['tin', 'name', 'region', 'district', 'address', 'phone', 'phone', 'responsible'];
            $normalized = array_map(fn($h) => strtolower(trim((string) $h)), $headers ?? []);

            foreach ($expected as $col) {
                if (!in_array($col, $normalized, true)) {
                    $v->errors()->add('file', "Отсутствует обязательная колонка: {$col}");
                }
            }

            $row2 = fgetcsv($fh, 0, $delimiter);
            if ($row2 === false) {
                $v->errors()->add('file', 'Файл не содержит данных (только заголовок).');
            }

            fclose($fh);
        });
    }
}
