<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'text' => ['nullable', 'string'],
            'agree' => 'required|boolean|in:1',
        ];
    }

    public function messages(): array
    {
        return [
            'agree.in' => 'Вы должны согласиться на обработку персональных данных.',
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
