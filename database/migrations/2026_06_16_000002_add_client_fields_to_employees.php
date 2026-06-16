<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Client linkage
            $table->unsignedBigInteger('client_id')->nullable()->after('branches_id');
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('set null');

            // Client-specific HR identifiers
            $table->string('hrid', 50)->nullable()->after('client_id')->comment('كود الموظف لدى العميل (KR-15)');
            $table->string('client_fake_id', 50)->nullable()->after('hrid')->comment('الرقم الداخلي للعميل');

            // Emergency contact
            $table->string('reference_mobile', 50)->nullable()->after('client_fake_id')->comment('رقم جهة الاتصال الطارئة');
            $table->string('relative_relation', 100)->nullable()->after('reference_mobile')->comment('صلة القرابة بجهة الاتصال');

            // Documents & social insurance details
            $table->string('hiring_documents_status', 255)->nullable()->after('relative_relation')->comment('حالة أوراق التعيين');
            $table->date('insurance_start_date')->nullable()->after('hiring_documents_status')->comment('تاريخ بداية التأمين الاجتماعي');
            $table->date('insurance_end_date')->nullable()->after('insurance_start_date')->comment('تاريخ انتهاء التأمين الاجتماعي');
            $table->text('form1_notes')->nullable()->after('insurance_end_date')->comment('ملاحظات نموذج 1');
            $table->text('form6_notes')->nullable()->after('form1_notes')->comment('ملاحظات نموذج 6');
            $table->text('client_notes')->nullable()->after('form6_notes')->comment('ملاحظات خاصة بالعميل');
        });

        // Make finger_id nullable to support client employees who have no fingerprint device
        Schema::table('employees', function (Blueprint $table) {
            $table->integer('finger_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn([
                'client_id', 'hrid', 'client_fake_id',
                'reference_mobile', 'relative_relation',
                'hiring_documents_status',
                'insurance_start_date', 'insurance_end_date',
                'form1_notes', 'form6_notes', 'client_notes',
            ]);
            $table->integer('finger_id')->nullable(false)->change();
        });
    }
};
