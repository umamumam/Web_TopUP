<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Product;
use App\Services\DigiflazzService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('game')->latest()->paginate(20);
        $games = Game::all();
        return Inertia::render('admin/products/index', [
            'products' => $products,
            'games' => $games
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'original_price' => 'required|numeric|min:0',
        ]);

        Product::create([
            'game_id' => $request->game_id,
            'name' => $request->name,
            'sku' => $request->sku,
            'price' => $request->price,
            'original_price' => $request->original_price,
            'is_active' => true,
        ]);

        return back()->with('success', 'Produk berhasil ditambahkan secara manual.');
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'price' => 'required|numeric|min:0',
            'original_price' => 'required|numeric|min:0',
        ]);

        $product->update($request->all());

        return back()->with('success', 'Produk berhasil diupdate.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Produk berhasil dihapus.');
    }

    public function getPriceList(DigiflazzService $digiflazz)
    {
        $cacheKey = 'digiflazz_pricelist_prepaid';
        
        $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($digiflazz) {
            $response = $digiflazz->getPriceList();
            
            if (is_array($response) && isset($response['data']) && is_array($response['data'])) {
                // Check for error RC
                if (isset($response['data']['rc']) && $response['data']['rc'] !== '00') {
                    return ['error' => $response['data']['message'] ?? 'Error dari provider'];
                }
                return $response['data'];
            }
            
            return null;
        });

        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data: Format response tidak valid.'], 400);
        }

        if (isset($data['error'])) {
            // Clean cache if it's an error so we can retry sooner
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
            return response()->json(['success' => false, 'message' => $data['error']], 400);
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function sync(DigiflazzService $digiflazz)
    {
        $cacheKey = 'digiflazz_pricelist_prepaid';
        
        // Try to get from cache first or fetch
        $products = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($digiflazz) {
            $response = $digiflazz->getPriceList();
            return (is_array($response) && isset($response['data']) && is_array($response['data'])) ? $response['data'] : null;
        });

        if (!$products || (is_array($products) && isset($products['rc']))) {
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
            return back()->with('error', "Gagal sinkronisasi: Data tidak ditemukan atau limit tercapai.");
        }

        $count = 0;
        foreach ($products as $item) {
            if (!is_array($item)) continue;

            if (isset($item['category']) && $item['category'] === 'Games') {
                $gameName = $item['brand'];
                $game = Game::where('name', 'LIKE', '%' . $gameName . '%')->first();

                if ($game) {
                    $settings = \App\Models\Setting::whereIn('key', ['profit_margin_type', 'profit_margin_value'])
                        ->get()
                        ->pluck('value', 'key');

                    $marginType = $settings['profit_margin_type'] ?? 'flat';
                    $marginValue = (float) ($settings['profit_margin_value'] ?? 2000);
                    $modalPrice = $item['price'];
                    
                    if ($marginType === 'percent') {
                        $markup = $modalPrice * ($marginValue / 100);
                    } else {
                        $markup = $marginValue;
                    }
                    
                    $sellingPrice = $modalPrice + $markup;

                    Product::updateOrCreate(
                        ['sku' => $item['buyer_sku_code']],
                        [
                            'game_id' => $game->id,
                            'name' => $item['product_name'],
                            'price' => $sellingPrice,
                            'original_price' => $modalPrice,
                            'is_active' => $item['seller_status'] === 'active' && $item['buyer_status'] === 'active',
                        ]
                    );
                    $count++;
                }
            }
        }

        return back()->with('success', "Berhasil sinkronisasi $count produk.");
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'category' => 'required',
                'brand' => 'required',
                'product_name' => 'required',
                'buyer_sku_code' => 'required',
                'price' => 'required|numeric',
                'seller_status' => 'nullable',
                'buyer_status' => 'nullable',
            ]);

            // Find or create Category
            $categoryName = $request->category;
            $category = \App\Models\Category::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($categoryName)],
                ['name' => $categoryName]
            );

            // Find or create Game
            $gameName = $request->brand;
            $game = Game::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($gameName)],
                [
                    'name' => $gameName,
                    'category_id' => $category->id,
                    'is_active' => true,
                    'input_label' => 'User ID',
                ]
            );

            // Profit Margin Logic
            $settings = \App\Models\Setting::whereIn('key', ['profit_margin_type', 'profit_margin_value'])
                ->get()
                ->pluck('value', 'key');

            $marginType = $settings['profit_margin_type'] ?? 'flat';
            $marginValue = (float) ($settings['profit_margin_value'] ?? 2000);
            $modalPrice = (float) $request->price;
            
            if ($marginType === 'percent') {
                $markup = $modalPrice * ($marginValue / 100);
            } else {
                $markup = $marginValue;
            }
            $sellingPrice = $modalPrice + $markup;
            $isSellerActive = ! $request->has('seller_status') || $request->seller_status === true || $request->seller_status === 'active';
            $isBuyerActive = ! $request->has('buyer_status') || $request->buyer_status === true || $request->buyer_status === 'active';

            $product = Product::updateOrCreate(
                ['sku' => $request->buyer_sku_code],
                [
                    'game_id' => $game->id,
                    'name' => $request->product_name,
                    'price' => $sellingPrice,
                    'original_price' => $modalPrice,
                    'is_active' => $isSellerActive && $isBuyerActive,
                ]
            );

            return response()->json([
                'success' => true, 
                'message' => "Berhasil mengimpor {$product->name}"
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Validasi gagal: ' . implode(', ', \Illuminate\Support\Arr::flatten($e->errors()))
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Import Error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
