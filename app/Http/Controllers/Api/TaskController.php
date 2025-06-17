<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\GetTasksRequest;
use App\Http\Requests\Task\SearchTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    private TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(GetTasksRequest $request): AnonymousResourceCollection
    {
        return TaskResource::collection($this->taskService->getTasks($request));
    }

    public function store(StoreTaskRequest $request): TaskResource
    {
        try {
            $task = $this->taskService->createTask($request->validated());
            return new TaskResource($task);
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    public function show(Task $task): TaskResource
    {
        $this->authorizeTask($task);
        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        try {
            $this->authorizeTask($task);
            $updatedTask = $this->taskService->updateTask($task, $request->validated());
            return new TaskResource($updatedTask);
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorizeTask($task);
        $this->taskService->deleteTask($task);
        return response()->json(null, 204);
    }

    public function search(SearchTaskRequest $request): AnonymousResourceCollection
    {
        $tasks = $this->taskService->searchTasks($request->validated()['query']);
        return TaskResource::collection($tasks);
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): TaskResource
    {
        try {
            $updatedTask = $this->taskService->updateTaskStatus($task, $request->validated()['status']);
            return new TaskResource($updatedTask);
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    protected function authorizeTask(Task $task): void
    {
        if ($task->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to view this task.');
        }
    }
}
