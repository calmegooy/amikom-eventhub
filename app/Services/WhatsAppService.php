<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message.
     */
    public static function send(string $phone, string $message): bool
    {
        $token = config('services.whatsapp.api_key');
        $url = config('services.whatsapp.api_url', 'https://api.fonnte.com/send');

        if (!$token) {
            Log::info("=== SIMULATED WHATSAPP MESSAGE ===");
            Log::info("To: {$phone}");
            Log::info("Message:\n{$message}");
            Log::info("==================================");
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post($url, [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62',
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp sent successfully to {$phone}");
                return true;
            }

            Log::error("Failed to send WhatsApp to {$phone}. Response: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("Error sending WhatsApp to {$phone}: " . $e->getMessage());
            return false;
        }
    }
}
