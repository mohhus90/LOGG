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
        Schema::create('org_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('name_en', 100)->nullable();
            $table->unsignedTinyInteger('level_order')->default(1);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->enum('level_type', [
                'top_management',
                'middle_management',
                'supervisor',
                'sales',
                'operational',
                'support',
                'other',
            ])->default('operational');
            $table->boolean('is_management')->default(false);
            $table->boolean('is_sales_role')->default(false);
            $table->boolean('receives_seller_commission')->default(false);
            $table->boolean('receives_manager_commission')->default(false);
            $table->text('description')->nullable();
            $table->string('com_code', 50);
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('org_levels')->onDelete('set null');
        });

        Schema::create('org_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name', 150);
            $table->string('company_type', 100);
            $table->json('levels_data');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_templates');
        Schema::dropIfExists('org_levels');
    }
};
