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
            "tin" => "required",
            "name" => "required",
            "region" => "required",
            "district" => "required",
            "address" => "required",
            "phone" => "required",
            "responsible" => "required"
        ];
    }
}
