<?php

namespace App\Http\Requests\Swift;

use App\Http\Requests\ApiRequest;
use App\Models\Swift;

class SwiftUpdateRequest extends ApiRequest
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
        $routeParam = $this->route('id') ?? $this->route('swift') ?? null;
        $id = $routeParam instanceof Swift ? $routeParam->getKey() : $routeParam;

        return [
            'swift_code' => 'bail|required|string|max:11|unique:swifts,swift_code,' . $id . ',id',
            'bank_name'  => 'bail|required|string|max:255',
            'country'    => 'bail|required|string|size:3',
            'city'       => 'nullable|string|max:120',
            'address'    => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'swift_code.required' => 'Поле swift_code обязательно',
            'swift_code.string'   => 'Поле swift_code должен быть строкой',
            'swift_code.max'      => 'Поле swift_code не должен превышать 11 символов',
            'swift_code.unique'   => 'Такой swift_code уже существует',

            'bank_name.required'  => 'Поле bank_name обязательно',
            'bank_name.string'    => 'Поле bank_name должно быть строкой',
            'bank_name.max'       => 'Поле bank_name не должно превышать 255 символов',

            'country.required'    => 'Поле country обязательно',
            'country.string'      => 'Поле country должен быть строкой',
            'country.size'        => 'Поле country должен состоять из 3 символов (ISO-формат)',

            'city.string'         => 'Поле city должен быть строкой',
            'city.max'            => 'Поле city не должно превышать 120 символов',

            'address.string'      => 'Поле address должен быть строкой',
            'address.max'         => 'Поле address не должен превышать 255 символов',
        ];
    }
}
