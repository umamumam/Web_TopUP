<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- GAMES ---\n";
foreach (\App\Models\Game::all() as $g) {
    echo "ID: {$g->id} | Name: {$g->name} | Slug: {$g->slug}\n";
}

echo "\n--- PRODUCTS (First 20) ---\n";
foreach (\App\Models\Product::limit(20)->get() as $p) {
    echo "ID: {$p->id} | Name: {$p->name} | SKU: {$p->sku} | Game ID: {$p->game_id}\n";
}
