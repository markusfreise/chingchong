<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    public function index(Project $project): AnonymousResourceCollection
    {
        $tasks = $project->tasks()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = Task::create($request->validated());

        return response()->json([
            'data' => new TaskResource($task),
        ], 201);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $task->update($request->validated());

        return response()->json([
            'data' => new TaskResource($task),
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->update(['is_active' => false]);

        return response()->json(null, 204);
    }
}
