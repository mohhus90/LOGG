<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول أجهزة البصمة
     * يدعم بروتوكول ZKTeco (الأكثر شيوعاً) وغيره
     * الاتصال عبر IP مباشرة من السيرفر بدون برنامج وسيط
     */
    public function up(): void
    {
        Schema::create('fingerprint_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_name', 100)->comment('اسم الجهاز');
            $table->string('device_code', 50)->unique()->comment('كود الجهاز');
            $table->string('ip_address', 45)->comment('عنوان IP للجهاز');
            $table->integer('port')->default(4370)->comment('البورت — ZKTeco الافتراضي 4370');
            $table->string('protocol', 30)->default('zkteco')
                ->comment('البروتوكول: zkteco | suprema | anviz | hikvision | dahua | generic');
            $table->string('location', 100)->nullable()->comment('موقع الجهاز');
            $table->string('model', 100)->nullable()->comment('موديل الجهاز');
            $table->string('serial_number', 100)->nullable()->comment('الرقم التسلسلي');
            $table->string('password', 50)->nullable()->comment('كلمة مرور الجهاز إن وُجدت');
            $table->tinyInteger('status')->default(1)->comment('(1=نشط),(2=معطل),(3=خطأ)');
            $table->timestamp('last_sync_at')->nullable()->comment('آخر مزامنة');
            $table->integer('last_sync_records')->default(0)->comment('عدد سجلات آخر مزامنة');
            $table->text('last_error')->nullable()->comment('آخر خطأ');
            $table->integer('com_code');
            $table->integer('added_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

        // جدول سجلات البصمة الخام المجلوبة من الأجهزة
        Schema::create('fingerprint_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('fingerprint_devices')->onDelete('cascade');
            $table->integer('finger_id')->comment('رقم البصمة في الجهاز');
            $table->dateTime('punch_time')->comment('وقت البصمة');
            $table->tinyInteger('punch_type')->default(0)
                ->comment('(0=بصمة عادية),(1=حضور),(2=انصراف),(255=غير محدد)');
            $table->tinyInteger('is_processed')->default(0)->comment('(0=لم تُعالج),(1=عولجت)');
            $table->integer('com_code');
            $table->timestamps();

            $table->index(['finger_id', 'punch_time']);
            $table->index(['is_processed', 'com_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fingerprint_logs');
        Schema::dropIfExists('fingerprint_devices');
    }
};
