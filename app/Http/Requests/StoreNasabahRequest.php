<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNasabahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'admin_unit']);
    }

    public function rules(): array
    {
        return [
            'nama'             => ['required', 'string', 'max:255'],
            'nik'              => ['required', 'string', 'size:16', 'unique:nasabah,nik'],
            'nomor_rekening'   => ['nullable', 'string', 'max:50', 'unique:nasabah,nomor_rekening'],
            'alamat'           => ['required', 'string'],
            'no_hp'            => ['required', 'string', 'max:20'],
            'pekerjaan'        => ['nullable', 'string', 'max:100'],
            'jaminan'          => ['nullable', 'required_if:kategori.*,pinjaman', 'string', 'max:255'],
            'tanggal_bergabung'=> ['nullable', 'date'],
            'status'           => ['nullable', 'in:aktif,tidak aktif'],
            'foto'             => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'foto_jaminan'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'kategori'         => ['nullable', 'array'],
            'kategori.*'       => ['string', 'in:tabungan,sembako,pinjaman'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required'   => 'Nama lengkap wajib diisi.',
            'nik.required'    => 'NIK wajib diisi.',
            'nik.size'        => 'NIK harus 16 digit.',
            'nik.unique'      => 'NIK sudah terdaftar.',
            'alamat.required' => 'Alamat wajib diisi.',
            'no_hp.required'  => 'Nomor WhatsApp wajib diisi.',
        ];
    }
}
