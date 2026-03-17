<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('quizzes.create');
    }

    public function rules(): array
    {
        return [
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'max_score' => ['required', 'integer', 'min:10', 'max:1000'],
            'start_time' => ['required', 'date', 'before:end_time'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:5'],
            
            // AI Generation fields
            'is_ai_generated' => ['boolean'],
            'ai_topic' => ['required_if:is_ai_generated,1', 'nullable', 'string', 'max:500'],
            'ai_question_count' => ['required_if:is_ai_generated,1', 'nullable', 'integer', 'min:5', 'max:20'],
            'ai_difficulty' => ['required_if:is_ai_generated,1', 'nullable', 'string', 'in:easy,medium,hard'],
        ];
    }
}
