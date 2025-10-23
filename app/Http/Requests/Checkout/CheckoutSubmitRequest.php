<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutSubmitRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'customer_name'    => ['required','string','max:120'],
            'customer_phone'   => ['required','string','max:32'],
            'customer_address' => ['required','string','max:2000'],

            'payment_method'   => ['required','in:bank,qris'],
            'payment_note'     => ['nullable','string','max:500'],
            'payment_proof'    => ['required','file','mimes:jpg,jpeg,png,webp,pdf','max:5120'], // 5MB

            // optional meta
            'meta'             => ['nullable','array'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_proof.mimes' => 'Bukti harus berupa gambar atau PDF.',
            'payment_proof.max'   => 'Ukuran bukti maksimal 5MB.',
        ];
    }
}
