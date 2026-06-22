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
}
