<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
