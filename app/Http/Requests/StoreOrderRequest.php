<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'product_id' => 'nullable|uuid|exists:products,id',
            'warehouse_product_id' => 'nullable|uuid|exists:warehouse_products,id',
            'quantity' => 'required|integer|min:1',
            'text' => 'nullable|string',
            'status' => ['nullable', Rule::in(['new', 'in_progress', 'done'])],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->validated();

            if (empty($data['product_id']) && empty($data['warehouse_product_id'])) {
                $validator->errors()->add('product_id', 'Необходимо указать product_id или warehouse_product_id');
            }

            if (empty($data['phone']) && empty($data['email'])) {
                $validator->errors()->add('contact', 'Необходимо указать телефон или email');
            }
        });
    }
}
