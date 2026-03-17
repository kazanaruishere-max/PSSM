<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubmissionService
{
    /**
     * Handle student assignment submission with versioning and security checks.
     *
     * @param Assignment $assignment
     * @param User $student
     * @param array $data
     * @return Submission
     * @throws Exception
     */
    public function submit(Assignment $assignment, User $student, array $data): Submission
    {
        // 1. Check if past deadline
        $isLate = now()->gt($assignment->deadline);

        // 2. Prevent re-submit if already graded
        $lastSubmission = Submission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->latest('version')
            ->first();

        if ($lastSubmission && $lastSubmission->isGraded()) {
            throw ValidationException::withMessages([
                'submission' => 'Tugas sudah dinilai. Tidak dapat mengirim ulang.',
            ]);
        }

        // 3. Determine new version number
        $version = $lastSubmission ? $lastSubmission->version + 1 : 1;

        // 4. Handle secure file upload
        $filePath = null;
        if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
            $filePath = $this->handleFileUpload($data['file'], $assignment);
        }

        if (empty($data['content']) && !$filePath) {
             throw ValidationException::withMessages([
                'submission' => 'Harus melampirkan file atau mengisi konten teks.',
            ]);
        }

        // 5. Create submission record
        return Submission::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $student->id,
            'content'       => $data['content'] ?? null,
            'file_path'     => $filePath,
            'is_late'       => $isLate,
            'version'       => $version,
        ]);
    }

    /**
     * Securely handle file uploads checking actual MIME types.
     */
    private function handleFileUpload(UploadedFile $file, Assignment $assignment): string
    {
        $realMime = mime_content_type($file->getPathname());
        
        $allowedMimes = [
            'application/pdf',
            'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg', 
            'image/png',
        ];

        if (!in_array($realMime, $allowedMimes)) {
            throw ValidationException::withMessages([
                'file' => "Tipe file tidak diizinkan untuk keamanan: {$realMime}",
            ]);
        }

        // Generate secure random filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Store privately
        return $file->storeAs(
            "submissions/{$assignment->id}",
            $filename,
            'private'
        );
    }
}
