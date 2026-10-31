<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'nominal_pembayaran' => ['required', 'numeric', 'min:0.01'],
            'tanggal_pembayaran' => ['required', 'date_format:Y-m-d'],
            'metode_pembayaran' => ['required', 'in:tunai,transfer'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'nominal_pembayaran.min' => 'Nominal pembayaran harus lebih besar dari nol.',
            'metode_pembayaran.in' => 'Metode pembayaran harus tunai atau transfer.',
        ];
    }
}
