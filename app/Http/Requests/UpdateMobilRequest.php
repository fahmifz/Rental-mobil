<?php

namespace App\Http\Requests;

use App\Models\Mobil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMobilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Mobil $mobil */
        $mobil = $this->route('mobil');

        return [
            'kategori_mobil_id' => ['nullable', 'integer', 'exists:kategori_mobil,id'],
            'nama_mobil' => ['required', 'string', 'max:255'],
            'merek' => ['required', 'string', 'max:255'],
            'tipe' => ['nullable', 'string', 'max:255'],
            'plat_nomor' => ['required', 'string', 'max:255', Rule::unique('mobil', 'plat_nomor')->ignore($mobil)],
            'tahun' => ['nullable', 'integer', 'between:1900,' . (now()->year + 1)],
            'transmisi' => ['nullable', 'string', 'max:50'],
            'kapasitas_penumpang' => ['required', 'integer', 'between:1,100'],
            'harga_sewa_per_hari' => ['required', 'numeric', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', Rule::in(['tersedia', 'disewa', 'perawatan'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var Mobil|null $mobil */
            $mobil = $this->route('mobil');

            if (! $mobil instanceof Mobil) {
                return;
            }

            $status = (string) $this->input('status');
            $hasActiveApprovedReservation = $mobil->reservasis()
                ->where('status', 'disetujui')
                ->whereDate('tanggal_mulai', '<=', today()->toDateString())
                ->whereDate('tanggal_selesai', '>=', today()->toDateString())
                ->exists();

            if ($status === 'tersedia' && $hasActiveApprovedReservation) {
                $validator->errors()->add(
                    'status',
                    'Mobil dengan reservasi aktif tidak dapat diubah menjadi tersedia.'
                );
            }

            if ($status === 'disewa' && ! $hasActiveApprovedReservation) {
                $validator->errors()->add(
                    'status',
                    'Mobil hanya dapat berstatus disewa jika memiliki reservasi aktif.'
                );
            }
        });
    }
}
