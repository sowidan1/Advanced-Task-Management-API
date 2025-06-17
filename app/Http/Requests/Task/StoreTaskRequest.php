<?php

namespace App\Http\Requests\Task;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date'    => ['required', 'date', 'after:now'],
            'priority'    => ['required', Rule::in(Task::getPriorities())],
            'status'      => ['required', Rule::in(Task::getStatuses())],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'     => 'The task title is required.',
            'due_date.required'  => 'The due date is required.',
            'due_date.after'     => 'The due date must be a future date.',
            'priority.required'  => 'The task priority is required.',
            'priority.in'        => 'The selected priority is invalid.',
            'status.required'    => 'The task status is required.',
            'status.in'          => 'The selected status is invalid.',
        ];
    }
}
