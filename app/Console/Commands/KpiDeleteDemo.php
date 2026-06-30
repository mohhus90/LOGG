<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class KpiDeleteDemo extends Command
{
    protected $signature   = 'kpi:delete-demo {--com=1 : كود الشركة}';
    protected $description  = 'حذف جميع بيانات KPI التجريبية (الأكواد التي تبدأ بـ DEMO_)';

    public function handle(): int
    {
        $comCode = (int) $this->option('com');

        $demoIds = DB::table('kpi_definitions')
            ->where('com_code', $comCode)
            ->where('code', 'like', 'DEMO_%')
            ->pluck('id');

        if ($demoIds->isEmpty()) {
            $this->warn("لا توجد بيانات تجريبية للشركة com_code={$comCode}");
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Code', 'Name'],
            DB::table('kpi_definitions')->whereIn('id', $demoIds)->get(['id', 'code', 'name'])->toArray()
        );

        if (!$this->confirm("هل تريد حذف هذه المؤشرات وجميع قراءاتها؟", false)) {
            $this->line('تم الإلغاء.');
            return self::SUCCESS;
        }

        $scores = DB::table('kpi_employee_scores')->whereIn('kpi_id', $demoIds)->count();
        DB::table('kpi_employee_scores')->whereIn('kpi_id', $demoIds)->delete();
        DB::table('kpi_definitions')->whereIn('id', $demoIds)->delete();

        $this->info("✅ تم حذف {$demoIds->count()} مؤشر و {$scores} قراءة بنجاح.");
        return self::SUCCESS;
    }
}
