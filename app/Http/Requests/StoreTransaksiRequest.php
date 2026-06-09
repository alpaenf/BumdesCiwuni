<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransaksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'admin_unit']);
    }

    public function rules(): array
    {
        $isTutupBuku = $this->boolean('is_tutup_buku');

        return [
            'nasabah_id'       => ['required', 'exists:nasabah,id'],
            'tanggal'          => ['required', 'date'],
            'nominal'          => [$isTutupBuku ? 'nullable' : 'required', 'numeric', 'min:0'],
            'keterangan'       => ['nullable', 'string', 'max:255'],
            'jenis_transaksi'  => ['nullable', 'in:tarik_tunai,tarik_sembako'],
            'is_tutup_buku'    => ['nullable', 'boolean'],
            'foto_barang'      => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
        ];
    }
}
