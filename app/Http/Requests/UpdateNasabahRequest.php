<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNasabahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        $nasabah = $this->route('nasabah');

        return [
            'nama'             => ['required', 'string', 'max:255'],
            'nik'              => ['required', 'string', 'size:16', Rule::unique('nasabah', 'nik')->ignore($nasabah)],
            'alamat'           => ['required', 'string'],
            'no_hp'            => ['required', 'string', 'max:20'],
            'pekerjaan'        => ['nullable', 'string', 'max:100'],
            'jaminan'          => ['nullable', 'string', 'max:255'],
            'tanggal_bergabung'=> ['nullable', 'date'],
            'status'           => ['nullable', 'in:aktif,tidak aktif'],
            'foto'             => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }
}
