<?php
// app/Domain/Sticker/Http/Requests/StickerQuoteRequest.php
namespace App\Domain\Sticker\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StickerQuoteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array {
        return [
            'product_slug' => ['required','string','max:100'],
            'material'     => ['required','string','max:32'],
            'lamination'   => ['required','in:none,doff,glossy'],
            'finish'       => ['required','in:none,straight_cut,kiss_cut,die_cut'],
            'qty'          => ['required','integer','min:1'],
            'note'         => ['nullable','string','max:300'],
        ];
    }
}
