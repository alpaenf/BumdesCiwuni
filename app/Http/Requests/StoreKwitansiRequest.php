<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKwitansiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'admin_unit']);
    }

    public function rules(): array
    {
        return [
            'nasabah_id' => ['required', 'exists:nasabah,id'],
            'nominal'    => ['required', 'numeric', 'min:1'],
            'keperluan'  => ['required', 'string', 'max:500'],
            'tanggal'    => ['required', 'date'],
            'foto'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }
}
