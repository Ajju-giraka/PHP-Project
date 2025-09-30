<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Task name is required',
            'project_id.required' => 'Please select a project',
        ];
    }
}