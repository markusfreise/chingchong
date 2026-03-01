<?php

namespace App\Http\Requests\TimeEntry;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'uuid', 'exists:projects,id'],
            'task_id' => ['nullable', 'uuid', 'exists:tasks,id'],
            'description' => ['nullable', 'string'],
            'started_at' => ['required', 'date'],
            'stopped_at' => ['nullable', 'date', 'after:started_at'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'is_billable' => ['sometimes', 'boolean'],
            'tag_ids' => ['sometimes', 'array'],
            'tag_ids.*' => ['uuid', 'exists:tags,id'],
        ];
    }
}
