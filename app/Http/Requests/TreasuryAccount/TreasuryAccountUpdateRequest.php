<?php

namespace App\Http\Requests\TreasuryAccount;

use Illuminate\Foundation\Http\FormRequest;

class TreasuryAccountUpdateRequest extends FormRequest
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
            "account" => "required|string|max:34",
            "mfo" => "required|string|max:9",
            "name" => "required",
            "department" => "required",
            "currency" => "required|string|max:3",
        ];
    }
}
