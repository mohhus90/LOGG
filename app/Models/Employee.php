<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Employee extends Model
{
    use HasFactory;
    
    protected $table = "employees";
    protected $guarded = [];
 
    public function addedBy() {
        return $this->belongsTo(Admin::class, 'added_by')->withDefault([
            'name' => 'غير معروف' // قيمة افتراضية إذا لم توجد العلاقة
        ]);
    }

    public function updatedBy() {
        return $this->belongsTo(Admin::class, 'updated_by')->withDefault([
            'name' => 'غير معروف'
        ]);
    }
}
