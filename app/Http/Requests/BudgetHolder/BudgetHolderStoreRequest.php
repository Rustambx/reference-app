<?php

namespace App\Http\Requests\BudgetHolder;

use App\Http\Requests\ApiRequest;

class BudgetHolderStoreRequest extends ApiRequest
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
            "tin" => "bail|required|string|max:14|unique:budget_holders,tin",
            "name" => "required",
            "region" => "nullable|string|max:120",
            "district" => "nullable|string|max:120",
            "address" => "nullable|string",
            "phone" => "nullable|string|max:50",
            "responsible" => "nullable|string|max:120"
        ];
    }

    public function messages(): array
    {
        return [
            'tin.required' => 'Поле tin обязательно',
            'tin.string'   => 'Поле tin должен быть строкой.',
            'tin.max'      => 'Поле tin не должен превышать 14 символов.',
            'tin.unique'   => 'Такой tin уже существует.',

            'name.required' => 'Поле name обязательно',

            'region.string'    => 'Поле region должно быть строкой.',
            'region.max'       => 'Поле region не должно превышать 120 символов.',

            'district.string'    => 'Поле district должно быть строкой.',
            'district.max'       => 'Поле district не должно превышать 120 символов.',

            'address.string'    => 'Поле district должно быть строкой.',

            'phone.string'    => 'Поле phone должно быть строкой.',
            'phone.max'       => 'Поле phone не должно превышать 50 символов.',

            'responsible.string'    => 'Поле responsible должно быть строкой.',
            'responsible.max'       => 'Поле responsible не должно превышать 120 символов.',
        ];
    }
}
