<?php

namespace App\Http\Requests\Task;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetTasksRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'status' => ['sometimes', Rule::in(Task::getStatuses())],
            'priority' => ['sometimes', Rule::in(Task::getPriorities())],
            'due_date_from' => ['sometimes', 'date'],
            'due_date_to' => ['sometimes', 'date', 'after_or_equal:due_date_from'],
            'search' => ['sometimes', 'string', 'min:1'],
            'sort' => ['sometimes', 'string'],
            'filter' => ['sometimes', 'array'],
        ];
    }
}
