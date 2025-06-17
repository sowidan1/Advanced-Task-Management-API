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
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Task API",
 *     description="Task management API"
 * )
 */

class TaskController extends Controller
{
    private TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     summary="Get all tasks for the authenticated user",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(GetTasksRequest $request): AnonymousResourceCollection
    {
        return TaskResource::collection($this->taskService->getTasks($request));
    }

    /**
     * @OA\Post(
     *     path="/api/tasks",
     *     summary="Create a new task",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *
     *     @OA\Response(response=201, description="Task created"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(StoreTaskRequest $request): TaskResource
    {
        try {
            $task = $this->taskService->createTask($request->validated());

            return new TaskResource($task);
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *     path="/api/tasks/{id}",
     *     summary="View a single task",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Task $task): TaskResource
    {
        $this->authorizeTask($task);

        return new TaskResource($task);
    }

    /**
     * @OA\Put(
     *     path="/api/tasks/{id}",
     *     summary="Update an existing task",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Task")),
     *
     *     @OA\Response(response=200, description="Task updated"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/tasks/{id}",
     *     summary="Delete a task",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=204, description="No content"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorizeTask($task);
        $this->taskService->deleteTask($task);

        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/tasks/search",
     *     summary="Search tasks",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="query", in="query", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="Search results"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function search(SearchTaskRequest $request): AnonymousResourceCollection
    {
        $tasks = $this->taskService->searchTasks($request->validated()['query']);

        return TaskResource::collection($tasks);
    }

    /**
     * @OA\Patch(
     *     path="/api/tasks/{id}/status",
     *     summary="Update status of a task",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"status"},
     *
     *             @OA\Property(property="status", type="string", example="completed")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Status updated"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
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
