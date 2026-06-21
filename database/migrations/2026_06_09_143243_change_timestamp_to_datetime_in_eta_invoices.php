<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('eta_invoices', function (Blueprint $table) {
            $table->dateTime('date_issued')->nullable()->change();
            $table->dateTime('date_received')->nullable()->change();
            $table->dateTime('posted_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('eta_invoices', function (Blueprint $table) {
            $table->timestamp('date_issued')->nullable()->change();
            $table->timestamp('date_received')->nullable()->change();
            $table->timestamp('posted_at')->nullable()->change();
        });
    }
};
