<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this-> user();
        return $user != null && $user->tokenCan('create'); //return true or false if user has access
       
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer'],
            'invoice_id'=> ['required', 'integer'],
            'amount'=> ['required', 'integer'],
            'paid_method'=> ['required', Rule::in(['CC','DC','FPX','cc','dc','fpx'])],
            'paid_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
        ];
    }
}