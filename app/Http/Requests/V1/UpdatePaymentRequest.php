<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
USE Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user != null && $user->tokenCan('update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $method = $this->method();

        if($method == 'PUT') {
            return [
                'customer_id' => ['required', 'integer'],
                'invoice_id' => ['required', 'integer'],
                'amount' => ['required','integer'],
                'paid_method' => ['required', Rule::in(['CC','DC','FPX','cc','dc','fpx'])],
                'paid_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
            ];
        }else{
            return [
                'customer_id' => ['sometimes','required', 'integer'],
                'invoice_id' => ['sometimes','required', 'integer'],
                'amount' => ['sometimes','required','integer'],
                'paid_method' => ['sometimes','required', Rule::in(['CC','DC','FPX','cc','dc','fpx'])],
                'paid_at' => ['sometimes','nullable', 'date_format:Y-m-d H:i:s'],
            ];
        }
    }

    // protected function prepareForValidation()
    // {
    //     if ($this->customer_id) {
    //         $this->merge([
    //             'customer_id' => $this->customer_id,
    //         ]);
    //     }

    //     if ($this->invoice_id) {
    //         $this->merge([
    //             'invoice_id' => $this->invoice_id,
    //         ]);
    //     }

    //     if ($this->paid_method) {
    //         $this->merge([
    //             'paid_method' => $this->paid_method,
    //         ]);
    //     }

    //     if (!is_null($this->paid_at)) {
    //         $this->merge([
    //             'paid_at' => $this->paid_at,
    //         ]);
    //     }
    // }
}
