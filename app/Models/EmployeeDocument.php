<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $table   = 'employee_documents';
    protected $guarded = [];

    public const TYPES = [
        'photo'           => ['ar' => 'صورة شخصية',             'icon' => 'fa-camera'],
        'cv'              => ['ar' => 'السيرة الذاتية',          'icon' => 'fa-file-alt'],
        'national_id'     => ['ar' => 'صورة الرقم القومي',      'icon' => 'fa-id-card'],
        'education_cert'  => ['ar' => 'شهادة المؤهل',           'icon' => 'fa-graduation-cap'],
        'military_cert'   => ['ar' => 'شهادة الجيش',            'icon' => 'fa-shield-alt'],
        'criminal_record' => ['ar' => 'فيش جنائي',              'icon' => 'fa-fingerprint'],
        'birth_cert'      => ['ar' => 'شهادة الميلاد',          'icon' => 'fa-baby'],
        'work_history'    => ['ar' => 'كعب عمل / برينت تأمينات','icon' => 'fa-briefcase'],
        'insurance_proof' => ['ar' => 'إثبات القيد',             'icon' => 'fa-file-invoice'],
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function accessRequests()
    {
        return $this->hasMany(EmployeeRequest::class, 'document_id')->where('request_type', 'document_download');
    }

    /**
     * Most recent access request for this document, if any - drives both the
     * download gate and the "طلب الوصول / قيد الانتظار / تنزيل" UI state.
     */
    public function latestAccessRequest(): ?EmployeeRequest
    {
        return $this->accessRequests()->orderByDesc('created_at')->first();
    }

    public function isApprovedForDownload(): bool
    {
        return $this->latestAccessRequest()?->status === 1;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->doc_type]['ar'] ?? $this->doc_type;
    }
}
