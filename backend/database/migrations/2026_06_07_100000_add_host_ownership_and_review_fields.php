<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_houses', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->timestamp('submitted_at')->nullable()->after('tax_rate_id');
            $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('reviewed_by');
        });

        Schema::table('cars', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('listing_status')->default('approved')->after('is_active');
            $table->timestamp('submitted_at')->nullable()->after('listing_status');
            $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('guest_houses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['submitted_at', 'reviewed_at', 'rejection_reason']);
        });

        Schema::table('cars', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['listing_status', 'submitted_at', 'reviewed_at', 'rejection_reason']);
        });
    }
};
