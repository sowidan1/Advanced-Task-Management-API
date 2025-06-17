<?php

namespace App\Console\Commands;

use App\Jobs\SendTaskNotification;
use App\Services\TaskService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckUpcomingTasks extends Command
{
    protected $signature = 'tasks:check-upcoming {--email=user@example.com : Email address to send notifications to}';

    protected $description = 'Check for upcoming tasks and send notifications';

    private TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        parent::__construct();
        $this->taskService = $taskService;
    }

    public function handle()
    {
        $this->info('Checking for upcoming tasks...');

        try {
            // Get tasks that need notification
            $tasksNeedingNotification = $this->taskService->getTasksNeedingNotification();

            if ($tasksNeedingNotification->isEmpty()) {
                $this->info('No tasks need notification at this time.');

                return 0;
            }

            $emailAddress = $this->option('email');
            $notificationsSent = 0;

            foreach ($tasksNeedingNotification as $task) {
                // Dispatch notification job
                SendTaskNotification::dispatch($task, $emailAddress);
                $notificationsSent++;

                $this->line("Queued notification for task: {$task->title}");
            }

            // Update overdue tasks
            $overdueCount = $this->taskService->updateOverdueTasks();

            $this->info("Successfully queued {$notificationsSent} task notifications.");

            if ($overdueCount > 0) {
                $this->warn("Marked {$overdueCount} tasks as overdue.");
            }

            Log::info('Upcoming tasks check completed', [
                'notifications_queued' => $notificationsSent,
                'overdue_tasks_updated' => $overdueCount,
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error('Error checking upcoming tasks: '.$e->getMessage());
            Log::error('Upcoming tasks check failed', ['error' => $e->getMessage()]);

            return 1;
        }
    }
}
