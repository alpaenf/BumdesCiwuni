<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAngsuranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'admin_unit']);
    }

    public function rules(): array
    {
        return [
            'pinjaman_id' => ['required', 'exists:pinjaman,id'],
            'tanggal'     => ['required', 'date'],
            'jumlah_bayar'=> ['required', 'numeric', 'min:1'],
            'pasaran'     => ['nullable', 'in:legi,pahing,pon,wage,kliwon'],
        ];
    }

    public function messages(): array
    {
        return [
            'pinjaman_id.required' => 'Pinjaman wajib dipilih.',
            'tanggal.required'     => 'Tanggal bayar wajib diisi.',
            'jumlah_bayar.required'=> 'Nominal bayar wajib diisi.',
            'pasaran.in'           => 'Hari pasaran tidak valid.',
        ];
    }
}
