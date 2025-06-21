<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'manufacturer_id' => 'required|exists:manufacturers,id',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Неверные данные',
            'errors' => $validator->errors(),
        ], 400));
    }
}
