<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array {
        return [
            'full_name' => ['nullable','string','max:120'],
            'phone'     => ['nullable','string','max:30'],
            'company'   => ['nullable','string','max:120'],
            'tax_id'    => ['nullable','string','max:32'],
            'address'   => ['nullable','array'],
            'address.street'      => ['nullable','string','max:200'],
            'address.district'    => ['nullable','string','max:120'],
            'address.city'        => ['nullable','string','max:120'],
            'address.province'    => ['nullable','string','max:120'],
            'address.postal_code' => ['nullable','string','max:10'],
            'address.notes'       => ['nullable','string','max:200'],
        ];
    }
}
