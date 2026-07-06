<?php
namespace App\Http\Controllers\Admin\Projects;

use App\Http\Controllers\Controller;
use App\Models\{Project, Customer};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index(Request $request)
    {
        $query = Project::with('customer')->where('com_code', $this->comCode());
        if ($request->filled('status')) $query->where('status', $request->status);
        $data = $query->orderByDesc('id')->paginate(20);
        return view('admin.projects.projects.index', compact('data'));
    }

    public function create()
    {
        $customers = Customer::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        return view('admin.projects.projects.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:150']);

        $project = Project::create([
            'com_code'    => $this->comCode(),
            'name'        => $request->name,
            'customer_id' => $request->customer_id ?: null,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'budget'      => $request->budget ?? 0,
            'status'      => $request->status ?? 'planning',
            'notes'       => $request->notes,
            'created_by'  => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('projects.show', $project->id)->with('success', 'تم إنشاء المشروع بنجاح');
    }

    public function show($id)
    {
        $project   = Project::with(['customer', 'tasks.assignee'])->where('com_code', $this->comCode())->findOrFail($id);
        $employees = \App\Models\Employee::where('com_code', $this->comCode())->orderBy('employee_name_A')->get();
        $statuses  = \App\Models\ProjectTask::statusOptions();
        return view('admin.projects.projects.show', compact('project', 'employees', 'statuses'));
    }

    public function edit($id)
    {
        $project   = Project::where('com_code', $this->comCode())->findOrFail($id);
        $customers = Customer::where('com_code', $this->comCode())->where('is_active', true)->orderBy('name')->get();
        return view('admin.projects.projects.edit', compact('project', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::where('com_code', $this->comCode())->findOrFail($id);
        $request->validate(['name' => 'required|string|max:150', 'status' => 'required|in:planning,active,on_hold,completed,cancelled']);

        $project->update([
            'name'        => $request->name,
            'customer_id' => $request->customer_id ?: null,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'budget'      => $request->budget ?? 0,
            'status'      => $request->status,
            'notes'       => $request->notes,
        ]);

        return redirect()->route('projects.show', $id)->with('success', 'تم تعديل المشروع');
    }

    public function delete($id)
    {
        Project::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('projects.index')->with('success', 'تم حذف المشروع');
    }
}
