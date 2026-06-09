<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pinjamans = App\Models\Pinjaman::all();
foreach($pinjamans as $p) {
    $expected = $p->pinjaman_pokok + ($p->pinjaman_pokok * $p->bunga / 100) + $p->biaya_tambahan;
    if ($p->total_tagihan != $expected) {
        $diff = $expected - $p->total_tagihan;
        $p->total_tagihan = $expected;
        $p->sisa_pinjaman += $diff;
        $p->save();
    }
}
echo "Done";
