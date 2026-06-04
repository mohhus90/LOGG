<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrgTemplate;

class OrgTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        OrgTemplate::truncate();

        $templates = [
            // 1. شركة تجارية بيع بالتجزئة
            [
                'template_name' => 'شركة تجارية - بيع بالتجزئة',
                'company_type'  => 'retail',
                'is_default'    => true,
                'levels_data'   => [
                    ['index'=>0, 'parent_index'=>null, 'level_order'=>1, 'name'=>'المدير العام',           'name_en'=>'General Manager',     'level_type'=>'top_management',    'is_management'=>true,  'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>true,  'description'=>'رأس الهرم الإداري'],
                    ['index'=>1, 'parent_index'=>0,    'level_order'=>2, 'name'=>'مدير المبيعات',          'name_en'=>'Sales Manager',       'level_type'=>'middle_management', 'is_management'=>true,  'is_sales_role'=>true,  'receives_seller_commission'=>false, 'receives_manager_commission'=>true,  'description'=>'مسؤول عن فريق المبيعات'],
                    ['index'=>2, 'parent_index'=>0,    'level_order'=>2, 'name'=>'مدير المخزن',            'name_en'=>'Warehouse Manager',   'level_type'=>'middle_management', 'is_management'=>true,  'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>'مسؤول عن المخزون'],
                    ['index'=>3, 'parent_index'=>1,    'level_order'=>3, 'name'=>'مشرف المبيعات',          'name_en'=>'Sales Supervisor',    'level_type'=>'supervisor',        'is_management'=>true,  'is_sales_role'=>true,  'receives_seller_commission'=>false, 'receives_manager_commission'=>true,  'description'=>'يشرف على مجموعة من البائعين'],
                    ['index'=>4, 'parent_index'=>3,    'level_order'=>4, 'name'=>'بائع / مندوب مبيعات',   'name_en'=>'Sales Representative','level_type'=>'sales',             'is_management'=>false, 'is_sales_role'=>true,  'receives_seller_commission'=>true,  'receives_manager_commission'=>false, 'description'=>'يقوم بعمليات البيع المباشر'],
                    ['index'=>5, 'parent_index'=>2,    'level_order'=>3, 'name'=>'أمين المخزن',            'name_en'=>'Storekeeper',         'level_type'=>'operational',       'is_management'=>false, 'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>'يدير حركة المخزون'],
                    ['index'=>6, 'parent_index'=>0,    'level_order'=>2, 'name'=>'محاسب',                  'name_en'=>'Accountant',          'level_type'=>'support',           'is_management'=>false, 'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>'مسؤول عن الحسابات'],
                ],
            ],
            // 2. شركة خدمية
            [
                'template_name' => 'شركة خدمية',
                'company_type'  => 'services',
                'is_default'    => false,
                'levels_data'   => [
                    ['index'=>0, 'parent_index'=>null, 'level_order'=>1, 'name'=>'الرئيس التنفيذي',        'name_en'=>'CEO',                 'level_type'=>'top_management',    'is_management'=>true,  'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>true,  'description'=>''],
                    ['index'=>1, 'parent_index'=>0,    'level_order'=>2, 'name'=>'مدير العمليات',          'name_en'=>'Operations Manager',  'level_type'=>'middle_management', 'is_management'=>true,  'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>true,  'description'=>''],
                    ['index'=>2, 'parent_index'=>0,    'level_order'=>2, 'name'=>'مدير تطوير الأعمال',     'name_en'=>'Business Dev Manager','level_type'=>'middle_management', 'is_management'=>true,  'is_sales_role'=>true,  'receives_seller_commission'=>false, 'receives_manager_commission'=>true,  'description'=>''],
                    ['index'=>3, 'parent_index'=>1,    'level_order'=>3, 'name'=>'مشرف الفريق',            'name_en'=>'Team Leader',         'level_type'=>'supervisor',        'is_management'=>true,  'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>4, 'parent_index'=>2,    'level_order'=>3, 'name'=>'مندوب مبيعات خدمات',    'name_en'=>'Services Sales Rep',  'level_type'=>'sales',             'is_management'=>false, 'is_sales_role'=>true,  'receives_seller_commission'=>true,  'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>5, 'parent_index'=>3,    'level_order'=>4, 'name'=>'موظف خدمة عملاء',        'name_en'=>'Customer Service',    'level_type'=>'operational',       'is_management'=>false, 'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>6, 'parent_index'=>3,    'level_order'=>4, 'name'=>'فني / متخصص',            'name_en'=>'Technician',          'level_type'=>'operational',       'is_management'=>false, 'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                ],
            ],
            // 3. شركة صناعية / تصنيع
            [
                'template_name' => 'شركة صناعية / تصنيع',
                'company_type'  => 'manufacturing',
                'is_default'    => false,
                'levels_data'   => [
                    ['index'=>0, 'parent_index'=>null, 'level_order'=>1, 'name'=>'المدير العام',           'name_en'=>'General Manager',     'level_type'=>'top_management',    'is_management'=>true,  'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>true,  'description'=>''],
                    ['index'=>1, 'parent_index'=>0,    'level_order'=>2, 'name'=>'مدير الإنتاج',           'name_en'=>'Production Manager',  'level_type'=>'middle_management', 'is_management'=>true,  'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>2, 'parent_index'=>0,    'level_order'=>2, 'name'=>'مدير المبيعات',          'name_en'=>'Sales Manager',       'level_type'=>'middle_management', 'is_management'=>true,  'is_sales_role'=>true,  'receives_seller_commission'=>false, 'receives_manager_commission'=>true,  'description'=>''],
                    ['index'=>3, 'parent_index'=>0,    'level_order'=>2, 'name'=>'مدير الجودة',            'name_en'=>'Quality Manager',     'level_type'=>'middle_management', 'is_management'=>true,  'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>4, 'parent_index'=>1,    'level_order'=>3, 'name'=>'مشرف إنتاج',             'name_en'=>'Production Supervisor','level_type'=>'supervisor',       'is_management'=>true,  'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>5, 'parent_index'=>2,    'level_order'=>3, 'name'=>'مندوب مبيعات',           'name_en'=>'Sales Representative','level_type'=>'sales',             'is_management'=>false, 'is_sales_role'=>true,  'receives_seller_commission'=>true,  'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>6, 'parent_index'=>4,    'level_order'=>4, 'name'=>'عامل إنتاج',             'name_en'=>'Production Worker',   'level_type'=>'operational',       'is_management'=>false, 'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>7, 'parent_index'=>3,    'level_order'=>3, 'name'=>'مفتش جودة',              'name_en'=>'Quality Inspector',   'level_type'=>'operational',       'is_management'=>false, 'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                ],
            ],
            // 4. شركة مقاولات
            [
                'template_name' => 'شركة مقاولات',
                'company_type'  => 'contracting',
                'is_default'    => false,
                'levels_data'   => [
                    ['index'=>0, 'parent_index'=>null, 'level_order'=>1, 'name'=>'المدير العام',           'name_en'=>'General Manager',     'level_type'=>'top_management',    'is_management'=>true,  'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>true,  'description'=>''],
                    ['index'=>1, 'parent_index'=>0,    'level_order'=>2, 'name'=>'مدير المشاريع',          'name_en'=>'Projects Manager',    'level_type'=>'middle_management', 'is_management'=>true,  'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>2, 'parent_index'=>0,    'level_order'=>2, 'name'=>'مدير التسويق والمبيعات', 'name_en'=>'Marketing Manager',   'level_type'=>'middle_management', 'is_management'=>true,  'is_sales_role'=>true,  'receives_seller_commission'=>false, 'receives_manager_commission'=>true,  'description'=>''],
                    ['index'=>3, 'parent_index'=>1,    'level_order'=>3, 'name'=>'مهندس موقع',             'name_en'=>'Site Engineer',       'level_type'=>'supervisor',        'is_management'=>true,  'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>4, 'parent_index'=>2,    'level_order'=>3, 'name'=>'مندوب تسويق',            'name_en'=>'Marketing Rep',       'level_type'=>'sales',             'is_management'=>false, 'is_sales_role'=>true,  'receives_seller_commission'=>true,  'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>5, 'parent_index'=>3,    'level_order'=>4, 'name'=>'رئيس عمال',              'name_en'=>'Foreman',             'level_type'=>'supervisor',        'is_management'=>false, 'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>6, 'parent_index'=>5,    'level_order'=>5, 'name'=>'عامل / فني',             'name_en'=>'Worker / Technician', 'level_type'=>'operational',       'is_management'=>false, 'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                ],
            ],
            // 5. مستشفى / مركز طبي
            [
                'template_name' => 'مستشفى / مركز طبي',
                'company_type'  => 'medical',
                'is_default'    => false,
                'levels_data'   => [
                    ['index'=>0, 'parent_index'=>null, 'level_order'=>1, 'name'=>'المدير الطبي',           'name_en'=>'Medical Director',    'level_type'=>'top_management',    'is_management'=>true,  'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>1, 'parent_index'=>0,    'level_order'=>2, 'name'=>'رئيس الأقسام الطبية',   'name_en'=>'Head of Medical Dept','level_type'=>'middle_management', 'is_management'=>true,  'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>2, 'parent_index'=>0,    'level_order'=>2, 'name'=>'المدير الإداري',         'name_en'=>'Admin Manager',       'level_type'=>'middle_management', 'is_management'=>true,  'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>3, 'parent_index'=>1,    'level_order'=>3, 'name'=>'طبيب استشاري',           'name_en'=>'Consultant Doctor',   'level_type'=>'operational',       'is_management'=>false, 'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>4, 'parent_index'=>1,    'level_order'=>3, 'name'=>'ممرض / ممرضة',           'name_en'=>'Nurse',               'level_type'=>'operational',       'is_management'=>false, 'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>5, 'parent_index'=>2,    'level_order'=>3, 'name'=>'موظف استقبال',           'name_en'=>'Receptionist',        'level_type'=>'support',           'is_management'=>false, 'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                    ['index'=>6, 'parent_index'=>2,    'level_order'=>3, 'name'=>'محاسب',                  'name_en'=>'Accountant',          'level_type'=>'support',           'is_management'=>false, 'is_sales_role'=>false, 'receives_seller_commission'=>false, 'receives_manager_commission'=>false, 'description'=>''],
                ],
            ],
        ];

        foreach ($templates as $t) {
            OrgTemplate::create($t);
        }
    }
}
