<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->longText('rental_terms_html')->nullable()->after('partner_commission_rate');
        });

        if (! Schema::hasTable('email_templates') || DB::table('email_templates')->where('key', 'host_login')->exists()) {
            return;
        }

        $now = now();
        DB::table('email_templates')->insert([
            'key' => 'host_login',
            'name' => 'Host login',
            'category' => 'account',
            'audience' => 'host',
            'is_enabled' => true,
            'subject' => 'Your {{brand_name}} host login',
            'preheader' => 'Use this email and temporary password to open your host account.',
            'heading' => 'Your host login',
            'greeting' => 'Hi {{host_name}},',
            'body_html' => '<p>Your host account on {{brand_name}} is ready.</p>'
                .'<p>Sign in with the email and temporary password below. From the host portal you can review vehicles, stays, and bookings.</p>'
                .'<p>Email: {{host_email}}<br>Temporary password: {{temporary_password}}</p>'
                .'<p>Change this password from Host settings after you sign in.</p>',
            'cta_label' => 'Sign in to the host portal',
            'cta_url_template' => '{{login_url}}',
            'footer_note' => 'If you were not expecting this email, contact us at {{support_email}}.',
            'available_variables' => json_encode(['host_name', 'host_email', 'temporary_password', 'login_url', 'brand_name', 'frontend_url', 'support_email']),
            'sort_order' => 25,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rental_terms_html');
        });

        if (Schema::hasTable('email_templates')) {
            DB::table('email_templates')->where('key', 'host_login')->delete();
        }
    }
};
