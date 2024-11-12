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
        $statusInput = $request->input('status');

        $status = $statusInput === 'completed' ? 1 : ($statusInput === 'incomplete' ? 0 : null);

        if (is_null($query) && is_null($statusInput)) {
            return AppResponse::error('Please provide a search term or completion status.', 400);
        }
    
        $tasks = Task::where('user_id', Auth::id())
        ->when($query, function ($q) use ($query) {
            $q->where(function ($subQuery) use ($query) {
                $subQuery->where('name', 'LIKE', "%{$query}%")
                         ->orWhere('task', 'LIKE', "%{$query}%")
                         ->orWhere('status', $query === 'completed' ? 1 : ($query === 'incomplete' ? 0 : null));
            });
            })
            ->when(!is_null($status), function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->paginate(10);
    
        // return AppResponse::success($tasks, 'Tasks retrieved successfully.');
        return $tasks->isEmpty()
            ? AppResponse::success([], 'No tasks found matching the criteria.', 200)
            : TaskResource::collection($tasks);
    }


    public function restore($id)
    {
        $task = Task::withTrashed()->find($id);

        if (!$task) {
            return AppResponse::error('Task not found.', 404);
        }

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
        $query = $request->input('query');
        $statusInput = $request->input('status');
        // $perPage = $request->input('per_page', 10); 

        $status = $statusInput === 'completed' ? 1 : ($statusInput === 'incomplete' ? 0 : null);
    
        $trashedTasks = Task::onlyTrashed()
            ->where('user_id', Auth::id())
            ->when($query, function ($q) use ($query) {
                $q->where(function ($q) use ($query) {
                 $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('task', 'LIKE', "%{$query}%")
                    ->orWhere('status', $query === 'completed' ? 1 : ($query === 'incomplete' ? 0 : null));
            });
        })
            ->when(!is_null($status), function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->paginate(5);
    
        if ($trashedTasks->isEmpty()) {
            return AppResponse::error('No trashed tasks found.', 404);
        }
        
        return AppResponse::success(data: $trashedTasks, message: 'Trashed tasks retrieved successfully.');
    }

    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return AppResponse::error('Task not found.', 404);
        }

        if ($task->user_id !== Auth::id()) {
            return AppResponse::error('Unauthorized to delete this task.', 403);
        }

        $task->delete();

        return AppResponse::success(message: 'Task deleted successfully.', statusCode: 200);
    }

    public function forceDelete($id)
    {
        $task = Task::withTrashed()->find($id);

        if (!$task) {
            return AppResponse::error('Task not found.', 404);
        }
    
        if ($task->user_id !== Auth::id()) {
            return AppResponse::error('Unauthorized to delete this task.', 403);
        }
    
        $task->forceDelete();
    
        return AppResponse::success(message: 'Task permanently deleted successfully!', statusCode: 200);
    }
}
