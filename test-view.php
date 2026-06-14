<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

try {
    echo view('exports.simpan-pinjam.laporan.tabungan', [
        'transaksi' => [],
        'summary' => ['total_setoran'=>0, 'total_penarikan'=>0, 'total_admin'=>0],
        'filters' => [],
        'jenis' => 'reguler'
    ])->render();
    echo "NO ERROR";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage();
}
