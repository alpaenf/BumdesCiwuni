<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

trait WithLogo
{
    public function drawings()
    {
        $logoPath = public_path('logo.png');
        if (!file_exists($logoPath)) {
            return [];
        }

        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo BUMDes');
        $drawing->setPath($logoPath);
        $drawing->setHeight(70);
        $drawing->setCoordinates('A1');
        return $drawing;
    }
}
