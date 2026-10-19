<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'priority', 'assigned_user_id', 'search']);

        $tasks = Task::query()
            ->with(['assignedUser', 'company', 'contact', 'deal', 'lead'])
            ->filter($filters)
            ->orderByRaw("CASE WHEN status = 'pending' THEN 1 WHEN status = 'in_progress' THEN 2 ELSE 3 END")
            ->orderBy('due_date', 'asc')
            ->paginate(20)
            ->withQueryString();

        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('tasks.index', [
            'tasks' => $tasks,
            'filters' => $filters,
            'users' => $users,
        ]);
    }

    public function store(TaskRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['assigned_user_id'])) {
            $data['assigned_user_id'] = $request->user()->id;
        }

        Task::create($data);

        return back()->with('success', 'Task scheduled successfully.');
    }

    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $data = $request->validated();
        if ($data['status'] === 'completed' && $task->status !== 'completed') {
            $data['completed_at'] = now();
        } elseif ($data['status'] !== 'completed') {
            $data['completed_at'] = null;
        }

        $task->update($data);

        return back()->with('success', "Task '{$task->title}' updated successfully.");
    }

    public function toggle(Task $task): RedirectResponse
    {
        $this->authorize('toggle', $task);

        if ($task->status === 'completed') {
            $task->update([
                'status' => 'pending',
                'completed_at' => null,
            ]);
            $msg = "Task '{$task->title}' reopened.";
        } else {
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            $msg = "Task '{$task->title}' marked as completed.";
        }

        return back()->with('success', $msg);
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return back()->with('success', 'Task deleted.');
    }
}
