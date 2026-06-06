<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePinjamanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'nasabah_id'      => ['required', 'exists:nasabah,id'],
            'tanggal_akad'    => ['required', 'date'],
            'pinjaman_pokok'  => ['required', 'numeric', 'min:1'],
            'bunga'           => ['required', 'numeric', 'min:0', 'max:100'],
            'nominal_setoran' => ['required', 'numeric', 'min:1'],
            'foto_perjanjian' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
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
