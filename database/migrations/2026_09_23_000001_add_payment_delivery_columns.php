<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('fulfillment_type', 20)->default('pickup')->after('notes');
            $table->text('delivery_address')->nullable()->after('fulfillment_type');
            $table->unsignedInteger('delivery_fee')->default(0)->after('delivery_address');
            $table->string('payment_proof')->nullable()->after('payment_status');
            $table->timestamp('paid_at')->nullable()->after('payment_proof');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'fulfillment_type',
                'delivery_address',
                'delivery_fee',
                'payment_proof',
                'paid_at',
            ]);
        });
    }
};
