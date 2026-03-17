<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assignments.grade');
    }

    public function rules(): array
    {
        // Get the submission from the route to find dynamic max_score
        $submission = $this->route('submission');
        $maxScore = $submission ? $submission->assignment->max_score : 100;

        return [
            'score' => ['required', 'numeric', 'min:0', "max:{$maxScore}"],
            'feedback' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
