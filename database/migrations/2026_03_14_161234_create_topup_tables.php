<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('thumbnail')->nullable();
            $table->string('banner')->nullable();
            $table->text('description')->nullable();
            $table->string('input_label')->default('User ID'); // e.g. "User ID" or "ID & Server"
            $table->boolean('has_server')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('sku')->unique(); // ID from Digiflazz
            $table->decimal('price', 15, 2);
            $table->decimal('original_price', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // e.g. gopay, qris, bca_va
            $table->string('type'); // e.g. e-wallet, bank_transfer
            $table->decimal('fee_flat', 15, 2)->default(0);
            $table->decimal('fee_percent', 5, 2)->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference_id')->unique(); // Internal Order ID
            $table->foreignId('game_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('payment_method_id')->nullable()->constrained();
            $table->string('target_id'); // ID Game User
            $table->string('server_id')->nullable();
            $table->string('whatsapp_number');
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->string('status')->default('pending'); // pending, processing, success, failed, canceled
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, expired, failed
            $table->string('sn')->nullable(); // Serial Number from Digiflazz
            $table->text('provider_payload')->nullable(); // Log from Digiflazz/Midtrans
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('products');
        Schema::dropIfExists('games');
        Schema::dropIfExists('categories');
    }
};
