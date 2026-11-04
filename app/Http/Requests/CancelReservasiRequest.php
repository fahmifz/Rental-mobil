<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelReservasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'alasan_pembatalan' => ['nullable', 'string', 'max:1000'],
            'jenis_refund' => ['nullable', 'in:penuh,sebagian,tidak_ada'],
            'nominal_refund' => ['nullable', 'numeric', 'min:0'],
            'alasan_refund' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_refund.in' => 'Jenis refund harus penuh, sebagian, atau tidak ada.',
            'nominal_refund.min' => 'Nominal refund tidak boleh negatif.',
        ];
    }
}
