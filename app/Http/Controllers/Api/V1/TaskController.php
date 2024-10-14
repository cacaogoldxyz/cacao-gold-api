<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Exceptions\InvalidQueryException;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);  
        $tasks = Task::select('id', 'name', 'status', 'task')->paginate($perPage);

        if ($tasks->isEmpty()) {
            return AppResponse::error('No tasks found.', 404);
        }

        return TaskResource::collection($tasks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'status' => 'required|boolean',
            'task' => 'required',
        ]);

        $tasks = Task::create($validated);
        return AppResponse::success($tasks, 'Tasks created successfully.', 201);
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'status' => 'required|boolean',
            'task' => 'required|string',
        ]);
    
        $task->update($validated);
    
        return AppResponse::success($task, 'Task updated successfully.', 200);
    }

    public function show(Task $task)
    {
        if (!$task) {
            return AppResponse::error('Task not found.', 404);
        }
        return TaskResource::make($task);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        // TODO: Change into $status variable
        // TODO: What if no data is entered in the first condition. How can I still show everything if completed and incomplete 
        $status = $request->input('status') && $request->input('status') == 'completed' ? 1 : 
                  ($request->input('status') && $request->input('status') == 'incomplete' ? 0 : null);
    
        if (is_null($query) && is_null($status)) {
            return AppResponse::error('Please provide a search term or completion status.', 403);
        }
    
        $tasks = Task::query()
            ->when($query, function ($queryBuilder) use ($query) {
                return $queryBuilder->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('task', 'LIKE', "%{$query}%");
            })
            ->when(!is_null($status), function ($queryBuilder) use ($status) {
                return $queryBuilder->where('status', $status);
            })
            ->paginate(10);
    
        if ($tasks->isEmpty()) {
            return AppResponse::error('No tasks found matching the criteria.', 404);
        }
    
        return TaskResource::success($tasks, 'Search results retrieved successfully.', statusCode: 200);
    }    

    public function restore($id)
    {
        $task = Task::withTrashed()->find($id);

        if (!$task || !$task->trashed()) {
            return AppResponse::error('Task not found or not deleted.', 404);
        }

        $task->restore();
        return AppResponse::success(message: 'Task restored successfully!', statusCode: 404);
    }

    public function trashed(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $trashTasks = Task::onlyTrashed()->paginate($perPage);

        if ($trashTasks->isEmpty()) {
            return AppResponse::error('No trashed tasks found.', 404);
        }

        return AppResponse::success($trashTasks, 'Trash tasks retrieved successfully.', 200);
    }

    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return AppResponse::error('Task not found.', 404);
        }

        $task->delete();

        return AppResponse::success(message: 'Task deleted successfully.', statusCode: 404);
    }
}
