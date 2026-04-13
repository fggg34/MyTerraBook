<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('car_id')->constrained()->restrictOnDelete();
            $table->foreignId('car_unit_id')->nullable()->constrained('car_units')->nullOnDelete();
            $table->foreignId('price_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pickup_location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('dropoff_location_id')->constrained('locations')->restrictOnDelete();
            $table->timestamp('pickup_at');
            $table->timestamp('dropoff_at');
            $table->string('order_status', 32)->default('pending');
            $table->string('rental_status', 32)->nullable();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 32)->nullable();
            $table->string('customer_country', 4)->nullable();
            $table->unsignedBigInteger('base_rental_cents')->default(0);
            $table->unsignedBigInteger('extras_cents')->default(0);
            $table->unsignedBigInteger('fees_cents')->default(0);
            $table->unsignedBigInteger('discount_cents')->default(0);
            $table->unsignedBigInteger('tax_cents')->default(0);
            $table->unsignedBigInteger('total_cents')->default(0);
            $table->string('currency', 8)->default('EUR');
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->json('pricing_snapshot')->nullable();
            $table->json('custom_field_values')->nullable();
            $table->text('notes')->nullable();
            $table->text('admin_internal_note')->nullable();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('payment_lock_expires_at')->nullable();
            $table->timestamps();

            $table->index(['car_id', 'pickup_at', 'dropoff_at']);
            $table->index('order_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
