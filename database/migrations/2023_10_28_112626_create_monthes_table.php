<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monthes', function (Blueprint $table) {
            $table->id();
            $table->string('monthe_name');
            $table->string('monthe_name_en');
        });
        DB::table('monthes')->insert([
            ['monthe_name'=>'يناير','monthe_name_en'=>'January'],
            ['monthe_name'=>'فبراير','monthe_name_en'=>'February'],
            ['monthe_name'=>'مارس','monthe_name_en'=>'March'],
            ['monthe_name'=>'ابريل','monthe_name_en'=>'April'],
            ['monthe_name'=>'مايو','monthe_name_en'=>'May'],
            ['monthe_name'=>'يونيو','monthe_name_en'=>'June'],
            ['monthe_name'=>'يوليو','monthe_name_en'=>'July'],
            ['monthe_name'=>'اغسطس','monthe_name_en'=>'August'],
            ['monthe_name'=>'سبتمبر','monthe_name_en'=>'September'],
            ['monthe_name'=>'اكتوبر','monthe_name_en'=>'October'],
            ['monthe_name'=>'نوفمبر','monthe_name_en'=>'November'],
            ['monthe_name'=>'ديسمبر','monthe_name_en'=>'December'],
        ]);
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthes');
    }
};
