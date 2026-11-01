<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengembalianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'tanggal_pengembalian' => ['required', 'date', 'before_or_equal:now'],
            'kondisi_mobil' => ['required', 'string', 'in:baik,rusak_ringan,rusak_berat'],
            'catatan_kondisi' => ['nullable', 'string', 'max:1000'],
            'status_mobil_setelah_kembali' => ['required', 'string', 'in:tersedia,perawatan'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_pengembalian.required' => 'Waktu pengembalian aktual wajib diisi.',
            'tanggal_pengembalian.date' => 'Format waktu pengembalian tidak valid.',
            'tanggal_pengembalian.before_or_equal' => 'Waktu pengembalian tidak boleh berada di masa depan.',
            'kondisi_mobil.required' => 'Kondisi mobil setelah dikembalikan wajib dipilih.',
            'kondisi_mobil.in' => 'Kondisi mobil harus salah satu dari: baik, rusak ringan, atau rusak berat.',
            'status_mobil_setelah_kembali.required' => 'Status mobil setelah pengembalian wajib dipilih.',
            'status_mobil_setelah_kembali.in' => 'Status mobil harus tersedia atau perawatan.',
        ];
    }
}

