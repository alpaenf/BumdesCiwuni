<?php
$dir = 'c:\laragon\www\simpan-pinjam\resources\views\exports\simpan-pinjam\laporan';
$files = glob($dir . '\*.blade.php');
foreach ($files as $file) {
    $content = file_get_contents($file);
    $content = str_replace(
        "(\$filters['start_date'] ?? \$filters['end_date'])",
        "(isset(\$filters['start_date']) || isset(\$filters['end_date']))",
        $content
    );
    file_put_contents($file, $content);
}
echo "Done replacing in " . count($files) . " files.\n";
