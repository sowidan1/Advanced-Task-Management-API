<?php

namespace App\Jobs;

use App\Mail\TaskDueNotification;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTaskNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public Task $task;

    public string $recipientEmail;

    public function __construct(Task $task, string $recipientEmail = 'user@example.com')
    {
        $this->task = $task;
        $this->recipientEmail = $recipientEmail;
    }

    public function handle(TaskService $taskService): void
    {
        try {
            // Send the notification email
            Mail::to($this->recipientEmail)->send(new TaskDueNotification($this->task));

            // Mark notification as sent
            $taskService->markNotificationSent($this->task);

            Log::info('Task notification sent', [
                'task_id' => $this->task->id,
                'task_title' => $this->task->title,
                'recipient' => $this->recipientEmail,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send task notification', [
                'task_id' => $this->task->id,
                'error' => $e->getMessage(),
                'recipient' => $this->recipientEmail,
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Task notification job failed', [
            'task_id' => $this->task->id,
            'exception' => $exception->getMessage(),
            'recipient' => $this->recipientEmail,
        ]);
    }
}
