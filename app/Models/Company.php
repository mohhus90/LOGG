<?php
// ============================================================
// FILE: app/Models/Company.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $table = 'companies';
    protected $guarded = [];

    public function admins()
    {
        return $this->hasMany(Admin::class, 'company_id');
    }

    public function settings()
    {
        return $this->hasOne(Admin_panel_setting::class, 'company_id');
    }

    /**
     * جلب إعدادات الشركة أو إنشاء افتراضية
     */
    public function getSettings(): Admin_panel_setting
    {
        return $this->settings ?? new Admin_panel_setting(['company_id' => $this->id]);
    }

    /**
     * جلب شركة من خلال company_id الأدمن
     */
    public static function forAdmin(): ?self
    {
        $admin = auth()->guard('admin')->user();
        if (!$admin) return null;
        return self::find($admin->company_id);
    }
}
