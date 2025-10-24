<?php

namespace App\Domain\BusinessPrint\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BizQuoteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'product_slug' => ['required','string','max:100'],
            'qty_unit'     => ['required','integer','min:1'],

            // semua kategori boleh kirim side
            'side'         => ['required','in:1s,2s'],

            // brosur only, tapi kita izinkan 'none' default
            'fold_type'    => ['nullable','in:none,half_fold,tri_fold,z_fold'],

            // kartu_nama only, tapi kita izinkan 'none'
            'lamination'   => ['nullable','in:none,doff,glossy'],
        ];
    }

    public function messages(): array
    {
        return [
            'side.in' => 'Pilihan sisi harus 1 sisi atau 2 sisi.',
        ];
    }
}
