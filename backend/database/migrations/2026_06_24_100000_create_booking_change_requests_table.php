<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_change_requests', function (Blueprint $table) {
            $table->id();
            $table->morphs('bookable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 32);
            $table->string('status', 32)->default('pending');
            $table->text('customer_message');
            $table->json('requested_changes')->nullable();
            $table->text('admin_response')->nullable();
            $table->foreignId('reviewed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->json('pricing_before')->nullable();
            $table->json('pricing_after')->nullable();
            $table->integer('price_delta_cents')->nullable();
            $table->timestamps();

            $table->index(['bookable_type', 'bookable_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_change_requests');
    }
};
