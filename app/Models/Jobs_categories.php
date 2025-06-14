<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jobs_categories extends Model
{
    use HasFactory;
    protected $table ="jobs_categories";
    protected $guarded = [];
    public function addedby(){
        return $this->belongsTo('App\Models\Admin','added_by');
    }
    public function updatedby(){
        return $this->belongsTo('App\Models\Admin','updated_by');
    }
}
