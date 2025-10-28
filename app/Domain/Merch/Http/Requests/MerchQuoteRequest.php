<?php

namespace App\Domain\Merch\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MerchQuoteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'product_slug' => ['required','string','max:100'],
            'variant_code' => ['required','string','max:50'],
            'qty'          => ['required','integer','min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'qty.min' => 'Jumlah minimal 1 pcs.',
        ];
    }
}
