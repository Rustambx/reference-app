<?php

namespace App\Http\Requests\TreasuryAccount;

use App\Http\Requests\ApiRequest;
use App\Models\TreasuryAccount;

class TreasuryAccountUpdateRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'account'  => strtoupper(trim((string) $this->input('account'))),
            'currency' => strtoupper(trim((string) $this->input('currency'))),
            'mfo'      => trim((string) $this->input('mfo')),
            'name'     => trim((string) $this->input('name')),
            'department'=> trim((string) $this->input('department')),
        ]);
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $routeParam = $this->route('id') ?? $this->route('treasury_account') ?? null;
        $id = $routeParam instanceof TreasuryAccount ? $routeParam->getKey() : $routeParam;

        return [
            "account" => "required|string|max:34|unique:treasury_accounts,account," . $id . ",id",
            "mfo" => "required|string|max:9",
            "name" => "required",
            "department" => "required",
            "currency" => "required|string|max:3",
        ];
    }

    public function messages(): array
    {
        return [
            'account.required' => 'Поле account обязательно',
            'account.string' => 'Поле account должен быть строкой.',
            'account.max' => 'Поле account не должен превышать 34 символов.',
            'account.unique' => 'Такой account уже существует.',

            'mfo.required' => 'Поле mfo обязательно',
            'mfo.string' => 'Поле mfo должно быть строкой.',
            'mfo.max' => 'Поле mfo не должно превышать 9 символов.',

            'name.required' => 'Поле name обязательно',

            'department.string' => 'Поле department должен быть строкой.',

            'currency.required' => 'Поле currency обязательно',
            'currency.string' => 'Поле currency должен быть строкой.',
            'currency.max' => 'Поле currency не должен превышать 3 символа.',
        ];
    }
}
