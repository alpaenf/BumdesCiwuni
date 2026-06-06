<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransaksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'nasabah_id'       => ['required', 'exists:nasabah,id'],
            'tanggal'          => ['required', 'date'],
            'nominal'          => ['required', 'numeric', 'min:1000'],
            'administrasi'     => ['nullable', 'numeric', 'min:0'],
            'keterangan'       => ['nullable', 'string', 'max:255'],
            'jenis_pengambilan'=> ['nullable', 'in:uang,barang'],
        ];
    }
}
