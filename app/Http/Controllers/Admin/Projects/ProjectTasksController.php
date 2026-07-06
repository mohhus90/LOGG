<?php
namespace App\Http\Controllers\Admin\Projects;

use App\Http\Controllers\Controller;
use App\Models\{Project, ProjectTask};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectTasksController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function store(Request $request, $projectId)
    {
        $project = Project::where('com_code', $this->comCode())->findOrFail($projectId);
        $request->validate(['title' => 'required|string|max:200']);

        ProjectTask::create([
            'project_id'  => $project->id,
            'title'       => $request->title,
            'assigned_to' => $request->assigned_to ?: null,
            'due_date'    => $request->due_date,
            'priority'    => $request->priority ?? 'medium',
            'status'      => 'todo',
            'notes'       => $request->notes,
            'created_by'  => Auth::guard('admin')->id(),
        ]);

        return back()->with('success', 'تم إضافة المهمة بنجاح');
    }

    public function updateStatus(Request $request, $id)
    {
        $task = ProjectTask::whereHas('project', fn ($q) => $q->where('com_code', $this->comCode()))->findOrFail($id);
        $request->validate(['status' => 'required|in:todo,in_progress,done']);
        $task->update(['status' => $request->status]);
        return back()->with('success', 'تم تحديث حالة المهمة');
    }

    public function delete($id)
    {
        $task = ProjectTask::whereHas('project', fn ($q) => $q->where('com_code', $this->comCode()))->findOrFail($id);
        $task->delete();
        return back()->with('success', 'تم حذف المهمة');
    }
}
