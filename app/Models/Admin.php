<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'admins';

    /**
     * ✅ FIX: إضافة company_id و is_super_admin للـ fillable
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'com_code',
        'company_id',    // ✅ مضاف
        'is_super_admin', // ✅ مضاف
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_super_admin'    => 'boolean', // ✅ مضاف
    ];

    // ─────────────────────────────────────────────
    // Relations
    // ─────────────────────────────────────────────

    /**
     * ✅ علاقة مع الشركة (nullable — للتوافق مع البيانات القديمة)
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function permissions()
    {
        return $this->hasMany(AdminPermission::class, 'admin_id');
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    /**
     * ✅ مساعد: هل هو سوبر أدمن؟
     */
    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    /**
     * ✅ FIX: com_code accessor — يعيد com_code دائماً كـ int غير null
     * يُستخدم في كل Controllers التي تعتمد على $admin->com_code
     */
    public function getComCodeAttribute($value): int
    {
        // إذا كان com_code موجوداً استخدمه، وإلا استخدم company_id
        if ($value) {
            return (int) $value;
        }
        return (int) ($this->attributes['company_id'] ?? 0);
    }
}