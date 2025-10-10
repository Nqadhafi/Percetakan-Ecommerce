<?php

namespace App\Domain\MMT\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Domain\MMT\Models\MmtProduct;

class MmtQuoteRequest extends FormRequest
{
    protected ?MmtProduct $product = null;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Tentukan produk lebih awal supaya rules bisa kondisional.
     */
    protected function prepareForValidation(): void
    {
        $slug = (string) $this->input('product_slug', '');
        $this->product = MmtProduct::where('slug', $slug)->first();
    }

    public function rules(): array
    {
        // Selalu wajib: slug harus ada
        $base = [
            'product_slug' => ['required','string','max:100'],
        ];

        // Jika produk tidak ketemu, biarkan validator umum yang akan komplain,
        // tapi supaya UX lebih jelas, kita tetap definisikan rules paling longgar.
        $kind = $this->product?->kind;

        if ($kind === 'package') {
            // ===== Paket (flat price) =====
            return $base + [
                'material'     => ['required','string','max:32'],
                'size'         => ['required','string','max:16'],
                'qty'          => ['required','integer','min:1'],
                'include_pole' => ['nullable','boolean'],
                // Bidang meteran & finishing tidak relevan → jangan diwajibkan
            ];
        }

        // ===== Meteran (by m2) — default =====
        return $base + [
            'width'    => ['required','numeric','gt:0'],
            'height'   => ['required','numeric','gt:0'],
            'unit'     => ['required', Rule::in(['cm','m'])],
            'material' => ['required','string','max:32'],
            'finishing' => ['nullable','array'],
            'finishing.*.name' => ['required','string','max:24'],
            'finishing.*.qty'  => ['nullable','integer','min:1'], // untuk per_point
        ];
    }

    public function messages(): array
    {
        return [
            'product_slug.required' => 'Produk wajib dipilih.',
            'width.required'        => 'Lebar wajib diisi untuk produk meteran.',
            'height.required'       => 'Tinggi wajib diisi untuk produk meteran.',
            'size.required'         => 'Ukuran wajib dipilih untuk produk paket.',
            'qty.min'               => 'Jumlah minimal 1.',
        ];
    }
}
