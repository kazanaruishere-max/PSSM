<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuizRequest;
use App\Models\Classes;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\Subject;
use App\Services\AIService;
use App\Services\QuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class QuizController extends Controller
{
    public function __construct(
        private AIService $aiService,
        private QuizService $quizService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            $quizzes = Quiz::with(['teacher', 'class_', 'subject'])->latest()->paginate(15);
        } elseif ($user->hasRole('teacher')) {
            $quizzes = Quiz::where('teacher_id', $user->id)
                ->with(['class_', 'subject'])
                ->latest()
                ->paginate(15);
        } else {
            $classIds = $user->classes()->pluck('classes.id');
            $quizzes = Quiz::whereIn('class_id', $classIds)
                ->published()
                ->with(['teacher', 'class_', 'subject'])
                ->latest()
                ->paginate(15);
        }

        return view('quizzes.index', compact('quizzes'));
    }

    public function create(Request $request)
    {
        if ($request->user()->cannot('quizzes.create')) {
            abort(403);
        }

        $classes = Classes::all();
        $subjects = Subject::all();

        return view('quizzes.create', compact('classes', 'subjects'));
    }

    public function store(StoreQuizRequest $request)
    {
        $data = $request->validated();
        $isAIGenerated = $request->has('is_ai_generated') && $request->is_ai_generated;

        DB::beginTransaction();
        try {
            $quiz = Quiz::create([
                'class_id' => $data['class_id'],
                'subject_id' => $data['subject_id'],
                'teacher_id' => $request->user()->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'duration_minutes' => $data['duration_minutes'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'max_attempts' => $data['max_attempts'],
                'max_score' => $data['max_score'],
                'is_published' => $request->has('is_published'),
                'is_ai_generated' => $isAIGenerated,
            ]);

            if ($isAIGenerated) {
                // Call AI to generate questions immediately
                $aiQuestions = $this->aiService->generateQuiz(
                    $data['ai_topic'],
                    $data['ai_question_count'],
                    $data['ai_difficulty']
                );

                $order = 1;
                foreach ($aiQuestions as $q) {
                    QuizQuestion::create([
                        'quiz_id' => $quiz->id,
                        'question_text' => $q['question'],
                        'question_type' => 'multiple_choice',
                        'options' => $q['options'],
                        'correct_answer_hash' => Hash::make($q['correct_answer']), // HASHED
                        'explanation' => $q['explanation'] ?? null,
                        'points' => 10,
                        'order_number' => $order++
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('quizzes.show', $quiz)->with('success', 'Kuis berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['ai_generation' => $e->getMessage()])->withInput();
        }
    }

    public function show(Request $request, Quiz $quiz)
    {
        $user = $request->user();

        // Security check
        if ($user->hasRole('student') || $user->hasRole('class_leader')) {
            if (!$user->classes()->where('classes.id', $quiz->class_id)->exists()) {
                abort(403);
            }
            
            $attempts = $quiz->attempts()->where('student_id', $user->id)->orderBy('attempt_number', 'desc')->get();
            return view('quizzes.student_show', compact('quiz', 'attempts'));
        }

        // Teacher / Admin view
        $quiz->load(['questions' => function($q) { $q->orderBy('order_number', 'asc'); }]);
        $attempts = $quiz->attempts()->with('student')->latest()->get();

        return view('quizzes.teacher_show', compact('quiz', 'attempts'));
    }

    public function destroy(Request $request, Quiz $quiz)
    {
        if ($request->user()->cannot('quizzes.delete') && $quiz->teacher_id !== $request->user()->id && !$request->user()->hasRole('super_admin')) {
            abort(403);
        }

        $quiz->delete();

        return redirect()->route('quizzes.index')
            ->with('success', 'Kuis berhasil dihapus.');
    }

    /**
     * Start taking a quiz (Student only)
     */
    public function take(Request $request, Quiz $quiz)
    {
        if ($request->user()->cannot('quizzes.take')) abort(403);

        try {
            $attempt = $this->quizService->startAttempt($quiz, $request->user());
            
            // Redirect to active quiz interface
            return redirect()->route('quizzes.active', ['quiz' => $quiz, 'attempt' => $attempt]);
        } catch (ValidationException $e) {
            return redirect()->route('quizzes.show', $quiz)->withErrors($e->errors());
        }
    }

    /**
     * Interface for active quiz. Realtime timer.
     */
    public function active(Request $request, Quiz $quiz, QuizAttempt $attempt)
    {
        if ($attempt->student_id !== $request->user()->id || $attempt->submitted_at) {
            abort(403, 'Akses ditolak atau kuis sudah selesai.');
        }

        $questions = $quiz->questions()->orderBy('order_number', 'asc')->get();

        // Calculate time left safely
        $elapsed = $attempt->started_at->diffInSeconds(now());
        $totalSeconds = $quiz->duration_minutes * 60;
        $timeLeft = max(0, $totalSeconds - $elapsed);

        return view('quizzes.active', compact('quiz', 'attempt', 'questions', 'timeLeft'));
    }

    /**
     * Process submission
     */
    public function submit(Request $request, Quiz $quiz, QuizAttempt $attempt)
    {
        if ($attempt->student_id !== $request->user()->id) {
            abort(403);
        }

        // e.g. ["question_id" => "A", "question2_id" => "C"]
        $answers = $request->input('answers', []);

        try {
            $this->quizService->submitAttempt($attempt, $answers);
            return redirect()->route('quizzes.show', $quiz)
                ->with('success', "Kuis selesai! Nilai Anda: {$attempt->score}");
        } catch (ValidationException $e) {
            return redirect()->route('quizzes.show', $quiz)->withErrors($e->errors());
        }
    }
}
