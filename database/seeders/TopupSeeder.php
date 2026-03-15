<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Game;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TopupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Category
        $mobileGame = Category::create([
            'name' => 'Mobile Games',
            'slug' => 'mobile-games',
            'icon' => 'smartphone'
        ]);

        // 2. Seed Games
        $mlbb = Game::create([
            'category_id' => $mobileGame->id,
            'name' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'thumbnail' => 'https://example.com/mlbb-thumb.jpg',
            'banner' => 'https://example.com/mlbb-banner.jpg',
            'description' => 'Top Up Diamond Mobile Legends murah dan cepat.',
            'input_label' => 'User ID & Zone ID',
            'has_server' => true,
        ]);

        $ff = Game::create([
            'category_id' => $mobileGame->id,
            'name' => 'Free Fire',
            'slug' => 'free-fire',
            'thumbnail' => 'https://example.com/ff-thumb.jpg',
            'banner' => 'https://example.com/ff-banner.jpg',
            'description' => 'Top Up Diamond Free Fire murah dan cepat.',
            'input_label' => 'Player ID',
            'has_server' => false,
        ]);

        // 3. Seed Products (Skus are dummy for now)
        Product::create([
            'game_id' => $mlbb->id,
            'name' => '5 Diamonds',
            'sku' => 'ML5',
            'price' => 1500,
            'original_price' => 1200,
        ]);

        Product::create([
            'game_id' => $mlbb->id,
            'name' => '50 Diamonds',
            'sku' => 'ML50',
            'price' => 15000,
            'original_price' => 12000,
        ]);

        Product::create([
            'game_id' => $ff->id,
            'name' => '70 Diamonds',
            'sku' => 'FF70',
            'price' => 10000,
            'original_price' => 8000,
        ]);

        // 4. Seed Payment Methods
        $payments = [
            // E-Wallet
            ['name' => 'QRIS (Semua E-Wallet)', 'code' => 'qris', 'type' => 'e-wallet', 'fee_percent' => 0.7, 'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a2/Logo_QRIS.svg/1200px-Logo_QRIS.svg.png'],
            ['name' => 'DANA', 'code' => 'dana', 'type' => 'e-wallet', 'fee_percent' => 1.5, 'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/72/Logo_dana_blue.svg/1200px-Logo_dana_blue.svg.png'],
            ['name' => 'OVO', 'code' => 'ovo', 'type' => 'e-wallet', 'fee_percent' => 1.5, 'image' => 'https://upload.wikimedia.org/wikipedia/commons/e/eb/Logo_ovo_purple.svg'],
            ['name' => 'ShopeePay', 'code' => 'shopeepay', 'type' => 'e-wallet', 'fee_percent' => 1.5, 'image' => 'https://upload.wikimedia.org/wikipedia/commons/f/fe/ShopeePay.svg'],
            ['name' => 'GoPay', 'code' => 'gopay', 'type' => 'e-wallet', 'fee_percent' => 2.0, 'image' => 'https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg'],

            // Virtual Account
            ['name' => 'BCA Virtual Account', 'code' => 'bca_va', 'type' => 'bank_transfer', 'fee_flat' => 4000, 'image' => 'https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg'],
            ['name' => 'Mandiri Virtual Account', 'code' => 'mandiri_va', 'type' => 'bank_transfer', 'fee_flat' => 4000, 'image' => 'https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg'],
            ['name' => 'BNI Virtual Account', 'code' => 'bni_va', 'type' => 'bank_transfer', 'fee_flat' => 4000, 'image' => 'https://upload.wikimedia.org/wikipedia/id/5/55/BNI_logo.svg'],
            ['name' => 'BRI Virtual Account', 'code' => 'bri_va', 'type' => 'bank_transfer', 'fee_flat' => 4000, 'image' => 'https://upload.wikimedia.org/wikipedia/commons/2/2e/BRI_Logo.svg'],
            ['name' => 'Permata Virtual Account', 'code' => 'permata_va', 'type' => 'bank_transfer', 'fee_flat' => 4000, 'image' => 'https://upload.wikimedia.org/wikipedia/id/f/f6/Permata_Bank_Logo.png'],

            // Retail
            ['name' => 'Alfamart', 'code' => 'alfamart', 'type' => 'retail', 'fee_flat' => 5000, 'image' => 'https://upload.wikimedia.org/wikipedia/commons/8/86/Alfamart_logo.svg'],
            ['name' => 'Indomaret', 'code' => 'indomaret', 'type' => 'retail', 'fee_flat' => 5000, 'image' => 'https://upload.wikimedia.org/wikipedia/commons/9/9d/Logo_Indomaret.png'],
        ];

        foreach ($payments as $payment) {
            PaymentMethod::create($payment);
        }
    }
}
