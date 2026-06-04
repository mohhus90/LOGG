<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrgLevel;
use App\Models\OrgTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrgLevelsController extends Controller
{
    public function index()
    {
        $comCode = auth()->guard('admin')->user()->com_code;
        $levels = OrgLevel::where('com_code', $comCode)
            ->orderBy('level_order')
            ->get();

        // بناء هيكل شجري
        $tree = $this->buildTree($levels);

        return view('admin.org_levels.index', compact('levels', 'tree'));
    }

    public function create()
    {
        $comCode = auth()->guard('admin')->user()->com_code;
        $parents = OrgLevel::where('com_code', $comCode)->orderBy('level_order')->get();
        $templates = OrgTemplate::orderBy('company_type')->get();
        return view('admin.org_levels.create', compact('parents', 'templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'level_order' => 'required|integer|min:1',
            'level_type'  => 'required|in:top_management,middle_management,supervisor,sales,operational,support,other',
        ], [
            'name.required'        => 'يجب إدخال اسم المستوى',
            'level_order.required' => 'يجب إدخال الترتيب الهرمي',
            'level_type.required'  => 'يجب اختيار نوع المستوى',
        ]);

        DB::beginTransaction();
        try {
            $admin = auth()->guard('admin')->user();
            OrgLevel::create([
                'name'                       => $request->name,
                'name_en'                    => $request->name_en,
                'level_order'                => $request->level_order,
                'parent_id'                  => $request->parent_id ?: null,
                'level_type'                 => $request->level_type,
                'is_management'              => $request->boolean('is_management'),
                'is_sales_role'              => $request->boolean('is_sales_role'),
                'receives_seller_commission' => $request->boolean('receives_seller_commission'),
                'receives_manager_commission'=> $request->boolean('receives_manager_commission'),
                'description'                => $request->description,
                'com_code'                   => $admin->com_code,
                'added_by'                   => $admin->id,
            ]);
            DB::commit();
            return redirect()->route('org_levels.index')->with('success', 'تم إضافة المستوى الوظيفي بنجاح');
        } catch (\Exception $ex) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ: ' . $ex->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $comCode = auth()->guard('admin')->user()->com_code;
        $data = OrgLevel::where('id', $id)->where('com_code', $comCode)->firstOrFail();
        $parents = OrgLevel::where('com_code', $comCode)
            ->where('id', '!=', $id)
            ->orderBy('level_order')->get();
        return view('admin.org_levels.edit', compact('data', 'parents'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'level_order' => 'required|integer|min:1',
            'level_type'  => 'required|in:top_management,middle_management,supervisor,sales,operational,support,other',
        ], [
            'name.required'        => 'يجب إدخال اسم المستوى',
            'level_order.required' => 'يجب إدخال الترتيب الهرمي',
        ]);

        DB::beginTransaction();
        try {
            $comCode = auth()->guard('admin')->user()->com_code;
            $level = OrgLevel::where('id', $id)->where('com_code', $comCode)->firstOrFail();
            $level->update([
                'name'                       => $request->name,
                'name_en'                    => $request->name_en,
                'level_order'                => $request->level_order,
                'parent_id'                  => $request->parent_id ?: null,
                'level_type'                 => $request->level_type,
                'is_management'              => $request->boolean('is_management'),
                'is_sales_role'              => $request->boolean('is_sales_role'),
                'receives_seller_commission' => $request->boolean('receives_seller_commission'),
                'receives_manager_commission'=> $request->boolean('receives_manager_commission'),
                'description'                => $request->description,
                'updated_by'                 => auth()->guard('admin')->user()->id,
            ]);
            DB::commit();
            return redirect()->route('org_levels.index')->with('success', 'تم تحديث المستوى الوظيفي بنجاح');
        } catch (\Exception $ex) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ: ' . $ex->getMessage())->withInput();
        }
    }

    public function delete($id)
    {
        try {
            $comCode = auth()->guard('admin')->user()->com_code;
            $level = OrgLevel::where('id', $id)->where('com_code', $comCode)->firstOrFail();

            // التحقق من وجود وظائف مرتبطة
            if ($level->jobs()->count() > 0) {
                return redirect()->back()->with('error', 'لا يمكن الحذف: يوجد وظائف مرتبطة بهذا المستوى');
            }
            $level->delete();
            return redirect()->route('org_levels.index')->with('success', 'تم حذف المستوى الوظيفي بنجاح');
        } catch (\Exception $ex) {
            return redirect()->back()->with('error', 'حدث خطأ: ' . $ex->getMessage());
        }
    }

    // تحميل نموذج جاهز وإنشاء الهيكل تلقائياً
    public function loadTemplate(Request $request)
    {
        $request->validate(['template_id' => 'required|exists:org_templates,id']);
        $template = OrgTemplate::findOrFail($request->template_id);
        $admin = auth()->guard('admin')->user();
        $comCode = $admin->com_code;

        DB::beginTransaction();
        try {
            // حذف الهيكل القديم إن وُجد
            OrgLevel::where('com_code', $comCode)->delete();

            $idMap = []; // لتتبع parent_id
            foreach ($template->levels_data as $item) {
                $parentId = isset($item['parent_index']) ? ($idMap[$item['parent_index']] ?? null) : null;
                $level = OrgLevel::create([
                    'name'                       => $item['name'],
                    'name_en'                    => $item['name_en'] ?? null,
                    'level_order'                => $item['level_order'],
                    'parent_id'                  => $parentId,
                    'level_type'                 => $item['level_type'],
                    'is_management'              => $item['is_management'] ?? false,
                    'is_sales_role'              => $item['is_sales_role'] ?? false,
                    'receives_seller_commission' => $item['receives_seller_commission'] ?? false,
                    'receives_manager_commission'=> $item['receives_manager_commission'] ?? false,
                    'description'                => $item['description'] ?? null,
                    'com_code'                   => $comCode,
                    'added_by'                   => $admin->id,
                ]);
                $idMap[$item['index']] = $level->id;
            }
            DB::commit();
            return redirect()->route('org_levels.index')->with('success', 'تم تحميل النموذج "' . $template->template_name . '" بنجاح');
        } catch (\Exception $ex) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ: ' . $ex->getMessage());
        }
    }

    public function templates()
    {
        $templates = OrgTemplate::orderBy('company_type')->get();
        return view('admin.org_levels.templates', compact('templates'));
    }

    private function buildTree($levels, $parentId = null): array
    {
        $tree = [];
        foreach ($levels as $level) {
            if ($level->parent_id == $parentId) {
                $level->children_tree = $this->buildTree($levels, $level->id);
                $tree[] = $level;
            }
        }
        return $tree;
    }
}
