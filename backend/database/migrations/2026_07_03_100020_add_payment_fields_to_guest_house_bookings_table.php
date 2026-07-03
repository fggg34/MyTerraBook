<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_house_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('guest_house_bookings', 'total_price')) {
                $table->decimal('total_price', 10, 2)->nullable()->after('total_amount');
            }
            if (! Schema::hasColumn('guest_house_bookings', 'platform_fee')) {
                $table->decimal('platform_fee', 10, 2)->default(0)->after('total_price');
            }
            if (! Schema::hasColumn('guest_house_bookings', 'cash_due_on_arrival')) {
                $table->decimal('cash_due_on_arrival', 10, 2)->default(0)->after('platform_fee');
            }
            if (! Schema::hasColumn('guest_house_bookings', 'payment_status')) {
                // pending = not paid online, partially_paid = 20% paid online awaiting cash,
                // confirmed = host confirmed the cash balance was received on arrival.
                $table->string('payment_status')->default('pending')->after('cash_due_on_arrival');
            }
            if (! Schema::hasColumn('guest_house_bookings', 'payment_method')) {
                $table->string('payment_method')->default('rapyd_card')->after('payment_status');
            }
            if (! Schema::hasColumn('guest_house_bookings', 'cash_received_at')) {
                $table->timestamp('cash_received_at')->nullable()->after('payment_method');
            }
            if (! Schema::hasColumn('guest_house_bookings', 'rapyd_checkout_id')) {
                $table->string('rapyd_checkout_id')->nullable()->after('cash_received_at');
            }
            if (! Schema::hasColumn('guest_house_bookings', 'rapyd_payment_id')) {
                $table->string('rapyd_payment_id')->nullable()->after('rapyd_checkout_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('guest_house_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'total_price',
                'platform_fee',
                'cash_due_on_arrival',
                'payment_status',
                'payment_method',
                'cash_received_at',
                'rapyd_checkout_id',
                'rapyd_payment_id',
            ]);
        });
    }
};
