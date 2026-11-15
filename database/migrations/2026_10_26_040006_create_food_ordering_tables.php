<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_items', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name', 120);
            $table->string('category', 60)->index();
            $table->text('description')->nullable();
            $table->unsignedInteger('price_cents');
            $table->char('currency', 3)->default('TJS');
            $table->string('image_url', 2048)->nullable();
            $table->boolean('is_available')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('food_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('request_key')->unique();
            $table->string('order_number', 24)->unique();
            $table->string('fulfillment_method', 16)->index();
            $table->text('delivery_address')->nullable();
            $table->string('delivery_phone', 40)->nullable();
            $table->string('location_note', 255)->nullable();
            $table->unsignedInteger('subtotal_cents');
            $table->unsignedInteger('delivery_fee_cents')->default(0);
            $table->unsignedInteger('total_cents');
            $table->char('currency', 3)->default('TJS');
            $table->string('status', 24)->default('pending')->index();
            $table->text('customer_note')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('food_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('food_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name', 120);
            $table->unsignedSmallInteger('quantity');
            $table->unsignedInteger('unit_price_cents');
            $table->unsignedInteger('line_total_cents');
            $table->timestamps();
            $table->index(['food_order_id', 'food_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_order_items');
        Schema::dropIfExists('food_orders');
        Schema::dropIfExists('food_items');
    }
};
