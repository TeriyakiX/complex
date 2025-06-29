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
            'email'  => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'text'  => 'required|string|min:10|max:1000',
            'agree' => 'required|boolean|in:1',
        ];
    }

    public function messages(): array
    {
        return [
            'agree.in' => 'Вы должны согласиться на обработку персональных данных.',
        ];
    }

}
