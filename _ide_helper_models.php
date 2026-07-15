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
 * App\Models\AccountingPeriod
 *
 * @property int $id
 * @property int $com_code
 * @property int $fiscal_year
 * @property int $period_month
 * @property string $start_date
 * @property string $end_date
 * @property bool $is_closed
 * @property string|null $closed_at
 * @property int|null $closed_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $closedBy
 * @method static \Illuminate\Database\Eloquent\Builder|AccountingPeriod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountingPeriod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountingPeriod query()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountingPeriod whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountingPeriod whereClosedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountingPeriod whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountingPeriod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountingPeriod whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountingPeriod whereFiscalYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountingPeriod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountingPeriod whereIsClosed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountingPeriod wherePeriodMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountingPeriod whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountingPeriod whereUpdatedAt($value)
 */
	class AccountingPeriod extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Admin
 *
 * @property int $id
 * @property int|null $company_id
 * @property bool $is_super_admin (1=سوبر أدمن),(0=أدمن عادي)
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed $password
 * @property string|null $remember_token
 * @property int $com_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdminPermission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereCompanyId($value)
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
 * @property int|null $company_id
 * @property string $com_name
 * @property int $saysem_status واحد مفعل- صفر معطل
 * @property string|null $image
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $email
 * @property int $sms_enabled
 * @property string $sms_api_url
 * @property string|null $sms_username
 * @property string|null $sms_password
 * @property string|null $sms_sender
 * @property int $sms_on_employee_create
 * @property int $sms_on_payroll_approve
 * @property int $sms_on_request_approve
 * @property int $sms_on_request_reject
 * @property int $sms_on_advance_create
 * @property int $sms_on_sanction_create
 * @property int $added_by
 * @property int|null $updated_by
 * @property int $com_code
 * @property string $after_minute_calc_delay بعد كم دقيقة تحسب تأخير حضور
 * @property string $after_minute_calc_early بعد كم دقيقة تحسب انصراف مبكر
 * @property string $after_minute_quarterday بعد كم دقيقة مجموع الانصراف المبكر والحضور المتأخر تخصم ربع يوم
 * @property int $delay_tier1_minutes
 * @property int $delay_halfday_minutes
 * @property int $delay_fullday_minutes
 * @property int $early_departure_halfday_minutes
 * @property int $early_departure_fullday_minutes
 * @property int $early_departure_fullplushalf_minutes
 * @property string $after_time_half_daycut بعد كم مرة تأخير أو انصراف مبكر يخصم نصف يوم
 * @property string $after_time_allday_daycut بعد كم مرة تأخير أو انصراف مبكر يخصم يوم
 * @property string $sanctions_value_minute_delay قيمة خصم التأخير والانصراف المبكر بالدقيقة
 * @property int $day_rate_divisor_type
 * @property string $day_rate_divisor_custom القيمة المخصصة لمقسوم اليوم — يُستخدم عند النوع 4
 * @property int $hour_rate_divisor_type
 * @property string $hour_rate_divisor_custom القيمة المخصصة لمقسوم الساعة — يُستخدم عند النوع 3
 * @property int $overtime_calc_type (1=ساعات ثابتة),(2=ما يزيد عن جدول الشيفت)
 * @property string $max_monthly_overtime_hours الحد الأقصى لساعات الأوفرتايم الشهرية (0 = بلا حد)
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
 * @property int $delay_calc_mode (1=بالدقيقة),(2=نصف يوم بعد X مرة),(3=يوم بعد X مرة)
 * @property string $overtime_multiplier مضاعف الأوفرتايم (1.5 = مرة ونصف، 2 = مرتين)
 * @property string $employee_insurance_rate نسبة اشتراك الموظف في التأمينات الاجتماعية %
 * @property string $company_insurance_rate نسبة اشتراك الشركة في التأمينات الاجتماعية %
 * @property string $annual_vacation_days رصيد الإجازة الاعتيادية السنوية (قانون مصري: 21 يوم)
 * @property string $casual_vacation_days رصيد الإجازة العارضة السنوية (قانون مصري: 6 أيام)
 * @property int $max_permissions_per_day عدد الإذونات المسموح بها في اليوم الواحد
 * @property int $max_permission_minutes_per_day أقصى مدة للإذونات بالدقائق في اليوم
 * @property string $income_tax_exemption_monthly الإعفاء الضريبي الشهري قبل تطبيق الشرائح
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
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereAnnualVacationDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereCasualVacationDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereComName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereCompanyInsuranceRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereDayRateDivisorCustom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereDayRateDivisorType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereDelayCalcMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereDelayFulldayMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereDelayHalfdayMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereDelayTier1Minutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereEarlyDepartureFulldayMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereEarlyDepartureFullplushalfMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereEarlyDepartureHalfdayMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereEmployeeInsuranceRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereFirstBalanceBegainVacation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereHourRateDivisorCustom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereHourRateDivisorType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereIncomeTaxExemptionMonthly($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereMaxMonthlyOvertimeHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereMaxPermissionMinutesPerDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereMaxPermissionsPerDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereMonthlyVacationBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereOvertimeCalcType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereOvertimeMultiplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSanctionsValueFirstAbcence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSanctionsValueForthAbcence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSanctionsValueHourDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSanctionsValueMinuteDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSanctionsValueSecondAbcence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSanctionsValueThirdAbcence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSaysemStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSmsApiUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSmsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSmsOnAdvanceCreate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSmsOnEmployeeCreate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSmsOnPayrollApprove($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSmsOnRequestApprove($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSmsOnRequestReject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSmsOnSanctionCreate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSmsPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSmsSender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin_panel_setting whereSmsUsername($value)
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
 * App\Models\AdvanceDeductionLog
 *
 * @property int $id
 * @property int $advance_id
 * @property int $monthly_payroll_id
 * @property string $amount المبلغ المخصوم من السلفة عند اعتماد هذا الكشف
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Advance $advance
 * @property-read \App\Models\MonthlyPayroll $payroll
 * @method static \Illuminate\Database\Eloquent\Builder|AdvanceDeductionLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvanceDeductionLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvanceDeductionLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvanceDeductionLog whereAdvanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvanceDeductionLog whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvanceDeductionLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvanceDeductionLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvanceDeductionLog whereMonthlyPayrollId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvanceDeductionLog whereUpdatedAt($value)
 */
	class AdvanceDeductionLog extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AssetCategory
 *
 * @property int $id
 * @property int $com_code
 * @property string $name
 * @property int $default_useful_life_years
 * @property string $default_depreciation_method
 * @property int|null $asset_gl_account_id
 * @property int|null $accum_depreciation_gl_account_id
 * @property int|null $depreciation_expense_gl_account_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ChartOfAccount|null $accumDepreciationGlAccount
 * @property-read \App\Models\ChartOfAccount|null $assetGlAccount
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FixedAsset> $assets
 * @property-read int|null $assets_count
 * @property-read \App\Models\ChartOfAccount|null $depreciationExpenseGlAccount
 * @method static \Illuminate\Database\Eloquent\Builder|AssetCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AssetCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AssetCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|AssetCategory whereAccumDepreciationGlAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetCategory whereAssetGlAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetCategory whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetCategory whereDefaultDepreciationMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetCategory whereDefaultUsefulLifeYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetCategory whereDepreciationExpenseGlAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetCategory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetCategory whereUpdatedAt($value)
 */
	class AssetCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AssetDepreciationEntry
 *
 * @property int $id
 * @property int $com_code
 * @property int $fixed_asset_id
 * @property int $period_year
 * @property int $period_month
 * @property float $depreciation_amount
 * @property int|null $journal_entry_id
 * @property string|null $run_at
 * @property int|null $run_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FixedAsset $fixedAsset
 * @property-read \App\Models\JournalEntry|null $journalEntry
 * @property-read \App\Models\Admin|null $runBy
 * @method static \Illuminate\Database\Eloquent\Builder|AssetDepreciationEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AssetDepreciationEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AssetDepreciationEntry query()
 * @method static \Illuminate\Database\Eloquent\Builder|AssetDepreciationEntry whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetDepreciationEntry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetDepreciationEntry whereDepreciationAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetDepreciationEntry whereFixedAssetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetDepreciationEntry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetDepreciationEntry whereJournalEntryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetDepreciationEntry wherePeriodMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetDepreciationEntry wherePeriodYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetDepreciationEntry whereRunAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetDepreciationEntry whereRunBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetDepreciationEntry whereUpdatedAt($value)
 */
	class AssetDepreciationEntry extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AssetTransfer
 *
 * @property int $id
 * @property int $fixed_asset_id
 * @property int|null $from_branch_id
 * @property int|null $to_branch_id
 * @property \Illuminate\Support\Carbon $transfer_date
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FixedAsset $fixedAsset
 * @property-read \App\Models\Branche|null $fromBranch
 * @property-read \App\Models\Branche|null $toBranch
 * @method static \Illuminate\Database\Eloquent\Builder|AssetTransfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AssetTransfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AssetTransfer query()
 * @method static \Illuminate\Database\Eloquent\Builder|AssetTransfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetTransfer whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetTransfer whereFixedAssetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetTransfer whereFromBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetTransfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetTransfer whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetTransfer whereToBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetTransfer whereTransferDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AssetTransfer whereUpdatedAt($value)
 */
	class AssetTransfer extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Attendance
 *
 * @property int $id
 * @property int $employee_id
 * @property int $shift_id
 * @property int|null $shift_override_id
 * @property \Illuminate\Support\Carbon $attendance_date تاريخ اليوم
 * @property string|null $check_in_time وقت الحضور الفعلي
 * @property string|null $check_in_lat
 * @property string|null $check_in_lng
 * @property string|null $check_out_time وقت الانصراف الفعلي
 * @property string|null $check_out_lat
 * @property string|null $check_out_lng
 * @property string $source fingerprint_device | mobile_app
 * @property int $device_verified client-reported local biometric pass — audit only
 * @property string|null $missing_punch
 * @property int|null $missing_punch_resolution
 * @property string|null $missing_punch_hours
 * @property int $late_minutes دقائق التأخير
 * @property string $overtime_hours ساعات الأوفرتايم
 * @property string $overtime_amount قيمة الأوفرتايم بالمال
 * @property string $late_deduction خصم التأخير بالمال
 * @property string $missing_punch_deduction خصم حل البصمة الناقصة (منفصل عن خصم التأخير)
 * @property int $status (1=حضر),(2=غياب),(3=إجازة),(4=إجازة رسمية),(5=مأمورية)
 * @property string|null $absence_deduction_days عدد أيام الخصم عند الغياب — null يعني استخدام الضبط العام
 * @property string|null $notes ملاحظات
 * @property int $com_code
 * @property int $is_before_hire 1 = التاريخ قبل تاريخ تعيين الموظف — لا يُحتسب في الراتب
 * @property int $is_manual_lock 1 = السجل محمي من أي معالجة بصمة تلقائية
 * @property int|null $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $early_departure_minutes دقائق الانصراف المبكر (بعد فترة السماح)
 * @property string $early_departure_deduction قيمة خصم الانصراف المبكر بالمال
 * @property int|null $early_departure_fraction
 * @property int $permission_minutes دقائق إذن التأخير المعتمدة (تُخصم من التأخير)
 * @property int $permission_early_minutes دقائق إذن الانصراف المبكر المعتمدة
 * @property int|null $late_fraction 1=ربع يوم، 2=نصف يوم، 3=يوم كامل (وضع جزء اليوم)
 * @property int|null $weekly_off_overtime 1=احتسب ساعات الإجازة الأسبوعية كأوفرتايم، 0=لا تحتسب
 * @property int $is_weekly_off_worked
 * @property string $leave_compensation_amount
 * @property-read \App\Models\Admin|null $addedBy
 * @property-read \App\Models\Employee $employee
 * @property-read string $early_departure_display
 * @property-read \App\Models\Shifts_type|null $effective_shift
 * @property-read string $late_display
 * @property-read string $missing_punch_resolution_label
 * @property-read string $status_label
 * @property-read \App\Models\Shifts_type $shift
 * @property-read \App\Models\Shifts_type|null $shiftOverride
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereAbsenceDeductionDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereAttendanceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereCheckInLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereCheckInLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereCheckInTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereCheckOutLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereCheckOutLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereCheckOutTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereDeviceVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereEarlyDepartureDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereEarlyDepartureFraction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereEarlyDepartureMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereIsBeforeHire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereIsManualLock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereIsWeeklyOffWorked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereLateDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereLateFraction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereLateMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereLeaveCompensationAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereMissingPunch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereMissingPunchDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereMissingPunchHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereMissingPunchResolution($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereOvertimeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereOvertimeHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance wherePermissionEarlyMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance wherePermissionMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereShiftOverrideId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereWeeklyOffOvertime($value)
 */
	class Attendance extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BankAccount
 *
 * @property int $id
 * @property int $com_code
 * @property string $bank_name
 * @property string $account_name
 * @property string|null $account_number
 * @property string|null $iban
 * @property string|null $swift_code
 * @property int|null $branch_id
 * @property int|null $gl_account_id
 * @property float $opening_balance
 * @property float $current_balance
 * @property string $currency_code
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read \App\Models\ChartOfAccount|null $glAccount
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereCurrentBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereGlAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereIban($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereOpeningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereSwiftCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankAccount whereUpdatedAt($value)
 */
	class BankAccount extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BillOfMaterial
 *
 * @property int $id
 * @property int $com_code
 * @property int $item_id
 * @property int $version
 * @property float $output_quantity
 * @property bool $is_active
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item|null $item
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BillOfMaterialLine> $lines
 * @property-read int|null $lines_count
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterial query()
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterial whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterial whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterial whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterial whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterial whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterial whereOutputQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterial whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterial whereVersion($value)
 */
	class BillOfMaterial extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BillOfMaterialLine
 *
 * @property int $id
 * @property int $bom_id
 * @property int $component_item_id
 * @property float $quantity
 * @property int|null $unit_id
 * @property float $scrap_percent
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BillOfMaterial $bom
 * @property-read \App\Models\Item|null $componentItem
 * @property-read \App\Models\ItemUnit|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterialLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterialLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterialLine query()
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterialLine whereBomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterialLine whereComponentItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterialLine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterialLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterialLine whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterialLine whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterialLine whereScrapPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterialLine whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BillOfMaterialLine whereUpdatedAt($value)
 */
	class BillOfMaterialLine extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Bonus
 *
 * @property int $id
 * @property int $employee_id
 * @property string $bonus_date تاريخ المكافأة
 * @property int $bonus_type (1=مبلغ ثابت),(2=أيام × مضاعف)
 * @property string|null $amount المبلغ الثابت (للنوع 1)
 * @property string|null $days عدد الأيام (للنوع 2)
 * @property string $day_multiplier مضاعف اليوم (للنوع 2، افتراضي 1)
 * @property int $month الشهر المرتبط (1-12)
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
 * @property-read string $type_name
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus query()
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus whereBonusDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus whereBonusType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus whereDayMultiplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bonus whereYear($value)
 */
	class Bonus extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BranchCommissionPlan
 *
 * @property int $id
 * @property string $name
 * @property int $branch_id
 * @property string|null $description
 * @property array|null $tiers
 * @property bool $is_active
 * @property int $com_code
 * @property int|null $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read string $tiers_summary
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BranchCommissionPlanMember> $members
 * @property-read int|null $members_count
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlan whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlan whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlan whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlan whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlan whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlan whereTiers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlan whereUpdatedAt($value)
 */
	class BranchCommissionPlan extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BranchCommissionPlanMember
 *
 * @property int $id
 * @property int $plan_id
 * @property int $employee_id
 * @property string $role
 * @property bool $also_as_seller
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employee
 * @property-read \App\Models\BranchCommissionPlan|null $plan
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlanMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlanMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlanMember query()
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlanMember whereAlsoAsSeller($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlanMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlanMember whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlanMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlanMember wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlanMember whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchCommissionPlanMember whereUpdatedAt($value)
 */
	class BranchCommissionPlanMember extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BranchTarget
 *
 * @property int $id
 * @property int $branch_id
 * @property int $month
 * @property int $year
 * @property string $target_amount
 * @property int $com_code
 * @property int|null $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @method static \Illuminate\Database\Eloquent\Builder|BranchTarget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BranchTarget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BranchTarget query()
 * @method static \Illuminate\Database\Eloquent\Builder|BranchTarget whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchTarget whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchTarget whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchTarget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchTarget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchTarget whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchTarget whereTargetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchTarget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BranchTarget whereYear($value)
 */
	class BranchTarget extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Branche
 *
 * @property int $id
 * @property string $branch_name
 * @property int $active واحد مفعل- صفر معطل
 * @property string|null $address
 * @property string|null $latitude
 * @property string|null $longitude
 * @property int|null $geofence_radius_m
 * @property string|null $phone
 * @property string|null $email
 * @property int $added_by
 * @property int|null $updated_by
 * @property int $com_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $added
 * @property-read string $name
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
 * @method static \Illuminate\Database\Eloquent\Builder|Branche whereGeofenceRadiusM($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branche whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branche whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branche whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branche wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branche whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branche whereUpdatedBy($value)
 */
	class Branche extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CashBox
 *
 * @property int $id
 * @property int $com_code
 * @property string $code
 * @property string $name
 * @property int|null $branch_id
 * @property int|null $gl_account_id
 * @property float $opening_balance
 * @property float $current_balance
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read \App\Models\ChartOfAccount|null $glAccount
 * @method static \Illuminate\Database\Eloquent\Builder|CashBox newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CashBox newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CashBox query()
 * @method static \Illuminate\Database\Eloquent\Builder|CashBox whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CashBox whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CashBox whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CashBox whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CashBox whereCurrentBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CashBox whereGlAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CashBox whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CashBox whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CashBox whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CashBox whereOpeningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CashBox whereUpdatedAt($value)
 */
	class CashBox extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ChartOfAccount
 *
 * @property int $id
 * @property int $com_code
 * @property string $account_code
 * @property string $account_name
 * @property string|null $account_name_en
 * @property string $account_type
 * @property string $account_nature
 * @property int|null $parent_id
 * @property int $level
 * @property bool $is_group
 * @property bool $is_active
 * @property bool $allow_cost_center
 * @property float $opening_balance
 * @property string|null $opening_balance_date
 * @property float $current_balance
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ChartOfAccount> $children
 * @property-read int|null $children_count
 * @property-read string $full_code_name
 * @property-read string $type_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\JournalEntryLine> $lines
 * @property-read int|null $lines_count
 * @property-read ChartOfAccount|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereAccountCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereAccountNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereAccountNature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereAccountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereAllowCostCenter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereCurrentBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereIsGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereOpeningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereOpeningBalanceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChartOfAccount whereUpdatedAt($value)
 */
	class ChartOfAccount extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Cheque
 *
 * @property int $id
 * @property int $com_code
 * @property string $direction
 * @property string $cheque_number
 * @property string|null $bank_name
 * @property \Illuminate\Support\Carbon $cheque_date
 * @property \Illuminate\Support\Carbon $due_date
 * @property float $amount
 * @property string $party_type
 * @property int $party_id
 * @property int|null $bank_account_id
 * @property int $treasury_voucher_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $collected_at
 * @property \Illuminate\Support\Carbon|null $bounced_at
 * @property string|null $bounce_reason
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BankAccount|null $bankAccount
 * @property-read string|null $party_name
 * @property-read string $status_color
 * @property-read string $status_label
 * @property-read \App\Models\TreasuryVoucher $treasuryVoucher
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereBankAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereBounceReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereBouncedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereChequeDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereChequeNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereCollectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque wherePartyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque wherePartyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereTreasuryVoucherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cheque whereUpdatedAt($value)
 */
	class Cheque extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Client
 *
 * @property int $id
 * @property string $client_name
 * @property string|null $client_name_A
 * @property string|null $contact_person
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address
 * @property string|null $industry
 * @property int $active 1=مفعل, 0=معطل
 * @property string|null $notes
 * @property int $com_code
 * @property int $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $addedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees
 * @property-read int|null $employees_count
 * @property-read \App\Models\Admin|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereClientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereClientNameA($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereContactPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereIndustry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereUpdatedBy($value)
 */
	class Client extends \Eloquent {}
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
 * App\Models\CommissionRule
 *
 * @property int $id
 * @property string $name اسم قاعدة العمولة
 * @property string $code
 * @property string $basis individual_sales | branch_sales | area_sales | company_sales | fixed | kpi_based
 * @property string $recipient_type employee | branch_manager | area_manager | sales_manager | all_branch
 * @property int|null $org_level_id
 * @property string $calc_type percentage | fixed_amount | tiered
 * @property string $percentage النسبة المئوية
 * @property string $fixed_amount المبلغ الثابت
 * @property array|null $tiers [{"from":0,"to":10000,"pct":1},{"from":10001,"to":null,"pct":2}]
 * @property int|null $branch_id
 * @property bool $is_active
 * @property string|null $description
 * @property int $com_code
 * @property int $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read string $basis_label
 * @property-read \App\Models\OrgLevel|null $orgLevel
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule query()
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereBasis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereCalcType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereFixedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereOrgLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule wherePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereRecipientType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereTiers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereUpdatedAt($value)
 */
	class CommissionRule extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Company
 *
 * @property int $id
 * @property string $name اسم الشركة
 * @property string $slug المعرف الفريد للشركة
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address
 * @property string|null $logo مسار اللوجو
 * @property int $is_active (1=نشطة),(0=معطلة)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Admin> $admins
 * @property-read int|null $admins_count
 * @property-read \App\Models\Admin_panel_setting|null $settings
 * @method static \Illuminate\Database\Eloquent\Builder|Company newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Company query()
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereUpdatedAt($value)
 */
	class Company extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CostCenter
 *
 * @property int $id
 * @property int $com_code
 * @property string $code
 * @property string $name
 * @property int|null $parent_id
 * @property int|null $branch_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CostCenter> $children
 * @property-read int|null $children_count
 * @property-read CostCenter|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter query()
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CostCenter whereUpdatedAt($value)
 */
	class CostCenter extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CrmActivity
 *
 * @property int $id
 * @property int $com_code
 * @property string $linked_type
 * @property int $linked_id
 * @property string $type
 * @property string $notes
 * @property \Illuminate\Support\Carbon $activity_date
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read string $type_label
 * @method static \Illuminate\Database\Eloquent\Builder|CrmActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmActivity query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmActivity whereActivityDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmActivity whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmActivity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmActivity whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmActivity whereLinkedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmActivity whereLinkedType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmActivity whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmActivity whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmActivity whereUpdatedAt($value)
 */
	class CrmActivity extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CrmLead
 *
 * @property int $id
 * @property int $com_code
 * @property string $name
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $source
 * @property string $status
 * @property string|null $notes
 * @property int|null $converted_customer_id
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Customer|null $convertedCustomer
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read string $status_color
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CrmOpportunity> $opportunities
 * @property-read int|null $opportunities_count
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereConvertedCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmLead whereUpdatedAt($value)
 */
	class CrmLead extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CrmOpportunity
 *
 * @property int $id
 * @property int $com_code
 * @property string $title
 * @property int|null $lead_id
 * @property int|null $customer_id
 * @property string $stage
 * @property float $value
 * @property \Illuminate\Support\Carbon|null $expected_close_date
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read \App\Models\Customer|null $customer
 * @property-read string $stage_color
 * @property-read string $stage_label
 * @property-read \App\Models\CrmLead|null $lead
 * @method static \Illuminate\Database\Eloquent\Builder|CrmOpportunity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmOpportunity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmOpportunity query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmOpportunity whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmOpportunity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmOpportunity whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmOpportunity whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmOpportunity whereExpectedCloseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmOpportunity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmOpportunity whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmOpportunity whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmOpportunity whereStage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmOpportunity whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmOpportunity whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmOpportunity whereValue($value)
 */
	class CrmOpportunity extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Customer
 *
 * @property int $id
 * @property int $com_code
 * @property string|null $code
 * @property string $name
 * @property string|null $name_en
 * @property string $type
 * @property string|null $phone
 * @property string|null $phone2
 * @property string|null $email
 * @property string|null $address
 * @property string|null $city
 * @property string|null $governorate
 * @property string|null $tax_number
 * @property string|null $commercial_register
 * @property string $credit_limit
 * @property int $payment_terms
 * @property string $opening_balance
 * @property string|null $notes
 * @property int $is_active
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read float $total_debt
 * @property-read string $type_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesInvoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesOrder> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesPayment> $payments
 * @property-read int|null $payments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesQuotation> $quotations
 * @property-read int|null $quotations_count
 * @method static \Illuminate\Database\Eloquent\Builder|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereCommercialRegister($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereCreditLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereGovernorate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereOpeningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer wherePhone2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereTaxNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereUpdatedAt($value)
 */
	class Customer extends \Eloquent {}
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
 * App\Models\Document
 *
 * @property int $id
 * @property int $com_code
 * @property int|null $category_id
 * @property string $title
 * @property string $file_path
 * @property string $file_original_name
 * @property string|null $linked_type
 * @property int|null $linked_id
 * @property int $version
 * @property string $status
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $uploaded_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $approver
 * @property-read \App\Models\DocumentCategory|null $category
 * @property-read string $status_color
 * @property-read string $status_label
 * @property-read \App\Models\Admin|null $uploadedBy
 * @method static \Illuminate\Database\Eloquent\Builder|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereFileOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereLinkedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereLinkedType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereUploadedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Document whereVersion($value)
 */
	class Document extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DocumentCategory
 *
 * @property int $id
 * @property int $com_code
 * @property string $name
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentCategory whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentCategory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentCategory whereUpdatedAt($value)
 */
	class DocumentCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Employee
 *
 * @property int $id
 * @property string $employee_id
 * @property int|null $finger_id
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
 * @property int|null $emp_departments_id
 * @property string|null $emp_home_tel
 * @property string|null $emp_mobile
 * @property string|null $emp_email
 * @property string|null $emp_photo
 * @property string|null $emp_cv
 * @property string|null $birth_date
 * @property string|null $emp_sal
 * @property string|null $emp_fixed_allowances
 * @property string|null $emp_sal_insurance
 * @property string|null $custom_overtime_multiplier مضاعف أوفرتايم مخصص (null = استخدام إعداد الشركة)
 * @property string|null $overtime_fixed_daily_amount مبلغ الأوفرتايم اليومي الثابت لكل موظف (override عند overtime_calc_type=2)
 * @property int $overtime_enabled (1=يُحتسب الأوفرتايم),(0=لا يُحتسب)
 * @property int $late_deduction_enabled (1=يُحتسب خصم التأخير),(0=معفى من خصم التأخير)
 * @property string|null $medical_insurance
 * @property int|null $is_has_fixed_shift (1=يوجد),(2= لايوجد)
 * @property int $shifts_types_id
 * @property int|null $is_has_finger (1=يوجد),(2= لايوجد)
 * @property int|null $vacation_formula (1=يوجد),(2= لايوجد)
 * @property int|null $sensitive_data (1=يوجد),(2= لايوجد)
 * @property int|null $branches_id
 * @property int|null $client_id
 * @property string|null $hrid كود الموظف لدى العميل (KR-15)
 * @property string|null $client_fake_id الرقم الداخلي للعميل
 * @property string|null $reference_mobile رقم جهة الاتصال الطارئة
 * @property string|null $relative_relation صلة القرابة بجهة الاتصال
 * @property string|null $hiring_documents_status حالة أوراق التعيين
 * @property string|null $insurance_start_date تاريخ بداية التأمين الاجتماعي
 * @property string|null $insurance_end_date تاريخ انتهاء التأمين الاجتماعي
 * @property string|null $form1_notes ملاحظات نموذج 1
 * @property string|null $form6_notes ملاحظات نموذج 6
 * @property string|null $client_notes ملاحظات خاصة بالعميل
 * @property string|null $login_username
 * @property string|null $login_password
 * @property string|null $login_password_hash
 * @property bool $location_tracking_enabled
 * @property string|null $medical_id
 * @property string|null $medical_status
 * @property string|null $medical_progress
 * @property int $com_code
 * @property int $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $weekly_off_day يوم الإجازة الأسبوعي: 0=الأحد،1=الاثنين،2=الثلاثاء،3=الأربعاء،4=الخميس،5=الجمعة،6=السبت،null=لا يوجد
 * @property int $apply_income_tax هل يُخصم من هذا الموظف ضريبة كسب عمل؟ (اختياري لكل موظف)
 * @property string|null $probation_end_date نهاية فترة الاختبار
 * @property string|null $contract_end_date نهاية العقد محدد المدة (فارغ = غير محدد المدة)
 * @property-read \App\Models\Admin|null $addedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendances
 * @property-read int|null $attendances_count
 * @property-read \App\Models\Branche|null $branches
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\Admin_panel_setting|null $comp
 * @property-read \App\Models\Department|null $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeDocument> $documents
 * @property-read int|null $documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeRequest> $employeeRequests
 * @property-read int|null $employee_requests_count
 * @property-read \App\Models\Jobs_categories $jobs_categories
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MonthlyPayroll> $monthlyPayrolls
 * @property-read int|null $monthly_payrolls_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeSalaryHistory> $salaryHistory
 * @property-read int|null $salary_history_count
 * @property-read \App\Models\Shifts_type $shifts_type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \App\Models\Admin|null $updatedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeVacationBalance> $vacationBalance
 * @property-read int|null $vacation_balance_count
 * @method static \Illuminate\Database\Eloquent\Builder|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereApplyIncomeTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBankAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBankBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBankID($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereBranchesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereClientFakeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereClientNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereContractEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereCustomOvertimeMultiplier($value)
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
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereForm1Notes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereForm6Notes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereFunctionalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereHiringDocumentsStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereHrid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereInsuranceEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereInsuranceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereInsuranceStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereInsuranceStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereIsHasFinger($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereIsHasFixedShift($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereLateDeductionEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereLocationTrackingEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereLoginPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereLoginPasswordHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereLoginUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMedicalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMedicalInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMedicalProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMedicalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMotivation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMotivationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereNationalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereOvertimeEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereOvertimeFixedDailyAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereProbationEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereQualificationGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereQualificationYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereReferenceMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereRelativeRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereResignationCause($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereResignationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereResignationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereSalCashVisa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereSensitiveData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereShiftsTypesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereVacationFormula($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Employee whereWeeklyOffDay($value)
 */
	class Employee extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeBranchTarget
 *
 * @property int $id
 * @property int $plan_id
 * @property int $employee_id
 * @property int $month
 * @property int $year
 * @property string $target_amount
 * @property int $com_code
 * @property int|null $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $employee
 * @property-read \App\Models\BranchCommissionPlan|null $plan
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeBranchTarget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeBranchTarget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeBranchTarget query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeBranchTarget whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeBranchTarget whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeBranchTarget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeBranchTarget whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeBranchTarget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeBranchTarget whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeBranchTarget wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeBranchTarget whereTargetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeBranchTarget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeBranchTarget whereYear($value)
 */
	class EmployeeBranchTarget extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeDocument
 *
 * @property int $id
 * @property int $employee_id
 * @property string $doc_type
 * @property string $doc_original_name
 * @property string $doc_path
 * @property int $com_code
 * @property int|null $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeRequest> $accessRequests
 * @property-read int|null $access_requests_count
 * @property-read \App\Models\Employee $employee
 * @property-read string $type_label
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereDocOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereDocPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereDocType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeDocument whereUpdatedAt($value)
 */
	class EmployeeDocument extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeRequest
 *
 * @property int $id
 * @property int $employee_id
 * @property int|null $document_id
 * @property string $request_type annual_vacation | casual_vacation | late_permission | early_leave | mission
 * @property string $request_date تاريخ الطلب
 * @property \Illuminate\Support\Carbon $start_date بداية الإجازة أو موعد التأخير
 * @property \Illuminate\Support\Carbon|null $end_date نهاية الإجازة
 * @property string|null $time_from وقت الإذن من
 * @property string|null $time_to وقت الإذن إلى
 * @property string $days_count عدد الأيام
 * @property string|null $reason سبب الطلب
 * @property int $status (0=قيد الانتظار),(1=مقبول),(2=مرفوض),(3=ملغي)
 * @property int|null $reviewed_by من راجع الطلب
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property \Illuminate\Support\Carbon|null $downloaded_at للطلبات من نوع document_download/salary_certificate: وقت أول تنزيل فعلي - كل موافقة تُستهلك بمجرد أول تنزيل
 * @property string|null $review_notes
 * @property int $com_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EmployeeDocument|null $document
 * @property-read \App\Models\Employee $employee
 * @property-read string $status_label
 * @property-read string $type_label
 * @property-read \App\Models\Admin|null $reviewer
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereDaysCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereDownloadedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereRequestDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereRequestType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereReviewNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereTimeFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereTimeTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeRequest whereUpdatedAt($value)
 */
	class EmployeeRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeSalaryHistory
 *
 * @property int $id
 * @property int $com_code
 * @property int $employee_id
 * @property string $old_salary
 * @property string $new_salary
 * @property string $effective_date
 * @property string|null $method
 * @property string|null $change_value
 * @property int|null $salary_increase_rule_id
 * @property string|null $reason
 * @property string $source
 * @property int|null $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $addedBy
 * @property-read \App\Models\Employee $employee
 * @property-read \App\Models\SalaryIncreaseRule|null $rule
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSalaryHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSalaryHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSalaryHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSalaryHistory whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSalaryHistory whereChangeValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSalaryHistory whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSalaryHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSalaryHistory whereEffectiveDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSalaryHistory whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSalaryHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSalaryHistory whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSalaryHistory whereNewSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSalaryHistory whereOldSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSalaryHistory whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSalaryHistory whereSalaryIncreaseRuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSalaryHistory whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSalaryHistory whereUpdatedAt($value)
 */
	class EmployeeSalaryHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeSanction
 *
 * @property int $id
 * @property int $com_code
 * @property int $employee_id
 * @property int $type
 * @property string $amount
 * @property int $suspension_days
 * @property string $deduct_days
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $date
 * @property int|null $attendance_id
 * @property string|null $deduct_month
 * @property int $status
 * @property int|null $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $addedBy
 * @property-read \App\Models\Employee $employee
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction whereAttendanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction whereDeductDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction whereDeductMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction whereSuspensionDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeSanction whereUpdatedBy($value)
 */
	class EmployeeSanction extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeTargetEvent
 *
 * @property int $id
 * @property int $month
 * @property int $year
 * @property int $branch_id
 * @property int $employee_id
 * @property int $last_day_present
 * @property int|null $replacement_employee_id
 * @property bool $redistribute_target
 * @property string|null $notes
 * @property int $com_code
 * @property int|null $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read \App\Models\Employee|null $employee
 * @property-read \App\Models\Employee|null $replacement
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTargetEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTargetEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTargetEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTargetEvent whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTargetEvent whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTargetEvent whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTargetEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTargetEvent whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTargetEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTargetEvent whereLastDayPresent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTargetEvent whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTargetEvent whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTargetEvent whereRedistributeTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTargetEvent whereReplacementEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTargetEvent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeTargetEvent whereYear($value)
 */
	class EmployeeTargetEvent extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmployeeVacationBalance
 *
 * @property int $id
 * @property int $employee_id
 * @property int $year السنة
 * @property string $annual_balance رصيد الإجازة الاعتيادية (21 يوم قانون مصري)
 * @property string $annual_used المستخدم من الاعتيادية
 * @property string $annual_remaining المتبقي من الاعتيادية
 * @property string $casual_balance رصيد الإجازة العارضة (6 أيام قانون مصري)
 * @property string $casual_used المستخدم من العارضة
 * @property string $casual_remaining المتبقي من العارضة
 * @property string $monthly_accrual الاستحقاق الشهري = الرصيد السنوي / 12
 * @property int $com_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeVacationBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeVacationBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeVacationBalance query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeVacationBalance whereAnnualBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeVacationBalance whereAnnualRemaining($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeVacationBalance whereAnnualUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeVacationBalance whereCasualBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeVacationBalance whereCasualRemaining($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeVacationBalance whereCasualUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeVacationBalance whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeVacationBalance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeVacationBalance whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeVacationBalance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeVacationBalance whereMonthlyAccrual($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeVacationBalance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmployeeVacationBalance whereYear($value)
 */
	class EmployeeVacationBalance extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EtaCredential
 *
 * @property int $id
 * @property int $com_code
 * @property string $auth_type
 * @property string $client_id
 * @property string $client_secret
 * @property string|null $taxpayer_id الرقم الضريبي للمنشأة
 * @property string|null $taxpayer_name
 * @property string|null $access_token
 * @property \Illuminate\Support\Carbon|null $token_expires_at
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EtaCredential newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EtaCredential newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EtaCredential query()
 * @method static \Illuminate\Database\Eloquent\Builder|EtaCredential whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaCredential whereAuthType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaCredential whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaCredential whereClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaCredential whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaCredential whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaCredential whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaCredential whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaCredential whereTaxpayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaCredential whereTaxpayerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaCredential whereTokenExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaCredential whereUpdatedAt($value)
 */
	class EtaCredential extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EtaFreeZone
 *
 * @property int $id
 * @property int $com_code
 * @property string $tax_id
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EtaFreeZone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EtaFreeZone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EtaFreeZone query()
 * @method static \Illuminate\Database\Eloquent\Builder|EtaFreeZone whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaFreeZone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaFreeZone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaFreeZone whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaFreeZone whereTaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaFreeZone whereUpdatedAt($value)
 */
	class EtaFreeZone extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EtaInvoice
 *
 * @property int $id
 * @property int $com_code
 * @property string $direction Sent=مبيعات, Received=مشتريات
 * @property string $uuid
 * @property string|null $long_id
 * @property string|null $internal_id الرقم الداخلي
 * @property int|null $sales_invoice_id
 * @property int|null $purchase_invoice_id
 * @property string $document_type I=فاتورة, C=إشعار دائن, D=إشعار مدين
 * @property string|null $document_type_version
 * @property string|null $issuer_id رقم ضريبي المُصدر
 * @property string|null $issuer_name
 * @property string|null $receiver_id رقم ضريبي المستلم
 * @property string|null $receiver_name
 * @property \Illuminate\Support\Carbon|null $date_issued
 * @property \Illuminate\Support\Carbon|null $date_received
 * @property string $total_sales
 * @property string $total_discount
 * @property string $net_amount
 * @property string $total_vat
 * @property string $total_amount
 * @property string $status
 * @property string|null $activity_code
 * @property bool $is_posted تم الترحيل محاسبياً
 * @property \Illuminate\Support\Carbon|null $posted_at
 * @property int|null $posted_by
 * @property string|null $posting_notes
 * @property array|null $raw_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $direction_label
 * @property-read string $doc_type_label
 * @property-read string $status_class
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EtaInvoiceItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Admin|null $poster
 * @property-read \App\Models\PurchaseInvoice|null $purchaseInvoice
 * @property-read \App\Models\SalesInvoice|null $salesInvoice
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereActivityCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereDateIssued($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereDateReceived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereDocumentTypeVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereInternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereIsPosted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereIssuerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereIssuerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereLongId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereNetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice wherePostedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice wherePostedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice wherePostingNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice wherePurchaseInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereRawData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereReceiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereReceiverName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereSalesInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereTotalDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereTotalSales($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereTotalVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoice whereUuid($value)
 */
	class EtaInvoice extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EtaInvoiceItem
 *
 * @property int $id
 * @property int $eta_invoice_id
 * @property string|null $item_code
 * @property string|null $description
 * @property string|null $unit_type
 * @property string $quantity
 * @property string $unit_price
 * @property string $total
 * @property string $discount
 * @property string $net_total
 * @property string $vat_rate
 * @property string $vat_amount
 * @property string $total_with_vat
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EtaInvoice $invoice
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem whereEtaInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem whereItemCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem whereNetTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem whereTotalWithVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem whereUnitType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem whereVatAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EtaInvoiceItem whereVatRate($value)
 */
	class EtaInvoiceItem extends \Eloquent {}
}

namespace App\Models{
/**
 * ✅ FIX: اسم الجدول في الـ migration هو 'finance_calenders' (lowercase)
 * لكن الـ model كان يستخدم 'Finance_calenders' (يسبب مشكلة على Linux)
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Finance_cln_period> $periods
 * @property-read int|null $periods_count
 * @property-read \App\Models\Admin_panel_setting|null $setting
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
 * @property int|null $working_days أيام العمل الفعلية في الشهر
 * @property string|null $vacation_days_accrual استحقاق الإجازة بالأيام لهذا الشهر تحديداً — null يعني استخدام الإعداد العام
 * @property-read \App\Models\Month|null $Month
 * @property-read \App\Models\Admin|null $added
 * @property-read \App\Models\Finance_calender $financeCalender
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
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereVacationDaysAccrual($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Finance_cln_period whereWorkingDays($value)
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
 * @property int|null $branches_id
 * @property array|null $extra_branch_ids
 * @property string|null $model موديل الجهاز
 * @property string|null $serial_number الرقم التسلسلي
 * @property string|null $password كلمة مرور الجهاز إن وُجدت
 * @property string|null $api_token توكن مصادقة Agent الفروع البعيدة
 * @property int $status (1=نشط),(2=معطل),(3=خطأ)
 * @property \Illuminate\Support\Carbon|null $last_sync_at آخر مزامنة
 * @property int $last_sync_records عدد سجلات آخر مزامنة
 * @property string|null $last_error آخر خطأ
 * @property int $com_code
 * @property int $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read string $protocol_label
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FingerprintLog> $logs
 * @property-read int|null $logs_count
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice query()
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereApiToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereBranchesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereDeviceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereDeviceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FingerprintDevice whereExtraBranchIds($value)
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
 * App\Models\FixedAsset
 *
 * @property int $id
 * @property int $com_code
 * @property string $asset_number
 * @property int $category_id
 * @property string $name
 * @property string|null $description
 * @property int|null $branch_id
 * @property string|null $location
 * @property \Illuminate\Support\Carbon $purchase_date
 * @property float $purchase_cost
 * @property int $useful_life_years
 * @property float $salvage_value
 * @property string $depreciation_method
 * @property float $accumulated_depreciation
 * @property float $book_value
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $disposal_date
 * @property float|null $disposal_amount
 * @property string|null $disposal_notes
 * @property int|null $source_purchase_invoice_id
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read \App\Models\AssetCategory $category
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AssetDepreciationEntry> $depreciationEntries
 * @property-read int|null $depreciation_entries_count
 * @property-read string $status_color
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AssetTransfer> $transfers
 * @property-read int|null $transfers_count
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset query()
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereAccumulatedDepreciation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereAssetNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereBookValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereDepreciationMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereDisposalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereDisposalDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereDisposalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset wherePurchaseCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset wherePurchaseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereSalvageValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereSourcePurchaseInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixedAsset whereUsefulLifeYears($value)
 */
	class FixedAsset extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\GlPostingRule
 *
 * @property int $id
 * @property int $com_code
 * @property string $event_type
 * @property string $line_role
 * @property int $account_id
 * @property string $side
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ChartOfAccount $account
 * @method static \Illuminate\Database\Eloquent\Builder|GlPostingRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GlPostingRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GlPostingRule query()
 * @method static \Illuminate\Database\Eloquent\Builder|GlPostingRule whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlPostingRule whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlPostingRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlPostingRule whereEventType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlPostingRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlPostingRule whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlPostingRule whereLineRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlPostingRule whereSide($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlPostingRule whereUpdatedAt($value)
 */
	class GlPostingRule extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\IncomeTaxBracket
 *
 * @property int $id
 * @property int $com_code
 * @property string $from_amount
 * @property string|null $to_amount فارغ = بلا حد أعلى (آخر شريحة)
 * @property string $rate نسبة الضريبة % على الجزء الواقع داخل هذه الشريحة
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeTaxBracket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeTaxBracket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeTaxBracket query()
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeTaxBracket whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeTaxBracket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeTaxBracket whereFromAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeTaxBracket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeTaxBracket whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeTaxBracket whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeTaxBracket whereToAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IncomeTaxBracket whereUpdatedAt($value)
 */
	class IncomeTaxBracket extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Item
 *
 * @property int $id
 * @property int $com_code
 * @property string|null $code
 * @property string|null $barcode
 * @property string $name
 * @property string|null $name_en
 * @property int|null $category_id
 * @property int|null $unit_id
 * @property string $type
 * @property string $cost_price
 * @property string $costing_method
 * @property string $selling_price
 * @property string $min_selling_price
 * @property string $reorder_level
 * @property string|null $description
 * @property string|null $image
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ItemCategory|null $category
 * @property-read string $type_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StockBalance> $stockBalances
 * @property-read int|null $stock_balances_count
 * @property-read \App\Models\ItemUnit|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Item query()
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereBarcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereCostPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereCostingMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereMinSellingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereReorderLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereSellingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereUpdatedAt($value)
 */
	class Item extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ItemCategory
 *
 * @property int $id
 * @property int $com_code
 * @property string|null $code
 * @property string $name
 * @property string|null $name_en
 * @property int|null $parent_id
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ItemCategory> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item> $items
 * @property-read int|null $items_count
 * @property-read ItemCategory|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder|ItemCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ItemCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ItemCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ItemCategory whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemCategory whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemCategory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemCategory whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemCategory whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemCategory whereUpdatedAt($value)
 */
	class ItemCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ItemUnit
 *
 * @property int $id
 * @property int $com_code
 * @property string $name
 * @property string|null $name_en
 * @property string|null $symbol
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder|ItemUnit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ItemUnit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ItemUnit query()
 * @method static \Illuminate\Database\Eloquent\Builder|ItemUnit whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemUnit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemUnit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemUnit whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemUnit whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemUnit whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemUnit whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemUnit whereUpdatedAt($value)
 */
	class ItemUnit extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Jobs_categories
 *
 * @property int $id
 * @property string $job_name
 * @property int|null $org_level_id
 * @property int $com_code
 * @property int $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $addedby
 * @property-read \App\Models\OrgLevel|null $orgLevel
 * @property-read \App\Models\Admin|null $updatedby
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories query()
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories whereJobName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories whereOrgLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs_categories whereUpdatedBy($value)
 */
	class Jobs_categories extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\JournalEntry
 *
 * @property int $id
 * @property int $com_code
 * @property string $entry_number
 * @property string $entry_date
 * @property string $entry_type
 * @property string|null $source_module
 * @property int|null $source_id
 * @property string|null $reference
 * @property string|null $description
 * @property string $total_debit
 * @property string $total_credit
 * @property string $status
 * @property int|null $reversed_entry_id
 * @property int|null $period_id
 * @property int|null $created_by
 * @property int|null $posted_by
 * @property string|null $posted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read string $status_color
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\JournalEntryLine> $lines
 * @property-read int|null $lines_count
 * @property-read \App\Models\AccountingPeriod|null $period
 * @property-read \App\Models\Admin|null $postedBy
 * @property-read JournalEntry|null $reversedEntry
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry query()
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry whereEntryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry whereEntryNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry whereEntryType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry wherePeriodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry wherePostedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry wherePostedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry whereReversedEntryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry whereSourceModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry whereTotalCredit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry whereTotalDebit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntry whereUpdatedAt($value)
 */
	class JournalEntry extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\JournalEntryLine
 *
 * @property int $id
 * @property int $journal_entry_id
 * @property int $account_id
 * @property int|null $cost_center_id
 * @property int|null $branch_id
 * @property string $debit
 * @property string $credit
 * @property string|null $description
 * @property string|null $party_type
 * @property int|null $party_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ChartOfAccount $account
 * @property-read \App\Models\CostCenter|null $costCenter
 * @property-read string|null $party_name
 * @property-read \App\Models\JournalEntry $journalEntry
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntryLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntryLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntryLine query()
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntryLine whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntryLine whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntryLine whereCostCenterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntryLine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntryLine whereCredit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntryLine whereDebit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntryLine whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntryLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntryLine whereJournalEntryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntryLine wherePartyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntryLine wherePartyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JournalEntryLine whereUpdatedAt($value)
 */
	class JournalEntryLine extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\KpiDefinition
 *
 * @property int $id
 * @property string $name اسم المؤشر
 * @property string $code كود المؤشر
 * @property string $category performance | quality | attendance | sales | custom
 * @property string|null $measurement_unit % | رقم | ريال | نقطة
 * @property string $target_value القيمة المستهدفة
 * @property string $weight الوزن النسبي للمؤشر من 100
 * @property bool $affects_salary (1=يؤثر على الراتب),(0=للإحصاء فقط)
 * @property string $salary_effect_type bonus=مكافأة | deduction=خصم | both
 * @property string $max_bonus_pct أقصى نسبة مكافأة من الراتب %
 * @property string $max_deduction_pct أقصى نسبة خصم من الراتب %
 * @property bool $is_active
 * @property int $sort_order ترتيب العرض
 * @property string|null $description
 * @property int $com_code
 * @property int $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $category_label
 * @property-read string $effect_type_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\KpiEmployeeScore> $scores
 * @property-read int|null $scores_count
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition query()
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereAffectsSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereMaxBonusPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereMaxDeductionPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereMeasurementUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereSalaryEffectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereTargetValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiDefinition whereWeight($value)
 */
	class KpiDefinition extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\KpiEmployeeScore
 *
 * @property int $id
 * @property int $kpi_id
 * @property int $employee_id
 * @property int $month
 * @property int $year
 * @property string $actual_value القيمة الفعلية المحققة
 * @property string $achievement_pct نسبة الإنجاز = actual / target × 100
 * @property string $score النقاط = achievement_pct × weight / 100
 * @property string $salary_effect_amount قيمة التأثير المالي على الراتب
 * @property int $effect_direction (1=مكافأة),(2=خصم)
 * @property string|null $notes
 * @property int $com_code
 * @property int $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @property-read \App\Models\KpiDefinition $kpi
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore query()
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore whereAchievementPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore whereActualValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore whereEffectDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore whereKpiId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore whereSalaryEffectAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiEmployeeScore whereYear($value)
 */
	class KpiEmployeeScore extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LeaveCompensationRate
 *
 * @property int $id
 * @property int $com_code
 * @property string $level_type
 * @property int $level_id
 * @property string $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationRate query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationRate whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationRate whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationRate whereLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationRate whereLevelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationRate whereUpdatedAt($value)
 */
	class LeaveCompensationRate extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LeaveCompensationSetting
 *
 * @property int $id
 * @property int $com_code
 * @property int $comp_type
 * @property string $day_multiplier
 * @property string $fixed_level
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationSetting whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationSetting whereCompType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationSetting whereDayMultiplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationSetting whereFixedLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeaveCompensationSetting whereUpdatedAt($value)
 */
	class LeaveCompensationSetting extends \Eloquent {}
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
 * @property int $weekly_off_days أيام الإجازة الأسبوعية المدفوعة
 * @property string $basic_salary الراتب الأساسي كامل الشهر
 * @property string $daily_rate قيمة اليوم الواحد
 * @property string $earned_salary الراتب المستحق بعد الحضور
 * @property string $fixed_allowances الإضافات الثابتة
 * @property string $overtime_amount إجمالي الأوفرتايم
 * @property string $commissions_amount إجمالي العمولات
 * @property string $bonuses_amount إجمالي المكافآت
 * @property string $leave_compensation_amount بدل العمل في الإجازة الأسبوعية
 * @property string $kpi_bonus_amount مكافأة مؤشرات الأداء KPI
 * @property string $late_deductions خصومات التأخير
 * @property string $absence_deductions خصومات الغياب
 * @property string $deductions_amount إجمالي الخصومات الأخرى
 * @property string $advance_installment قسط السلفة
 * @property string $insurance_deduction خصم التأمينات
 * @property string $income_tax_deduction
 * @property string $kpi_deduction_amount خصم مؤشرات الأداء KPI
 * @property string $sanctions_deduction خصم الجزاءات (مالي / باليوم / إيقاف عن العمل)
 * @property string $company_insurance_contribution حصة الشركة في التأمينات الاجتماعية
 * @property string $total_insurance إجمالي التأمينات المدفوعة (الموظف + الشركة)
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
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereBonusesAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereCommissionsAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereCompanyInsuranceContribution($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereDailyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereDeductionsAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereEarnedSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereFixedAllowances($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereGrossSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereIncomeTaxDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereInsuranceDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereKpiBonusAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereKpiDeductionAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereLateDeductions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereLeaveCompensationAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereLeaveDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereNetSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereOvertimeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll wherePeriodFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll wherePeriodTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereSanctionsDeduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereTotalDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereTotalInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereWeeklyOffDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereWorkDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonthlyPayroll whereYear($value)
 */
	class MonthlyPayroll extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\NameDictionary
 *
 * @property int $id
 * @property string $ar_name
 * @property string $en_name
 * @property int|null $com_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|NameDictionary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NameDictionary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NameDictionary query()
 * @method static \Illuminate\Database\Eloquent\Builder|NameDictionary whereArName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NameDictionary whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NameDictionary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NameDictionary whereEnName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NameDictionary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NameDictionary whereUpdatedAt($value)
 */
	class NameDictionary extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\OrgLevel
 *
 * @property int $id
 * @property string $name
 * @property string|null $name_en
 * @property int $level_order
 * @property int|null $parent_id
 * @property string $level_type
 * @property bool $is_management
 * @property bool $is_sales_role
 * @property bool $receives_seller_commission
 * @property bool $receives_manager_commission
 * @property string|null $description
 * @property string $com_code
 * @property int|null $added_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, OrgLevel> $children
 * @property-read int|null $children_count
 * @property-read string $level_type_badge
 * @property-read string $level_type_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Jobs_categories> $jobs
 * @property-read int|null $jobs_count
 * @property-read OrgLevel|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel whereIsManagement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel whereIsSalesRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel whereLevelOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel whereLevelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel whereReceivesManagerCommission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel whereReceivesSellerCommission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgLevel whereUpdatedBy($value)
 */
	class OrgLevel extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\OrgTemplate
 *
 * @property int $id
 * @property string $template_name
 * @property string $company_type
 * @property array $levels_data
 * @property bool $is_default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $company_type_label
 * @method static \Illuminate\Database\Eloquent\Builder|OrgTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgTemplate whereCompanyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgTemplate whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgTemplate whereLevelsData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgTemplate whereTemplateName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrgTemplate whereUpdatedAt($value)
 */
	class OrgTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PosRegister
 *
 * @property int $id
 * @property int $com_code
 * @property string $name
 * @property int $cash_box_id
 * @property int $warehouse_id
 * @property int|null $branch_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read \App\Models\CashBox $cashBox
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PosSession> $sessions
 * @property-read int|null $sessions_count
 * @property-read \App\Models\Warehouse $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder|PosRegister newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PosRegister newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PosRegister query()
 * @method static \Illuminate\Database\Eloquent\Builder|PosRegister whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosRegister whereCashBoxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosRegister whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosRegister whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosRegister whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosRegister whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosRegister whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosRegister whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosRegister whereWarehouseId($value)
 */
	class PosRegister extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PosSession
 *
 * @property int $id
 * @property int $com_code
 * @property int $register_id
 * @property int $opened_by
 * @property string $opening_amount
 * @property string|null $expected_closing_amount
 * @property string|null $counted_closing_amount
 * @property string|null $difference
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $opened_at
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read float $sales_total
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesInvoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \App\Models\Admin $openedBy
 * @property-read \App\Models\PosRegister $register
 * @method static \Illuminate\Database\Eloquent\Builder|PosSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PosSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PosSession query()
 * @method static \Illuminate\Database\Eloquent\Builder|PosSession whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosSession whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosSession whereCountedClosingAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosSession whereDifference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosSession whereExpectedClosingAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosSession whereOpenedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosSession whereOpenedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosSession whereOpeningAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosSession whereRegisterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosSession whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PosSession whereUpdatedAt($value)
 */
	class PosSession extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProductionOrder
 *
 * @property int $id
 * @property int $com_code
 * @property string $order_number
 * @property int $bom_id
 * @property int $item_id
 * @property float $planned_quantity
 * @property float $produced_quantity
 * @property int $source_warehouse_id
 * @property int $target_warehouse_id
 * @property int|null $branch_id
 * @property \Illuminate\Support\Carbon|null $planned_start_date
 * @property \Illuminate\Support\Carbon|null $planned_end_date
 * @property \Illuminate\Support\Carbon|null $actual_start_date
 * @property \Illuminate\Support\Carbon|null $actual_end_date
 * @property float $labor_cost
 * @property float $overhead_cost
 * @property float $material_cost
 * @property float $total_cost
 * @property string $status
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BillOfMaterial $bom
 * @property-read \App\Models\Branche|null $branch
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read string $status_color
 * @property-read string $status_label
 * @property-read \App\Models\Item|null $item
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductionOrderMaterial> $materials
 * @property-read int|null $materials_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductionReceipt> $receipts
 * @property-read int|null $receipts_count
 * @property-read \App\Models\Warehouse|null $sourceWarehouse
 * @property-read \App\Models\Warehouse|null $targetWarehouse
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereActualEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereActualStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereBomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereLaborCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereMaterialCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereOverheadCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder wherePlannedEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder wherePlannedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder wherePlannedStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereProducedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereSourceWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereTargetWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereUpdatedAt($value)
 */
	class ProductionOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProductionOrderMaterial
 *
 * @property int $id
 * @property int $production_order_id
 * @property int $item_id
 * @property float $planned_quantity
 * @property float $issued_quantity
 * @property float|null $unit_cost
 * @property float|null $total_cost
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item|null $item
 * @property-read \App\Models\ProductionOrder $productionOrder
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrderMaterial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrderMaterial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrderMaterial query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrderMaterial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrderMaterial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrderMaterial whereIssuedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrderMaterial whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrderMaterial wherePlannedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrderMaterial whereProductionOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrderMaterial whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrderMaterial whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrderMaterial whereUpdatedAt($value)
 */
	class ProductionOrderMaterial extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProductionReceipt
 *
 * @property int $id
 * @property int $production_order_id
 * @property float $quantity
 * @property float $unit_cost
 * @property float $total_cost
 * @property \Illuminate\Support\Carbon $date
 * @property int $warehouse_id
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read \App\Models\ProductionOrder $productionOrder
 * @property-read \App\Models\Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionReceipt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionReceipt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionReceipt query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionReceipt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionReceipt whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionReceipt whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionReceipt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionReceipt whereProductionOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionReceipt whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionReceipt whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionReceipt whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionReceipt whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductionReceipt whereWarehouseId($value)
 */
	class ProductionReceipt extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Project
 *
 * @property int $id
 * @property int $com_code
 * @property string $name
 * @property int|null $customer_id
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property float $budget
 * @property string $status
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read \App\Models\Customer|null $customer
 * @property-read string $status_color
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProjectTask> $tasks
 * @property-read int|null $tasks_count
 * @method static \Illuminate\Database\Eloquent\Builder|Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project query()
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereUpdatedAt($value)
 */
	class Project extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProjectTask
 *
 * @property int $id
 * @property int $project_id
 * @property string $title
 * @property int|null $assigned_to
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property string $status
 * @property string $priority
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee|null $assignee
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read string $priority_color
 * @property-read string $priority_label
 * @property-read string $status_color
 * @property-read string $status_label
 * @property-read \App\Models\Project $project
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTask query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTask whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTask whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTask whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTask whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTask whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTask whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTask wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTask whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTask whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTask whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectTask whereUpdatedAt($value)
 */
	class ProjectTask extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PurchaseInvoice
 *
 * @property int $id
 * @property int $com_code
 * @property string $invoice_number
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property int|null $supplier_id
 * @property int|null $branch_id
 * @property int|null $warehouse_id
 * @property int|null $order_id
 * @property string|null $supplier_invoice_no
 * @property string $invoice_type
 * @property string $subtotal
 * @property string $discount_amount
 * @property string $tax_rate
 * @property string $tax_amount
 * @property string $total
 * @property string $paid_amount
 * @property string $remaining_amount
 * @property string $payment_status
 * @property string $status
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read string $payment_status_label
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseInvoiceItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\PurchaseOrder|null $order
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchasePayment> $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\Supplier|null $supplier
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereInvoiceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereRemainingAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereSupplierInvoiceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoice whereWarehouseId($value)
 */
	class PurchaseInvoice extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PurchaseInvoiceItem
 *
 * @property int $id
 * @property int $invoice_id
 * @property int|null $item_id
 * @property string|null $description
 * @property int|null $unit_id
 * @property string $quantity
 * @property string $unit_price
 * @property string $discount_percent
 * @property string $discount_amount
 * @property string $tax_rate
 * @property string $tax_amount
 * @property string $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PurchaseInvoice $invoice
 * @property-read \App\Models\Item|null $item
 * @property-read \App\Models\ItemUnit|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoiceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoiceItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoiceItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoiceItem whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoiceItem whereDiscountPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoiceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoiceItem whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoiceItem whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoiceItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoiceItem whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoiceItem whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoiceItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoiceItem whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoiceItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseInvoiceItem whereUpdatedAt($value)
 */
	class PurchaseInvoiceItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PurchaseOrder
 *
 * @property int $id
 * @property int $com_code
 * @property string $order_number
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon|null $expected_date
 * @property int|null $supplier_id
 * @property int|null $branch_id
 * @property int|null $request_id
 * @property string $subtotal
 * @property string $discount_amount
 * @property string $tax_rate
 * @property string $tax_amount
 * @property string $total
 * @property string $status
 * @property string|null $delivery_address
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseInvoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseOrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\PurchaseRequest|null $request
 * @property-read \App\Models\Supplier|null $supplier
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereDeliveryAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereExpectedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrder whereUpdatedAt($value)
 */
	class PurchaseOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PurchaseOrderItem
 *
 * @property int $id
 * @property int $order_id
 * @property int|null $item_id
 * @property string|null $description
 * @property int|null $unit_id
 * @property string $quantity
 * @property string $received_qty
 * @property string $unit_price
 * @property string $discount_percent
 * @property string $discount_amount
 * @property string $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read float $remaining_qty
 * @property-read \App\Models\Item|null $item
 * @property-read \App\Models\PurchaseOrder $order
 * @property-read \App\Models\ItemUnit|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrderItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrderItem whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrderItem whereDiscountPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrderItem whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrderItem whereReceivedQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrderItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrderItem whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrderItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseOrderItem whereUpdatedAt($value)
 */
	class PurchaseOrderItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PurchasePayment
 *
 * @property int $id
 * @property int|null $treasury_voucher_id
 * @property int $com_code
 * @property string $payment_number
 * @property \Illuminate\Support\Carbon $date
 * @property int|null $supplier_id
 * @property int|null $invoice_id
 * @property int|null $branch_id
 * @property string $amount
 * @property string $payment_method
 * @property string|null $bank_name
 * @property string|null $cheque_number
 * @property \Illuminate\Support\Carbon|null $cheque_date
 * @property string|null $reference_number
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read string $method_label
 * @property-read \App\Models\PurchaseInvoice|null $invoice
 * @property-read \App\Models\Supplier|null $supplier
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment whereChequeDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment whereChequeNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment wherePaymentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment whereReferenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment whereTreasuryVoucherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchasePayment whereUpdatedAt($value)
 */
	class PurchasePayment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PurchaseRequest
 *
 * @property int $id
 * @property int $com_code
 * @property string $request_number
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon|null $needed_by_date
 * @property int|null $branch_id
 * @property string $status
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseRequestItem> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseOrder> $orders
 * @property-read int|null $orders_count
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequest whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequest whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequest whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequest whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequest whereNeededByDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequest whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequest whereRequestNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequest whereUpdatedAt($value)
 */
	class PurchaseRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PurchaseRequestItem
 *
 * @property int $id
 * @property int $request_id
 * @property int|null $item_id
 * @property string|null $description
 * @property int|null $unit_id
 * @property string $quantity
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item|null $item
 * @property-read \App\Models\PurchaseRequest $request
 * @property-read \App\Models\ItemUnit|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequestItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequestItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequestItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequestItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequestItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequestItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequestItem whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequestItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequestItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequestItem whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequestItem whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseRequestItem whereUpdatedAt($value)
 */
	class PurchaseRequestItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PurchaseReturn
 *
 * @property int $id
 * @property int $com_code
 * @property string $return_number
 * @property \Illuminate\Support\Carbon $date
 * @property int|null $supplier_id
 * @property int|null $invoice_id
 * @property int|null $branch_id
 * @property int|null $warehouse_id
 * @property string|null $reason
 * @property string $subtotal
 * @property string $tax_amount
 * @property string $total
 * @property string $status
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read string $status_label
 * @property-read \App\Models\PurchaseInvoice|null $invoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseReturnItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Supplier|null $supplier
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereReturnNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturn whereWarehouseId($value)
 */
	class PurchaseReturn extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PurchaseReturnItem
 *
 * @property int $id
 * @property int $return_id
 * @property int|null $item_id
 * @property string|null $description
 * @property int|null $unit_id
 * @property string $quantity
 * @property string $unit_price
 * @property string $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item|null $item
 * @property-read \App\Models\PurchaseReturn $return_
 * @property-read \App\Models\ItemUnit|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturnItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturnItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturnItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturnItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturnItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturnItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturnItem whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturnItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturnItem whereReturnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturnItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturnItem whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturnItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PurchaseReturnItem whereUpdatedAt($value)
 */
	class PurchaseReturnItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\QualityChecklist
 *
 * @property int $id
 * @property int $com_code
 * @property string $name
 * @property string $applies_to
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $applies_to_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QualityChecklistItem> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklist query()
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklist whereAppliesTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklist whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklist whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklist whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklist whereUpdatedAt($value)
 */
	class QualityChecklist extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\QualityChecklistItem
 *
 * @property int $id
 * @property int $checklist_id
 * @property string $criterion
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\QualityChecklist $checklist
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklistItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklistItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklistItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklistItem whereChecklistId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklistItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklistItem whereCriterion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklistItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklistItem whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityChecklistItem whereUpdatedAt($value)
 */
	class QualityChecklistItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\QualityInspection
 *
 * @property int $id
 * @property int $com_code
 * @property string $inspection_number
 * @property int $checklist_id
 * @property string $source_type
 * @property int $source_id
 * @property int|null $inspector_id
 * @property \Illuminate\Support\Carbon $date
 * @property string $overall_result
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\QualityChecklist $checklist
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read string $result_color
 * @property-read string $result_label
 * @property-read mixed $source
 * @property-read string $source_type_label
 * @property-read \App\Models\Admin|null $inspector
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QualityInspectionItem> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspection query()
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspection whereChecklistId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspection whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspection whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspection whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspection whereInspectionNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspection whereInspectorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspection whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspection whereOverallResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspection whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspection whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspection whereUpdatedAt($value)
 */
	class QualityInspection extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\QualityInspectionItem
 *
 * @property int $id
 * @property int $inspection_id
 * @property int $checklist_item_id
 * @property string $result
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\QualityChecklistItem $checklistItem
 * @property-read \App\Models\QualityInspection $inspection
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspectionItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspectionItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspectionItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspectionItem whereChecklistItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspectionItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspectionItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspectionItem whereInspectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspectionItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspectionItem whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityInspectionItem whereUpdatedAt($value)
 */
	class QualityInspectionItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SalaryIncreaseRule
 *
 * @property int $id
 * @property int $com_code
 * @property string $scope_type
 * @property int|null $scope_id
 * @property string $method
 * @property string $value
 * @property string $effective_date
 * @property string|null $notes
 * @property int $status
 * @property int|null $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $addedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeSalaryHistory> $history
 * @property-read int|null $history_count
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryIncreaseRule label()
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryIncreaseRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryIncreaseRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryIncreaseRule query()
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryIncreaseRule whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryIncreaseRule whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryIncreaseRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryIncreaseRule whereEffectiveDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryIncreaseRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryIncreaseRule whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryIncreaseRule whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryIncreaseRule whereScopeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryIncreaseRule whereScopeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryIncreaseRule whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryIncreaseRule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalaryIncreaseRule whereValue($value)
 */
	class SalaryIncreaseRule extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SalesInvoice
 *
 * @property int $id
 * @property int $com_code
 * @property string $invoice_number
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property int|null $customer_id
 * @property int|null $branch_id
 * @property int|null $warehouse_id
 * @property int|null $order_id
 * @property int|null $pos_session_id
 * @property string $invoice_type
 * @property string $subtotal
 * @property string $discount_amount
 * @property string $tax_rate
 * @property string $tax_amount
 * @property string $total
 * @property string $paid_amount
 * @property string $remaining_amount
 * @property string $payment_status
 * @property string $status
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read \App\Models\Customer|null $customer
 * @property-read string $payment_status_label
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesInvoiceItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\SalesOrder|null $order
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesPayment> $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\PosSession|null $posSession
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereInvoiceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice wherePosSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereRemainingAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoice whereWarehouseId($value)
 */
	class SalesInvoice extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SalesInvoiceItem
 *
 * @property int $id
 * @property int $invoice_id
 * @property int|null $item_id
 * @property string|null $description
 * @property int|null $unit_id
 * @property string $quantity
 * @property string $unit_price
 * @property string $discount_percent
 * @property string $discount_amount
 * @property string $tax_rate
 * @property string $tax_amount
 * @property string $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SalesInvoice $invoice
 * @property-read \App\Models\Item|null $item
 * @property-read \App\Models\ItemUnit|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoiceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoiceItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoiceItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoiceItem whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoiceItem whereDiscountPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoiceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoiceItem whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoiceItem whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoiceItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoiceItem whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoiceItem whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoiceItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoiceItem whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoiceItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesInvoiceItem whereUpdatedAt($value)
 */
	class SalesInvoiceItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SalesOrder
 *
 * @property int $id
 * @property int $com_code
 * @property string $order_number
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon|null $delivery_date
 * @property int|null $customer_id
 * @property int|null $branch_id
 * @property int|null $quotation_id
 * @property string $subtotal
 * @property string $discount_amount
 * @property string $tax_rate
 * @property string $tax_amount
 * @property string $total
 * @property string $status
 * @property string|null $delivery_address
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read \App\Models\Customer|null $customer
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesInvoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesOrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\SalesQuotation|null $quotation
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereDeliveryAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereDeliveryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereQuotationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrder whereUpdatedAt($value)
 */
	class SalesOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SalesOrderItem
 *
 * @property int $id
 * @property int $order_id
 * @property int|null $item_id
 * @property string|null $description
 * @property int|null $unit_id
 * @property string $quantity
 * @property string $delivered_qty
 * @property string $unit_price
 * @property string $discount_percent
 * @property string $discount_amount
 * @property string $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read float $remaining_qty
 * @property-read \App\Models\Item|null $item
 * @property-read \App\Models\SalesOrder $order
 * @property-read \App\Models\ItemUnit|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrderItem whereDeliveredQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrderItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrderItem whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrderItem whereDiscountPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrderItem whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrderItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrderItem whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrderItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesOrderItem whereUpdatedAt($value)
 */
	class SalesOrderItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SalesPayment
 *
 * @property int $id
 * @property int|null $treasury_voucher_id
 * @property int $com_code
 * @property string $payment_number
 * @property \Illuminate\Support\Carbon $date
 * @property int|null $customer_id
 * @property int|null $invoice_id
 * @property int|null $branch_id
 * @property string $amount
 * @property string $payment_method
 * @property string|null $bank_name
 * @property string|null $cheque_number
 * @property \Illuminate\Support\Carbon|null $cheque_date
 * @property string|null $reference_number
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read \App\Models\Customer|null $customer
 * @property-read string $method_label
 * @property-read \App\Models\SalesInvoice|null $invoice
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment query()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment whereChequeDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment whereChequeNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment wherePaymentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment whereReferenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment whereTreasuryVoucherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesPayment whereUpdatedAt($value)
 */
	class SalesPayment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SalesQuotation
 *
 * @property int $id
 * @property int $com_code
 * @property string $quote_number
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon|null $valid_until
 * @property int|null $customer_id
 * @property int|null $branch_id
 * @property string $subtotal
 * @property string $discount_type
 * @property string $discount_value
 * @property string $discount_amount
 * @property string $tax_rate
 * @property string $tax_amount
 * @property string $total
 * @property string $status
 * @property string|null $notes
 * @property string|null $terms
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read \App\Models\Customer|null $customer
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesQuotationItem> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation query()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereDiscountValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereQuoteNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotation whereValidUntil($value)
 */
	class SalesQuotation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SalesQuotationItem
 *
 * @property int $id
 * @property int $quotation_id
 * @property int|null $item_id
 * @property string|null $description
 * @property int|null $unit_id
 * @property string $quantity
 * @property string $unit_price
 * @property string $discount_percent
 * @property string $discount_amount
 * @property string $total
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item|null $item
 * @property-read \App\Models\SalesQuotation $quotation
 * @property-read \App\Models\ItemUnit|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotationItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotationItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotationItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotationItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotationItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotationItem whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotationItem whereDiscountPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotationItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotationItem whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotationItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotationItem whereQuotationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotationItem whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotationItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotationItem whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotationItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesQuotationItem whereUpdatedAt($value)
 */
	class SalesQuotationItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SalesRecord
 *
 * @property int $id
 * @property int|null $employee_id
 * @property int|null $branch_id
 * @property int $month
 * @property int $year
 * @property string $sales_amount قيمة المبيعات
 * @property string|null $sales_type نوع المبيعات
 * @property string|null $notes
 * @property int|null $from_day اليوم الأول لفترة المبيعات
 * @property int|null $to_day اليوم الأخير لفترة المبيعات
 * @property int $com_code
 * @property int $added_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read \App\Models\Employee|null $employee
 * @method static \Illuminate\Database\Eloquent\Builder|SalesRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesRecord whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesRecord whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesRecord whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesRecord whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesRecord whereFromDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesRecord whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesRecord whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesRecord whereSalesAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesRecord whereSalesType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesRecord whereToDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesRecord whereYear($value)
 */
	class SalesRecord extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SalesReturn
 *
 * @property int $id
 * @property int $com_code
 * @property string $return_number
 * @property \Illuminate\Support\Carbon $date
 * @property int|null $customer_id
 * @property int|null $invoice_id
 * @property int|null $branch_id
 * @property int|null $warehouse_id
 * @property string|null $reason
 * @property string $subtotal
 * @property string $tax_amount
 * @property string $total
 * @property string $status
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branche|null $branch
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read \App\Models\Customer|null $customer
 * @property-read string $status_label
 * @property-read \App\Models\SalesInvoice|null $invoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesReturnItem> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn query()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn whereReturnNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturn whereWarehouseId($value)
 */
	class SalesReturn extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SalesReturnItem
 *
 * @property int $id
 * @property int $return_id
 * @property int|null $item_id
 * @property string|null $description
 * @property int|null $unit_id
 * @property string $quantity
 * @property string $unit_price
 * @property string $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item|null $item
 * @property-read \App\Models\SalesReturn $salesReturn
 * @property-read \App\Models\ItemUnit|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturnItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturnItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturnItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturnItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturnItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturnItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturnItem whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturnItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturnItem whereReturnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturnItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturnItem whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturnItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalesReturnItem whereUpdatedAt($value)
 */
	class SalesReturnItem extends \Eloquent {}
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
 * App\Models\StockAdjustment
 *
 * @property int $id
 * @property int $com_code
 * @property string $adjustment_number
 * @property \Illuminate\Support\Carbon $date
 * @property int $warehouse_id
 * @property string $type
 * @property string|null $reason
 * @property string $status
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read string $status_label
 * @property-read string $type_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StockAdjustmentItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustment query()
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustment whereAdjustmentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustment whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustment whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustment whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustment whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustment whereWarehouseId($value)
 */
	class StockAdjustment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\StockAdjustmentItem
 *
 * @property int $id
 * @property int $adjustment_id
 * @property int $item_id
 * @property string $quantity
 * @property string|null $unit_cost
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\StockAdjustment $adjustment
 * @property-read \App\Models\Item|null $item
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustmentItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustmentItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustmentItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustmentItem whereAdjustmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustmentItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustmentItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustmentItem whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustmentItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustmentItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustmentItem whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockAdjustmentItem whereUpdatedAt($value)
 */
	class StockAdjustmentItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\StockBalance
 *
 * @property int $id
 * @property int $com_code
 * @property int $warehouse_id
 * @property int $item_id
 * @property float $quantity
 * @property float $avg_cost
 * @property float $total_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item|null $item
 * @property-read \App\Models\Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder|StockBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockBalance query()
 * @method static \Illuminate\Database\Eloquent\Builder|StockBalance whereAvgCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockBalance whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockBalance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockBalance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockBalance whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockBalance whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockBalance whereTotalValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockBalance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockBalance whereWarehouseId($value)
 */
	class StockBalance extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\StockMovement
 *
 * @property int $id
 * @property int $com_code
 * @property int $warehouse_id
 * @property int $item_id
 * @property string $movement_type
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property string $quantity
 * @property string|null $unit_cost
 * @property string|null $total_cost
 * @property string $balance_after
 * @property \Illuminate\Support\Carbon $date
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read float $signed_quantity
 * @property-read string $type_label
 * @property-read \App\Models\Item|null $item
 * @property-read \App\Models\Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement query()
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereBalanceAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereMovementType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereWarehouseId($value)
 */
	class StockMovement extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\StockTransfer
 *
 * @property int $id
 * @property int $com_code
 * @property string $transfer_number
 * @property \Illuminate\Support\Carbon $date
 * @property int $from_warehouse_id
 * @property int $to_warehouse_id
 * @property string $status
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read \App\Models\Warehouse|null $fromWarehouse
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StockTransferItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Warehouse|null $toWarehouse
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransfer query()
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransfer whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransfer whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransfer whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransfer whereFromWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransfer whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransfer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransfer whereToWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransfer whereTransferNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransfer whereUpdatedAt($value)
 */
	class StockTransfer extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\StockTransferItem
 *
 * @property int $id
 * @property int $transfer_id
 * @property int $item_id
 * @property string $quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item|null $item
 * @property-read \App\Models\StockTransfer $transfer
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransferItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransferItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransferItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransferItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransferItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransferItem whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransferItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransferItem whereTransferId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockTransferItem whereUpdatedAt($value)
 */
	class StockTransferItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Supplier
 *
 * @property int $id
 * @property int $com_code
 * @property string|null $code
 * @property string $name
 * @property string|null $name_en
 * @property string $type
 * @property string|null $phone
 * @property string|null $phone2
 * @property string|null $email
 * @property string|null $address
 * @property string|null $city
 * @property string|null $governorate
 * @property string|null $tax_number
 * @property string|null $commercial_register
 * @property int $payment_terms
 * @property string $opening_balance
 * @property string|null $notes
 * @property int $is_active
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read float $total_debt
 * @property-read string $type_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseInvoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseOrder> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchasePayment> $payments
 * @property-read int|null $payments_count
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier query()
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereCommercialRegister($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereGovernorate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereOpeningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier wherePhone2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereTaxNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Supplier whereUpdatedAt($value)
 */
	class Supplier extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\TreasuryVoucher
 *
 * @property int $id
 * @property int $com_code
 * @property string $voucher_number
 * @property string $voucher_type
 * @property \Illuminate\Support\Carbon $date
 * @property string $payment_method
 * @property int|null $cash_box_id
 * @property int|null $bank_account_id
 * @property string $party_type
 * @property int|null $party_id
 * @property float $amount
 * @property int|null $gl_account_id
 * @property string|null $linked_type
 * @property int|null $linked_id
 * @property int|null $cheque_id
 * @property string|null $reference_number
 * @property string|null $notes
 * @property string $status
 * @property int|null $created_by
 * @property int|null $posted_by
 * @property string|null $posted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BankAccount|null $bankAccount
 * @property-read \App\Models\CashBox|null $cashBox
 * @property-read \App\Models\Cheque|null $cheque
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read string|null $party_name
 * @property-read string $status_label
 * @property-read string $type_label
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher query()
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereBankAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereCashBoxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereChequeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereGlAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereLinkedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereLinkedType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher wherePartyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher wherePartyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher wherePostedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher wherePostedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereReferenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereVoucherNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreasuryVoucher whereVoucherType($value)
 */
	class TreasuryVoucher extends \Eloquent {}
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

namespace App\Models{
/**
 * App\Models\Warehouse
 *
 * @property int $id
 * @property int $com_code
 * @property string|null $code
 * @property string $name
 * @property int|null $branch_id
 * @property string|null $location
 * @property int $is_default
 * @property int $is_active
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StockBalance> $balances
 * @property-read int|null $balances_count
 * @property-read \App\Models\Branche|null $branch
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse query()
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereComCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Warehouse whereUpdatedAt($value)
 */
	class Warehouse extends \Eloquent {}
}

