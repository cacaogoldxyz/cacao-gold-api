<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use Request;

class TaskController extends Controller
{
     public function index(Request $request)
    {
        $perPage = $request->per_page ??10;
        $page = $request->page ??1;
        return TaskResource::collection(Task::select('id', 'name', 'is_completed')->paginate($perPage, $page));
    }

    public function store(StoreTaskRequest $request)
    {
        $task = $request->user()->tasks()->create($request->validate([
            'name' => 'required|string',
            'is_completed' => 'boolean',
        ]));
    
        return TaskResource::make($task);
    }

    public function show(Task $task)
    {
        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }
        return TaskResource::make($task);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        if (!$task->exists) {
            return response()->json(['error' => 'Task not found'], 404);
        }
        $task->update($request->validated());

        return TaskResource::make($task);
    }

    public function destroy(Task $task)
    {
        if(!$task->exists) {
            return response()->json(['error'=> 'Task not found'],404);
        } 
        $task->delete(); 

        return response()->noContent();
    }

    public function __invoke(Request $request, Task $task)

    {
        $task->is_completed = $request->is_completed;
        
        $task->save();

        return TaskResource::make($task);

    }
}