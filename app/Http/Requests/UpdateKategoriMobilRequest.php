<?php

namespace App\Http\Requests;

use App\Models\KategoriMobil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKategoriMobilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        /** @var KategoriMobil $kategoriMobil */
        $kategoriMobil = $this->route('kategori_mobil');

        return [
            'nama_kategori' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategori_mobil', 'nama_kategori')->ignore($kategoriMobil),
            ],
            'deskripsi' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_kategori.unique' => 'Nama kategori sudah digunakan oleh kategori lain.',
        ];
    }
}
