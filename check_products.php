<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = \App\Models\Product::orderBy('game_id')->get(['id', 'name', 'sku', 'game_id']);
foreach ($products as $p) {
    echo "ID: {$p->id} | Name: {$p->name} | SKU: {$p->sku} | Game ID: {$p->game_id}\n";
}
