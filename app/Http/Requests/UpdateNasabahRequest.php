<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNasabahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'admin_unit']);
    }

    public function rules(): array
    {
        $nasabah = $this->route('nasabah');

        return [
            'nama'             => ['required', 'string', 'max:255'],
            'nik'              => ['required', 'string', 'size:16', Rule::unique('nasabah', 'nik')->ignore($nasabah)],
            'nomor_rekening'   => ['required', 'string', 'max:50', Rule::unique('nasabah', 'nomor_rekening')->ignore($nasabah)],
            'alamat'           => ['required', 'string'],
            'no_hp'            => ['required', 'string', 'max:20'],
            'pekerjaan'        => ['nullable', 'string', 'max:100'],
            'jaminan'          => ['nullable', 'required_if:kategori.*,pinjaman', 'string', 'max:255'],
            'tanggal_bergabung'=> ['nullable', 'date'],
            'status'           => ['nullable', 'in:aktif,tidak aktif'],
            'foto'             => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'kategori'         => ['nullable', 'array'],
            'kategori.*'       => ['string', 'in:tabungan,sembako,pinjaman'],
        ];
    }
}
