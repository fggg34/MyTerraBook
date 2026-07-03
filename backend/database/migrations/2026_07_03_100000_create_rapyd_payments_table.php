<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapyd_payments', function (Blueprint $table) {
            $table->id();
            // Generic reference to the booking/order being paid for. The platform has
            // two bookable types (car orders and guest house bookings) so this is a
            // loose reference rather than a hard FK constraint.
            $table->unsignedBigInteger('order_id')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('host_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('checkout_id')->index();
            $table->string('payment_id')->nullable()->index();
            $table->decimal('total_price', 10, 2);
            $table->decimal('platform_fee', 10, 2);
            $table->decimal('cash_due_on_arrival', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapyd_payments');
    }
};
