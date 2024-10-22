<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $tasks = Task::where('user_id', Auth::id())
            ->select('id', 'name', 'status', 'task', 'created_at', 'updated_at')
            ->paginate($perPage);

        return $tasks->isEmpty()
            ? AppResponse::error('No tasks found.', 200)
            : TaskResource::collection($tasks);
    }

    public function store(TaskRequest $request)
    {
        $validated = $request->getValidatedData();
        $task = Task::create($validated);

        return TaskResource::make($task);
    }

    public function update(TaskRequest $request, Task $task)
    {
        $validated = $request->getValidatedData();
        $task->update($validated);

        return TaskResource::make($task);
    }

    public function show(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return AppResponse::error('Unauthorized to view this task.', 403);
        }

        return TaskResource::make($task);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $status = $request->input('status');

        $status = $status === 'completed' ? 1 : ($status === 'incomplete' ? 0 : null);

        if (is_null($query) && is_null($status)) {
        return AppResponse::error('Please provide a search term or completion status.', 403);
        }


        $tasks = Task::where('user_id', Auth::id())
            ->when($query, fn($q) => $q->where('name', 'LIKE', "%{$query}%")
                                  ->orWhere('task', 'LIKE', "%{$query}%"))
            ->when(!is_null($status), fn($q) => $q->where('status', $status))
            ->paginate(10);

        return $tasks->isEmpty()
        ? AppResponse::success([], 'No tasks found matching the criteria.', 200)
        : TaskResource::collection($tasks);
    }


    public function restore($id)
    {
        // Find the trashed task
        $task = Task::withTrashed()->find($id);

        if (!$task) {
            return AppResponse::error('Task not found.', 404);
        }

        // Check if the task is trashed and if the user owns it
        if (!$task->trashed()) {
            return AppResponse::error('Task is not deleted.', 400);
        }

        if ($task->user_id !== Auth::id()) {
            return AppResponse::error('Unauthorized to restore this task.', 403);
        }

        $task->restore();

        return AppResponse::success(message: 'Task restored successfully!', statusCode: 200);
    }

    public function trashed(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        // Get only trashed tasks for the authenticated user
        $trashTasks = Task::onlyTrashed()->where('user_id', Auth::id())->paginate($perPage);

        return $trashTasks->isEmpty()
            ? response()->json(['message' => 'No trashed tasks found.', 'data' => []], 200)
            : AppResponse::success($trashTasks, 'Trash tasks retrieved successfully.', 200);
    }

    public function destroy($id)
    {
        // Find the task
        $task = Task::find($id);

        if (!$task) {
            return AppResponse::error('Task not found.', 404);
        }

        // Ensure the authenticated user owns the task
        if ($task->user_id !== Auth::id()) {
            return AppResponse::error('Unauthorized to delete this task.', 403);
        }

        $task->delete();

        return AppResponse::success(message: 'Task deleted successfully.', statusCode: 200);
    }
}
