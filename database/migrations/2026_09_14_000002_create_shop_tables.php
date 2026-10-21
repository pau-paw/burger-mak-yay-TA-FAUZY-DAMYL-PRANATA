<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('description', 500)->nullable();
            $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name', 150);
            $table->text('description');
            $table->unsignedInteger('price'); // Rupiah utuh, bukan float.
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('revision')->default(0);
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('number')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->uuid('checkout_token');
            $table->unique(['user_id', 'checkout_token']);
            $table->string('customer_name', 100);
            $table->string('phone', 25);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->string('payment_method', 30)->default('cash_on_pickup');
            $table->string('payment_status', 20)->default('unpaid');
            $table->unsignedBigInteger('total');
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name', 150);
            $table->unsignedInteger('unit_price');
            $table->unsignedSmallInteger('quantity');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
