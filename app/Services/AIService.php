<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIServiceException extends Exception {}

class AIService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey  = env('OPENROUTER_API_KEY', '');
        $this->baseUrl = 'https://openrouter.ai/api/v1';
        $this->model   = env('OPENROUTER_MODEL', 'anthropic/claude-3.5-sonnet');
    }

    /**
     * Generates a quiz securely via AI.
     */
    public function generateQuiz(string $topic, int $count, string $difficulty): array
    {
        // Sanitization (prevent prompt injection)
        $topic = $this->sanitize($topic);
        $count = max(5, min(20, $count)); // Limit questions to 5-20
        $difficulty = in_array(strtolower($difficulty), ['easy', 'medium', 'hard']) ? $difficulty : 'medium';

        $cacheKey = "ai_quiz_" . md5("{$topic}:{$count}:{$difficulty}:" . date('Y-m-d'));

        return Cache::remember($cacheKey, 3600, function () use ($topic, $count, $difficulty) {
            $prompt = $this->buildQuizPrompt($topic, $count, $difficulty);

            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(30)
                ->retry(2, 1000)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'HTTP-Referer'  => config('app.url'),
                ])
                ->post("{$this->baseUrl}/chat/completions", [
                    'model'    => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an expert Indonesian high school teacher. Return ONLY valid JSON.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens'  => 4000,
                    'temperature' => 0.7,
                ]);

            if ($response->failed()) {
                Log::error('AI API failed: ' . $response->body());
                throw new AIServiceException('Gagal generate kuis. Silakan coba lagi nanti.');
            }

            return $this->parseAndValidate($response->json('choices.0.message.content'));
        });
    }

    private function buildQuizPrompt(string $topic, int $count, string $difficulty): string
    {
        return "Tolong buatkan {$count} soal pilihan ganda bahasa Indonesia dengan tingkat kesulitan '{$difficulty}' untuk topik: {$topic}. 
        Formatkan respons-mu HANYA dalam JSON array tanpa ada teks tambahan atau penjelasan. Setiap object JSON harus memiliki format:
        {
            \"question\": \"Pertanyaan soal\",
            \"options\": {
                \"A\": \"Jawaban A\",
                \"B\": \"Jawaban B\",
                \"C\": \"Jawaban C\",
                \"D\": \"Jawaban D\"
            },
            \"correct_answer\": \"Huruf A/B/C/D yang benar\",
            \"explanation\": \"Penjelasan singkat mengapa jawaban tsbt benar\"
        }";
    }

    // === SECURITY HELPERS ===

    private function sanitize(string $input): string
    {
        $blacklist = [
            'ignore previous', 'forget your', 'you are now',
            'system prompt', 'act as', '<script', '<?php',
            'DROP TABLE', 'DELETE FROM', 'INSERT INTO',
        ];
        
        foreach ($blacklist as $term) {
            $input = str_ireplace($term, '', $input);
        }
        
        return strip_tags(mb_substr($input, 0, 5000));
    }

    private function parseAndValidate(string $content): array
    {
        // Strip out any markdown coding blocks if the AI wrapper included them
        $content = preg_replace('/```json\s*|\s*```/', '', $content);
        
        $data = json_decode(trim($content), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('AI Parsing Error:', ['content' => $content, 'error' => json_last_error_msg()]);
            throw new AIServiceException('Format respons AI tidak valid. API mungkin sedang overload.');
        }
        
        return $data;
    }
}
