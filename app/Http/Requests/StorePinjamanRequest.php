<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePinjamanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'admin_unit']);
    }

    public function rules(): array
    {
        return [
            'nasabah_id'      => ['required', 'exists:nasabah,id'],
            'tanggal_akad'    => ['required', 'date'],
            'pinjaman_pokok'  => ['required', 'numeric', 'min:1'],
            'bunga'           => ['required', 'numeric', 'min:0', 'max:100'],
            'nominal_setoran' => ['required', 'numeric', 'min:1'],
            'biaya_tambahan'  => ['nullable', 'numeric', 'min:0'],
            'jenis_pinjaman'  => ['required', 'in:uang,barang,sembako'],
            'keterangan'      => ['nullable', 'string', 'max:255'],
            'foto_perjanjian' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
            'foto_barang'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'nasabah_id.required'      => 'Nasabah wajib dipilih.',
            'nasabah_id.exists'        => 'Nasabah tidak ditemukan.',
            'tanggal_akad.required'    => 'Tanggal akad wajib diisi.',
            'pinjaman_pokok.required'  => 'Nominal pinjaman wajib diisi.',
            'nominal_setoran.required' => 'Nominal setoran wajib diisi.',
        ];
    }
}
