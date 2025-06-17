<?php

namespace App\Mail;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskDueNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Task Due Tomorrow: '.$this->task->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.task-due',
            with: [
                'task' => $this->task,
            ],
        );
    }
}
