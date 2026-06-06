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
            'keterangan'       => ['nullable', 'string', 'max:255'],
            'jenis_transaksi'  => ['nullable', 'in:tarik_tunai,tarik_sembako'],
        ];
    }
}
