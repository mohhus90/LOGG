<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentCategory extends Model
{
    protected $table = 'document_categories';
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function documents() { return $this->hasMany(Document::class, 'category_id'); }
}
