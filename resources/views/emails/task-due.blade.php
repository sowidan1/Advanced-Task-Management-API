<x-mail::message>
# Task Due Tomorrow

Hello!

This is a friendly reminder that your task "**{{ $task->title }}**" is due tomorrow.

**Task Details:**
- **Title:** {{ $task->title }}
- **Description:** {{ $task->description ?? 'No description provided' }}
- **Due Date:** {{ $task->due_date->format('F j, Y \a\t g:i A') }}
- **Priority:** {{ ucfirst($task->priority) }}
- **Status:** {{ ucfirst(str_replace('_', ' ', $task->status)) }}

Please make sure to complete this task on time to avoid it becoming overdue.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
