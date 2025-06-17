<?php

namespace App\Services;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class TaskService
{
    public function createTask(array $data): Task
    {
        $data['user_id'] = Auth::id();

        $task = Task::create($data);

        Cache::forget('tasks:'.Auth::id());

        return $task;
    }

    public function updateTaskStatus(Task $task, string $status): Task
    {
        if ($status === Task::STATUS_COMPLETED && ! $task->canBeCompleted()) {
            throw ValidationException::withMessages([
                'status' => ['Task must be in progress before it can be completed.'],
            ]);
        }

        $task->update(['status' => $status]);

        Cache::forget('tasks:'.$task->user_id);

        return $task->refresh();
    }

    public function updateTask(Task $task, array $data): Task
    {
        if (isset($data['status']) && $data['status'] !== $task->status) {
            return $this->updateTaskStatus($task, $data['status']);
        }
        $task->update($data);

        Cache::forget('tasks:'.$task->user_id);

        return $task->refresh();
    }

    public function getTasks(Request $request): CursorPaginator
    {
        $userId = Auth::id();
        $cacheKey = 'tasks:'.$userId.':'.md5(json_encode($request->all()));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($request) {
            $query = QueryBuilder::for(Task::class)
                ->where('user_id', Auth::id())
                ->allowedFilters([
                    'status',
                    'priority',
                    AllowedFilter::callback('due_date_from', function ($query, $value) {
                        $query->where('due_date', '>=', $value);
                    }),
                    AllowedFilter::callback('due_date_to', function ($query, $value) {
                        $query->where('due_date', '<=', $value);
                    }),
                    AllowedFilter::callback('search', function ($query, $value) {
                        $query->where(function ($q) use ($value) {
                            $q->where('title', 'like', "%{$value}%")
                                ->orWhere('description', 'like', "%{$value}%");
                        });
                    }),
                ])
                ->allowedSorts([
                    'due_date',
                    'created_at',
                    AllowedSort::callback('priority', function ($query, $descending) {
                        $direction = $descending ? 'desc' : 'asc';
                        $priorities = Task::getPriorityOrder();
                        $case = collect($priorities)
                            ->map(fn ($order, $priority) => "WHEN '{$priority}' THEN {$order}")
                            ->implode(' ');
                        $query->orderByRaw("CASE priority {$case} END {$direction}");
                    }),
                ])
                ->defaultSort('-created_at');

            return $query->cursorPaginate($request->input('per_page', 15));
        });
    }

    public function searchTasks(string $query): Collection
    {
        $userId = Auth::id();
        $cacheKey = 'tasks:search:'.$userId.':'.md5($query);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($query) {
            return Task::search($query)->get();
        });
    }

    public function updateOverdueTasks(): int
    {
        $overdueTasks = Task::overdue()->get();

        foreach ($overdueTasks as $task) {
            $task->updateOverdueStatus();
        }

        return $overdueTasks->count();
    }

    public function deleteTask(Task $task): bool
    {
        $result = $task->delete();

        Cache::forget('tasks:'.$task->user_id);

        return $result;
    }

    public function getTasksNeedingNotification(): Collection
    {
        return Task::needsNotification()->get();
    }

    public function markNotificationSent(Task $task): void
    {
        $task->update(['notification_sent_at' => Carbon::now()]);
    }
}
