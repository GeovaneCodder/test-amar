<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0', 'regex:/^\d{1,15}(?:[.,]\d{1,2})?$/'],
            'type' => ['required', 'string', 'in:income,expense,Receita,Despesa'],
        ];
    }
}
