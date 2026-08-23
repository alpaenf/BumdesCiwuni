<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePelangganWifiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $lat = $this->input('gps_lat');
        $long = $this->input('gps_long');

        if ($lat !== null && (float)$lat > 90 && $long !== null && (float)$long < 0) {
            $this->merge([
                'gps_lat'  => $long,
                'gps_long' => $lat,
            ]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('pelanggan') instanceof \App\Models\PelangganWifi
            ? $this->route('pelanggan')->id
            : $this->route('pelanggan');

        return [
            'provider_wifi_id'            => 'nullable|exists:provider_wifi,id',
            'no'                          => 'nullable|integer',
            'nama'                        => 'required|string|max:255',
            'tanggal_daftar'              => 'nullable|date',
            'paket'                       => 'nullable|string|max:100',
            'nik'                         => 'nullable|string|max:20',
            'alamat'                      => 'nullable|string',
            'rt'                          => 'nullable|string|max:10',
            'rw'                          => 'nullable|string|max:10',
            'no_id_pel'                   => ['nullable', 'string', 'max:50', Rule::unique('pelanggan_wifi', 'no_id_pel')->ignore($id)],
            'no_wa'                       => 'nullable|string|max:20',
            'total_dasar_tarikan_non_ppn' => 'nullable|numeric|min:0',
            'ppn_dan_pph'                 => 'nullable|numeric|min:0',
            'ppn_pph'                     => 'nullable|numeric|min:0',
            'total_tarikan'               => 'nullable|numeric|min:0',
            'bagi_hasil_bumdes'           => 'nullable|numeric|min:0',
            'hasil_bumdes'                => 'nullable|numeric|min:0',
            'nota_bayar_provider'         => 'nullable|numeric|min:0',
            'total_provider'              => 'nullable|numeric|min:0',
            'gelombang'                   => 'nullable|in:1_15,16_30',
            'gps_long'                    => 'nullable|numeric|between:-180,180',
            'gps_lat'                     => 'nullable|numeric|between:-180,180',
            'foto_rumah'                  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama'                        => 'Nama',
            'tanggal_daftar'              => 'Tanggal Daftar',
            'paket'                       => 'Paket',
            'nik'                         => 'NIK',
            'alamat'                      => 'Alamat',
            'rt'                          => 'RT',
            'rw'                          => 'RW',
            'no_id_pel'                   => 'No ID Pelanggan',
            'no_wa'                       => 'No WhatsApp',
            'total_dasar_tarikan_non_ppn' => 'Total Dasar Tarikan Non PPN',
            'ppn_dan_pph'                 => 'PPN dan PPH',
            'ppn_pph'                     => 'PPN/PPH',
            'total_tarikan'               => 'Total Tarikan',
            'bagi_hasil_bumdes'           => 'Bagi Hasil BUMDes',
            'hasil_bumdes'                => 'Hasil BUMDes',
            'nota_bayar_provider'         => 'Nota Bayar Provider',
            'total_provider'              => 'Total Provider',
            'gelombang'                   => 'Jadwal Gelombang Tagihan',
            'gps_long'                    => 'GPS Longitude',
            'gps_lat'                     => 'GPS Latitude',
            'foto_rumah'                  => 'Foto Rumah',
        ];
    }
}
