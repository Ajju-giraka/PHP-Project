<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $selectedProjectId = $request->get('project_id');
        
        $projects = Project::with('tasks')->get();
        $tasks = collect();
        
        if ($selectedProjectId) {
            $tasks = Task::where('project_id', $selectedProjectId)
                ->orderBy('priority')
                ->get();
        }

        return view('tasks.index', compact('projects', 'tasks', 'selectedProjectId'));
    }

    public function create(): View
    {
        $projects = Project::all();
        return view('tasks.create', compact('projects'));
    }

    public function store(TaskRequest $request): RedirectResponse
    {
        $projectId = $request->project_id;
        
        $maxPriority = Task::where('project_id', $projectId)->max('priority') ?? 0;
        
        Task::create([
            'name' => $request->name,
            'priority' => $maxPriority + 1,
            'project_id' => $projectId,
        ]);

        return redirect()->route('tasks.index')
            ->with('success', 'Task created successfully.');
    }

    public function edit(Task $task): View
    {
        $projects = Project::all();
        return view('tasks.edit', compact('task', 'projects'));
    }

    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->validated());

        return redirect()->route('tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    public function reorder(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
        ]);

        $taskIds = $request->task_ids;
        
        foreach ($taskIds as $index => $taskId) {
            Task::where('id', $taskId)->update(['priority' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}