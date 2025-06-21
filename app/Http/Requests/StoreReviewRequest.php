<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => 'required|string|min:10|max:1000',
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        $message = 'Неверные данные: ' . implode(', ', $errors);

        // Генерируем ответ
        throw new HttpResponseException(response()->json([
            'message' => $message,
            'errors'  => $validator->errors(),
        ], 400));
    }
}
