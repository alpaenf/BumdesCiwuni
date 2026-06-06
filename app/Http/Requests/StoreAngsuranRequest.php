<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAngsuranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'pinjaman_id' => ['required', 'exists:pinjaman,id'],
            'tanggal'     => ['required', 'date'],
            'jumlah_bayar'=> ['required', 'numeric', 'min:1'],
            'pasaran'     => ['required', 'in:legi,pahing,pon,wage,kliwon'],
        ];
    }

    public function messages(): array
    {
        return [
            'pinjaman_id.required' => 'Pinjaman wajib dipilih.',
            'tanggal.required'     => 'Tanggal bayar wajib diisi.',
            'jumlah_bayar.required'=> 'Nominal bayar wajib diisi.',
            'pasaran.required'     => 'Hari pasaran wajib dipilih.',
            'pasaran.in'           => 'Hari pasaran tidak valid.',
        ];
    }
}
