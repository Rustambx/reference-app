<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class ApiRequest extends FormRequest
{
    public function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        $response = response()->json([
            'message' => 'Ошибка валидации',
            'data' => [
                'field' => $errors
            ],
            'timestamp' => Carbon::now('UTC')->format('Y-m-d\TH:i:s.u\Z'),
            'success' => false,
        ], 422);

        throw new HttpResponseException($response);
    }
}
