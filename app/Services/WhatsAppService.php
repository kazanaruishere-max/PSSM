<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.fonnte.api_key', '');
        $this->baseUrl = config('services.fonnte.base_url', 'https://api.fonnte.com');
    }

    /**
     * Send a WhatsApp message via Fonnte.
     * 
     * @param string $target Phone number (e.g. 628123456789)
     * @param string $message Message content
     * @return bool
     */
    public function sendMessage(string $target, string $message): bool
    {
        if (empty($this->apiKey)) {
            Log::warning('WhatsAppService: Fonnte API Key is missing.');
            return false;
        }

        // Clean target number (remove +, etc)
        $target = preg_replace('/[^0-9]/', '', $target);

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])->post("{$this->baseUrl}/send", [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62', // Default to Indonesia
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('WhatsAppService Error: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('WhatsAppService Exception: ' . $e->getMessage());
            return false;
        }
    }
}
