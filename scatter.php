<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$hospitals = App\Models\MapPoint::where('status', 'unverified')->get();
foreach($hospitals as $h) {
    $h->latitude = 10.4806 + (rand(-100, 100) / 10000);
    $h->longitude = -66.9036 + (rand(-100, 100) / 10000);
    $h->save();
}
echo "Dispersed " . count($hospitals) . " hospitals.\n";
