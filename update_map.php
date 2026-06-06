<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\WebsiteSetting::setMany([
    'google_maps_embed' => '<iframe src="https://maps.google.com/maps?q=-7.6035548,109.0816557&hl=id&z=17&output=embed" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>'
]);
echo "Updated successfully!\n";
