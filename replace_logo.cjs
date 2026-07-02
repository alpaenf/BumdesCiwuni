const fs = require('fs');
const files = [
  'resources/views/exports/simpan-pinjam/struk-pinjaman-pdf.blade.php',
  'resources/views/exports/simpan-pinjam/struk-tabungan-sembako-pdf.blade.php'
];

files.forEach(f => {
  let content = fs.readFileSync(f, 'utf8');
  content = content.replace(/<img src="\{\{ public_path\('logo2\.png'\) \}\}"/g, '<img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path(\'logo2.png\'))) }}"');
  fs.writeFileSync(f, content);
});
