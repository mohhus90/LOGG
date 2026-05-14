<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Admin
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed $password
 * @property string|null $remember_token
 * @property int $com_code
 * @property int $is_super_admin (1=سوبر ادمن),(0=ادمن عادي)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereIsSuperAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereUpdatedAt($value)
 */
	class Admin extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AdminModule
 *
 * @property int $id
 * @property string $module_key مفتاح القسم مثل employees, attendance ...
 * @property string $module_name اسم القسم بالعربي
 * @property string|null $module_icon أيقونة FontAwesome
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdminPermission> $permissions
 * @property-read int|null $permissions_count
 * @method static \Illuminate\Database\Eloquent\Builder|AdminModule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminModule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminModule query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminModule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminModule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminModule whereModuleIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminModule whereModuleKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminModule whereModuleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminModule whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminModule whereUpdatedAt($value)
 */
	class AdminModule extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AdminPermission
 *
 * @property int $id
 * @property int $admin_id
 * @property int $module_id
 * @property int $can_read صلاحية القراءة
 * @property int $can_create صلاحية الإضافة
 * @property int $can_update صلاحية التعديل
 * @property int $can_delete صلاحية الحذف
 * @property int $com_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin $admin
 * @property-read \App\Models\AdminModule $module
 * @method static \Illuminate\Database\Eloquent\Builder|AdminPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminPermission whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminPermission whereCanCreate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminPermission whereCanDelete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminPermission whereCanRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminPermission whereCanUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminPermission whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminPermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminPermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminPermission whereModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminPermission whereUpdatedAt($value)
 */
	class AdminPermission extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Admin_panel_setting
 *
 * @property int $id
 * @property string $com_name
 * @property int $saysem_status واحد مفعل- صفر معطل
 * @property string|null $image
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $email
 * @property int $added_by
 * @property int|null $updated_by
 * @property int $com_code
 * @property string $after_minute_calc_delay بعد كم دقيقة تحسب تأخير حضور
 * @property string $after_minute_calc_early بعد كم دقيقة تحسب انصراف مبكر
 * @property string $after_minute_quarterday بعد كم دقيقة مجموع الانصراف المبكر والحضور المتأخر تخصم ربع يوم
 * @property string $after_time_half_daycut بعد كم مرة تأخير أو انصراف مبكر يخصم نصف يوم
 * @property string $after_time_allday_daycut بعد كم مرة تأخير أو انصراف مبكر يخصم يوم
 * @property string $sanctions_value_minute_delay قيمة خصم التأخير والانصراف المبكر بالدقيقة
 * @property string $sanctions_value_hour_delay قيمة خصم التأخير والانصراف المبكر بالساعة
 * @property string $monthly_vacation_balance رصيد اجازات الموظف الشهرى
 * @property string $first_balance_begain_vacation رصيد الاجازات الاولى بعد مدة 6 شهور مثلا
 * @property string $after_days_begain_vacation بعد كم يوم ينزل للموظف رصيد اجازات
 * @property string $sanctions_value_first_abcence قيمة خصم الايام بعد اول مرة غياب
 * @property string $sanctions_value_second_abcence قيمة خصم الايام بعد ثانى غياب
 * @property string $sanctions_value_third_abcence قيمة خصم الايام بعد ثالث غياب
 * @property string $sanctions_value_forth_abcence قيمة خصم الايام بعد رابع غياب
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereAfterDaysBegainVacation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereAfterMinuteCalcDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereAfterMinuteCalcEarly($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereAfterMinuteQuarterday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereAfterTimeAlldayDaycut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereAfterTimeHalfDaycut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereComName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereFirstBalanceBegainVacation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereMonthlyVacationBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSanctionsValueFirstAbcence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSanctionsValueForthAbcence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSanctionsValueHourDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSanctionsValueMinuteDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSanctionsValueSecondAbcence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSanctionsValueThirdAbcence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSaysemStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereUpdatedBy($value)
 */
	class Admin_panel_setting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Advance
 *
 * @property int $id
 * @property int $employee_id
 * @property string $advance_date تاريخ السلفة
 * @property string $amount قيمة السلفة
 * @property int $installments عدد الأقساط
 * @property string $monthly_installment القسط الشهري
 * @property string $remaining_amount المبلغ المتبقي
 * @property int $status (1=جارية),(2=مسددة),(3=ملغاة)
 * @property string|null $notes
 * @property int $com_code
 * @property int $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @property-read string $status_label
 * @method static \Illuminate\Database\Eloquent\Builder|Advance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Advance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Advance query()
 * @method static \Illuminate\Database\Eloquent\Builder|Advance whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advance whereAdvanceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advance whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advance whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advance whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advance whereInstallments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advance whereMonthlyInstallment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advance whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advance whereRemainingAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advance whereUpdatedBy($value)
 */
	class Advance extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Attendance
 *
 * @property int $id
 * @property int $employee_id
 * @property int $shift_id
 * @property \Illuminate\Support\Carbon $attendance_date تاريخ اليوم
 * @property string|null $check_in_time وقت الحضور الفعلي
 * @property string|null $check_out_time وقت الانصراف الفعلي
 * @property int $late_minutes دقائق التأخير
 * @property string $overtime_hours ساعات الأوفرتايم
 * @property string $overtime_amount قيمة الأوفرتايم بالمال
 * @property string $late_deduction خصم التأخير بالمال
 * @property int $status (1=حضر),(2=غياب),(3=إجازة),(4=إجازة رسمية),(5=مأمورية)
 * @property string|null $notes ملاحظات
 * @property int $com_code
 * @property int|null $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $addedBy
 * @property-read \App\Models\Employee $employee
 * @property-read string $status_label
 * @property-read \App\Models\Shifts_type $shift
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereAttendanceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereCheckInTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereCheckOutTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereLateDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereLateMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereOvertimeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereOvertimeHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereUpdatedBy($value)
 */
	class Attendance extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Branche
 *
 * @property int $id
 * @property string $branch_name
 * @property int $active واحد مفعل- صفر معطل
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $email
 * @property int $added_by
 * @property int|null $updated_by
 * @property int $com_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $added
 * @property-read \App\Models\Admin|null $updatedby
 * @method static \Illuminate\Database\Eloquent\Builder|Branche newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Branche newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Branche query()
 * @method static \Illuminate\Database\Eloquent\Builder|Branche whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branche whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branche whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branche whereBranchName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branche whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branche whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branche whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branche whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branche wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branche whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branche whereUpdatedBy($value)
 */
	class Branche extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Commission
 *
 * @property int $id
 * @property int $employee_id
 * @property string $commission_date تاريخ العمولة
 * @property string|null $commission_type نوع العمولة
 * @property string $amount قيمة العمولة
 * @property int $month الشهر المرتبط به (1-12)
 * @property int $year السنة
 * @property int $status (1=معتمدة),(2=معلقة),(3=ملغاة)
 * @property string|null $notes
 * @property int $com_code
 * @property int $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @property-read string $status_label
 * @method static \Illuminate\Database\Eloquent\Builder|Commission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Commission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Commission query()
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereCommissionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereCommissionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereYear($value)
 */
	class Commission extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Deduction
 *
 * @property int $id
 * @property int $employee_id
 * @property string $deduction_date تاريخ الخصم
 * @property string|null $deduction_type نوع الخصم
 * @property string $amount قيمة الخصم
 * @property int $month الشهر المرتبط به (1-12)
 * @property int $year السنة
 * @property int $status (1=معتمدة),(2=معلقة),(3=ملغاة)
 * @property string|null $notes
 * @property int $com_code
 * @property int $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @property-read string $status_label
 * @method static \Illuminate\Database\Eloquent\Builder|Deduction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Deduction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Deduction query()
 * @method static \Illuminate\Database\Eloquent\Builder|Deduction whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deduction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deduction whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deduction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deduction whereDeductionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deduction whereDeductionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deduction whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deduction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deduction whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deduction whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deduction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deduction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deduction whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deduction whereYear($value)
 */
	class Deduction extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Department
 *
 * @property int $id
 * @property string $dep_name
 * @property string|null $phone
 * @property string|null $notes
 * @property string|null $email
 * @property int $com_code
 * @property int $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $added
 * @property-read \App\Models\Admin|null $updatedby
 * @method static \Illuminate\Database\Eloquent\Builder|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereDepName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereUpdatedBy($value)
 */
	class Department extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Employee
 *
 * @property int $id
 * @property string $employee_id
 * @property int $finger_id
 * @property string $employee_name_A
 * @property string $employee_name_E
 * @property string|null $employee_address
 * @property int|null $emp_gender (1=ذكر),(2= انثى)
 * @property int|null $emp_social_status (1=اعزب),(2= متزوج),(3= متزوج ويعول)
 * @property int|null $emp_military_status (1=ادى الخدمة),(2= اعفاء),(3= مؤجل)
 * @property string|null $emp_qualification
 * @property string|null $qualification_year
 * @property int|null $qualification_grade (1=امتياز),(2= جيد جدا),(3= جيد مرتفع),(4= جيد),(4= مقبول)
 * @property string|null $emp_start_date
 * @property int|null $functional_status (1=يعمل),(2= لايعمل)
 * @property int|null $insurance_status (1= مؤمن),(2= غير مؤمن),(3= تدريب),(4= منتهى خدمته)
 * @property int|null $resignation_status (1=استقالة),(2= فصل),(3= ترك العمل),(4= سن المعاش),(5= الوفاة)
 * @property string|null $resignation_date
 * @property string|null $resignation_cause سبب ترك العمل
 * @property int|null $motivation_type (1=ثابت),(2= متغير),(0= لايوجد)
 * @property string|null $motivation
 * @property int|null $sal_cash_visa (1=كاش),(2= فيزا)
 * @property string|null $bank_name
 * @property string|null $bank_account
 * @property string|null $bank_ID
 * @property string|null $bank_branch
 * @property string|null $daily_work_hours
 * @property int $emp_jobs_id
 * @property string|null $national_id
 * @property string|null $insurance_no
 * @property int $emp_departments_id
 * @property string|null $emp_home_tel
 * @property string|null $emp_mobile
 * @property string|null $emp_email
 * @property string|null $emp_photo
 * @property string|null $emp_cv
 * @property string|null $birth_date
 * @property string|null $emp_sal
 * @property string|null $emp_fixed_allowances
 * @property string|null $emp_sal_insurance
 * @property string|null $medical_insurance
 * @property int|null $is_has_fixed_shift (1=يوجد),(2= لايوجد)
 * @property int $shifts_types_id
 * @property int|null $is_has_finger (1=يوجد),(2= لايوجد)
 * @property int|null $vacation_formula (1=يوجد),(2= لايوجد)
 * @property int|null $sensitive_data (1=يوجد),(2= لايوجد)
 * @property int $branches_id
 * @property int $com_code
 * @property int $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $addedBy
 * @property-read \App\Models\Branche $branches
 * @property-read \App\Models\Admin_panel_setting|null $comp
 * @property-read \App\Models\Department $department
 * @property-read \App\Models\Jobs_categories $jobs_categories
 * @property-read \App\Models\Shifts_type $shifts_type
 * @property-read \App\Models\Admin|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBankAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBankBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBankID($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBranchesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereDailyWorkHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmpCv($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmpDepartmentsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmpEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmpFixedAllowances($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmpGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmpHomeTel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmpJobsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmpMilitaryStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmpMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmpPhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmpQualification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmpSal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmpSalInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmpSocialStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmpStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmployeeAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmployeeNameA($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmployeeNameE($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereFingerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereFunctionalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereInsuranceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereInsuranceStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereIsHasFinger($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereIsHasFixedShift($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMedicalInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMotivation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMotivationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereNationalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereQualificationGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereQualificationYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereResignationCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereResignationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereResignationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereSalCashVisa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereSensitiveData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereShiftsTypesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereVacationFormula($value)
 */
	class Employee extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Finance_calender
 *
 * @property int $id
 * @property int $finance_yr
 * @property string|null $finance_yr_desc
 * @property string $start_date
 * @property string $end_date
 * @property int $is_open
 * @property int|null $com_code
 * @property int $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $added
 * @property-read \App\Models\Admin|null $updatedby
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_calender newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_calender newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_calender query()
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_calender whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_calender whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_calender whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_calender whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_calender whereFinanceYr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_calender whereFinanceYrDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_calender whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_calender whereIsOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_calender whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_calender whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_calender whereUpdatedBy($value)
 */
	class Finance_calender extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Finance_cln_period
 *
 * @property int $id
 * @property int $finance_calenders_id
 * @property int $number_of_days
 * @property string $year_of_month
 * @property int $finance_year
 * @property int $month_id
 * @property string $start_date
 * @property string $end_date
 * @property int $is_open
 * @property string $start_date_finger_print
 * @property string $end_date_finger_print
 * @property int $com_code
 * @property int $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Month|null $Month
 * @property-read \App\Models\Admin|null $added
 * @property-read \App\Models\Admin|null $updatedby
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period query()
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereEndDateFingerPrint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereFinanceCalendersId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereFinanceYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereIsOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereMonthId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereNumberOfDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereStartDateFingerPrint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereYearOfMonth($value)
 */
	class Finance_cln_period extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\FingerprintDevice
 *
 * @property int $id
 * @property string $device_name اسم الجهاز
 * @property string $device_code كود الجهاز
 * @property string $ip_address عنوان IP للجهاز
 * @property int $port البورت — ZKTeco الافتراضي 4370
 * @property string $protocol البروتوكول: zkteco | suprema | anviz | hikvision | dahua | generic
 * @property string|null $location موقع الجهاز
 * @property string|null $model موديل الجهاز
 * @property string|null $serial_number الرقم التسلسلي
 * @property string|null $password كلمة مرور الجهاز إن وُجدت
 * @property int $status (1=نشط),(2=معطل),(3=خطأ)
 * @property \Illuminate\Support\Carbon|null $last_sync_at آخر مزامنة
 * @property int $last_sync_records عدد سجلات آخر مزامنة
 * @property string|null $last_error آخر خطأ
 * @property int $com_code
 * @property int $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $protocol_label
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FingerprintLog> $logs
 * @property-read int|null $logs_count
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice query()
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereDeviceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereDeviceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereLastError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereLastSyncAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereLastSyncRecords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice wherePort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereProtocol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereSerialNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereUpdatedBy($value)
 */
	class FingerprintDevice extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\FingerprintLog
 *
 * @property int $id
 * @property int $device_id
 * @property int $finger_id رقم البصمة في الجهاز
 * @property \Illuminate\Support\Carbon $punch_time وقت البصمة
 * @property int $punch_type (0=بصمة عادية),(1=حضور),(2=انصراف),(255=غير محدد)
 * @property int $is_processed (0=لم تُعالج),(1=عولجت)
 * @property int $com_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FingerprintDevice $device
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintLog whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintLog whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintLog whereFingerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintLog whereIsProcessed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintLog wherePunchTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintLog wherePunchType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintLog whereUpdatedAt($value)
 */
	class FingerprintLog extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Jobs_categories
 *
 * @property int $id
 * @property string $job_name
 * @property int $com_code
 * @property int $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $addedby
 * @property-read \App\Models\Admin|null $updatedby
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories query()
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories whereJobName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories whereUpdatedBy($value)
 */
	class Jobs_categories extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Main_vacations_balance
 *
 * @property int $id
 * @property string $employee_id
 * @property string|null $year_and_month السنة والشهر
 * @property int|null $finance_yr السنةالمالية
 * @property string|null $carryover_from_previous_month الرصيد المرحل من الشهر السابق
 * @property string|null $currentmonth_balance رصيد  الشهر الحالى
 * @property string|null $total_available_balance اجمالى الرصيد المتاح
 * @property string|null $spent_balance الرصيد المستهلك
 * @property string|null $net_balance صافى الرصيد
 * @property int $com_code
 * @property int $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $addedBy
 * @property-read \App\Models\Branche|null $branches
 * @property-read \App\Models\Admin_panel_setting|null $comp
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\Employee|null $empname
 * @property-read \App\Models\Jobs_categories|null $jobs_categories
 * @property-read \App\Models\Shifts_type|null $shifts_type
 * @property-read \App\Models\Admin|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder|Main_vacations_balance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Main_vacations_balance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Main_vacations_balance query()
 * @method static \Illuminate\Database\Eloquent\Builder|Main_vacations_balance whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Main_vacations_balance whereCarryoverFromPreviousMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Main_vacations_balance whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Main_vacations_balance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Main_vacations_balance whereCurrentmonthBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Main_vacations_balance whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Main_vacations_balance whereFinanceYr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Main_vacations_balance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Main_vacations_balance whereNetBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Main_vacations_balance whereSpentBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Main_vacations_balance whereTotalAvailableBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Main_vacations_balance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Main_vacations_balance whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Main_vacations_balance whereYearAndMonth($value)
 */
	class Main_vacations_balance extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Month
 *
 * @property int $id
 * @property string $monthe_name
 * @property string $monthe_name_en
 * @method static \Illuminate\Database\Eloquent\Builder|Month newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Month newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Month query()
 * @method static \Illuminate\Database\Eloquent\Builder|Month whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Month whereMontheName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Month whereMontheNameEn($value)
 */
	class Month extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\MonthlyPayroll
 *
 * @property int $id
 * @property int $employee_id
 * @property int $month الشهر (1-12)
 * @property int $year السنة
 * @property string $period_from بداية فترة الاحتساب
 * @property string $period_to نهاية فترة الاحتساب
 * @property int $total_days إجمالي أيام الفترة
 * @property int $work_days أيام العمل الفعلية
 * @property int $absence_days أيام الغياب
 * @property int $leave_days أيام الإجازة
 * @property string $basic_salary الراتب الأساسي كامل الشهر
 * @property string $daily_rate قيمة اليوم الواحد
 * @property string $earned_salary الراتب المستحق بعد الحضور
 * @property string $fixed_allowances الإضافات الثابتة
 * @property string $overtime_amount إجمالي الأوفرتايم
 * @property string $commissions_amount إجمالي العمولات
 * @property string $late_deductions خصومات التأخير
 * @property string $absence_deductions خصومات الغياب
 * @property string $deductions_amount إجمالي الخصومات الأخرى
 * @property string $advance_installment قسط السلفة
 * @property string $insurance_deduction خصم التأمينات
 * @property string $gross_salary الراتب الإجمالي قبل الخصومات
 * @property string $net_salary الراتب الصافي
 * @property int $status (1=مسودة),(2=معتمد),(3=مدفوع)
 * @property string|null $notes
 * @property int $com_code
 * @property int $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @property-read string $month_name
 * @property-read string $status_label
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll query()
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereAbsenceDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereAbsenceDeductions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereAdvanceInstallment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereBasicSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereCommissionsAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereDailyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereDeductionsAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereEarnedSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereFixedAllowances($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereGrossSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereInsuranceDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereLateDeductions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereLeaveDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereNetSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereOvertimeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll wherePeriodFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll wherePeriodTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereTotalDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereWorkDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereYear($value)
 */
	class MonthlyPayroll extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Shifts_type
 *
 * @property int $id
 * @property string $type
 * @property string $from_time
 * @property string $to_time
 * @property string $total_hour
 * @property int $com_code
 * @property int $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $added
 * @property-read \App\Models\Admin|null $updatedby
 * @method static \Illuminate\Database\Eloquent\Builder|Shifts_type newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Shifts_type newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Shifts_type query()
 * @method static \Illuminate\Database\Eloquent\Builder|Shifts_type whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shifts_type whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shifts_type whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shifts_type whereFromTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shifts_type whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shifts_type whereToTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shifts_type whereTotalHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shifts_type whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shifts_type whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shifts_type whereUpdatedBy($value)
 */
	class Shifts_type extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed $password
 * @property string|null $remember_token
 * @property int $com_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 */
	class User extends \Eloquent implements \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

