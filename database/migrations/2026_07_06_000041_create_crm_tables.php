<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->string('name', 150);
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('source', 100)->nullable();
            $table->enum('status', ['new', 'contacted', 'qualified', 'converted', 'lost'])->default('new');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('converted_customer_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('crm_opportunities', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->string('title', 150);
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->enum('stage', ['prospecting', 'proposal', 'negotiation', 'won', 'lost'])->default('prospecting');
            $table->decimal('value', 15, 4)->default(0);
            $table->date('expected_close_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('lead_id')->references('id')->on('crm_leads')->nullOnDelete();
        });

        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->integer('com_code');
            $table->enum('linked_type', ['lead', 'customer', 'opportunity']);
            $table->unsignedBigInteger('linked_id');
            $table->enum('type', ['call', 'meeting', 'note'])->default('note');
            $table->text('notes');
            $table->date('activity_date');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['linked_type', 'linked_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_activities');
        Schema::dropIfExists('crm_opportunities');
        Schema::dropIfExists('crm_leads');
    }
};
