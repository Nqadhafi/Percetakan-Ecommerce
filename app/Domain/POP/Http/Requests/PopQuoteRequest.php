<?php

namespace App\Domain\POP\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PopQuoteRequest extends FormRequest
{
    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            'product_slug' => ['required','string','max:100'],
            'material'     => ['required','string','max:32'],
            'side'         => ['required','in:1s,2s'],
            'lamination'   => ['required','in:none,doff,glossy'],
            'cutting'      => ['required','in:tanpa_potong,potong_lurus,potong_pola_die_cut'],
            'qty'          => ['required','integer','min:1'],
        ];
    }
}
