<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            $table->boolean('sms_enabled')->default(false)->after('email');
            $table->string('sms_api_url')->default('http://smsvas.vlserv.com/KannelSending/SendSMS.aspx')->after('sms_enabled');
            $table->string('sms_username')->nullable()->after('sms_api_url');
            $table->string('sms_password')->nullable()->after('sms_username');
            $table->string('sms_sender')->nullable()->after('sms_password');
            // أحداث SMS
            $table->boolean('sms_on_employee_create')->default(true)->after('sms_sender');
            $table->boolean('sms_on_payroll_approve')->default(true)->after('sms_on_employee_create');
            $table->boolean('sms_on_request_approve')->default(true)->after('sms_on_payroll_approve');
            $table->boolean('sms_on_request_reject')->default(true)->after('sms_on_request_approve');
            $table->boolean('sms_on_advance_create')->default(true)->after('sms_on_request_reject');
            $table->boolean('sms_on_sanction_create')->default(true)->after('sms_on_advance_create');
        });
    }

    public function down(): void
    {
        Schema::table('admin_panel_settings', function (Blueprint $table) {
            $table->dropColumn([
                'sms_enabled',
                'sms_api_url',
                'sms_username',
                'sms_password',
                'sms_sender',
                'sms_on_employee_create',
                'sms_on_payroll_approve',
                'sms_on_request_approve',
                'sms_on_request_reject',
                'sms_on_advance_create',
                'sms_on_sanction_create',
            ]);
        });
    }
};
