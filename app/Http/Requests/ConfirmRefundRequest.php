<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'metode_refund' => ['required', 'in:tunai,transfer'],
            'tanggal_refund' => ['required', 'date', 'before_or_equal:now'],
            'referensi_refund' => ['nullable', 'string', 'max:100', 'required_if:metode_refund,transfer'],
        ];
    }

    public function messages(): array
    {
        return [
            'metode_refund.required' => 'Metode refund wajib dipilih.',
            'metode_refund.in' => 'Metode refund harus tunai atau transfer.',
            'tanggal_refund.required' => 'Tanggal refund aktual wajib diisi.',
            'tanggal_refund.before_or_equal' => 'Tanggal refund tidak boleh berada di masa depan.',
            'referensi_refund.required_if' => 'Referensi atau bukti transfer wajib diisi untuk refund transfer.',
        ];
    }
}
