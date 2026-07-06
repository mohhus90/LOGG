<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchTarget extends Model
{
    protected $table   = 'branch_targets';
    protected $guarded = [];

    public function branch()
    {
        return $this->belongsTo(Branche::class, 'branch_id');
    }
}
